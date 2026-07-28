<?php

namespace App\Livewire\Report\Job;

use App\Models\Finance\Adjustment\CreditNote;
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
        $companyId = auth()->user()->company_id ?? 1;
        $jobs = Job::where('company_id', $companyId)
            ->whereBetween(DB::raw('DATE(posted_at)'), [$this->startDate, $this->endDate]);

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
            // Get all customer invoices (income) for this job — not filtered
            // by status, so the per-job breakdown can show approved vs draft
            // separately (matching how the summary cards split the same data).
            $customerInvoices = CustomerInvoice::where('job_id', $job->id)
                ->with('customerInvoiceSubs.description')
                ->get();

            // Skip jobs with no invoices
            if ($customerInvoices->isEmpty()) {
                continue;
            }

            // Credit notes reduce recognised income — omitting them
            // overstates income by whatever was later credited back.
            // Credited invoices are approved by the time a credit note
            // exists against them, so this comes out of approvedIncome.
            $creditedAmount = CreditNote::where('job_id', $job->id)->sum('base_grand_total');

            $invoiceDetails = [];
            $jobTotalIncome = 0;
            $approvedIncome = 0;
            $draftIncome = 0;

            foreach ($customerInvoices as $invoice) {
                $invoiceTotal = 0;

                foreach ($invoice->customerInvoiceSubs as $sub) {
                    // base_total_with_tax (company-currency-normalized), not
                    // total_with_tax (the invoice's own currency) — a
                    // foreign-currency invoice would otherwise silently
                    // corrupt this job's income totals.
                    $amount = $sub->base_total_with_tax ?? 0;
                    $invoiceTotal += $amount;

                    $invoiceDetails[] = [
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date,
                        // customer_invoice_subs has its own plain "description"
                        // text column, which shadows the description() belongsTo
                        // relation of the same name — $sub->description is
                        // already the text, not a related model.
                        'description' => $sub->description ?? 'N/A',
                        'amount' => $amount,
                        'invoice_status' => $invoice->status,
                    ];
                }

                $jobTotalIncome += $invoiceTotal;

                // 3 = approved, 1 = draft (customer_invoices.status is numeric)
                if ($invoice->status == 3) {
                    $approvedIncome += $invoiceTotal;
                } elseif ($invoice->status == 1) {
                    $draftIncome += $invoiceTotal;
                }
            }

            $jobTotalIncome -= $creditedAmount;
            $approvedIncome -= $creditedAmount;

            // Add to total income
            $totalIncome += $jobTotalIncome;

            // Only include jobs with income
            if ($jobTotalIncome > 0) {
                $jobIncomeData[] = [
                    'job_number' => $job->row_no,
                    'job_date' => $job->posted_at,
                    'customer' => $job->customer->name ?? 'N/A',
                    'activity' => $job->activity->name ?? 'N/A',
                    'invoice_count' => $customerInvoices->count(),
                    'total_income' => $jobTotalIncome,
                    'approved_income' => $approvedIncome,
                    'draft_income' => $draftIncome,
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
