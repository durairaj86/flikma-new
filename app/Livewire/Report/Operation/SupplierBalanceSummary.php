<?php

namespace App\Livewire\Report\Operation;

use App\Exports\ReportTableExport;
use App\Models\Supplier\Supplier;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SupplierBalanceSummary extends Component
{
    public $startDate;
    public $endDate;
    public $supplierId = '';
    public $suppliers = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->endOfMonth()->format('Y-m-d');
        $this->loadSuppliers();
    }

    public function loadSuppliers()
    {
        $this->suppliers = Supplier::where('company_id', auth()->user()->company_id ?? 1)
            ->orderBy('name_en')
            ->select('id', 'row_no', 'name_en')
            ->get()
            ->toArray();
    }

    public function applyFilter()
    {
        // Triggers re-render
    }

    public function resetFilter()
    {
        $this->startDate  = now()->startOfMonth()->format('Y-m-d');
        $this->endDate    = now()->endOfMonth()->format('Y-m-d');
        $this->supplierId = '';
    }

    protected function getReportData(): array
    {
        $companyId = auth()->user()->company_id ?? 1;

        $suppliers = Supplier::where('company_id', $companyId);

        if (!empty($this->supplierId)) {
            $suppliers->where('id', $this->supplierId);
        }

        $suppliers = $suppliers->orderBy('name_en')->get();

        $rows   = [];
        $totals = [
            'opening' => 0.0,
            'invoiced' => 0.0,
            'paid' => 0.0,
            'closing' => 0.0,
        ];

        foreach ($suppliers as $supplier) {
            // Opening balance: everything before startDate — same formula
            // used by the Customer Balance Summary report.
            $openingInvoiced = (float) SupplierInvoice::where('supplier_id', $supplier->id)
                ->where('company_id', $companyId)
                ->where('invoice_date', '<', $this->startDate)
                ->sum('grand_total');

            $openingPaid = (float) Payment::where('supplier_id', $supplier->id)
                ->where('company_id', $companyId)
                ->where('payment_date', '<', $this->startDate)
                ->sum('grand_total');

            $opening = $openingInvoiced - $openingPaid;

            $invoiced = (float) SupplierInvoice::where('supplier_id', $supplier->id)
                ->where('company_id', $companyId)
                ->whereBetween('invoice_date', [$this->startDate, $this->endDate])
                ->sum('grand_total');

            $paid = (float) Payment::where('supplier_id', $supplier->id)
                ->where('company_id', $companyId)
                ->whereBetween('payment_date', [$this->startDate, $this->endDate])
                ->sum('grand_total');

            $closing = $opening + $invoiced - $paid;

            // Skip suppliers with no activity at all in this period and no
            // carried-forward balance — keeps the report focused, matching
            // the Customer Balance Summary's convention.
            if ($opening == 0.0 && $invoiced == 0.0 && $paid == 0.0) {
                continue;
            }

            $rows[] = [
                'supplier' => $supplier,
                'opening'  => $opening,
                'invoiced' => $invoiced,
                'paid'     => $paid,
                'closing'  => $closing,
            ];

            $totals['opening']  += $opening;
            $totals['invoiced'] += $invoiced;
            $totals['paid']     += $paid;
            $totals['closing']  += $closing;
        }

        return [$rows, $totals];
    }

    public function exportExcel()
    {
        [$rows, $totals] = $this->getReportData();

        $columns = ['Supplier', 'Code', 'Opening Balance', 'Invoiced', 'Paid', 'Closing Balance'];

        $exportRows = [];
        foreach ($rows as $row) {
            $exportRows[] = [
                $row['supplier']->name_en, $row['supplier']->row_no,
                (float) $row['opening'], (float) $row['invoiced'], (float) $row['paid'], (float) $row['closing'],
            ];
        }

        $totalsRow = [
            '', 'TOTAL',
            (float) $totals['opening'], (float) $totals['invoiced'], (float) $totals['paid'], (float) $totals['closing'],
        ];

        $meta = [
            'title' => 'SUPPLIER BALANCE SUMMARY',
            'lines' => [
                'Period: ' . Carbon::parse($this->startDate)->format('d M Y') . ' — ' . Carbon::parse($this->endDate)->format('d M Y'),
                'Total Suppliers: ' . count($rows),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 3,
        ];

        $filename = 'SupplierBalanceSummary-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($exportRows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        [$rows, $totals] = $this->getReportData();

        return view('livewire.report.operation.supplier-balance-summary', [
            'rows'   => $rows,
            'totals' => $totals,
        ]);
    }
}
