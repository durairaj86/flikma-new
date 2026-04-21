<?php

namespace App\Livewire\Report\Job;

use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class JobBalanceReportTable extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $status = '';

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'searchChanged' => 'updateSearch',
        'statusChanged' => 'updateStatus'
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

    }

    public function updateDateRange($dateRange)
    {
        $this->startDate = $dateRange['startDate'];
        $this->endDate = $dateRange['endDate'];
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function updateStatus($status)
    {
        $this->status = $status;
    }

    public function getJobBalanceReportData()
    {
        // Get jobs within date range
        $jobs = Job::whereBetween(DB::raw('DATE(posted_at)'), [$this->startDate, $this->endDate]);

        // Apply search filter if provided
        if (!empty($this->search)) {
            $jobs = $jobs->where(function ($query) {
                $query->where('row_no', 'like', '%' . $this->search . '%')
                    ->orWhere('awb_number', 'like', '%' . $this->search . '%')
                    ->orWhere('hbl_number', 'like', '%' . $this->search . '%')
                    ->orWhere('shipper', 'like', '%' . $this->search . '%')
                    ->orWhere('consignee', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Apply status filter if provided
        if (!empty($this->status)) {
            $jobs = $jobs->where('status', $this->status);
        }

        // Get jobs with related data
        $jobs = $jobs->with(['customer', 'activity'])
            ->orderBy('posted_at', 'desc')
            ->get();

        $jobBalanceData = [];
        $totalIncome = 0;
        $totalExpense = 0;
        $totalProfit = 0;

        foreach ($jobs as $job) {
            // Get customer invoices (income) for this job
            $customerInvoices = CustomerInvoice::where('job_id', $job->id)
                ->where('status', 'approved')
                ->with('customerInvoiceSubs')
                ->get();

            // Get supplier invoices (expenses) for this job
            $supplierInvoices = SupplierInvoice::where('job_id', $job->id)
                ->where('status', 'approved')
                ->with('supplierInvoiceSubs')
                ->get();

            // Calculate total income
            $income = $customerInvoices->sum('total_amount') ?? 0;

            // Calculate total expense
            $expense = $supplierInvoices->sum('total_amount') ?? 0;

            // Calculate profit
            $profit = $income - $expense;

            // Add to totals
            $totalIncome += $income;
            $totalExpense += $expense;
            $totalProfit += $profit;

            // Only include jobs with financial activity
            if ($income > 0 || $expense > 0) {
                $jobBalanceData[] = [
                    'job_number' => $job->row_no,
                    'job_date' => $job->posted_at,
                    'customer' => $job->customer->name ?? 'N/A',
                    'activity' => $job->activity->name ?? 'N/A',
                    'income' => $income,
                    'expense' => $expense,
                    'profit' => $profit,
                    'status' => $job->status
                ];
            }
        }

        return [
            'jobs' => $jobBalanceData,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'total_profit' => $totalProfit
        ];
    }

    public function render()
    {
        $jobBalanceReportData = $this->getJobBalanceReportData();

        return view('livewire.report.job.job-balance-report-table', [
            'jobBalanceReportData' => $jobBalanceReportData
        ]);
    }
}
