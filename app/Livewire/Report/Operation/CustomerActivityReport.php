<?php

namespace App\Livewire\Report\Operation;

use App\Exports\ReportTableExport;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

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

    protected function getReportData()
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
            // jobs.job_date is a stale legacy column only populated on a
            // handful of old records — real jobs use posted_at (same fix
            // already applied to the Job Report / Provisional Report).
            $jobs = Job::where('company_id', $companyId)
                ->where('customer_id', $customer->id)
                ->whereBetween(DB::raw('DATE(posted_at)'), [$this->startDate, $this->endDate])
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

        return [$rows, $totals];
    }

    public function exportExcel()
    {
        [$rows, $totals] = $this->getReportData();

        $columns = ['Customer', 'Code', 'Jobs', 'Active', 'Completed', 'Draft', 'Revenue', 'Cost', 'Profit', 'Margin %'];

        $exportRows = [];
        foreach ($rows as $row) {
            $exportRows[] = [
                $row['customer']->name_en, $row['customer']->row_no,
                $row['job_count'], $row['active'], $row['completed'], $row['draft'],
                (float) $row['revenue'], (float) $row['cost'], (float) $row['profit'], round($row['margin'], 1),
            ];
        }

        $totalsRow = [
            '', 'TOTAL', $totals['total_jobs'], '', '', '',
            (float) $totals['total_revenue'], (float) $totals['total_cost'], (float) $totals['total_profit'],
            $totals['total_revenue'] > 0 ? round(($totals['total_profit'] / $totals['total_revenue']) * 100, 1) : 0,
        ];

        $meta = [
            'title' => 'CUSTOMER ACTIVITY REPORT',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Total Customers: ' . $totals['total_customers'],
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 7,
        ];

        $filename = 'CustomerActivityReport-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($exportRows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        [$rows, $totals] = $this->getReportData();

        return view('livewire.report.operation.customer-activity-report', [
            'rows'   => $rows,
            'totals' => $totals,
        ]);
    }
}
