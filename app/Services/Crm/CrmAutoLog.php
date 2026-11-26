<?php

namespace App\Services\Crm;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\EnquiryEnum;
use App\Enums\JobEnum;
use App\Enums\QuotationEnum;
use App\Models\Crm\CrmAction;
use App\Models\Crm\CrmActivity;
use App\Models\Enquiry\Enquiry;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Job\Job;
use App\Models\Prospect\Prospect;
use App\Models\Quotation\Quotation;
use Illuminate\Database\Eloquent\Model;

/**
 * The CRM actions that already happen in the system are logged by the system, so a salesperson never types them twice:
 * an enquiry is "RFQ Received", a quotation "Quote Sent" (a revision "Re-Quoted"), an accepted / converted quotation "Confirmed",
 * a job "Job Running" then "Job Done", an approved invoice "Invoice Issued", an approved collection "Payment Received", and a
 * cancelled / lost deal "Lost / Cancelled". Each document event is logged once (auto_key), on the record it belongs to
 * (jobs, invoices and collections on their customer). A failure here never stops the document from saving.
 */
class CrmAutoLog
{
    /** Action codes the system logs by itself (marked "Auto" in the CRM Actions master). */
    public const CODES = ['rfq_received', 'quote_sent', 're_quoted', 'confirmed', 'job_running', 'job_done', 'invoice_issued', 'payment_received', 'lost'];

    /** Hook the document events (AppServiceProvider::boot). */
    public static function listen(): void
    {
        Enquiry::created(fn(Enquiry $e) => self::safe(fn() => self::log('enquiry', $e->id, $e, 'rfq_received', "enquiry:{$e->id}:rfq",
            __('Enquiry :no', ['no' => $e->row_no]))));
        Enquiry::updated(function (Enquiry $e) {
            if ($e->wasChanged('status') && (int) $e->status === EnquiryEnum::CANCELLED->value) {
                self::safe(fn() => self::log('enquiry', $e->id, $e, 'lost', "enquiry:{$e->id}:lost", self::lostNote(__('Enquiry :no cancelled', ['no' => $e->row_no]), $e)));
            }
        });

        Quotation::created(function (Quotation $q) {
            $revision = (bool) $q->parent_quotation_id;
            self::safe(fn() => self::log('quotation', $q->id, $q, $revision ? 're_quoted' : 'quote_sent', "quotation:{$q->id}:sent",
                __('Quotation :no', ['no' => $q->row_no])));
        });
        Quotation::updated(function (Quotation $q) {
            if (!$q->wasChanged('status')) {
                return;
            }
            $status = (int) $q->status;
            if (in_array($status, [QuotationEnum::ACCEPTED->value, QuotationEnum::CONVERTED->value], true)) {
                self::safe(fn() => self::log('quotation', $q->id, $q, 'confirmed', "quotation:{$q->id}:confirmed",
                    __('Quotation :no confirmed', ['no' => $q->row_no])));
            } elseif (in_array($status, [QuotationEnum::CANCELLED->value, QuotationEnum::EXPIRED->value], true)) {
                $what = $status === QuotationEnum::EXPIRED->value ? __('Quotation :no expired', ['no' => $q->row_no]) : __('Quotation :no cancelled', ['no' => $q->row_no]);
                self::safe(fn() => self::log('quotation', $q->id, $q, 'lost', "quotation:{$q->id}:lost", self::lostNote($what, $q)));
            }
        });

        Prospect::updated(function (Prospect $p) {
            if ($p->wasChanged('status') && $p->status === 'lost') {
                self::safe(fn() => self::log('prospect', $p->id, $p, 'lost', "prospect:{$p->id}:lost", self::lostNote(__('Marked as lost'), $p)));
            }
        });

        Job::created(fn(Job $j) => self::safe(fn() => self::logOnParty($j, 'job_running', "job:{$j->id}:running", __('Job :no', ['no' => $j->row_no]))));
        Job::updated(function (Job $j) {
            if ($j->wasChanged('status') && self::statusValue($j->status) === JobEnum::COMPLETED->value) {
                self::safe(fn() => self::logOnParty($j, 'job_done', "job:{$j->id}:done", __('Job :no completed', ['no' => $j->row_no])));
            }
        });

        CustomerInvoice::saved(function (CustomerInvoice $i) {
            if (self::statusValue($i->status) === CustomerInvoiceEnum::APPROVED->value && ($i->wasRecentlyCreated || $i->wasChanged('status'))) {
                self::safe(fn() => self::logOnParty($i, 'invoice_issued', "invoice:{$i->id}:issued",
                    __('Invoice :no', ['no' => $i->row_no]) . ' · ' . amountFormat($i->grand_total) . ' ' . $i->currency));
            }
        });
        Collection::saved(function (Collection $c) {
            if (self::statusValue($c->status) === CollectionEnum::APPROVED->value && ($c->wasRecentlyCreated || $c->wasChanged('status'))) {
                self::safe(fn() => self::logOnParty($c, 'payment_received', "collection:{$c->id}:received",
                    __('Collection :no', ['no' => $c->row_no]) . ' · ' . amountFormat($c->grand_total ?: $c->amount)));
            }
        });
    }

    /** A status may be set as an enum case or as its integer value: compare the value. */
    private static function statusValue($status): int
    {
        return $status instanceof \BackedEnum ? (int) $status->value : (int) $status;
    }

    /** Log the action on a job's / invoice's / collection's customer (or the job's prospect when there is no customer yet). */
    private static function logOnParty(Model $doc, string $code, string $key, string $note): void
    {
        if ($doc->customer_id) {
            self::log('customer', (int) $doc->customer_id, $doc, $code, $key, $note);
        } elseif (!empty($doc->prospect_id)) {
            self::log('prospect', (int) $doc->prospect_id, $doc, $code, $key, $note);
        }
    }

    private static function log(string $subjectType, ?int $subjectId, Model $doc, string $code, string $key, string $note): void
    {
        $companyId = (int) ($doc->company_id ?: companyId());
        if (!$subjectId || !$companyId) {
            return;
        }
        $action = CrmAction::byCode($code, $companyId);
        // switched off by the company, or this document event is already logged (also when that activity was deleted)
        if (!$action || CrmActivity::withoutGlobalScopes()->withTrashed()->where('company_id', $companyId)->where('auto_key', $key)->exists()) {
            return;
        }
        $a = new CrmActivity();
        $a->company_id = $companyId;
        $a->subject_type = $subjectType;
        $a->subject_id = $subjectId;
        $a->action_id = $action->id;
        $a->auto_key = $key;
        $a->notes = $note;
        $a->activity_at = CrmActivity::localNowNaive($companyId);
        $a->user_id = auth()->id();
        $a->save();
    }

    /** "Quotation X cancelled · Price too high". */
    private static function lostNote(string $what, Model $doc): string
    {
        $reason = $doc->lost_reason_id ? \App\Models\Crm\LostReason::withoutGlobalScopes()->whereKey($doc->lost_reason_id)->value('name') : null;

        return $reason ? $what . ' · ' . __($reason) : $what;
    }

    private static function safe(callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
