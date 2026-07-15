<?php

namespace App\Livewire\Report\Job;

use App\Exports\ReportTableExport;
use App\Models\Job\Job;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class JobReport extends Component
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
        $child = new JobReportTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->search = $this->search;
        $child->status = $this->status;
        $data = $child->getJobReportData();

        $columns = ['Job No', 'Date', 'Customer', 'Activity', 'AWB/MBL', 'HBL/HAWB', 'POL', 'POD', 'Status'];

        $rows = [];
        foreach ($data['jobs'] as $job) {
            $rows[] = [
                $job->row_no,
                \Carbon\Carbon::parse($job->posted_at)->format('d M Y'),
                $job->customer->name ?? 'N/A',
                $job->activity->name ?? 'N/A',
                $job->awb_no ?? '',
                $job->hbl_no ?? '',
                $job->pol ?? '',
                $job->pod ?? '',
                ucfirst($job->status ?? ''),
            ];
        }

        $totalsRow = ['', '', '', '', '', '', '', '', 'Total: ' . count($data['jobs']) . ' job(s)'];

        $meta = [
            'title' => 'JOB REPORT',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
        ];

        $filename = 'JobReport-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $query = Job::where('company_id', $companyId)
            ->whereBetween('posted_at', [$this->startDate, $this->endDate . ' 23:59:59'])
            ->whereNull('deleted_at');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('row_no', 'like', '%' . $this->search . '%')
                  ->orWhere('awb_number', 'like', '%' . $this->search . '%')
                  ->orWhere('hbl_number', 'like', '%' . $this->search . '%')
                  ->orWhere('shipper', 'like', '%' . $this->search . '%')
                  ->orWhere('consignee', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        $all = $query->get();

        $summary = [
            'total'      => $all->count(),
            'active'     => $all->where('status', 'active')->count(),
            'completed'  => $all->where('status', 'completed')->count(),
            'draft'      => $all->where('status', 'draft')->count(),
            'cancelled'  => $all->where('status', 'cancelled')->count(),
        ];

        return view('livewire.report.job.job-report', [
            'summary' => $summary,
        ]);
    }
}
