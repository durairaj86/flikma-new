<?php

namespace App\Services\Reports;

use App\Enums\CollectionEnum;
use App\Enums\CreditNoteEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\DebitNoteEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Models\Job\Job;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Sales commission (incentive) on gross profit, the way the Q2 incentive sheet works, with each salesperson's own scheme
 * (Masters > Salesperson):
 *
 *  - Gross profit of every approved customer invoice in the period = invoiced (ex VAT, less credit notes)
 *    minus the job's cost (approved supplier invoices less debit notes), the job cost shared over the job's invoices.
 *  - Each invoice's profit is paid by WHEN the customer paid (each collection counts for its share):
 *      collected on or before the due date        -> on-time rate
 *      collected late, within the salesperson's late limit (days from the invoice date) -> late rate
 *      collected after the late limit             -> 0 % (reversal of G/P value)
 *      not collected yet                          -> 0 % (reversal, "non collected customer")
 *  - Flat scheme: the salesperson's on-time / late rates. Slab scheme: the period's gross profit picks one slab, whose
 *    on-time / late rates apply to all of it.
 *  - Target = the salesperson's monthly target x months in the period.
 *
 * Amounts are in company currency. The salesperson is the job's salesperson, or else the customer's (sales_persons).
 */
class SalesCommission
{
    public const DEFAULT_LATE_DAYS = 120;

