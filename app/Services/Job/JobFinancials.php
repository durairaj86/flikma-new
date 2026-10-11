<?php

namespace App\Services\Job;

use App\Enums\CollectionEnum;
use App\Enums\CreditNoteEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\ExpenseEnum;
use App\Enums\PaymentEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\ProformaInvoice\ProformaInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use Illuminate\Support\Carbon;

/** Everything the books hold for one job: documents, money, attachments and a timeline. */
class JobFinancials
{
    private static function date($v): ?Carbon
    {
        return filled($v) ? Carbon::parse($v) : null;
    }

    /** Pretty name + drawer type per doc_type in job_links. */
    private const DOC = [
        'customer_invoice' => ['Customer Invoices', 'invoice', CustomerInvoiceEnum::class],
        'proforma' => ['Proforma Invoices', null, null],
        'credit_note' => ['Credit Notes', 'credit_note', CreditNoteEnum::class],
        'collection' => ['Collections', 'collection', CollectionEnum::class],
        'supplier_invoice' => ['Supplier Invoices', 'supplier_invoice', SupplierInvoiceEnum::class],
        'payment' => ['Payments', null, PaymentEnum::class],
        'expense' => ['Expenses', null, ExpenseEnum::class],
    ];

    public static function collect(Job $job): array
    {
        $groups = [];
        $docs = collect();
        $timeline = collect();

        // Everything that belongs to the job comes from the one common table (job_links).
        $links = \App\Models\Job\JobLink::withoutGlobalScopes()->where('job_id', $job->id)
            ->with(['linkable' => function ($morph) {
                $morph->morphWith([
                    CustomerInvoice::class => ['documents'], SupplierInvoice::class => ['documents'], CreditNote::class => ['documents'],
                    Expense::class => ['documents'], Collection::class => ['documents'], Payment::class => ['documents'],
                ]);
            }])->orderBy('id')->get()->groupBy('doc_type');

        foreach (self::DOC as $docType => [$label, $type, $enum]) {
            $rows = ($links[$docType] ?? collect())->map(function ($l) use ($docType, $label, $type, $enum, &$docs, &$timeline) {
                $m = $l->linkable;
                $statusLabel = $enum && $l->status !== null ? ($enum::tryFrom((int) $l->status)?->label() ?? '') : '';
                $isApproved = $l->state === 'approved';
                $cancelled = $l->state === 'cancelled';
                $files = ($m && method_exists($m, 'documents')) ? $m->documents : collect();
                foreach ($files as $d) {
                    $docs->push(['title' => $d->title ?: $d->file_name, 'file' => $d->file_name, 'path' => $d->file_path, 'date' => self::date($d->posted_date ?? $d->created_at), 'source' => __($label) . ' ' . $l->doc_no, 'id' => $d->id]);
                    $timeline->push(['at' => self::date($d->created_at ?? $d->posted_date), 'icon' => 'bi-paperclip', 'title' => __('Attachment added') . ' · ' . ($d->title ?: $d->file_name), 'text' => $l->doc_no, 'kind' => 'document']);
                }
                $created = self::date($m?->created_at ?? $l->created_at);
                $timeline->push(['at' => $created, 'icon' => 'bi-receipt', 'title' => __($label) . ' · ' . $l->doc_no, 'text' => trim(($l->party_name ?? '') . ' · ' . $statusLabel . ' · ' . number_format($l->amount, 2), ' ·'), 'kind' => $docType]);
                if ($isApproved && $m && $m->updated_at && $m->created_at && $m->updated_at->gt($m->created_at)) {
                    $timeline->push(['at' => $m->updated_at, 'icon' => 'bi-check-circle', 'title' => $l->doc_no . ' ' . __('approved'), 'text' => number_format($l->amount, 2), 'kind' => $docType]);
                }

                return [
                    'key' => $docType, 'type' => $type, 'id' => $l->linkable_id, 'no' => $l->doc_no, 'date' => $l->doc_date, 'party' => $l->party_name,
                    'status' => $statusLabel, 'approved' => $isApproved, 'cancelled' => $cancelled, 'currency' => $l->currency,
                    'amount' => (float) $l->amount, 'paid' => $l->paid, 'files' => $files->count(),
                ];
            });
            $groups[$docType] = ['label' => $label, 'rows' => $rows];
        }

        // Documents attached straight to the job (incl. the Documents center).
        foreach ($job->documents()->get() as $d) {
            $docs->push(['title' => $d->title ?: $d->file_name, 'file' => $d->file_name, 'path' => $d->file_path, 'date' => self::date($d->posted_date ?? $d->created_at), 'source' => __('Job'), 'id' => $d->id, 'expiry' => $d->expiry_date]);
            $timeline->push(['at' => self::date($d->created_at ?? $d->posted_date), 'icon' => 'bi-paperclip', 'title' => __('Document added') . ' · ' . ($d->title ?: $d->file_name), 'text' => $d->doc_type ? __($d->doc_type) : '', 'kind' => 'document']);
        }

        // Job life: creation, tracking steps, clearance, containers.
        $timeline->push(['at' => self::date($job->created_at), 'icon' => 'bi-briefcase', 'title' => __('Job created') . ' · ' . $job->row_no, 'text' => $job->customer?->name_en, 'kind' => 'job']);
        foreach (\App\Http\Controllers\Job\TrackingController::steps($job->loadMissing('clearance', 'milestones'), $job->milestones) as $s) {
            if ($s['actual']) {
                $timeline->push(['at' => $s['actual'], 'icon' => 'bi-geo-alt', 'title' => __($s['label']), 'text' => $s['location'], 'kind' => 'tracking']);
            }
        }
        foreach ([['bayan_date', 'Bayan (customs declaration)'], ['bl_receive_date', 'B/L received'], ['saber_certificate_date', 'SABER certificate']] as [$f, $l]) {
            if (self::date($job->$f)) {
                $timeline->push(['at' => self::date($job->$f), 'icon' => 'bi-file-earmark-check', 'title' => __($l), 'text' => $f === 'bayan_date' ? $job->bayan_no : '', 'kind' => 'customs']);
            }
        }

        // Money summary in base currency, cancelled documents ignored.
        $sum = fn($k, $onlyApproved = true) => collect($groups[$k]['rows'])->filter(fn($r) => !$r['cancelled'] && (!$onlyApproved || $r['approved']))->sum('amount');
        $invoiced = $sum('customer_invoice');
        $credits = $sum('credit_note');
        $collected = $sum('collection');
        $cost = $sum('supplier_invoice') + $sum('expense');
        $paidOut = $sum('payment');
        $summary = [
            'invoiced' => $invoiced, 'credits' => $credits, 'collected' => $collected, 'outstanding' => $invoiced - $credits - $collected,
            'cost' => $cost, 'paid_out' => $paidOut, 'payable' => max(0, $sum('supplier_invoice') - $paidOut),
            'profit' => $invoiced - $credits - $cost,
        ];

        return [
            'groups' => $groups, 'summary' => $summary,
            'docs' => $docs->sortByDesc(fn($d) => $d['date']?->timestamp ?? 0)->values(),
            'timeline' => $timeline->filter(fn($t) => $t['at'])->sortByDesc(fn($t) => $t['at']->timestamp)->values(),
        ];
    }
}
