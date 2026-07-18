<?php

namespace App\Http\Controllers\Sales;

use App\Enums\CustomerInvoiceEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoiceSub;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Master\Description;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOverviewController extends Controller
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

        $data = [
            'sales' => $this->getTotalSales($startDate, $endDate),
            'pendingApproval' => $this->getPendingApprovalValue($startDate, $endDate),
            'approvalRate' => $this->getApprovalRate($startDate, $endDate),
            'outstanding' => $this->getOutstandingAmount(),
            'invoices_avg' => $this->getAverageInvoiceValue($startDate, $endDate),
            'recurring_ratio' => $this->getRecurringRatio($startDate, $endDate),
            'invoices_count' => $this->getInvoicesCount($startDate, $endDate),
            'customers_count' => $this->getCustomersCount($startDate, $endDate),
            'salesTrend' => $this->getSalesTrend($startDate, $endDate, $range),
            'categories' => $this->getSalesByServiceType($startDate, $endDate),
            'regions' => $this->getSalesByRegion($startDate, $endDate),
            'salespeople' => $this->getSalespeoplePerformance($startDate, $endDate),
            'customers' => $this->getTopCustomers($startDate, $endDate),
            'items' => $this->getTopItems($startDate, $endDate),
            'invoiceStatuses' => $this->getInvoiceStatusBreakdown($startDate, $endDate),
            'monthlyComparison' => $this->getMonthlyComparison(),
        ];

        return view('modules.sales.overview', compact('data', 'range'));
    }

    private function getTotalSales($startDate, $endDate): float
    {
        return (float) CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->sum('base_grand_total');
    }

    private function getPendingApprovalValue($startDate, $endDate): float
    {
        return (float) CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('status', [CustomerInvoiceEnum::DRAFT->value, CustomerInvoiceEnum::SENT->value])
            ->sum('base_grand_total');
    }

    private function getApprovalRate($startDate, $endDate): float
    {
        $total = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])->count();
        if ($total === 0) {
            return 0;
        }

        $approved = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->count();

        return $approved / $total;
    }

    private function getOutstandingAmount(): float
    {
        $total = (float) CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->sum('base_grand_total');
        $paid = (float) CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->sum('paid_amount');
        return max(0, $total - $paid);
    }

    private function getAverageInvoiceValue($startDate, $endDate): float
    {
        $count = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->count();

        $total = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->sum('base_grand_total');

        return $count > 0 ? $total / $count : 0;
    }

    private function getRecurringRatio($startDate, $endDate): float
    {
        $totalCustomers = (int) CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->distinct('customer_id')
            ->count('customer_id');

        $recurring = (int) CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->select('customer_id', DB::raw('COUNT(*) as invoice_count'))
            ->groupBy('customer_id')
            ->having('invoice_count', '>', 1)
            ->count();

        return $totalCustomers > 0 ? $recurring / $totalCustomers : 0;
    }

    private function getInvoicesCount($startDate, $endDate): int
    {
        return (int) CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->count();
    }

    private function getCustomersCount($startDate, $endDate): int
    {
        return (int) CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', CustomerInvoiceEnum::APPROVED->value)
            ->distinct('customer_id')
            ->count('customer_id');
    }

    private function getSalesTrend($startDate, $endDate, $range): array
    {
        $trend = [];

        if ($range === 'this_year') {
            for ($i = 1; $i <= 12; $i++) {
                $monthStart = Carbon::create(Carbon::now()->year, $i, 1)->startOfMonth();
                $monthEnd = Carbon::create(Carbon::now()->year, $i, 1)->endOfMonth();

                $sales = (float) CustomerInvoice::whereBetween('invoice_date', [$monthStart, $monthEnd])
                    ->where('status', CustomerInvoiceEnum::APPROVED->value)
                    ->sum('base_grand_total');

                $trend[] = $sales;

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

                $sales = (float) CustomerInvoice::whereBetween('invoice_date', [$weekStart, $weekEnd])
                    ->where('status', CustomerInvoiceEnum::APPROVED->value)
                    ->sum('base_grand_total');

                $trend[] = $sales;
                $currentDate->addDays(7);
                $weekNumber++;

                if ($weekNumber > 5) break;
            }
        }

        return $trend;
    }

    private function getSalesByServiceType($startDate, $endDate): array
    {
        return CustomerInvoiceSub::whereHas('customerInvoice', fn($q) =>
            $q->whereBetween('invoice_date', [$startDate, $endDate])->where('customer_invoices.status', CustomerInvoiceEnum::APPROVED->value)
        )
        ->join('descriptions', 'customer_invoice_subs.description_id', '=', 'descriptions.id')
        ->select('descriptions.category as label', DB::raw('SUM(customer_invoice_subs.line_total) as value'))
        ->whereNotNull('descriptions.category')
        ->groupBy('descriptions.category')
        ->orderBy('value', 'desc')
        ->take(6)
        ->get()
        ->map(fn($r) => ['label' => $r->label, 'value' => (float) $r->value])
        ->toArray();
    }

    private function getSalesByRegion($startDate, $endDate): array
    {
        return CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('customer_invoices.status', CustomerInvoiceEnum::APPROVED->value)
            ->whereNotNull('customer_invoices.customer_id')
            ->join('customers', 'customer_invoices.customer_id', '=', 'customers.id')
            ->select('customers.city_en as label', DB::raw('SUM(customer_invoices.base_grand_total) as value'))
            ->whereNotNull('customers.city_en')
            ->groupBy('customers.city_en')
            ->orderBy('value', 'desc')
            ->take(6)
            ->get()
            ->map(fn($r) => ['label' => $r->label, 'value' => (float) $r->value])
            ->toArray();
    }

    private function getSalespeoplePerformance($startDate, $endDate): array
    {
        return CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('customer_invoices.status', CustomerInvoiceEnum::APPROVED->value)
            ->whereNotNull('customer_invoices.created_by')
            ->join('users', 'customer_invoices.created_by', '=', 'users.id')
            ->select('users.name', DB::raw('SUM(customer_invoices.base_grand_total) as value'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('value', 'desc')
            ->take(5)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'value' => (float) $r->value])
            ->toArray();
    }

    private function getTopCustomers($startDate, $endDate): array
    {
        $rows = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('customer_invoices.status', CustomerInvoiceEnum::APPROVED->value)
            ->select('customer_id',
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(base_grand_total) as revenue'),
                DB::raw('SUM(base_grand_total) - SUM(COALESCE(paid_amount, 0)) as outstanding')
            )
            ->groupBy('customer_id')
            ->orderBy('revenue', 'desc')
            ->take(10)
            ->get();

        $customers = Customer::whereIn('id', $rows->pluck('customer_id'))->get()->keyBy('id');

        return $rows->map(fn($r) => [
            'name' => $customers[$r->customer_id]->name ?? "Customer #{$r->customer_id}",
            'invoices' => (int) $r->invoice_count,
            'revenue' => (float) $r->revenue,
            'outstanding' => (float) $r->outstanding,
        ])->toArray();
    }

    private function getTopItems($startDate, $endDate): array
    {
        $items = CustomerInvoiceSub::whereHas('customerInvoice', fn($q) =>
            $q->whereBetween('invoice_date', [$startDate, $endDate])->where('customer_invoices.status', CustomerInvoiceEnum::APPROVED->value)
        )
        ->select('description_id',
            DB::raw('SUM(quantity) as qty'),
            DB::raw('SUM(line_total) as revenue')
        )
        ->groupBy('description_id')
        ->orderBy('revenue', 'desc')
        ->take(10)
        ->get();

        $descriptions = Description::whereIn('id', $items->pluck('description_id'))->get()->keyBy('id');

        return $items->map(fn($i) => [
            'name' => $descriptions[$i->description_id]->name ?? "Item #{$i->description_id}",
            'qty' => (float) $i->qty,
            'revenue' => (float) $i->revenue,
        ])->toArray();
    }

    private function getInvoiceStatusBreakdown($startDate, $endDate): array
    {
        return CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->select('customer_invoices.status', DB::raw('COUNT(*) as count'), DB::raw('SUM(base_grand_total) as total'))
            ->groupBy('customer_invoices.status')
            ->get()
            ->map(fn($r) => [
                'status' => CustomerInvoiceEnum::tryFrom($r->status)?->name ? strtolower(CustomerInvoiceEnum::tryFrom($r->status)->name) : (string) $r->status,
                'label' => CustomerInvoiceEnum::tryFrom($r->status)?->label() ?? (string) $r->status,
                'count' => (int) $r->count,
                'total' => (float) $r->total,
            ])
            ->toArray();
    }

    private function getMonthlyComparison(): array
    {
        $currentMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        $lastMonth = [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];

        $currentSales = (float) CustomerInvoice::whereBetween('invoice_date', $currentMonth)
            ->where('status', CustomerInvoiceEnum::APPROVED->value)->sum('base_grand_total');

        $lastSales = (float) CustomerInvoice::whereBetween('invoice_date', $lastMonth)
            ->where('status', CustomerInvoiceEnum::APPROVED->value)->sum('base_grand_total');

        $currentCount = (int) CustomerInvoice::whereBetween('invoice_date', $currentMonth)
            ->where('status', CustomerInvoiceEnum::APPROVED->value)->count();

        $lastCount = (int) CustomerInvoice::whereBetween('invoice_date', $lastMonth)
            ->where('status', CustomerInvoiceEnum::APPROVED->value)->count();

        return [
            'current' => ['sales' => $currentSales, 'invoices' => $currentCount],
            'previous' => ['sales' => $lastSales, 'invoices' => $lastCount],
        ];
    }
}
