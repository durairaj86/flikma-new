<?php

namespace App\Livewire\Report\Crm;

use App\Enums\CustomerInvoiceEnum;
use App\Enums\QuotationEnum;
use App\Models\Crm\CrmActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Per salesperson for the period: CRM work on their deals (activities, follow-ups done, follow-ups overdue now),
 * quotations made, deals won and their value, and invoiced revenue against the monthly target.
 * Activities count for the salesperson who owns the record they were logged on (quotation / enquiry / prospect / customer).
 * Revenue: approved customer invoices without VAT, credited to the job's salesperson, else the customer's
 * (the same rule as the Sales Commission report).
 */
class SalespersonPerformance extends CrmReport
{
    protected function title(): string { return __('Salesperson Performance'); }
    protected function subtitle(): string { return __('Sales activity, deals won and revenue against target'); }
    protected function groups(): array { return ['salesperson' => __('Salesperson')]; }

    protected function columns(): array
    {
        return [__('Activities'), __('Follow-ups done'), __('Overdue follow-ups'), __('Quotations'), __('Won'), __('Won value'),
            __('Invoiced'), __('Target'), __('Achieved %')];
    }

    protected function rateColumns(): array
    {
        return [9 => fn($t) => self::pct($t[7], $t[8])];
    }

    protected function emptyText(): string
    {
        return __('No salespeople yet. Add them in Master Data › Salesperson.');
    }

    /** Activities with the salesperson of the record they were logged on (joins, so the strict GROUP BY mode accepts it). */
    private function activities()
    {
        return $this->base('crm_activities', 'a')
            ->leftJoin('quotations as oq', fn($j) => $j->on('oq.id', '=', 'a.subject_id')->where('a.subject_type', 'quotation'))
            ->leftJoin('enquiries as oe', fn($j) => $j->on('oe.id', '=', 'a.subject_id')->where('a.subject_type', 'enquiry'))
            ->leftJoin('prospects as op', fn($j) => $j->on('op.id', '=', 'a.subject_id')->where('a.subject_type', 'prospect'))
            ->leftJoin('customers as oc', fn($j) => $j->on('oc.id', '=', 'a.subject_id')->where('a.subject_type', 'customer'));
    }

    private const OWNER = 'COALESCE(oq.salesperson_id, oe.salesperson_id, op.salesperson_id, oc.salesperson_id, 0)';

    protected function rows(): array
    {
        $owner = self::OWNER;
        [$col, $range] = $this->between('a.activity_at');
        // what people did: done actions they logged themselves (not planned ones, not the ones the system logged from documents)
        $activities = $this->grouped($this->activities()->whereNull('a.follow_up_at')->whereNull('a.auto_key')->whereBetween($col, $range), $owner, 'COUNT(*)');
        [$col, $range] = $this->between('a.follow_up_done_at');
        $done = $this->grouped($this->activities()->whereNotNull('a.follow_up_done_at')->whereBetween($col, $range), $owner, 'COUNT(*)');
        $overdue = $this->grouped($this->activities()->whereNotNull('a.follow_up_at')->whereNull('a.follow_up_done_at')
            ->where('a.follow_up_at', '<', CrmActivity::localNowNaive()->startOfDay()), $owner, 'COUNT(*)');

        [$col, $range] = $this->between('q.posted_at');
        $quotations = $this->grouped($this->base('quotations', 'q')->whereBetween($col, $range)->where('q.status', '!=', QuotationEnum::REVISED->value),
            'COALESCE(q.salesperson_id, 0)', 'COUNT(*)');
        [$col, $range] = $this->between('q.updated_at');
        $wonQuery = $this->base('quotations', 'q')->whereBetween($col, $range)
            ->whereIn('q.status', [QuotationEnum::ACCEPTED->value, QuotationEnum::CONVERTED->value])
            ->leftJoin(DB::raw($this->chargesSub() . ' qc'), 'qc.quotation_id', '=', 'q.id');
        $won = $this->grouped(clone $wonQuery, 'COALESCE(q.salesperson_id, 0)', 'COUNT(*)');
        $wonValue = $this->grouped(clone $wonQuery, 'COALESCE(q.salesperson_id, 0)', 'COALESCE(SUM(qc.amt), 0)');

        $invoiced = DB::table('customer_invoices as i')->where('i.company_id', companyId())->whereNull('i.deleted_at')
            ->where('i.status', CustomerInvoiceEnum::APPROVED->value)->whereBetween('i.invoice_date', [$this->startDate, $this->endDate])
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')->leftJoin('jobs as j', 'j.id', '=', 'i.job_id');
        $invoiced = $this->grouped($invoiced, 'COALESCE(j.salesperson_id, c.salesperson_id, 0)', 'SUM(i.base_sub_total)');

        // every salesperson is listed (an empty row is information too); work on records without one shows as "(no salesperson)"
        $people = DB::table('sales_persons')->where('company_id', companyId())->whereNull('deleted_at')->orderBy('name')
            ->get(['id', 'name', 'monthly_target']);
        $months = $this->months();
        $rows = [];
        foreach ($people as $p) {
            $rows[] = $this->row($p->id, $p->name, (float) $p->monthly_target * $months, $activities, $done, $overdue, $quotations, $won, $wonValue, $invoiced);
        }
        $unassigned = $this->row(0, __('(no salesperson)'), 0, $activities, $done, $overdue, $quotations, $won, $wonValue, $invoiced);
        if (array_sum(array_slice($unassigned, 1)) > 0) {
            $rows[] = $unassigned;
        }

        return $rows;
    }

    private function row($id, string $name, float $target, array ...$sources): array
    {
        [$activities, $done, $overdue, $quotations, $won, $wonValue, $invoiced] = $sources;
        $inv = round((float) ($invoiced[$id] ?? 0), 2);

        return [$name, (int) ($activities[$id] ?? 0), (int) ($done[$id] ?? 0), (int) ($overdue[$id] ?? 0), (int) ($quotations[$id] ?? 0),
            (int) ($won[$id] ?? 0), round((float) ($wonValue[$id] ?? 0), 2), $inv, round($target, 2), self::pct($inv, $target)];
    }

    /** Length of the period in months (a target is per month). */
    private function months(): float
    {
        $days = Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1;

        return round($days / (365.25 / 12), 2);
    }
}
