<?php

namespace App\Livewire\Report\Job;

use Livewire\Component;
use App\Exports\ReportTableExport;
use App\Models\Job\Job;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Finance\ProformaInvoice\ProformaInvoice;
use App\Models\Finance\Expense\Expense;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProvisionalReport extends Component
{
    public $startDate;
    public $endDate;
    public $search       = '';
    public $shipmentMode = '';
    public $shipmentType = '';
    public $viewMode     = 'job'; // 'job' or 'activity'

    public function mount()
    {
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');
    }

    public function applyFilter()
    {
        // Triggers re-render
    }

    public function resetFilter()
    {
        $this->startDate    = now()->startOfYear()->format('Y-m-d');
        $this->endDate      = now()->format('Y-m-d');
        $this->search       = '';
        $this->shipmentMode = '';
        $this->shipmentType = '';
    }

    protected function getReportData()
    {
        $companyId = auth()->user()->company_id ?? 1;

        // jobs.job_date/job_no are stale legacy columns only populated on a
        // handful of old records — every job created through the current
        // flow uses posted_at/row_no instead (same columns the sibling Job
        // Income/Balance reports already use).
        $query = Job::where('company_id', $companyId)
            ->whereBetween(DB::raw('DATE(posted_at)'), [$this->startDate, $this->endDate])
            ->whereNull('deleted_at');

        if (!empty($this->search)) {
            $query->where('row_no', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->shipmentMode)) {
            $query->where('shipment_mode', $this->shipmentMode);
        }

        if (!empty($this->shipmentType)) {
            $query->where('shipment_type', $this->shipmentType);
        }

        $jobs = $query->orderBy('posted_at', 'desc')->get();

        $rows   = [];
        $totals = [
            'provisional_cost'  => 0,
            'actual_cost'       => 0,
            'provisional_sales' => 0,
            'actual_sales'      => 0,
            'profit_loss'       => 0,
            'margin'            => 0,
        ];

        if ($this->viewMode === 'activity') {
            // Group by shipment_mode (activity)
            $groups = [];

            foreach ($jobs as $job) {
                $activity = $job->shipment_mode ?: 'Unspecified';

                $provisionalSales = (float) ProformaInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('grand_total');
                $actualSales      = (float) CustomerInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('grand_total');
                $provisionalCost  = (float) Expense::where('job_id', $job->id)->where('company_id', $companyId)->sum('grand_total');
                $actualCost       = (float) SupplierInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('grand_total');

                if (!isset($groups[$activity])) {
                    $groups[$activity] = [
                        'activity'          => $activity,
                        'job_count'         => 0,
                        'provisional_cost'  => 0,
                        'actual_cost'       => 0,
                        'provisional_sales' => 0,
                        'actual_sales'      => 0,
                    ];
                }

                $groups[$activity]['job_count']++;
                $groups[$activity]['provisional_cost']  += $provisionalCost;
                $groups[$activity]['actual_cost']        += $actualCost;
                $groups[$activity]['provisional_sales']  += $provisionalSales;
                $groups[$activity]['actual_sales']       += $actualSales;
            }

            foreach ($groups as $key => $g) {
                $salesForCalc = $g['actual_sales'] > 0 ? $g['actual_sales'] : $g['provisional_sales'];
                $costForCalc  = $g['actual_cost']  > 0 ? $g['actual_cost']  : $g['provisional_cost'];
                $profitLoss   = $salesForCalc - $costForCalc;
                $margin       = $salesForCalc > 0 ? ($profitLoss / $salesForCalc) * 100 : 0;

                $rows[] = [
                    'activity'          => $g['activity'],
                    'job_count'         => $g['job_count'],
                    'provisional_cost'  => $g['provisional_cost'],
                    'actual_cost'       => $g['actual_cost'],
                    'provisional_sales' => $g['provisional_sales'],
                    'actual_sales'      => $g['actual_sales'],
                    'profit_loss'       => $profitLoss,
                    'margin'            => $margin,
                ];

                $totals['provisional_cost']  += $g['provisional_cost'];
                $totals['actual_cost']        += $g['actual_cost'];
                $totals['provisional_sales']  += $g['provisional_sales'];
                $totals['actual_sales']       += $g['actual_sales'];
                $totals['profit_loss']        += $profitLoss;
            }

        } else {
            // Job-based (original logic)
            foreach ($jobs as $job) {
                $proformaInvoices  = ProformaInvoice::where('job_id', $job->id)->where('company_id', $companyId)->get();
                $customerInvoices  = CustomerInvoice::where('job_id', $job->id)->where('company_id', $companyId)->get();
                $expenses          = Expense::where('job_id', $job->id)->where('company_id', $companyId)->get();
                $supplierInvoices  = SupplierInvoice::where('job_id', $job->id)->where('company_id', $companyId)->get();

                $provisionalSales = (float) $proformaInvoices->sum('grand_total');
                $actualSales      = (float) $customerInvoices->sum('grand_total');
                $provisionalCost  = (float) $expenses->sum('grand_total');
                $actualCost       = (float) $supplierInvoices->sum('grand_total');

                $salesForCalc = $actualSales > 0 ? $actualSales : $provisionalSales;
                $costForCalc  = $actualCost  > 0 ? $actualCost  : $provisionalCost;
                $profitLoss   = $salesForCalc - $costForCalc;
                $margin       = $salesForCalc > 0 ? ($profitLoss / $salesForCalc) * 100 : 0;

                // Line-item breakdown for the details toggle — one entry per
                // underlying document that contributed to this job's totals.
                // CustomerInvoice/SupplierInvoice use invoice_date, not
                // posted_at (which is always null on those two tables) —
                // ProformaInvoice/Expense use posted_at.
                $details = [];
                foreach ($proformaInvoices as $doc) {
                    $details[] = ['type' => 'Provisional Sale', 'row_no' => $doc->row_no, 'date' => $doc->posted_at, 'amount' => (float) $doc->grand_total];
                }
                foreach ($customerInvoices as $doc) {
                    $details[] = ['type' => 'Actual Sale', 'row_no' => $doc->row_no, 'date' => $doc->invoice_date, 'amount' => (float) $doc->grand_total];
                }
                foreach ($expenses as $doc) {
                    $details[] = ['type' => 'Provisional Cost', 'row_no' => $doc->row_no, 'date' => $doc->posted_at, 'amount' => (float) $doc->grand_total];
                }
                foreach ($supplierInvoices as $doc) {
                    $details[] = ['type' => 'Actual Cost', 'row_no' => $doc->row_no, 'date' => $doc->invoice_date, 'amount' => (float) $doc->grand_total];
                }

                $rows[] = [
                    'job'               => $job,
                    'provisional_cost'  => $provisionalCost,
                    'actual_cost'       => $actualCost,
                    'provisional_sales' => $provisionalSales,
                    'actual_sales'      => $actualSales,
                    'profit_loss'       => $profitLoss,
                    'margin'            => $margin,
                    'details'           => $details,
                ];

                $totals['provisional_cost']  += $provisionalCost;
                $totals['actual_cost']        += $actualCost;
                $totals['provisional_sales']  += $provisionalSales;
                $totals['actual_sales']       += $actualSales;
                $totals['profit_loss']        += $profitLoss;
            }
        }

        // Overall margin
        $totalSales       = $totals['actual_sales'] > 0 ? $totals['actual_sales'] : $totals['provisional_sales'];
        $totals['margin'] = $totalSales > 0 ? ($totals['profit_loss'] / $totalSales) * 100 : 0;

        $modes = Job::where('company_id', $companyId)->whereNotNull('shipment_mode')->where('shipment_mode', '!=', '')->distinct()->pluck('shipment_mode');
        $types = Job::where('company_id', $companyId)->whereNotNull('shipment_type')->where('shipment_type', '!=', '')->distinct()->pluck('shipment_type');

        return view('livewire.report.job.provisional-report', [
            'rows'   => $rows,
            'totals' => $totals,
            'modes'  => $modes,
            'types'  => $types,
        ]);
    }
}
