<?php

namespace App\Services\Dashboard;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\PaymentEnum;
use App\Enums\QuotationEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Models\Customer\Customer;
use App\Enums\ExpenseEnum;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\Expense\Expense;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Enquiry\Enquiry;
use App\Models\Job\Job;
use App\Models\Quotation\Quotation;
use App\Enums\EnquiryEnum;
use App\Enums\JobEnum;
use Carbon\Carbon;

/**
 * Month-scoped numbers for the dashboard KPI widgets. One method per module;
 * each takes the first day of the selected month and returns a plain array,
 * so a widget only queries what it displays.
 */
class WidgetData
{
    public static function change($cur, $prev): float
    {
        return $prev > 0 ? round((($cur - $prev) / $prev) * 100, 2) : ($cur > 0 ? 100.0 : 0.0);
    }

    private static function bounds(Carbon $start): array
    {
        $end = $start->copy()->endOfMonth();
        $prevStart = $start->copy()->subMonth()->startOfMonth();
        return [$start, $end, $prevStart, $prevStart->copy()->endOfMonth()];
    }

    private static function between($query, string $col, Carbon $a, Carbon $b)
    {
        return $query->whereBetween($col, [$a->toDateString(), $b->toDateString()]);
    }

    /** Per-day totals for a month, index 0 = day 1. */
    private static function daily(Carbon $start, $rows, string $dateCol, ?string $valCol = null): array
    {
        $out = array_fill(1, $start->daysInMonth, 0.0);
        foreach ($rows as $r) {
            $d = (int) Carbon::parse($r->{$dateCol})->format('j');
            if (isset($out[$d])) {
                $out[$d] += $valCol ? (float) $r->{$valCol} : 1;
            }
        }
        return array_values($out);
    }

    private static function approvedInvoices(Carbon $a, Carbon $b)
    {
        return self::between(CustomerInvoice::query()->where('status', CustomerInvoiceEnum::APPROVED->value), 'invoice_date', $a, $b);
    }

    private static function approvedBills(Carbon $a, Carbon $b)
    {
        return self::between(SupplierInvoice::query()->where('status', SupplierInvoiceEnum::APPROVED->value), 'invoice_date', $a, $b);
    }

    private static function liveInvoices(Carbon $a, Carbon $b)
    {
        return self::between(CustomerInvoice::query()->whereIn('status', [CustomerInvoiceEnum::DRAFT->value, CustomerInvoiceEnum::APPROVED->value]), 'invoice_date', $a, $b);
    }

    public static function sales(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $total = (float) self::approvedInvoices($s, $e)->sum('grand_total');
        $collected = (float) self::between(Collection::query()->where('status', CollectionEnum::APPROVED->value), 'collection_date', $s, $e)->sum('base_grand_total');

        return [
            'total' => $total,
            'count' => self::approvedInvoices($s, $e)->count(),
            'change' => self::change($total, (float) self::approvedInvoices($ps, $pe)->sum('grand_total')),
            'collected' => $collected,
            'pending' => max(0, $total - $collected),
            'labels' => range(1, $start->daysInMonth),
            'series' => self::daily($start, self::approvedInvoices($s, $e)->get(['invoice_date', 'grand_total']), 'invoice_date', 'grand_total'),
        ];
    }

    public static function invoices(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $count = self::liveInvoices($s, $e)->count();

        return [
            'count' => $count,
            'change' => self::change($count, self::liveInvoices($ps, $pe)->count()),
            'approved' => self::liveInvoices($s, $e)->where('status', CustomerInvoiceEnum::APPROVED->value)->count(),
            'draft' => self::liveInvoices($s, $e)->where('status', CustomerInvoiceEnum::DRAFT->value)->count(),
            'due' => CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
                ->where('due_at', '<', Carbon::now())->where('due_at', '>', Carbon::now()->subDays(30))->count(),
        ];
    }

    public static function customers(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $in = fn ($a, $b) => Customer::query()->whereBetween('created_at', [$a->copy()->startOfDay(), $b->copy()->endOfDay()]);
        $new = $in($s, $e)->count();
        $prev = $in($ps, $pe)->count();

        return [
            'total' => Customer::count(),
            'new' => $new,
            'prevNew' => $prev,
            'change' => self::change($new, $prev),
            'labels' => range(1, $start->daysInMonth),
            'series' => self::daily($start, $in($s, $e)->get(['created_at']), 'created_at'),
        ];
    }

    private static function approvedCreditNotes(Carbon $a, Carbon $b)
    {
        return self::between(\App\Models\Finance\Adjustment\CreditNote::query()->where('status', \App\Enums\CreditNoteEnum::APPROVED->value), 'posted_at', $a, $b);
    }

