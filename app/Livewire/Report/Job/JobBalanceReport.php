<?php

namespace App\Livewire\Report\Job;

use App\Exports\ReportTableExport;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class JobBalanceReport extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $status = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function applyFilter()
    {
        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
        $this->dispatch('searchChanged', $this->search);
        $this->dispatch('statusChanged', $this->status);
    }

    public function resetFilter()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->search = '';
        $this->status = '';

        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
        $this->dispatch('searchChanged', '');
        $this->dispatch('statusChanged', '');
    }

    public function updatedStartDate($value)
    {
        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }

    public function updatedEndDate($value)
    {
        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }

    public function updatedSearch($value)
    {
        $this->dispatch('searchChanged', $value);
    }

    public function updatedStatus($value)
    {
        $this->dispatch('statusChanged', $value);
    }

    public function exportExcel()
    {
        $child = new JobBalanceReportTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->search = $this->search;
        $child->status = $this->status;
        $data = $child->getJobBalanceReportData();

        $columns = ['Job No', 'Date', 'Customer', 'Activity', 'Income', 'Expense', 'Profit / Loss'];

        $rows = [];
        foreach ($data['jobs'] as $job) {
            $rows[] = [
                $job['job_number'],
                \Carbon\Carbon::parse($job['job_date'])->format('d M Y'),
                $job['customer'],
                $job['activity'],
                (float) $job['income'],
                (float) $job['expense'],
                (float) $job['profit'],
            ];
        }

        $totalsRow = ['', '', '', 'TOTAL', (float) $data['total_income'], (float) $data['total_expense'], (float) $data['total_profit']];

        $meta = [
            'title' => 'JOB BALANCE REPORT',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 5,
        ];

        $filename = 'JobBalanceReport-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $jobQuery = Job::where('company_id', $companyId)
            ->whereBetween('posted_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->whereNull('deleted_at');

        if (!empty($this->search)) {
            $jobQuery->where(function ($q) {
                $q->where('row_no', 'like', '%' . $this->search . '%')
                  ->orWhere('awb_number', 'like', '%' . $this->search . '%')
                  ->orWhere('hbl_number', 'like', '%' . $this->search . '%')
                  ->orWhere('shipper', 'like', '%' . $this->search . '%')
                  ->orWhere('consignee', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->status)) {
            $jobQuery->where('status', $this->status);
        }

        $jobs = $jobQuery->get();

        $totalIncome  = 0;
        $totalExpense = 0;
        $totalJobs    = 0;

        foreach ($jobs as $job) {
            // base_grand_total (company-currency-normalized), not grand_total
            // (the invoice's own currency) — a foreign-currency invoice would
            // otherwise silently corrupt the job's income/expense totals.
            // Credit notes reduce recognised income the same way a discount
            // would — omitting them overstates the job's income by whatever
            // was later credited back.
            $income  = (float) CustomerInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('base_grand_total')
                - (float) CreditNote::where('job_id', $job->id)->where('company_id', $companyId)->sum('base_grand_total');
            $expense = (float) SupplierInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('base_grand_total');

            if ($income > 0 || $expense > 0) {
                $totalJobs++;
            }

            $totalIncome  += $income;
            $totalExpense += $expense;
        }

        $profitLoss = $totalIncome - $totalExpense;
        $margin     = $totalIncome > 0 ? ($profitLoss / $totalIncome) * 100 : 0;

        $summary = [
            'total_jobs'    => $totalJobs,
            'total_income'  => $totalIncome,
            'total_expense' => $totalExpense,
            'profit_loss'   => $profitLoss,
            'margin'        => $margin,
        ];

        return view('livewire.report.job.job-balance-report', [
            'summary' => $summary,
        ]);
    }
}
