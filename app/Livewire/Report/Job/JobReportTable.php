<?php

namespace App\Livewire\Report\Job;

use App\Models\Job\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class JobReportTable extends Component
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

    public function getJobReportData()
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

        return [
            'jobs' => $jobs
        ];
    }

    public function render()
    {
        $jobReportData = $this->getJobReportData();

        return view('livewire.report.job.job-report-table', [
            'jobReportData' => $jobReportData
        ]);
    }
}
