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
        $data = $this->dashboardData();

        $data['widgetOrder'] = DashboardLayoutController::orderFor(auth()->id());
        $data['widgetSizes'] = DashboardLayoutController::sizes();
        $data['widgetCatalog'] = DashboardLayoutController::catalog();

        return view('dashboard', $data);
    }

    /** Renders a single widget cell (used when a widget is added from the panel without reloading the page). */
    public function widget(string $key): \Illuminate\Http\JsonResponse
    {
        abort_unless(isset(DashboardLayoutController::WIDGETS[$key]), 404);

        $data = $this->dashboardData() + ['key' => $key, 'widgetSizes' => DashboardLayoutController::sizes()];

        return response()->json([
            'html' => view('dashboard._cell', $data)->render(),
            'size' => DashboardLayoutController::WIDGETS[$key]['size'],
        ]);
    }

    private function dashboardData(): array
    {
        return [
            'shipmentSeries' => $this->getShipmentSeries(),
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
            'etaEtdList' => $this->getShipmentList(['eta' => 'ETA', 'etd' => 'ETD'], Carbon::today(), Carbon::today()->addDays(6)->endOfDay(), 'asc'),
            'ataAtdList' => $this->getShipmentList(['ata' => 'ATA', 'atd' => 'ATD'], Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), 'desc'),
            'shipmentExtra' => $this->getShipmentExtra(),
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

    /** Jobs with any of the given date columns inside the range, one row per (job, event), for the scrollable large widgets. */
    private function getShipmentList(array $columns, Carbon $from, Carbon $to, string $dir): array
    {
        $rows = [];
        Job::with('customer:id,name_en')
            ->where(function ($q) use ($columns, $from, $to) {
                foreach (array_keys($columns) as $col) {
                    $q->orWhereBetween($col, [$from, $to]);
                }
            })
            ->limit(100)
            ->get(['id', 'job_no', 'row_no', 'customer_id', 'origin', 'destination', 'pol', 'pod', 'shipment_mode', ...array_keys($columns)])
            ->each(function ($job) use ($columns, $from, $to, &$rows) {
                foreach ($columns as $col => $label) {
                    $date = $job->{$col} ? Carbon::parse($job->{$col}) : null;
                    if ($date && $date->between($from, $to)) {
                        $rows[] = [
                            'job' => $job->row_no ?: $job->job_no,
                            'customer' => $job->customer->name_en ?? '-',
                            'route' => trim(($job->pol ?: $job->origin ?: '') . ' → ' . ($job->pod ?: $job->destination ?: ''), ' →') ?: '-',
                            'mode' => strtolower((string) ($job->shipment_mode ?: '')),
                            'event' => $label,
                            'date' => $date->format('d M'),
                            'ts' => $date->timestamp,
                        ];
                    }
                }
            });
        usort($rows, fn ($a, $b) => $dir === 'asc' ? $a['ts'] <=> $b['ts'] : $b['ts'] <=> $a['ts']);

        return $rows;
    }

    /** Extra shipment numbers that fill the large ETA/ETD and ATA/ATD widgets. */
    private function getShipmentExtra(): array
    {
        $today = Carbon::today();
        $pending = fn () => Job::where('status', JobEnum::PENDING->value);
        $week = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
        $arrived = Job::whereBetween('ata', $week)->get(['eta', 'ata']);
        $onTime = $arrived->filter(fn ($j) => !$j->eta || Carbon::parse($j->ata)->startOfDay()->lte(Carbon::parse($j->eta)->startOfDay()))->count();

        return [
            'overdueEta' => $pending()->whereDate('eta', '<', $today)->whereNull('ata')->count(),
            'lateDeparture' => $pending()->whereDate('etd', '<', $today)->whereNull('atd')->count(),
            'onTime' => $onTime,
            'late' => $arrived->count() - $onTime,
            'departedWeek' => Job::whereBetween('atd', $week)->count(),
        ];
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
            ->take(10)
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

    /**
     * Per-day job counts for the ETA/ETD widget (today + next 6 days) and the
     * ATA/ATD widget (Monday to Sunday of the current week).
     */
    private function getShipmentSeries(): array
    {
        $count = function (string $col, Carbon $from, int $days) {
            $rows = Job::whereBetween($col, [$from->copy()->startOfDay(), $from->copy()->addDays($days - 1)->endOfDay()])->get([$col]);
            $out = array_fill(0, $days, 0);
            foreach ($rows as $r) {
                $i = (int) $from->copy()->startOfDay()->diffInDays(Carbon::parse($r->{$col})->startOfDay());
                if ($i >= 0 && $i < $days) {
                    $out[$i]++;
                }
            }
            return $out;
        };
        $today = Carbon::today();
        $week = Carbon::now()->startOfWeek();
        $dayLabels = fn (Carbon $from, int $n) => collect(range(0, $n - 1))->map(fn ($i) => $from->copy()->addDays($i)->format('D'))->all();

        return [
            'upcomingLabels' => $dayLabels($today, 7),
            'eta' => $count('eta', $today, 7),
            'etd' => $count('etd', $today, 7),
            'weekLabels' => $dayLabels($week, 7),
            'ata' => $count('ata', $week, 7),
            'atd' => $count('atd', $week, 7),
        ];
    }
}
