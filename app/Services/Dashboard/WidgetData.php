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
use App\Models\Quotation\Quotation;
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

    public static function profit(Carbon $start): array
    {
        [$s, $e, $ps, $pe] = self::bounds($start);
        $revenue = (float) self::approvedInvoices($s, $e)->sum('grand_total');
        $expenses = (float) self::approvedBills($s, $e)->sum('grand_total');
        $prev = (float) self::approvedInvoices($ps, $pe)->sum('grand_total') - (float) self::approvedBills($ps, $pe)->sum('grand_total');
        $sales = self::daily($start, self::approvedInvoices($s, $e)->get(['invoice_date', 'grand_total']), 'invoice_date', 'grand_total');
        $bills = self::daily($start, self::approvedBills($s, $e)->get(['invoice_date', 'grand_total']), 'invoice_date', 'grand_total');

        return [
            'total' => $revenue - $expenses,
            'change' => self::change($revenue - $expenses, $prev),
            'margin' => $revenue > 0 ? round(($revenue - $expenses) / $revenue * 100, 1) : 0,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'labels' => range(1, $start->daysInMonth),
            'series' => array_map(fn ($a, $b) => round($a - $b, 2), $sales, $bills),
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
}
