<?php

namespace App\Livewire\Report\Job;

use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Job\Job;
use Livewire\Component;

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