    public static function compute(string $from, string $to, ?int $salespersonId = null): array
    {
        $today = Carbon::today();

        $invoices = DB::table('customer_invoices as i')
            ->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('jobs as j', 'j.id', '=', 'i.job_id')
            ->whereNull('i.deleted_at')
            ->where('i.company_id', companyId())
            ->where('i.status', CustomerInvoiceEnum::APPROVED->value)
            ->whereBetween('i.invoice_date', [$from, $to])
            ->select('i.id', 'i.row_no', 'i.job_id', 'i.customer_id', 'i.invoice_date', 'i.due_at', 'i.base_sub_total', 'i.grand_total',
                'c.name_en as customer', 'j.row_no as job_no', DB::raw('COALESCE(j.salesperson_id, c.salesperson_id) as salesperson_id'))
            ->orderBy('i.invoice_date')->orderBy('i.id')
            ->get();
        if ($salespersonId) {
            $invoices = $invoices->where('salesperson_id', $salespersonId)->values();
        }

        $invoiceIds = $invoices->pluck('id');
        $jobIds = $invoices->pluck('job_id')->filter()->unique()->values();
        $people = self::schemes($invoices->pluck('salesperson_id')->filter()->unique()->all());

        $credits = DB::table('credit_notes')->whereNull('deleted_at')->where('status', CreditNoteEnum::APPROVED->value)
            ->whereIn('invoice_id', $invoiceIds)
            ->groupBy('invoice_id')->selectRaw('invoice_id, SUM(sub_total * COALESCE(NULLIF(currency_rate,0),1)) net, SUM(grand_total) gross')
            ->get()->keyBy('invoice_id');
        $costs = DB::table('supplier_invoices')->whereNull('deleted_at')->where('status', SupplierInvoiceEnum::APPROVED->value)
            ->whereIn('job_id', $jobIds)->groupBy('job_id')->selectRaw('job_id, SUM(base_sub_total) v')->pluck('v', 'job_id');
        $debits = DB::table('debit_notes')->whereNull('deleted_at')->where('status', DebitNoteEnum::APPROVED->value)
            ->whereIn('job_id', $jobIds)->groupBy('job_id')
            ->selectRaw('job_id, SUM(sub_total * COALESCE(NULLIF(currency_rate,0),1)) v')->pluck('v', 'job_id');
        // what every invoice of those jobs is worth (all periods), to share the job cost fairly
        $jobInvoiceNet = DB::table('customer_invoices as i')->whereNull('i.deleted_at')->where('i.status', CustomerInvoiceEnum::APPROVED->value)
            ->whereIn('i.job_id', $jobIds)
            ->leftJoin(DB::raw('(select invoice_id, SUM(sub_total * COALESCE(NULLIF(currency_rate,0),1)) cn from credit_notes where deleted_at is null and status = '
                . CreditNoteEnum::APPROVED->value . ' group by invoice_id) cr'), 'cr.invoice_id', '=', 'i.id')
            ->groupBy('i.job_id')->selectRaw('i.job_id, SUM(i.base_sub_total - COALESCE(cr.cn,0)) v')->pluck('v', 'job_id');
        $collections = DB::table('collection_invoices as ci')->join('collections as co', 'co.id', '=', 'ci.collection_id')
            ->whereNull('co.deleted_at')->where('co.status', CollectionEnum::APPROVED->value)
            ->whereIn('ci.customer_invoice_id', $invoiceIds)->where('ci.amount', '>', 0)
            ->orderBy('co.collection_date')->get(['ci.customer_invoice_id', 'ci.amount', 'co.collection_date'])
            ->groupBy('customer_invoice_id');
        $activities = $jobIds->isEmpty() ? collect()
            : Job::with('activity')->whereIn('id', $jobIds)->get()->mapWithKeys(fn ($j) => [$j->id => optional($j->activity)->name]);

        $rows = [];
        foreach ($invoices as $inv) {
            $person = $people[$inv->salesperson_id] ?? null;
            $lateDays = (int) ($person['late_days'] ?? self::DEFAULT_LATE_DAYS);
            $credit = $credits[$inv->id] ?? null;
            $invoiced = (float) $inv->base_sub_total - (float) ($credit->net ?? 0);
            $jobNet = (float) ($jobInvoiceNet[$inv->job_id] ?? 0);
            $jobCost = (float) ($costs[$inv->job_id] ?? 0) - (float) ($debits[$inv->job_id] ?? 0);
            $cost = $inv->job_id && $jobNet != 0 ? $jobCost * ($invoiced / $jobNet) : 0.0;
            $income = $invoiced - $cost;

            // the amount the customer has to pay after credit notes; each collection pays that share of the profit
            $payable = max(0.0, (float) $inv->grand_total - (float) ($credit->gross ?? 0));
            $due = $inv->due_at ? Carbon::parse($inv->due_at)->startOfDay() : Carbon::parse($inv->invoice_date);
            $issued = Carbon::parse($inv->invoice_date);
            $tiers = ['on_time' => 0.0, 'late' => 0.0, 'over' => 0.0, 'pending' => 0.0];
            $shareDone = 0.0;
            $lastDate = null;
            foreach ($collections[$inv->id] ?? [] as $col) {
                $share = $payable > 0 ? min(1 - $shareDone, (float) $col->amount / $payable) : 0;
                if ($share <= 0) break;
                $paidOn = Carbon::parse($col->collection_date);
                $tier = $paidOn->lte($due) ? 'on_time' : ($issued->diffInDays($paidOn) <= $lateDays ? 'late' : 'over');
                $tiers[$tier] += $income * $share;
                $shareDone += $share;
                $lastDate = $paidOn;
            }
            $tiers['pending'] = $income * max(0, 1 - $shareDone);
            $days = max(0, (int) $issued->diffInDays($today));

            $rows[] = [
                'invoice' => $inv->row_no, 'job' => $inv->job_no, 'activity' => $activities[$inv->job_id] ?? null,
                'customer' => $inv->customer, 'customer_id' => (int) $inv->customer_id, 'salesperson_id' => $person ? (int) $inv->salesperson_id : null,
                'salesperson' => $person['name'] ?? __('Not assigned'),
                'invoiced' => round($invoiced, 2), 'cost' => round($cost, 2), 'income' => round($income, 2),
                'profit_pct' => $invoiced != 0 ? round($income / $invoiced * 100, 1) : null,
                'invoice_date' => $issued->format('d-m-Y'), 'due_date' => $due->format('d-m-Y'), 'month' => $issued->format('Y-m'),
                'status' => $shareDone >= 0.9999 ? __('Paid') . ' ' . $lastDate->format('d-m-Y')
                    : ($shareDone > 0 ? __('Part paid') . ' · ' . __('pending for :n days', ['n' => $days]) : __('Pending for :n days', ['n' => $days])),
                'paid' => $shareDone >= 0.9999,
                'tiers' => array_map(fn ($v) => round($v, 2), $tiers),
            ];
        }

        // one calculation per salesperson with their own scheme; the totals add them up
        $byPerson = collect($rows)->groupBy(fn ($r) => (string) ($r['salesperson_id'] ?? '0'))->map(function ($g, $key) use ($people, $from, $to) {
            $person = $people[(int) $key] ?? null;
            $s = self::summarise($g->all(), $from, $to, $person);
            return ['id' => $person ? (int) $key : null, 'name' => $person['name'] ?? __('Not assigned'), 'scheme' => self::schemeLabel($person, $s)] + $s;
        })->sortByDesc('incentive')->values();

        // per-invoice commission at the rate of its salesperson
        $rateOf = $byPerson->keyBy(fn ($p) => (string) ($p['id'] ?? '0'));
        foreach ($rows as &$r) {
            $p = $rateOf[(string) ($r['salesperson_id'] ?? '0')] ?? null;
            $r['commission'] = round($r['tiers']['on_time'] * ($p['rate_on_time'] ?? 0) / 100 + $r['tiers']['late'] * ($p['rate_late'] ?? 0) / 100, 2);
        }
        unset($r);

        return [
            'summary' => self::total($byPerson, $rows, $from, $to),
            'people' => $byPerson->all(),
            'rows' => $rows,
            'single' => $salespersonId && $byPerson->count() <= 1 ? $byPerson->first() : null,
        ];
    }