    /** Net profit excludes VAT and nets approved credit notes against revenue. */
    public static function profit(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $net = fn ($a, $b) => (float) self::approvedInvoices($a, $b)->sum('base_sub_total') - (float) self::approvedCreditNotes($a, $b)->sum('base_sub_total');
        $revenue = $net($s, $e);
        $expenses = (float) self::approvedBills($s, $e)->sum('base_sub_total');
        $prev = $net($ps, $pe) - (float) self::approvedBills($ps, $pe)->sum('base_sub_total');
        $sales = self::daily($start, self::approvedInvoices($s, $e)->get(['invoice_date', 'base_sub_total']), 'invoice_date', 'base_sub_total');
        $credits = self::daily($start, self::approvedCreditNotes($s, $e)->get(['posted_at', 'base_sub_total']), 'posted_at', 'base_sub_total');
        $bills = self::daily($start, self::approvedBills($s, $e)->get(['invoice_date', 'base_sub_total']), 'invoice_date', 'base_sub_total');

        return [
            'total' => $revenue - $expenses,
            'change' => self::change($revenue - $expenses, $prev),
            'margin' => $revenue > 0 ? round(($revenue - $expenses) / $revenue * 100, 1) : 0,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'labels' => range(1, $start->daysInMonth),
            'series' => array_map(fn ($a, $c, $b) => round($a - $c - $b, 2), $sales, $credits, $bills),
        ];
    }

    public static function quotation(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $base = fn ($a, $b) => self::between(Quotation::query(), 'posted_at', $a, $b);
        $total = (float) $base($s, $e)->sum('grand_total');

        return [
            'total' => $total,
            'count' => $base($s, $e)->count(),
            'change' => self::change($total, (float) $base($ps, $pe)->sum('grand_total')),
            'completed' => $base($s, $e)->where('status', QuotationEnum::CONVERTED->value)->count(),
            'approved' => $base($s, $e)->where('status', QuotationEnum::ACCEPTED->value)->count(),
        ];
    }

    public static function enquiry(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $base = fn ($a, $b) => Enquiry::query()->whereBetween('created_at', [$a->copy()->startOfDay(), $b->copy()->endOfDay()]);
        $total = $base($s, $e)->count();

        return [
            'total' => $total,
            'change' => self::change($total, $base($ps, $pe)->count()),
            'confirmed' => $base($s, $e)->where('status', EnquiryEnum::CONFIRMED->value)->count(),
            'pending' => $base($s, $e)->where('status', EnquiryEnum::PENDING->value)->count(),
        ];
    }

    public static function job(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $base = fn ($a, $b) => Job::query()->whereBetween('created_at', [$a->copy()->startOfDay(), $b->copy()->endOfDay()]);
        $total = $base($s, $e)->count();

        return [
            'total' => $total,
            'change' => self::change($total, $base($ps, $pe)->count()),
            'active' => $base($s, $e)->where('status', JobEnum::PENDING->value)->count(),
            'completed' => $base($s, $e)->where('status', JobEnum::COMPLETED->value)->count(),
        ];
    }

    private static function money(string $model, string $dateCol, int $draft, int $approved, Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $base = fn ($a, $b) => self::between($model::query()->whereIn('status', [$draft, $approved]), $dateCol, $a, $b);
        $total = (float) $base($s, $e)->sum('base_grand_total');

        return [
            'total' => $total,
            'count' => $base($s, $e)->count(),
            'change' => self::change($total, (float) $base($ps, $pe)->sum('base_grand_total')),
            'approved' => (float) $base($s, $e)->where('status', $approved)->sum('base_grand_total'),
            'draft' => (float) $base($s, $e)->where('status', $draft)->sum('base_grand_total'),
        ];
    }

    public static function payments(Carbon $start): array
    {
        return self::money(Payment::class, 'payment_date', PaymentEnum::DRAFT->value, PaymentEnum::APPROVED->value, $start);
    }

    public static function collection(Carbon $start): array
    {
        return self::money(Collection::class, 'collection_date', CollectionEnum::DRAFT->value, CollectionEnum::APPROVED->value, $start);
    }

    /**
     * Expenses for the selected month against the month before it, as running
     * totals per day so the two lines can be compared like Google Analytics.
     * The selected month stops at today when it is the current month.
     */
    public static function expense(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $base = fn ($a, $b) => self::between(Expense::query()->where('status', '!=', ExpenseEnum::CANCELLED->value), 'posted_at', $a, $b);
        $cumulative = function (Carbon $from, int $limitDay) use ($base) {
            $rows = $base($from, $from->copy()->endOfMonth())->get(['posted_at', 'grand_total']);
            $daily = self::daily($from, $rows, 'posted_at', 'grand_total');
            $out = [];
            $run = 0.0;
            foreach ($daily as $i => $v) {
                $run += $v;
                $out[] = ($i + 1) <= $limitDay ? round($run, 2) : null;
            }
            return $out;
        };
        $today = Carbon::today();
        $thisLimit = $start->isSameMonth($today) ? (int) $today->format('j') : $start->daysInMonth;
        $thisTotal = (float) $base($s, $e)->sum('grand_total');
        $lastTotal = (float) $base($ps, $pe)->sum('grand_total');

        return [
            'total' => $thisTotal,
            'last' => $lastTotal,
            'count' => $base($s, $e)->count(),
            'change' => self::change($thisTotal, $lastTotal),
            'labels' => range(1, max($start->daysInMonth, $ps->daysInMonth)),
            'this' => $cumulative($s, $thisLimit),
            'prev' => $cumulative($ps, $ps->daysInMonth),
            'thisLabel' => $start->format('M'),
            'prevLabel' => $ps->format('M'),
        ];
    }

