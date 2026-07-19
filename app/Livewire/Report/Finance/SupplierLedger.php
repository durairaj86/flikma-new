<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Models\Supplier\Supplier;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SupplierLedger extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $supplierId = '';
    public $suppliers = [];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

        $this->loadSuppliers();

        // A supplier is always required — with a large supplier base,
        // querying every supplier's ledger at once is too expensive, so
        // there is no "all suppliers" option. Default to the first one.
        if (count($this->suppliers) > 0) {
            $this->supplierId = (string) $this->suppliers[0]['id'];
        }
    }

    public function loadSuppliers()
    {
        $this->suppliers = Supplier::where('company_id', companyId())
            ->orderBy('name_en')
            ->select('id', 'row_no', 'name_en')
            ->get()
            ->toArray();
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

    public function applyFilter()
    {
        // Triggers re-render with current filter values — called from the
        // Generate button. Livewire forbids calling lifecycle hooks like
        // updatedStartDate() directly via wire:click, so this plain action
        // method is the safe way to force a manual refresh (matches the
        // same pattern used by customer-statement.blade.php's Generate
        // button).
    }

    public function updatedSearch($value)
    {
        $this->dispatch('searchChanged', $value);
    }

    public function updatedSupplierId($value)
    {
        $this->dispatch('supplierChanged', $this->supplierId);
    }

    public function exportExcel()
    {
        // Reuse the child table component's query rather than duplicating
        // it — the Print/PDF/Excel buttons live here on the parent, but the
        // ledger calculation lives on SupplierLedgerTable.
        $child = new SupplierLedgerTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->supplierId = $this->supplierId;
        $child->search = $this->search;
        $data = $child->getSupplierLedgerData();

        $rows = [];
        $supplierLine = null;
        foreach ($data['suppliers'] as $sup) {
            if (abs($sup['opening_balance'] ?? 0) < 0.001 && count($sup['transactions'] ?? []) === 0 && abs($sup['closing_balance'] ?? 0) < 0.001) {
                continue;
            }
            $supplierLine = $sup['supplier_code'] . ' — ' . $sup['supplier_name'];
            $rows[] = ['', '', '', 'Opening Balance', null, null, (float) $sup['opening_balance']];
            foreach ($sup['transactions'] as $txn) {
                $rows[] = [
                    $txn['date'], $txn['voucher_no'], $txn['account_code'], $txn['description'],
                    $txn['debit'] > 0 ? (float) $txn['debit'] : null,
                    $txn['credit'] > 0 ? (float) $txn['credit'] : null,
                    (float) $txn['balance'],
                ];
            }
            $rows[] = ['', '', '', 'Closing Balance', (float) $sup['total_debit'], (float) $sup['total_credit'], (float) $sup['closing_balance']];
        }

        $columns = ['Date', 'Voucher No', 'Account', 'Description', 'Debit', 'Credit', 'Balance'];
        $totalsRow = ['', '', '', 'GRAND TOTAL', (float) $data['grand_total_debit'], (float) $data['grand_total_credit'], (float) $data['net_balance']];

        $lines = [
            'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
        ];
        if ($supplierLine) {
            $lines[] = 'Supplier: ' . $supplierLine;
        }
        $lines[] = 'Generated on: ' . now()->format('d-m-Y H:i');

        $meta = [
            'title' => 'SUPPLIER LEDGER',
            'lines' => $lines,
            'numeric_from' => 5,
        ];

        $filename = 'SupplierLedger-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        return view('livewire.report.finance.supplier-ledger');
    }
}
