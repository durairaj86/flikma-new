<?php

namespace App\Livewire\Report\Operation;

use App\Exports\ReportTableExport;
use App\Models\Supplier\Supplier;
use App\Services\Finance\LedgerReports;
use Illuminate\Support\Facades\DB;
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

    /** The shared supplier dropdown posts hashed ids; keep the property as the plain id. */
    public function updatedSupplierId($value)
    {
        $this->supplierId = $value ? (string) decodeId($value) : '';
    }

    public function resetFilter()
    {
        $this->startDate  = now()->startOfMonth()->format('Y-m-d');
        $this->endDate    = now()->endOfMonth()->format('Y-m-d');
        $this->supplierId = '';
        $this->dispatch('sbs-filter-reset');
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

        // From the books, in the company currency: each supplier's payable (2110) lines. Credits (bills, opening
        // balances) raise what is owed, debits (payments, debit notes) lower it. Drafts and cancelled documents are
        // not in the ledger, and a bill in another currency counts at its booked company-currency amount rather
        // than being added up with the other currencies' own numbers.
        $start = $this->startDate;
        $end   = $this->endDate;
        $sums = DB::table('finance_ledgers')
            ->where('company_id', $companyId)
            ->where('account_code', LedgerReports::PAYABLE)
            ->where('is_provisional', 0)
            ->whereNotNull('supplier_id')
            ->where('transaction_date', '<=', $end)
            ->when(!empty($this->supplierId), fn ($q) => $q->where('supplier_id', $this->supplierId))
            ->groupBy('supplier_id')
            ->selectRaw('supplier_id,
                SUM(CASE WHEN transaction_date < ? THEN base_credit - base_debit ELSE 0 END) AS opening,
                SUM(CASE WHEN transaction_date >= ? THEN base_credit ELSE 0 END) AS invoiced,
                SUM(CASE WHEN transaction_date >= ? THEN base_debit ELSE 0 END) AS paid', [$start, $start, $start])
            ->get()
            ->keyBy('supplier_id');

        foreach ($suppliers as $supplier) {
            $m = $sums->get($supplier->id);
            $opening  = round((float) ($m->opening ?? 0), 2);
            $invoiced = round((float) ($m->invoiced ?? 0), 2);
            $paid     = round((float) ($m->paid ?? 0), 2);
            $closing  = $opening + $invoiced - $paid;

            // Skip suppliers with no activity in this period and no carried-forward balance.
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

    /** The company's own currency: every figure on this report is in it. */
    public function currency(): string
    {
        return strtoupper(optional(authUserCompany())->base_currency ?: 'SAR');
    }

    public function exportExcel()
    {
        [$rows, $totals] = $this->getReportData();

        $columns = ['Supplier', 'Code', 'Opening Balance', 'Invoiced', 'Paid / Adjusted', 'Closing Balance'];

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
                'Currency: ' . $this->currency(),
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
            'currency' => $this->currency(),
        ]);
    }
}
