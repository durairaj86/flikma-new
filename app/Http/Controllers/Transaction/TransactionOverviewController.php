<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\CollectionEnum;
use App\Enums\PaymentEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\Payment\Payment;
use App\Models\Supplier\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionOverviewController extends Controller
{
    private function getRange($request): array
    {
        $range = $request->input('range', 'this_month');

        return match ($range) {
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth(), $range],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), $range],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), $range],
        };
    }

    public function index(Request $request)
    {
        [$startDate, $endDate, $range] = $this->getRange($request);

        $totalCollected = $this->getTotalCollected($startDate, $endDate);
        $totalPaid = $this->getTotalPaid($startDate, $endDate);

        $data = [
            'totalCollected' => $totalCollected,
            'totalPaid' => $totalPaid,
            'netCashFlow' => $totalCollected - $totalPaid,
            'avgTransactionValue' => $this->getAvgTransactionValue($startDate, $endDate),
            'pendingPayments' => $this->getPendingPayments(),
            'pendingCollections' => $this->getPendingCollections(),
            'paymentsCount' => $this->getPaymentsCount($startDate, $endDate),
            'collectionsCount' => $this->getCollectionsCount($startDate, $endDate),
            'cashFlowTrend' => $this->getCashFlowTrend($startDate, $endDate, $range),
            'paymentsByAccount' => $this->getPaymentsByAccount($startDate, $endDate),
            'collectionsByAccount' => $this->getCollectionsByAccount($startDate, $endDate),
            'paymentStatuses' => $this->getPaymentStatusBreakdown($startDate, $endDate),
            'collectionStatuses' => $this->getCollectionStatusBreakdown($startDate, $endDate),
            'topSuppliers' => $this->getTopSuppliersByPayment($startDate, $endDate),
            'topCustomers' => $this->getTopCustomersByCollection($startDate, $endDate),
            'monthlyComparison' => $this->getMonthlyComparison(),
        ];

        return view('modules.transaction.overview', compact('data', 'range'));
    }

    private function getTotalCollected($startDate, $endDate): float
    {
        return (float) Collection::whereBetween('collection_date', [$startDate, $endDate])
            ->where('status', CollectionEnum::APPROVED->value)
            ->sum('base_grand_total');
    }

    private function getTotalPaid($startDate, $endDate): float
    {
        return (float) Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', PaymentEnum::APPROVED->value)
            ->sum('base_grand_total');
    }

    private function getAvgTransactionValue($startDate, $endDate): float
    {
        $collections = Collection::whereBetween('collection_date', [$startDate, $endDate])
            ->where('status', CollectionEnum::APPROVED->value)
            ->get(['base_grand_total']);

        $payments = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', PaymentEnum::APPROVED->value)
            ->get(['base_grand_total']);

        $count = $collections->count() + $payments->count();
        if ($count === 0) {
            return 0;
        }

        $total = (float) $collections->sum('base_grand_total') + (float) $payments->sum('base_grand_total');

        return $total / $count;
    }

    private function getPendingPayments(): int
    {
        return (int) Payment::where('status', PaymentEnum::DRAFT->value)->count();
    }

    private function getPendingCollections(): int
    {
        return (int) Collection::where('status', CollectionEnum::DRAFT->value)->count();
    }

    private function getPaymentsCount($startDate, $endDate): int
    {
        return (int) Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', PaymentEnum::APPROVED->value)
            ->count();
    }

    private function getCollectionsCount($startDate, $endDate): int
    {
        return (int) Collection::whereBetween('collection_date', [$startDate, $endDate])
            ->where('status', CollectionEnum::APPROVED->value)
            ->count();
    }

    private function getCashFlowTrend($startDate, $endDate, $range): array
    {
        $collected = [];
        $paid = [];

        if ($range === 'this_year') {
            for ($i = 1; $i <= 12; $i++) {
                $monthStart = Carbon::create(Carbon::now()->year, $i, 1)->startOfMonth();
                $monthEnd = Carbon::create(Carbon::now()->year, $i, 1)->endOfMonth();

                $collected[] = (float) Collection::whereBetween('collection_date', [$monthStart, $monthEnd])
                    ->where('status', CollectionEnum::APPROVED->value)
                    ->sum('base_grand_total');

                $paid[] = (float) Payment::whereBetween('payment_date', [$monthStart, $monthEnd])
                    ->where('status', PaymentEnum::APPROVED->value)
                    ->sum('base_grand_total');

                if ($monthStart->month === Carbon::now()->month) {
                    break;
                }
            }
        } else {
            $currentDate = clone $startDate;
            $weekNumber = 1;

            while ($currentDate->lte($endDate)) {
                $weekStart = clone $currentDate;
                $weekEnd = (clone $currentDate)->addDays(6)->min($endDate);

                $collected[] = (float) Collection::whereBetween('collection_date', [$weekStart, $weekEnd])
                    ->where('status', CollectionEnum::APPROVED->value)
                    ->sum('base_grand_total');

                $paid[] = (float) Payment::whereBetween('payment_date', [$weekStart, $weekEnd])
                    ->where('status', PaymentEnum::APPROVED->value)
                    ->sum('base_grand_total');

                $currentDate->addDays(7);
                $weekNumber++;

                if ($weekNumber > 5) break;
            }
        }

        return ['collected' => $collected, 'paid' => $paid];
    }

    private function getPaymentsByAccount($startDate, $endDate): array
    {
        $accounts = Account::pluck('name', 'id');

        return Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', PaymentEnum::APPROVED->value)
            ->whereNotNull('account')
            ->select('account', DB::raw('SUM(base_grand_total) as value'))
            ->groupBy('account')
            ->orderBy('value', 'desc')
            ->take(6)
            ->get()
            ->map(fn($r) => [
                'label' => $accounts[$r->account] ?? "Account #{$r->account}",
                'value' => (float) $r->value,
            ])
            ->toArray();
    }

    private function getCollectionsByAccount($startDate, $endDate): array
    {
        $accounts = Account::pluck('name', 'id');

        return Collection::whereBetween('collection_date', [$startDate, $endDate])
            ->where('status', CollectionEnum::APPROVED->value)
            ->whereNotNull('account')
            ->select('account', DB::raw('SUM(base_grand_total) as value'))
            ->groupBy('account')
            ->orderBy('value', 'desc')
            ->take(6)
            ->get()
            ->map(fn($r) => [
                'label' => $accounts[$r->account] ?? "Account #{$r->account}",
                'value' => (float) $r->value,
            ])
            ->toArray();
    }

    private function getPaymentStatusBreakdown($startDate, $endDate): array
    {
        return Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(base_grand_total) as total'))
            ->groupBy('status')
            ->get()
            ->map(function ($r) {
                $enum = PaymentEnum::tryFrom($r->status);
                return [
                    'status' => $enum ? strtolower($enum->name) : (string) $r->status,
                    'label' => $enum?->label() ?? (string) $r->status,
                    'count' => (int) $r->count,
                    'total' => (float) $r->total,
                ];
            })
            ->toArray();
    }

    private function getCollectionStatusBreakdown($startDate, $endDate): array
    {
        return Collection::whereBetween('collection_date', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(base_grand_total) as total'))
            ->groupBy('status')
            ->get()
            ->map(function ($r) {
                $enum = CollectionEnum::tryFrom($r->status);
                return [
                    'status' => $enum ? strtolower($enum->name) : (string) $r->status,
                    'label' => $enum?->label() ?? (string) $r->status,
                    'count' => (int) $r->count,
                    'total' => (float) $r->total,
                ];
            })
            ->toArray();
    }

    private function getTopSuppliersByPayment($startDate, $endDate): array
    {
        $rows = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', PaymentEnum::APPROVED->value)
            ->whereNotNull('supplier_id')
            ->select('supplier_id', DB::raw('COUNT(*) as payment_count'), DB::raw('SUM(base_grand_total) as total'))
            ->groupBy('supplier_id')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        $suppliers = Supplier::whereIn('id', $rows->pluck('supplier_id'))->get()->keyBy('id');

        return $rows->map(fn($r) => [
            'name' => $suppliers[$r->supplier_id]->name_en ?? ($suppliers[$r->supplier_id]->name ?? "Supplier #{$r->supplier_id}"),
            'payments' => (int) $r->payment_count,
            'total' => (float) $r->total,
        ])->toArray();
    }

    private function getTopCustomersByCollection($startDate, $endDate): array
    {
        $rows = Collection::whereBetween('collection_date', [$startDate, $endDate])
            ->where('status', CollectionEnum::APPROVED->value)
            ->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as collection_count'), DB::raw('SUM(base_grand_total) as total'))
            ->groupBy('customer_id')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        $customers = Customer::whereIn('id', $rows->pluck('customer_id'))->get()->keyBy('id');

        return $rows->map(fn($r) => [
            'name' => $customers[$r->customer_id]->name ?? "Customer #{$r->customer_id}",
            'collections' => (int) $r->collection_count,
            'total' => (float) $r->total,
        ])->toArray();
    }

    private function getMonthlyComparison(): array
    {
        $currentMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        $lastMonth = [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];

        $currentCollected = (float) Collection::whereBetween('collection_date', $currentMonth)
            ->where('status', CollectionEnum::APPROVED->value)->sum('base_grand_total');
        $lastCollected = (float) Collection::whereBetween('collection_date', $lastMonth)
            ->where('status', CollectionEnum::APPROVED->value)->sum('base_grand_total');

        $currentPaid = (float) Payment::whereBetween('payment_date', $currentMonth)
            ->where('status', PaymentEnum::APPROVED->value)->sum('base_grand_total');
        $lastPaid = (float) Payment::whereBetween('payment_date', $lastMonth)
            ->where('status', PaymentEnum::APPROVED->value)->sum('base_grand_total');

        return [
            'current' => ['collected' => $currentCollected, 'paid' => $currentPaid, 'net' => $currentCollected - $currentPaid],
            'previous' => ['collected' => $lastCollected, 'paid' => $lastPaid, 'net' => $lastCollected - $lastPaid],
        ];
    }
}