    /** sales_persons rows with their scheme */
    private static function schemes(array $ids): array
    {
        if (!$ids) return [];
        return DB::table('sales_persons')->whereIn('id', $ids)
            ->get(['id', 'name', 'commission_type', 'commission_rate', 'late_rate', 'late_days', 'monthly_target', 'commission_slabs'])
            ->mapWithKeys(fn ($p) => [(int) $p->id => [
                'name' => $p->name, 'type' => $p->commission_type ?: 'flat',
                'rate' => (float) $p->commission_rate, 'late_rate' => (float) $p->late_rate,
                'late_days' => (int) ($p->late_days ?: self::DEFAULT_LATE_DAYS), 'monthly_target' => (float) $p->monthly_target,
                'slabs' => json_decode($p->commission_slabs ?? '[]', true) ?: [],
            ]])->all();
    }

    /** the months of the period, each with its gross profit */
    private static function months(iterable $rows, string $from, string $to): array
    {
        $months = [];
        for ($m = Carbon::parse($from)->startOfMonth(); $m->lte(Carbon::parse($to)); $m->addMonth()) {
            $months[$m->format('Y-m')] = ['label' => $m->format('F'), 'income' => 0.0];
        }
        foreach ($rows as $r) {
            if (isset($months[$r['month']])) $months[$r['month']]['income'] += $r['income'];
        }
        return $months;
    }

    private static function summarise(array $rows, string $from, string $to, ?array $person): array
    {
        $rows = collect($rows);
        $months = self::months($rows, $from, $to);
        $sum = fn ($k) => round($rows->sum(fn ($r) => $r['tiers'][$k]), 2);
        $income = round($rows->sum('income'), 2);

        // the rates: flat, or the slab that the period's gross profit falls in
        $rateOn = $rateLate = 0.0;
        $slab = null;
        if ($person) {
            if ($person['type'] === 'slab' && $person['slabs']) {
                foreach ($person['slabs'] as $i => $s) {
                    $upTo = ($s['to'] === null || $s['to'] === '') ? INF : (float) $s['to'];
                    if ($income >= (float) $s['from'] && $income < $upTo) { $slab = $i; break; }
                }
                if ($slab === null && $income >= (float) end($person['slabs'])['from']) $slab = count($person['slabs']) - 1;
                if ($slab !== null) { $rateOn = (float) $person['slabs'][$slab]['rate']; $rateLate = (float) $person['slabs'][$slab]['late_rate']; }
            } else {
                $rateOn = $person['rate'];
                $rateLate = $person['late_rate'];
            }
        }
        $onTime = $sum('on_time');
        $late = $sum('late');
        $target = round(($person['monthly_target'] ?? 0) * count($months), 2);

        return [
            'months' => array_values($months),
            'invoiced' => round($rows->sum('invoiced'), 2), 'cost' => round($rows->sum('cost'), 2), 'income' => $income,
            'target' => $target, 'achievement' => $target > 0 ? round($income / $target * 100, 1) : null,
            'rate_on_time' => $rateOn, 'rate_late' => $rateLate, 'late_days' => $person['late_days'] ?? self::DEFAULT_LATE_DAYS,
            'slabs' => (($person['type'] ?? null) === 'slab') ? $person['slabs'] : [], 'slab_index' => $slab,
            'on_time' => $onTime, 'late' => $late, 'over' => $sum('over'), 'pending' => $sum('pending'),
            'pay_on_time' => round($onTime * $rateOn / 100, 2), 'pay_late' => round($late * $rateLate / 100, 2),
            'incentive' => round($onTime * $rateOn / 100 + $late * $rateLate / 100, 2),
            'count' => $rows->count(),
        ];
    }

    /** all salespeople together: amounts added up; the rates only make sense for one person, so they are left out */
    private static function total($people, array $rows, string $from, string $to): array
    {
        $add = fn ($k) => round($people->sum($k), 2);
        $target = $add('target');
        $income = round(collect($rows)->sum('income'), 2);
        return [
            'months' => array_values(self::months($rows, $from, $to)),
            'invoiced' => $add('invoiced'), 'cost' => $add('cost'), 'income' => $income,
            'target' => $target, 'achievement' => $target > 0 ? round($income / $target * 100, 1) : null,
            'on_time' => $add('on_time'), 'late' => $add('late'), 'over' => $add('over'), 'pending' => $add('pending'),
            'pay_on_time' => $add('pay_on_time'), 'pay_late' => $add('pay_late'), 'incentive' => $add('incentive'),
            'count' => count($rows),
        ];
    }

    private static function schemeLabel(?array $person, array $s): string
    {
        if (!$person) return __('No salesperson on these invoices');
        $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.') . '%';
        if ($person['type'] === 'slab') {
            return __('Slab') . ' · ' . ($s['slab_index'] !== null ? $pct($s['rate_on_time']) . ' / ' . $pct($s['rate_late']) : __('no slab reached'));
        }
        return __('Flat') . ' · ' . $pct($s['rate_on_time']) . ' / ' . $pct($s['rate_late']);
    }
}
