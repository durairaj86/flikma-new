<?php

namespace App\Livewire\Report\Job;

use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use Livewire\Component;

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
            $income  = (float) CustomerInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('grand_total');
            $expense = (float) SupplierInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('grand_total');

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
