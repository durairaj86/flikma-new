<?php

namespace App\Livewire\Report\Job;

use Livewire\Component;
use App\Exports\ReportTableExport;
use App\Models\Job\Job;
use App\Models\Finance\Adjustment\CreditNote;
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

                // base_grand_total / base_sub_total+base_tax_total (company-
                // currency-normalized), not grand_total (the document's own
                // currency) — a foreign-currency document would otherwise
                // silently corrupt these totals. ProformaInvoice/Expense have
                // no base_grand_total column, so it's derived from their base
                // sub/tax components instead.
                $provisionalSales = (float) ProformaInvoice::where('job_id', $job->id)->where('company_id', $companyId)->selectRaw('SUM(base_sub_total + base_tax_total) as total')->value('total');
                // Credit notes reduce recognised income — omitting them
                // overstates actual sales by whatever was later credited back.
                $actualSales      = (float) CustomerInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('base_grand_total')
                    - (float) CreditNote::where('job_id', $job->id)->where('company_id', $companyId)->sum('base_grand_total');
                $provisionalCost  = (float) Expense::where('job_id', $job->id)->where('company_id', $companyId)->selectRaw('SUM(base_sub_total + base_tax_total) as total')->value('total');
                $actualCost       = (float) SupplierInvoice::where('job_id', $job->id)->where('company_id', $companyId)->sum('base_grand_total');

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
                $creditNotes       = CreditNote::where('job_id', $job->id)->where('company_id', $companyId)->get();

                // See currency note above — same base-currency substitution.
                // Credit notes reduce recognised income — omitting them
                // overstates actual sales by whatever was later credited back.
                $provisionalSales = (float) $proformaInvoices->sum(fn($doc) => (float)$doc->base_sub_total + (float)$doc->base_tax_total);
                $actualSales      = (float) $customerInvoices->sum('base_grand_total') - (float) $creditNotes->sum('base_grand_total');
                $provisionalCost  = (float) $expenses->sum(fn($doc) => (float)$doc->base_sub_total + (float)$doc->base_tax_total);
                $actualCost       = (float) $supplierInvoices->sum('base_grand_total');

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
                    $details[] = ['type' => 'Provisional Sale', 'row_no' => $doc->row_no, 'date' => $doc->posted_at, 'amount' => (float) $doc->base_sub_total + (float) $doc->base_tax_total];
                }
                foreach ($customerInvoices as $doc) {
                    $details[] = ['type' => 'Actual Sale', 'row_no' => $doc->row_no, 'date' => $doc->invoice_date, 'amount' => (float) $doc->base_grand_total];
                }
                foreach ($creditNotes as $doc) {
                    $details[] = ['type' => 'Credit Note', 'row_no' => $doc->row_no, 'date' => $doc->posted_at, 'amount' => -(float) $doc->base_grand_total];
                }
                foreach ($expenses as $doc) {
                    $details[] = ['type' => 'Provisional Cost', 'row_no' => $doc->row_no, 'date' => $doc->posted_at, 'amount' => (float) $doc->base_sub_total + (float) $doc->base_tax_total];
                }
                foreach ($supplierInvoices as $doc) {
                    $details[] = ['type' => 'Actual Cost', 'row_no' => $doc->row_no, 'date' => $doc->invoice_date, 'amount' => (float) $doc->base_grand_total];
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

        return [$rows, $totals, $modes, $types];
    }

    public function exportExcel()
    {
        [$rows, $totals] = $this->getReportData();

        if ($this->viewMode === 'activity') {
            $columns = ['Activity', 'Jobs', 'Provisional Cost', 'Actual Cost', 'Provisional Sales', 'Actual Sales', 'Profit / Loss', 'Margin %'];
            $exportRows = [];
            foreach ($rows as $row) {
                $exportRows[] = [
                    $row['activity'], $row['job_count'],
                    (float) $row['provisional_cost'], (float) $row['actual_cost'],
                    (float) $row['provisional_sales'], (float) $row['actual_sales'],
                    (float) $row['profit_loss'], round($row['margin'], 1),
                ];
            }
            $totalsRow = [
                'TOTAL', collect($rows)->sum('job_count'),
                (float) $totals['provisional_cost'], (float) $totals['actual_cost'],
                (float) $totals['provisional_sales'], (float) $totals['actual_sales'],
                (float) $totals['profit_loss'], round($totals['margin'], 1),
            ];
            $numericFrom = 2;
        } else {
            $columns = ['Job No', 'Date', 'Mode', 'Type', 'Provisional Cost', 'Actual Cost', 'Provisional Sales', 'Actual Sales', 'Profit / Loss', 'Margin %'];
            $exportRows = [];
            foreach ($rows as $row) {
                $job = $row['job'];
                $exportRows[] = [
                    $job->row_no,
                    \Carbon\Carbon::parse($job->posted_at)->format('d M Y'),
                    $job->shipment_mode, $job->shipment_type,
                    (float) $row['provisional_cost'], (float) $row['actual_cost'],
                    (float) $row['provisional_sales'], (float) $row['actual_sales'],
                    (float) $row['profit_loss'], round($row['margin'], 1),
                ];
            }
            $totalsRow = [
                '', '', '', 'TOTAL',
                (float) $totals['provisional_cost'], (float) $totals['actual_cost'],
                (float) $totals['provisional_sales'], (float) $totals['actual_sales'],
                (float) $totals['profit_loss'], round($totals['margin'], 1),
            ];
            $numericFrom = 5;
        }

        $meta = [
            'title' => 'PROVISIONAL REPORT' . ($this->viewMode === 'activity' ? ' (BY ACTIVITY)' : ' (BY JOB)'),
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => $numericFrom,
        ];

        $filename = 'ProvisionalReport-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($exportRows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        [$rows, $totals, $modes, $types] = $this->getReportData();

        return view('livewire.report.job.provisional-report', [
            'rows'   => $rows,
            'totals' => $totals,
            'modes'  => $modes,
            'types'  => $types,
        ]);
    }
}
