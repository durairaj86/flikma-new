<?php

namespace App\Livewire\Report\Job;

use App\Exports\ReportTableExport;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Job\Job;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class JobIncomeReport extends Component
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
        $child = new JobIncomeReportTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->search = $this->search;
        $child->status = $this->status;
        $data = $child->getJobIncomeReportData();

        $columns = ['Job No', 'Customer', 'Activity', 'Invoices', 'Approved Income', 'Draft Income', 'Job Total', 'Status'];

        $rows = [];
        foreach ($data['jobs'] as $job) {
            $rows[] = [
                $job['job_number'],
                $job['customer'],
                $job['activity'],
                $job['invoice_count'],
                (float) $job['approved_income'],
                (float) $job['draft_income'],
                (float) $job['total_income'],
                ucfirst($job['status'] ?: ''),
            ];
        }

        $totalsRow = ['', '', 'GRAND TOTAL', '', '', '', (float) $data['total_income'], ''];

        $meta = [
            'title' => 'JOB INCOME REPORT',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 4,
        ];

        $filename = 'JobIncomeReport-' . $this->startDate . '-' . $this->endDate . '.xlsx';

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

        $jobIds = $jobQuery->pluck('id');

        $invoices = CustomerInvoice::whereIn('job_id', $jobIds)->get();

        $totalJobs  = $invoices->pluck('job_id')->unique()->count();
        $totalIncome = $invoices->sum('grand_total');
        $approvedIncome = $invoices->where('status', 3)->sum('grand_total');
        $draftIncome    = $invoices->where('status', 1)->sum('grand_total');

        $summary = [
            'total_jobs'      => $totalJobs,
            'total_income'    => $totalIncome,
            'approved_income' => $approvedIncome,
            'draft_income'    => $draftIncome,
        ];

        return view('livewire.report.job.job-income-report', [
            'summary' => $summary,
        ]);
    }
}
