<?php

namespace App\Livewire\Report\Operation;

use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use Livewire\Component;

class CustomerActivityReport extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->endOfMonth()->format('Y-m-d');
    }

    public function applyFilter()
    {
        // Triggers re-render
    }

    public function resetFilter()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->endOfMonth()->format('Y-m-d');
        $this->search    = '';
    }

    public function render()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $customers = Customer::where('company_id', $companyId);

        if (!empty($this->search)) {
            $customers->where(function ($q) {
                $q->where('name_en', 'like', '%' . $this->search . '%')
                  ->orWhere('name_ar', 'like', '%' . $this->search . '%');
            });
        }

        $customers = $customers->orderBy('name_en')->get();

        $rows   = [];
        $totals = [
            'total_customers' => 0,
            'total_jobs'      => 0,
            'total_revenue'   => 0,
            'total_cost'      => 0,
            'total_profit'    => 0,
        ];

        foreach ($customers as $customer) {
            $jobs = Job::where('company_id', $companyId)
                ->where('customer_id', $customer->id)
                ->whereBetween('job_date', [$this->startDate, $this->endDate])
                ->whereNull('deleted_at')
                ->get();

            if ($jobs->isEmpty()) {
                continue;
            }

            $jobIds    = $jobs->pluck('id');
            $jobCount  = $jobs->count();
            $active    = $jobs->where('status', 'active')->count();
            $completed = $jobs->where('status', 'completed')->count();
            $draft     = $jobs->where('status', 'draft')->count();

            $revenue = (float) CustomerInvoice::whereIn('job_id', $jobIds)
                ->where('company_id', $companyId)
                ->sum('grand_total');

            $cost = (float) SupplierInvoice::whereIn('job_id', $jobIds)
                ->where('company_id', $companyId)
                ->sum('grand_total');

            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            $rows[] = [
                'customer'    => $customer,
                'job_count'   => $jobCount,
                'active'      => $active,
                'completed'   => $completed,
                'draft'       => $draft,
                'revenue'     => $revenue,
                'cost'        => $cost,
                'profit'      => $profit,
                'margin'      => $margin,
            ];

            $totals['total_customers']++;
            $totals['total_jobs']    += $jobCount;
            $totals['total_revenue'] += $revenue;
            $totals['total_cost']    += $cost;
            $totals['total_profit']  += $profit;
        }

        return view('livewire.report.operation.customer-activity-report', [
            'rows'   => $rows,
            'totals' => $totals,
        ]);
    }
}
