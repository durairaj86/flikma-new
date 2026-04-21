<?php

namespace App\Livewire\Report\Job;

use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoiceSub;
use App\Models\Job\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class JobIncomeReportTable extends Component
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

    public function getJobIncomeReportData()
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

        $jobIncomeData = [];
        $totalIncome = 0;

        foreach ($jobs as $job) {
            // Get customer invoices (income) for this job
            $customerInvoices = CustomerInvoice::where('job_id', $job->id)
                ->where('status', 'approved')
                ->with('customerInvoiceSubs.description')
                ->get();

            // Skip jobs with no invoices
            if ($customerInvoices->isEmpty()) {
                continue;
            }

            $invoiceDetails = [];
            $jobTotalIncome = 0;

            foreach ($customerInvoices as $invoice) {
                $invoiceTotal = 0;

                foreach ($invoice->customerInvoiceSubs as $sub) {
                    $amount = $sub->amount ?? 0;
                    $invoiceTotal += $amount;

                    $invoiceDetails[] = [
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date,
                        'description' => $sub->description->name ?? 'N/A',
                        'amount' => $amount
                    ];
                }

                $jobTotalIncome += $invoiceTotal;
            }

            // Add to total income
            $totalIncome += $jobTotalIncome;

            // Only include jobs with income
            if ($jobTotalIncome > 0) {
                $jobIncomeData[] = [
                    'job_number' => $job->row_no,
                    'job_date' => $job->posted_at,
                    'customer' => $job->customer->name ?? 'N/A',
                    'activity' => $job->activity->name ?? 'N/A',
                    'total_income' => $jobTotalIncome,
                    'invoice_details' => $invoiceDetails,
                    'status' => $job->status
                ];
            }
        }

        return [
            'jobs' => $jobIncomeData,
            'total_income' => $totalIncome
        ];
    }

    public function render()
    {
        $jobIncomeReportData = $this->getJobIncomeReportData();

        return view('livewire.report.job.job-income-report-table', [
            'jobIncomeReportData' => $jobIncomeReportData
        ]);
    }
}
