<?php

namespace App\Http\Controllers;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\JobEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Models\Job\Job;
use App\Models\Customer\Customer;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            // Total Sales
            'totalSales' => $this->getTotalSales(),
            'salesGrowth' => $this->getSalesGrowth(),

            // Invoices
            'totalInvoices' => $this->getTotalInvoices(),
            'dueInvoices' => $this->getDueInvoices(),

            // Customers
            'totalCustomers' => $this->getTotalCustomers(),
            'newCustomers' => $this->getNewCustomers(),

            // Profit
            'totalRevenue' => $this->getTotalRevenue(),
            'totalExpenses' => $this->getTotalExpenses(),
            'profit' => $this->getProfit(),
            'profitMargin' => $this->getProfitMargin(),

            // Shipping metrics
            'etaToday' => $this->getEtaToday(),
            'etdTomorrow' => $this->getEtdTomorrow(),
            'ataThisWeek' => $this->getAtaThisWeek(),
            'atdThisWeek' => $this->getAtdThisWeek(),

            // Job Follow-ups
            'jobFollowupsToday' => $this->getJobFollowupsToday(),
            'jobFollowupsThisWeek' => $this->getJobFollowupsThisWeek(),

            // Payments
            'toCollect' => $this->getToCollect(),
            'toPay' => $this->getToPay(),

            // Outstanding amounts
            'outstanding' => $this->getOutstanding(),
            'outstanding30d' => $this->getOutstanding30d(),
            'outstanding60d' => $this->getOutstanding60d(),
            'outstanding60Plus' => $this->getOutstanding60Plus(),
            'outstandingChange' => $this->getOutstandingChange(),

            // Awaiting Approval
            'awaitingApproval' => $this->getAwaitingApproval(),
            'awaitingApprovalTotal' => $this->getAwaitingApprovalTotal(),

            // Recent Transactions
            'recentTransactions' => $this->getRecentTransactions(),

            // Chart data
            'monthlyLabels' => $this->getMonthlyLabels(),
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'monthlyExpenses' => $this->getMonthlyExpenses(),
            'weeklyLabels' => $this->getWeeklyLabels(),
            'weeklyRevenueData' => $this->getWeeklyRevenueData(),
            'materialPercent' => $this->getMaterialPercent(),
            'labourPercent' => $this->getLabourPercent(),
            'transportPercent' => $this->getTransportPercent(),
            'dailyRevenueData' => $this->getDailyRevenueData(),
            'currentMonthCollected' => $this->getCurrentMonthCollected(),
            'currentMonthPending' => $this->getCurrentMonthPending(),
            'currentMonthSales' => $this->getCurrentMonthSales(),
        ];

        return view('dashboard', $data);
    }

    private function getTotalSales()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->sum('grand_total');
    }

    private function getSalesGrowth()
    {
        $previousMonthSales = CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->whereMonth('invoice_date', Carbon::now()->subMonth()->month)
            ->sum('grand_total');
        $currentMonthSales = $this->getCurrentMonthSales();

        return $previousMonthSales > 0
            ? round((($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100, 1)
            : 0;
    }

    private function getCurrentMonthSales()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->whereMonth('invoice_date', Carbon::now()->month)
            ->sum('grand_total');
    }

    private function getTotalInvoices()
    {
        return CustomerInvoice::count();
    }

    private function getDueInvoices()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->where('due_at', '<', Carbon::now())
            ->where('due_at', '>', Carbon::now()->subDays(30))
            ->count();
    }

    private function getTotalCustomers()
    {
        return Customer::count();
    }

    private function getNewCustomers()
    {
        return Customer::where('created_at', '>', Carbon::now()->subDays(30))->count();
    }

    private function getTotalRevenue()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)->sum('grand_total');
    }

    private function getTotalExpenses()
    {
        return SupplierInvoice::where('status', SupplierInvoiceEnum::APPROVED->value)->sum('grand_total');
    }

    private function getProfit()
    {
        return $this->getTotalRevenue() - $this->getTotalExpenses();
    }

    private function getProfitMargin()
    {
        $totalRevenue = $this->getTotalRevenue();
        return $totalRevenue > 0 ? round(($this->getProfit() / $totalRevenue) * 100) : 0;
    }

    private function getEtaToday()
    {
        return Job::whereDate('eta', Carbon::today())->count();
    }

    private function getEtdTomorrow()
    {
        return Job::whereDate('etd', Carbon::tomorrow())->count();
    }

    private function getAtaThisWeek()
    {
        return Job::whereBetween('ata', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
    }

    private function getAtdThisWeek()
    {
        return Job::whereBetween('atd', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
    }

    private function getJobFollowupsToday()
    {
        return Job::where('status', JobEnum::PENDING->value)->whereDate('updated_at', Carbon::today())->count();
    }

    private function getJobFollowupsThisWeek()
    {
        return Job::where('status', JobEnum::PENDING->value)
            ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
    }

    private function getToCollect()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->where('due_at', '<', Carbon::now()->addDays(7))
            ->sum('grand_total');
    }

    private function getToPay()
    {
        return SupplierInvoice::where('status', SupplierInvoiceEnum::APPROVED->value)
            ->where('due_at', '<', Carbon::now()->addDays(7))
            ->sum('grand_total');
    }

    private function getOutstanding()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->where('due_at', '<', Carbon::now())
            ->sum('grand_total');
    }

    private function getOutstanding30d()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->whereBetween('due_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->sum('grand_total');
    }

    private function getOutstanding60d()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->whereBetween('due_at', [Carbon::now()->subDays(60), Carbon::now()->subDays(31)])
            ->sum('grand_total');
    }

    private function getOutstanding60Plus()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->where('due_at', '<', Carbon::now()->subDays(60))
            ->sum('grand_total');
    }

    private function getOutstandingChange()
    {
        $previousMonthOutstanding = CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
            ->where('due_at', '<', Carbon::now()->subMonth())
            ->sum('grand_total');

        return $previousMonthOutstanding > 0
            ? round((($this->getOutstanding() - $previousMonthOutstanding) / $previousMonthOutstanding) * 100)
            : 0;
    }

    private function getAwaitingApproval()
    {
        return CustomerInvoice::where('status', CustomerInvoiceEnum::DRAFT->value)->get();
    }

    private function getAwaitingApprovalTotal()
    {
        return $this->getAwaitingApproval()->sum('grand_total');
    }

    private function getRecentTransactions()
    {
        return CustomerInvoice::with('customer', 'job')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    private function getMonthlyLabels()
    {
        $labels = [];
        for ($i = 0; $i < 10; $i++) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M');
        }
        return array_reverse($labels);
    }

    private function getMonthlyRevenue()
    {
        $revenue = [];
        for ($i = 0; $i < 10; $i++) {
            $month = Carbon::now()->subMonths($i);
            $revenue[] = CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
                ->whereMonth('invoice_date', $month->month)
                ->whereYear('invoice_date', $month->year)
                ->sum('grand_total') / 1000; // Convert to thousands
        }
        return array_reverse($revenue);
    }

    private function getMonthlyExpenses()
    {
        $expenses = [];
        for ($i = 0; $i < 10; $i++) {
            $month = Carbon::now()->subMonths($i);
            $expenses[] = SupplierInvoice::where('status', SupplierInvoiceEnum::APPROVED->value)
                ->whereMonth('invoice_date', $month->month)
                ->whereYear('invoice_date', $month->year)
                ->sum('grand_total') / 1000; // Convert to thousands
        }
        return array_reverse($expenses);
    }

    private function getWeeklyLabels()
    {
        return ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
    }

    private function getWeeklyRevenueData()
    {
        $data = [];
        for ($i = 0; $i < 4; $i++) {
            $startDate = Carbon::now()->startOfMonth()->addWeeks($i);
            $endDate = $startDate->copy()->endOfWeek();

            if ($endDate->month != Carbon::now()->month) {
                $endDate = Carbon::now()->endOfMonth();
            }

            $data[] = CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->sum('grand_total') / 1000; // Convert to thousands
        }
        return $data;
    }

    private function getMaterialCost()
    {
        return SupplierInvoice::where('status', SupplierInvoiceEnum::APPROVED->value)
            ->whereMonth('invoice_date', Carbon::now()->month)
            /*->where('category', 'material')*/
            ->sum('grand_total');
    }

    private function getLabourCost()
    {
        return SupplierInvoice::where('status', SupplierInvoiceEnum::APPROVED->value)
            ->whereMonth('invoice_date', Carbon::now()->month)
            /*->where('category', 'labour')*/
            ->sum('grand_total');
    }

    private function getTransportCost()
    {
        return SupplierInvoice::where('status', SupplierInvoiceEnum::APPROVED->value)
            ->whereMonth('invoice_date', Carbon::now()->month)
            /*->where('category', 'transport')*/
            ->sum('grand_total');
    }

    private function getTotalCost()
    {
        return $this->getMaterialCost() + $this->getLabourCost() + $this->getTransportCost();
    }

    private function getMaterialPercent()
    {
        $totalCost = $this->getTotalCost();
        return $totalCost > 0 ? round(($this->getMaterialCost() / $totalCost) * 100) : 0;
    }

    private function getLabourPercent()
    {
        $totalCost = $this->getTotalCost();
        return $totalCost > 0 ? round(($this->getLabourCost() / $totalCost) * 100) : 0;
    }

    private function getTransportPercent()
    {
        $totalCost = $this->getTotalCost();
        return $totalCost > 0 ? round(($this->getTransportCost() / $totalCost) * 100) : 0;
    }

    private function getDailyRevenueData()
    {
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->subDays($i);
            $data[] = CustomerInvoice::where('status', CustomerInvoiceEnum::APPROVED->value)
                ->whereDate('invoice_date', $date)
                ->sum('grand_total') / 1000; // Convert to thousands
        }
        return array_reverse($data);
    }

    private function getCurrentMonthCollected()
    {
        return (float) Collection::where('status', CollectionEnum::APPROVED->value)
            ->whereMonth('collection_date', Carbon::now()->month)
            ->whereYear('collection_date', Carbon::now()->year)
            ->sum('grand_total');
    }

    private function getCurrentMonthPending()
    {
        return max(0, $this->getCurrentMonthSales() - $this->getCurrentMonthCollected());
    }
}
