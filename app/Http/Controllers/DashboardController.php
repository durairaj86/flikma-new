<?php

namespace App\Http\Controllers;

use App\Enums\CollectionEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Enums\JobEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Models\Finance\Payment\Payment;
use App\Models\Job\Job;
use App\Models\Quotation\Quotation;
use App\Enums\PaymentEnum;
use App\Enums\QuotationEnum;
use App\Models\Customer\Customer;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'summaryCards' => $this->getSummaryCards($request->query('month')),
            'summaryMonths' => $this->getSummaryMonths(),
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
            'activeJobs' => $this->getActiveJobs(),
            'completedJobsThisMonth' => $this->getCompletedJobsThisMonth(),

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

    private function getActiveJobs()
    {
        return Job::where('status', JobEnum::PENDING->value)->count();
    }

    private function getCompletedJobsThisMonth()
    {
        return Job::where('status', JobEnum::COMPLETED->value)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
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

    private function getSummaryMonths(): array
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $months[$m->format('Y-m')] = $m->format('M Y');
        }
        return $months;
    }

    /**
     * Month-scoped totals for the Quotation / Final Invoice / Payments /
     * Collection cards. $month is "YYYY-MM"; anything else means this month.
     */
    private function getSummaryCards(?string $month): array
    {
        $start = ($month && preg_match('/^\d{4}-\d{2}$/', $month))
            ? Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay()
            : Carbon::now()->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $prevStart = $start->copy()->subMonth()->startOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        $change = fn ($cur, $prev) => $prev > 0 ? round((($cur - $prev) / $prev) * 100, 2) : ($cur > 0 ? 100.0 : 0.0);
        $range = fn ($q, $col, $a, $b) => $q->whereBetween($col, [$a->toDateString(), $b->toDateString()]);

        // Quotations
        $qBase = fn ($a, $b) => $range(Quotation::query(), 'posted_at', $a, $b);
        $qCount = $qBase($start, $end)->count();
        $qTotal = (float) $qBase($start, $end)->sum('grand_total');
        $qPrev = (float) $qBase($prevStart, $prevEnd)->sum('grand_total');

        // Final invoices (customer invoices; cancelled/rejected excluded)
        $live = [CustomerInvoiceEnum::DRAFT->value, CustomerInvoiceEnum::APPROVED->value];
        $iBase = fn ($a, $b) => $range(CustomerInvoice::query()->whereIn('status', $live), 'invoice_date', $a, $b);
        $iTotal = (float) $iBase($start, $end)->sum('grand_total');
        $iPrev = (float) $iBase($prevStart, $prevEnd)->sum('grand_total');
        $iApproved = (float) $iBase($start, $end)->where('status', CustomerInvoiceEnum::APPROVED->value)->sum('grand_total');
        $iDraft = (float) $iBase($start, $end)->where('status', CustomerInvoiceEnum::DRAFT->value)->sum('grand_total');

        // Payments / Collections
        $money = function ($model, $col, $draft, $approved) use ($start, $end, $prevStart, $prevEnd, $range) {
            $base = fn ($a, $b) => $range($model::query()->whereIn('status', [$draft, $approved]), $col, $a, $b);
            return [
                'total' => (float) $base($start, $end)->sum('base_grand_total'),
                'prev' => (float) $base($prevStart, $prevEnd)->sum('base_grand_total'),
                'approved' => (float) $base($start, $end)->where('status', $approved)->sum('base_grand_total'),
                'draft' => (float) $base($start, $end)->where('status', $draft)->sum('base_grand_total'),
                'count' => $base($start, $end)->count(),
            ];
        };
        $pay = $money(Payment::class, 'payment_date', PaymentEnum::DRAFT->value, PaymentEnum::APPROVED->value);
        $col = $money(Collection::class, 'collection_date', CollectionEnum::DRAFT->value, CollectionEnum::APPROVED->value);

        // Total Sales / Profit (approved invoices vs approved supplier bills)
        $apprInv = fn ($a, $b) => $range(CustomerInvoice::query()->where('status', CustomerInvoiceEnum::APPROVED->value), 'invoice_date', $a, $b);
        $apprBill = fn ($a, $b) => $range(SupplierInvoice::query()->where('status', SupplierInvoiceEnum::APPROVED->value), 'invoice_date', $a, $b);
        $sales = (float) $apprInv($start, $end)->sum('grand_total');
        $salesPrev = (float) $apprInv($prevStart, $prevEnd)->sum('grand_total');
        $expenses = (float) $apprBill($start, $end)->sum('grand_total');
        $expensesPrev = (float) $apprBill($prevStart, $prevEnd)->sum('grand_total');
        $profit = $sales - $expenses;
        $profitPrev = $salesPrev - $expensesPrev;

        $custMonth = fn ($a, $b) => Customer::query()->whereBetween('created_at', [$a->copy()->startOfDay(), $b->copy()->endOfDay()])->count();
        $newCust = $custMonth($start, $end);
        $newCustPrev = $custMonth($prevStart, $prevEnd);

        // Daily series for the selected month (medium widget charts)
        $days = $start->daysInMonth;
        $series = fn () => array_fill(1, $days, 0.0);
        $fill = function (array $arr, $rows, string $dateCol, string $valCol = null) use ($days) {
            foreach ($rows as $r) {
                $d = (int) Carbon::parse($r->{$dateCol})->format('j');
                if ($d >= 1 && $d <= $days) {
                    $arr[$d] += $valCol ? (float) $r->{$valCol} : 1;
                }
            }
            return array_values($arr);
        };
        $salesDaily = $fill($series(), $apprInv($start, $end)->get(['invoice_date', 'grand_total']), 'invoice_date', 'grand_total');
        $billsDaily = $fill($series(), $apprBill($start, $end)->get(['invoice_date', 'grand_total']), 'invoice_date', 'grand_total');
        $profitDaily = array_map(fn ($a, $b) => round($a - $b, 2), $salesDaily, $billsDaily);
        $custDaily = $fill($series(), Customer::query()->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])->get(['created_at']), 'created_at');

        return [
            'month' => $start->format('Y-m'),
            'series' => [
                'labels' => range(1, $days),
                'sales' => $salesDaily,
                'profit' => $profitDaily,
                'customers' => $custDaily,
            ],
            'sales' => [
                'total' => $sales, 'count' => $apprInv($start, $end)->count(), 'change' => $change($sales, $salesPrev),
                'collected' => $col['approved'], 'pending' => max(0, $sales - $col['approved']),
            ],
            'invoices' => [
                'count' => $iBase($start, $end)->count(), 'change' => $change($iBase($start, $end)->count(), $iBase($prevStart, $prevEnd)->count()),
                'approved' => $iBase($start, $end)->where('status', CustomerInvoiceEnum::APPROVED->value)->count(),
                'draft' => $iBase($start, $end)->where('status', CustomerInvoiceEnum::DRAFT->value)->count(),
            ],
            'customers' => [
                'total' => Customer::count(), 'new' => $newCust, 'change' => $change($newCust, $newCustPrev), 'prevNew' => $newCustPrev,
            ],
            'profit' => [
                'total' => $profit, 'change' => $change($profit, $profitPrev),
                'margin' => $sales > 0 ? round($profit / $sales * 100, 1) : 0,
                'revenue' => $sales, 'expenses' => $expenses,
            ],
            'quotation' => [
                'total' => $qTotal, 'count' => $qCount, 'change' => $change($qTotal, $qPrev),
                'completed' => $qBase($start, $end)->where('status', QuotationEnum::CONVERTED->value)->count(),
                'approved' => $qBase($start, $end)->where('status', QuotationEnum::ACCEPTED->value)->count(),
            ],
            'invoice' => [
                'total' => $iTotal, 'count' => $iBase($start, $end)->count(), 'change' => $change($iTotal, $iPrev),
                'approved' => $iApproved, 'draft' => $iDraft,
            ],
            'payment' => $pay + ['percent' => $pay['total'] > 0 ? round($pay['approved'] / $pay['total'] * 100) : 0],
            'collection' => $col + ['percent' => $col['total'] > 0 ? round($col['approved'] / $col['total'] * 100) : 0],
        ];
    }
}