    // ------------------------------------------------------------------
    // Payroll / attendance / punching (live snapshots, current company only)
    // ------------------------------------------------------------------

    private static function activeEmployees()
    {
        return \App\Models\User::query()->where('company_id', companyId())->where('is_employee', true)->whereNull('terminated_at');
    }

    /** This month's payroll: how much is generated, paid and still pending. */
    public static function payroll(): array
    {
        $now = Carbon::now('Asia/Riyadh');
        $recs = \App\Models\Payroll\PayrollRecord::with('employee:id,name')->where('company_id', companyId())
            ->where('month', $now->month)->where('year', $now->year)->where('status', '!=', 'cancelled')->get();
        $paid = $recs->where('status', 'paid');
        $pending = $recs->where('status', '!=', 'paid');
        $net = (float) $recs->sum('net_payable');

        return [
            'month' => $now->translatedFormat('F Y'),
            'employees' => self::activeEmployees()->count(),
            'count' => $recs->count(),
            'net_total' => $net,
            'paid_total' => (float) $paid->sum('net_payable'),
            'pending_total' => (float) $pending->sum('net_payable'),
            'paid_count' => $paid->count(),
            'pending_count' => $pending->count(),
            'loan_total' => (float) $recs->sum('loan_deduction'),
            'paid_pct' => $net > 0 ? (int) round($paid->sum('net_payable') / $net * 100) : 0,
            'rows' => $recs->sortBy(fn ($r) => mb_strtolower($r->employee->name ?? ''))->map(fn ($r) => [
                'name' => $r->employee->name ?? '—', 'net' => (float) $r->net_payable, 'status' => $r->status,
            ])->values()->all(),
        ];
    }

    /** Today's attendance of every active employee (people with no record are "not marked"). */
    public static function attendance(): array
    {
        $today = Carbon::today('Asia/Riyadh')->toDateString();
        $emps = self::activeEmployees()->orderBy('name')->get(['id', 'name']);
        $att = \App\Models\Payroll\Attendance::where('company_id', companyId())->where('date', $today)->get()->keyBy('employee_id');

        $rows = $emps->map(function ($e) use ($att) {
            $a = $att->get($e->id);

            return [
                'name' => $e->name,
                'status' => $a->status ?? 'not_marked',
                'in' => $a && $a->check_in ? substr((string) $a->check_in, 0, 5) : null,
                'out' => $a && $a->check_out ? substr((string) $a->check_out, 0, 5) : null,
            ];
        });
        $count = fn (string $st) => $rows->where('status', $st)->count();
        $total = $emps->count();
        $present = $count('present') + $count('half_day');

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $count('absent'),
            'leave' => $count('leave') + $count('holiday'),
            'not_marked' => $count('not_marked'),
            'pct' => $total > 0 ? (int) round($present / $total * 100) : 0,
            'date' => Carbon::today('Asia/Riyadh')->translatedFormat('l, d M'),
            // not marked / absent first: those are the people to follow up
            'rows' => $rows->sortBy(fn ($r) => ['not_marked' => 0, 'absent' => 1, 'leave' => 2, 'holiday' => 2, 'half_day' => 3, 'present' => 4][$r['status']] ?? 5)->values()->all(),
        ];
    }

    /** Today's punches coming from the machines, and how the machines themselves are doing. */
    public static function punching(): array
    {
        $today = Carbon::today('Asia/Riyadh')->toDateString();
        $punches = \App\Models\Payroll\AttendancePunch::with(['employee:id,name', 'device:id,name'])->where('company_id', companyId())
            ->whereDate('punched_at', $today)->orderByDesc('punched_at')->get();
        $devices = \App\Models\Payroll\AttendanceDevice::where('company_id', companyId())->whereNotIn('protocol', ['import', 'manual'])->get();
        $last = $punches->first();

        return [
            'punches' => $punches->count(),
            'people' => $punches->whereNotNull('employee_id')->pluck('employee_id')->unique()->count(),
            'devices_total' => $devices->count(),
            'devices_online' => $devices->filter(fn ($d) => $d->isOnline())->count(),
            'unmapped' => \App\Models\Payroll\AttendancePunch::where('company_id', companyId())->where('status', 'unmapped')->count(),
            'last' => $last ? Carbon::parse($last->punched_at)->format('H:i') : null,
            'rows' => $punches->take(30)->map(fn ($p) => [
                'time' => Carbon::parse($p->punched_at)->format('H:i'),
                'name' => $p->employee->name ?? ('#' . $p->device_user_id),
                'dir' => $p->direction,
                'device' => $p->device->name ?? '—',
                'status' => $p->status,
            ])->values()->all(),
        ];
    }

    /** Live (not month-filtered) snapshots for the HR widgets. */
    public static function snapshot(string $view): array
    {
        $view = preg_replace('/-(small|medium|tall)$/', '', $view);

        return match ($view) {
            'payroll' => self::payroll(),
            'attendance' => self::attendance(),
            'punching' => self::punching(),
            default => [],
        };
    }
}
