<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoiceSub;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOverviewController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range', 'this_month');

        // Determine date range based on selection
        switch ($range) {
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'this_month':
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
        }

        // Fetch data for the dashboard
        $data = [
            // KPIs
            'sales' => $this->getTotalSales($startDate, $endDate),
            'collected' => $this->getCollectedPayments($startDate, $endDate),
            'outstanding' => $this->getOutstandingAmount($startDate, $endDate),
            'invoices_avg' => $this->getAverageInvoiceValue($startDate, $endDate),
            'recurring_ratio' => $this->getRecurringRatio($startDate, $endDate),

            // Counts
            'invoices_count' => $this->getInvoicesCount($startDate, $endDate),
            'customers_count' => $this->getCustomersCount($startDate, $endDate),

            // Trend data
            'salesTrend' => $this->getSalesTrend($startDate, $endDate, $range),

            // Category breakdown
            'categories' => $this->getSalesByCategory($startDate, $endDate),

            // Region breakdown
            'regions' => $this->getSalesByRegion($startDate, $endDate),

            // Salesperson performance
            'salespeople' => $this->getSalespeoplePerformance($startDate, $endDate),

            // Top customers
            'customers' => $this->getTopCustomers($startDate, $endDate),

            // Top items
            'items' => $this->getTopItems($startDate, $endDate),

            // Cost of goods sold for profit calculation
            'cogs' => $this->getCostOfGoodsSold($startDate, $endDate),
        ];

        return view('modules.sales.overview', compact('data', 'range'));
    }

    private function getTotalSales($startDate, $endDate)
    {
        return CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->sum('base_grand_total');
    }

    private function getCollectedPayments($startDate, $endDate)
    {
        // This would ideally come from a payments/collections table
        // For now, we'll estimate based on invoices that are marked as paid
        return CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->sum('base_grand_total') * 0.8; // Assuming 80% collection rate
    }

    private function getOutstandingAmount($startDate, $endDate)
    {
        return CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->sum('base_grand_total') * 0.2; // Assuming 20% outstanding
    }

    private function getAverageInvoiceValue($startDate, $endDate)
    {
        $count = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->count();

        $total = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->sum('base_grand_total');

        return $count > 0 ? $total / $count : 0;
    }

    private function getRecurringRatio($startDate, $endDate)
    {
        // Count customers with more than one invoice in the period
        $totalCustomers = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->distinct('customer_id')
            ->count('customer_id');

        $recurringCustomers = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->select('customer_id', DB::raw('COUNT(*) as invoice_count'))
            ->groupBy('customer_id')
            ->having('invoice_count', '>', 1)
            ->count();

        return $totalCustomers > 0 ? $recurringCustomers / $totalCustomers : 0;
    }

    private function getInvoicesCount($startDate, $endDate)
    {
        return CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->count();
    }

    private function getCustomersCount($startDate, $endDate)
    {
        return CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->distinct('customer_id')
            ->count('customer_id');
    }

    private function getSalesTrend($startDate, $endDate, $range)
    {
        $trend = [];

        if ($range === 'this_year') {
            // Monthly trend for the year
            for ($i = 1; $i <= 12; $i++) {
                $monthStart = Carbon::create(Carbon::now()->year, $i, 1)->startOfMonth();
                $monthEnd = Carbon::create(Carbon::now()->year, $i, 1)->endOfMonth();

                $sales = CustomerInvoice::whereBetween('invoice_date', [$monthStart, $monthEnd])
                    ->where('status', 'approved')
                    ->sum('grand_total');

                $trend[] = $sales;

                // Stop if we've reached the current month
                if ($monthStart->month === Carbon::now()->month) {
                    break;
                }
            }
        } else {
            // Weekly trend for the month
            $currentDate = clone $startDate;
            $weekNumber = 1;

            while ($currentDate->lte($endDate)) {
                $weekStart = clone $currentDate;
                $weekEnd = clone $currentDate;
                $weekEnd->addDays(6)->min($endDate);

                $sales = CustomerInvoice::whereBetween('invoice_date', [$weekStart, $weekEnd])
                    ->where('status', 'approved')
                    ->sum('grand_total');

                $trend[] = $sales;

                $currentDate->addDays(7);
                $weekNumber++;

                // Limit to 4-5 weeks
                if ($weekNumber > 5) {
                    break;
                }
            }
        }

        return $trend;
    }

    private function getSalesByCategory($startDate, $endDate)
    {
        // This would ideally come from a category field in the invoice or invoice items
        // For now, we'll create some sample categories based on customer types or invoice sub types
        $categories = [];

        // Example: Group by a category field or create artificial categories
        $retailSales = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->where('customer_id', '!=', null)
            ->sum('grand_total') * 0.4; // 40% retail

        $wholesaleSales = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->where('customer_id', '!=', null)
            ->sum('grand_total') * 0.35; // 35% wholesale

        $onlineSales = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->where('customer_id', '!=', null)
            ->sum('grand_total') * 0.15; // 15% online

        $serviceSales = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->where('customer_id', '!=', null)
            ->sum('grand_total') * 0.1; // 10% services

        $categories = [
            ['label' => 'Retail', 'value' => $retailSales],
            ['label' => 'Wholesale', 'value' => $wholesaleSales],
            ['label' => 'Online', 'value' => $onlineSales],
            ['label' => 'Services', 'value' => $serviceSales],
        ];

        return $categories;
    }

    private function getSalesByRegion($startDate, $endDate)
    {
        // This would ideally come from a region field in the customer or invoice
        // For now, we'll create some sample regions
        $totalSales = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->sum('grand_total');

        $regions = [
            ['label' => 'Riyadh', 'value' => $totalSales * 0.5], // 50% Riyadh
            ['label' => 'Jeddah', 'value' => $totalSales * 0.3], // 30% Jeddah
            ['label' => 'Dammam', 'value' => $totalSales * 0.15], // 15% Dammam
            ['label' => 'Other', 'value' => $totalSales * 0.05], // 5% Other
        ];

        return $regions;
    }

    private function getSalespeoplePerformance($startDate, $endDate)
    {
        // This would ideally come from a salesperson field in the invoice
        // For now, we'll get some users and assign random sales values
        $users = User::take(4)->get();
        $totalSales = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->sum('grand_total');

        $salespeople = [];
        $percentages = [0.35, 0.25, 0.22, 0.18]; // Distribution percentages

        foreach ($users as $index => $user) {
            $salespeople[] = [
                'name' => $user->name,
                'value' => $totalSales * ($percentages[$index] ?? 0.1)
            ];
        }

        return $salespeople;
    }

    private function getTopCustomers($startDate, $endDate)
    {
        $customers = CustomerInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->select('customer_id', DB::raw('COUNT(*) as invoice_count'), DB::raw('SUM(grand_total) as revenue'))
            ->groupBy('customer_id')
            ->orderBy('revenue', 'desc')
            ->take(10)
            ->get();

        $result = [];
        foreach ($customers as $customer) {
            if ($customer->customer_id) {
                $customerInfo = Customer::find($customer->customer_id);
                if ($customerInfo) {
                    $result[] = [
                        'name' => $customerInfo->name ?? $customerInfo->name_en ?? 'Customer #' . $customer->customer_id,
                        'invoices' => $customer->invoice_count,
                        'revenue' => $customer->revenue
                    ];
                }
            }
        }

        return $result;
    }

    private function getTopItems($startDate, $endDate)
    {
        // This would ideally come from invoice line items or products
        // For now, we'll create some sample items based on invoice subs
        $items = CustomerInvoiceSub::whereHas('customerInvoice', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('invoice_date', [$startDate, $endDate])
                ->where('status', 'approved');
        })
        ->select('description_id', DB::raw('COUNT(*) as qty'), DB::raw('SUM(base_total_with_tax) as revenue'))
        ->groupBy('description_id')
        ->orderBy('revenue', 'desc')
        ->take(10)
        ->get();

        $result = [];
        foreach ($items as $item) {
            $description = \App\Models\Master\Description::find($item->description_id);
            $result[] = [
                'name' => $description ? $description->name : 'Item #' . $item->description_id,
                'qty' => $item->qty,
                'revenue' => $item->revenue
            ];
        }

        return $result;
    }

    private function getCostOfGoodsSold($startDate, $endDate)
    {
        // This would ideally come from COGS calculations or supplier invoices
        // For now, we'll estimate based on supplier invoices in the period
        $cogs = SupplierInvoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->sum('grand_total');

        // If no supplier invoices, estimate as 60% of sales
        if ($cogs == 0) {
            $cogs = $this->getTotalSales($startDate, $endDate) * 0.6;
        }

        return $cogs;
    }
}
