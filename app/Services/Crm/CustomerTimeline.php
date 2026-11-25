<?php

namespace App\Services\Crm;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\EnquiryEnum;
use App\Enums\JobEnum;
use App\Enums\QuotationEnum;
use App\Models\Crm\CrmActivity;
use App\Models\Customer\Customer;
use App\Models\Quotation\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Customer 360: one dated list of everything that happened with a customer — CRM activities, enquiries, quotations
 * (and the customer's own answers), jobs, invoices and collections — plus a sales summary. Each kind is included only
 * when the user may view that section, so the tab never shows more than the rest of the app would.
 * Activities, enquiries and quotations from the customer's time as a prospect are included.
 */
class CustomerTimeline
{
    /** filter => [label, icon, colour] for the chips above the list */
    public const KINDS = [
        'activity' => ['Activities', 'bi-chat-dots', '#7c3aed'],
        'sales' => ['Enquiries & Quotations', 'bi-file-earmark-text', '#2563eb'],
        'job' => ['Jobs', 'bi-truck', '#0891b2'],
        'finance' => ['Invoices & Collections', 'bi-receipt', '#16a34a'],
    ];

    public function __construct(private Customer $customer)
    {
    }

    /** Ids of the prospects this customer was converted from (their history is the customer's history). */
    private function prospectIds(): array
    {
        return DB::table('prospects')->where('customer_id', $this->customer->id)->whereNull('deleted_at')->pluck('id')->all();
    }

    /** @return Collection<int, array> newest first, at most $limit items before $before */
    public function items(?Carbon $before = null, int $limit = 60): Collection
    {
        $items = collect()
            ->concat(userCan('sales.activities') ? $this->activities($before, $limit) : [])
            ->concat(userCan('sales.enquiries') ? $this->enquiries($before, $limit) : [])
            ->concat(userCan('sales.quotations') ? $this->quotations($before, $limit) : [])
            ->concat(userCan('operations.jobs') ? $this->jobs($before, $limit) : [])
            ->concat(userCan('finances.customer-invoices') ? $this->invoices($before, $limit) : [])
            ->concat(userCan('transactions.collections') ? $this->collections($before, $limit) : []);

        return $items->sortByDesc(fn($i) => $i['at']->format('YmdHis') . str_pad((string) $i['sort'], 10, '0', STR_PAD_LEFT))
            ->take($limit)->values();
    }

    /** Sales numbers for the strip above the timeline (the last 12 months). */
    public function summary(): array
    {
        $since = Carbon::today()->subYear();
        $party = fn($q) => $q->where(fn($w) => $w->where('customer_id', $this->customer->id)
            ->when($this->prospectIds(), fn($x, $ids) => $x->orWhereIn('prospect_id', $ids)));

        $out = ['canSales' => userCan('sales.quotations'), 'canJobs' => userCan('operations.jobs'), 'canActivities' => userCan('sales.activities')];
        if ($out['canSales']) {
            $open = Quotation::tap($party)->where('status', QuotationEnum::PENDING->value)->withSum('charges as total', 'local_amount')->get(['id']);
            $won = Quotation::tap($party)->whereIn('status', [QuotationEnum::ACCEPTED->value, QuotationEnum::CONVERTED->value])->where('updated_at', '>=', $since)->count();
            $lost = Quotation::tap($party)->whereIn('status', [QuotationEnum::CANCELLED->value, QuotationEnum::EXPIRED->value])->where('updated_at', '>=', $since)->count();
            $out += [
                'openQuotations' => $open->count(),
                'openValue' => round((float) $open->sum('total'), 2),
                'winRate' => ($won + $lost) ? (int) round($won * 100 / ($won + $lost)) : null,
            ];
        }
        if ($out['canJobs']) {
            $out['jobs12m'] = DB::table('jobs')->where('company_id', companyId())->where('customer_id', $this->customer->id)
                ->whereNull('deleted_at')->where('created_at', '>=', $since)->count();
        }
        if ($out['canActivities']) {
            $next = $this->activityQuery()->openFollowUps()->with('action:id,name,category')->orderBy('follow_up_at')->first(['follow_up_at', 'action_id', 'notes', 'assigned_to']);
            $out['nextFollowUp'] = $next?->follow_up_at;
            $out['nextFollowUpNote'] = $next ? trim($next->actionLabel() . ($next->notes ? ': ' . \Illuminate\Support\Str::limit($next->notes, 80) : '')) : null;
            $out['lastActivity'] = $this->activityQuery()->whereNull('follow_up_at')->max('activity_at');
        }

        return $out;
    }

    // ───────────────────────── sources ─────────────────────────

    /** Activities on the customer, on its prospect(s), and on its enquiries and quotations. */
    private function activityQuery()
    {
        $prospects = $this->prospectIds();
        $enquiries = DB::table('enquiries')->where('company_id', companyId())->whereNull('deleted_at')
            ->where(fn($w) => $w->where('customer_id', $this->customer->id)->when($prospects, fn($x) => $x->orWhereIn('prospect_id', $prospects)))->pluck('id')->all();
        $quotations = DB::table('quotations')->where('company_id', companyId())->whereNull('deleted_at')
            ->where(fn($w) => $w->where('customer_id', $this->customer->id)->when($prospects, fn($x) => $x->orWhereIn('prospect_id', $prospects)))->pluck('id')->all();

        return CrmActivity::where(function ($q) use ($prospects, $enquiries, $quotations) {
            $q->where(fn($w) => $w->where('subject_type', 'customer')->where('subject_id', $this->customer->id));
            foreach (['prospect' => $prospects, 'enquiry' => $enquiries, 'quotation' => $quotations] as $type => $ids) {
                if ($ids) {
                    $q->orWhere(fn($w) => $w->where('subject_type', $type)->whereIn('subject_id', $ids));
                }
            }
        });
    }

    private function activities(?Carbon $before, int $limit): array
    {
        $rows = $this->activityQuery()->with(['assignee:id,name', 'creator:id,name', 'action:id,name,category'])
            ->when($before, fn($q) => $q->where('activity_at', '<', $before))
            ->orderByDesc('activity_at')->limit($limit)->get();
        $names = CrmActivity::subjectNames($rows);

        return $rows->map(function ($a) use ($names) {
            $open = $a->follow_up_at && !$a->follow_up_done_at;
            $about = $a->subject_type !== 'customer' ? ($names[$a->subject_type][$a->subject_id]['no'] ?? null) : null;

            return $this->item('activity', $a->activity_at, $a->id,
                $a->actionLabel() . ($about ? ' · ' . $about : ''),
                $a->notes,
                ['icon' => \App\Models\Crm\CrmAction::icon($a->action->category ?? null), 'by' => $a->creator->name ?? __('System'),
                    'chip' => $open ? __('Due') . ' ' . $a->follow_up_at->format('d-m-Y') : null,
                    'chipTone' => $open && $a->follow_up_at->lt(CrmActivity::localNowNaive()->startOfDay()) ? 'danger' : 'primary',
                    'url' => $a->subject_type === 'customer' ? null : $this->openUrl($a->subject_type, $a->subject_id)]);
        })->all();
    }

    private function enquiries(?Carbon $before, int $limit): array
    {
        $prospects = $this->prospectIds();
        $rows = DB::table('enquiries')->where('company_id', companyId())->whereNull('deleted_at')
            ->where(fn($w) => $w->where('customer_id', $this->customer->id)->when($prospects, fn($x) => $x->orWhereIn('prospect_id', $prospects)))
            ->when($before, fn($q) => $q->where('created_at', '<', $before))
            ->orderByDesc('created_at')->limit($limit)->get(['id', 'row_no', 'status', 'pol', 'pod', 'created_at']);

        return $rows->map(fn($e) => $this->item('sales', Carbon::parse($e->created_at), $e->id,
            __('Enquiry') . ' ' . $e->row_no, $this->route($e->pol, $e->pod),
            ['icon' => 'bi-envelope-paper', 'status' => EnquiryEnum::tryFrom((int) $e->status)?->label(), 'url' => $this->openUrl('enquiry', $e->id)]))->all();
    }

    private function quotations(?Carbon $before, int $limit): array
    {
        $prospects = $this->prospectIds();
        $rows = Quotation::where(fn($w) => $w->where('customer_id', $this->customer->id)->when($prospects, fn($x) => $x->orWhereIn('prospect_id', $prospects)))
            ->withSum('charges as total', 'local_amount')
            ->when($before, fn($q) => $q->where('created_at', '<', $before))
            ->orderByDesc('created_at')->limit($limit)
            ->get(['id', 'row_no', 'status', 'pol', 'pod', 'created_at', 'customer_status', 'customer_response_at', 'customer_response_note', 'lost_reason_id', 'lost_note', 'revision_notes']);
        $reasons = DB::table('lost_reasons')->whereIn('id', $rows->pluck('lost_reason_id')->filter()->unique())->pluck('name', 'id');

        $out = [];
        foreach ($rows as $q) {
            $lost = $q->lost_reason_id ? trim(($reasons[$q->lost_reason_id] ?? '') . ($q->lost_note ? ' — ' . $q->lost_note : '')) : null;
            $out[] = $this->item('sales', Carbon::parse($q->created_at), $q->id,
                __('Quotation') . ' ' . $q->row_no,
                collect([$this->route($q->pol, $q->pod), $q->revision_notes ? __('Revised') . ': ' . $q->revision_notes : null, $lost ? __('Lost reason') . ': ' . $lost : null])->filter()->implode("\n"),
                ['icon' => 'bi-file-earmark-text', 'status' => QuotationEnum::tryFrom((int) $q->status)?->label(), 'amount' => (float) $q->total ?: null,
                    'url' => $this->openUrl('quotation', $q->id)]);
            // the customer's own answer from the email link is an event of its own
            if ((int) $q->customer_status !== Quotation::CUSTOMER_NO_ANSWER && $q->customer_response_at && (!$before || $q->customer_response_at->lt($before))) {
                $accepted = (int) $q->customer_status === Quotation::CUSTOMER_ACCEPTED;
                $out[] = $this->item('sales', $q->customer_response_at, $q->id,
                    ($accepted ? __('Customer accepted') : __('Customer rejected')) . ' · ' . $q->row_no, $q->customer_response_note,
                    ['icon' => $accepted ? 'bi-hand-thumbs-up' : 'bi-hand-thumbs-down', 'chip' => __('Online'), 'chipTone' => $accepted ? 'success' : 'danger',
                        'url' => $this->openUrl('quotation', $q->id)]);
            }
        }

        return $out;
    }

    private function jobs(?Carbon $before, int $limit): array
    {
        $rows = DB::table('jobs')->where('company_id', companyId())->where('customer_id', $this->customer->id)->whereNull('deleted_at')
            ->when($before, fn($q) => $q->where('created_at', '<', $before))
            ->orderByDesc('created_at')->limit($limit)->get(['id', 'row_no', 'status', 'pol', 'pod', 'created_at']);

        return $rows->map(fn($j) => $this->item('job', Carbon::parse($j->created_at), $j->id,
            __('Job') . ' ' . ($j->row_no ?: '#' . $j->id), $this->route($j->pol, $j->pod),
            ['icon' => 'bi-truck', 'status' => JobEnum::tryFrom((int) $j->status)?->label(), 'url' => url('/operation/jobs?open=' . $j->id)]))->all();
    }

    private function invoices(?Carbon $before, int $limit): array
    {
        $rows = DB::table('customer_invoices')->where('company_id', companyId())->where('customer_id', $this->customer->id)->whereNull('deleted_at')
            ->whereIn('status', [CustomerInvoiceEnum::APPROVED->value, CustomerInvoiceEnum::CANCELLED->value])
            ->when($before, fn($q) => $q->where('invoice_date', '<', $before->toDateString()))
            ->orderByDesc('invoice_date')->orderByDesc('id')->limit($limit)
            ->get(['id', 'row_no', 'status', 'invoice_date', 'grand_total', 'currency']);

        return $rows->map(fn($i) => $this->item('finance', Carbon::parse($i->invoice_date)->endOfDay(), $i->id,
            __('Invoice') . ' ' . $i->row_no, null,
            ['icon' => 'bi-receipt', 'status' => CustomerInvoiceEnum::tryFrom((int) $i->status)?->label(),
                'amount' => (float) $i->grand_total, 'currency' => strtoupper((string) $i->currency)]))->all();
    }

    private function collections(?Carbon $before, int $limit): array
    {
        $rows = DB::table('collections')->where('company_id', companyId())->where('customer_id', $this->customer->id)->whereNull('deleted_at')
            ->whereIn('status', [CollectionEnum::APPROVED->value])
            ->when($before, fn($q) => $q->where('collection_date', '<', $before->toDateString()))
            ->orderByDesc('collection_date')->orderByDesc('id')->limit($limit)
            ->get(['id', 'row_no', 'collection_no', 'collection_date', 'base_grand_total', 'base_amount']);

        return $rows->map(fn($c) => $this->item('finance', Carbon::parse($c->collection_date)->endOfDay(), $c->id,
            __('Collection') . ' ' . ($c->row_no ?: $c->collection_no), null,
            ['icon' => 'bi-cash-coin', 'amount' => (float) ($c->base_grand_total ?: $c->base_amount)]))->all();
    }

    // ───────────────────────── helpers ─────────────────────────

    private function item(string $kind, Carbon $at, int $sort, string $title, ?string $text, array $extra = []): array
    {
        return array_merge(['kind' => $kind, 'at' => $at, 'sort' => $sort, 'title' => $title, 'text' => $text,
            'icon' => 'bi-dot', 'status' => null, 'amount' => null, 'currency' => null, 'chip' => null, 'chipTone' => 'primary', 'by' => null, 'url' => null], $extra);
    }

    private function openUrl(string $type, int $id): ?string
    {
        return match ($type) {
            'enquiry' => url('/sales/enquiries?open=' . $id),
            'quotation' => url('/sales/quotations?open=' . $id),
            default => null,
        };
    }

    private function route($pol, $pod): ?string
    {
        $code = fn($v) => ($v === null || $v === '') ? null : trim(preg_split('/\s+-\s+/', (string) $v, 2)[0]);

        return ($code($pol) || $code($pod)) ? ($code($pol) ?? '—') . ' → ' . ($code($pod) ?? '—') : null;
    }
}
