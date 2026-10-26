<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Models\Supplier\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SupplierStatement extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $supplierId;
    public $suppliers = [];

    // AP account ID in chart of accounts
    const AP_ACCOUNT_ID = 18;

    public function mount()
    {
        $this->startDate = now()->subMonth(3)->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');

        $this->loadSuppliers();

        // Deep link (?supplier=ID), like the customer statement: preselect that supplier.
        $requested = request()->query('supplier');
        if ($requested && collect($this->suppliers)->contains('id', (int) $requested)) {
            $this->supplierId = (string) $requested;
        } elseif (count($this->suppliers) > 0 && !$this->supplierId) {
            $this->supplierId = (string) $this->suppliers[0]['id'];
        }
    }

    public function loadSuppliers()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $this->suppliers = Supplier::where('company_id', $companyId)
            ->when(!empty($this->search), function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name_en', 'like', '%' . $this->search . '%')
                          ->orWhere('name_ar', 'like', '%' . $this->search . '%');
                });
            })
            ->select('id', 'row_no', 'name_en', 'company_id', 'email', 'phone', 'currency')
            ->get()
            ->toArray();
    }

    public function updatedSearch()
    {
        $this->loadSuppliers();
    }

    /** The shared supplier dropdown posts hashed ids; keep the property as the plain id. */
    public function updatedSupplierId($value)
    {
        $this->supplierId = $value ? (string) decodeId((string) $value) : '';
    }

    public function applyFilter()
    {
        // date fields are wire:model.live — triggers re-render automatically
    }

    public function resetFilter()
    {
        $this->startDate  = now()->subMonth(3)->startOfMonth()->format('Y-m-d');
        $this->endDate    = now()->format('Y-m-d');
        $this->search     = '';
        $this->supplierId = null;
        $this->loadSuppliers();
        if (count($this->suppliers) > 0) {
            $this->supplierId = (string)$this->suppliers[0]['id'];
        }

        $this->dispatch('statement-dates-reset', startDate: $this->startDate, endDate: $this->endDate,
            supplier: $this->supplierId ? encodeId((int) $this->supplierId) : '');
    }

    public function exportExcel()
    {
        $data = $this->getStatementData();

        if (!$data['supplier']) {
            return;
        }

        $baseCurrency = optional(authUserCompany())->base_currency ?: 'SAR';
        $columns = ['Date', 'Voucher No', 'Type', 'Description', 'FCY Amount', 'Credit (owed)', 'Debit (paid / adjusted)', 'Balance'];

        $rows = [[
            '', 'Balance Brought Forward', '', '', '', '', '', (float)$data['openingBalance'],
        ]];

        foreach ($data['transactions'] as $txn) {
            $fcy = $txn->fcy_amount !== null
                ? $txn->currency . ' ' . number_format($txn->fcy_amount, 2, '.', '') . ' (' . $baseCurrency . ' ' . number_format($txn->exchange_rate, 4, '.', '') . ')'
                : '';
            $rows[] = [
                Carbon::parse($txn->reference_date)->format('d M Y'),
                $txn->voucher_no,
                $this->voucherTypeLabel($txn->voucher_type),
                $txn->description,
                $fcy,
                (float)$txn->base_credit > 0 ? (float)$txn->base_credit : '',
                (float)$txn->base_debit > 0 ? (float)$txn->base_debit : '',
                (float)$txn->balance,
            ];
        }

        $totalsRow = [
            '', '', '', 'CLOSING TOTALS', '',
            (float)$data['invoicedAmount'],
            (float)$data['paidAmount'],
            (float)$data['closingBalance'],
        ];

        $meta = [
            'title' => __('SUPPLIER STATEMENT'),
            'lines' => [
                'Supplier: ' . $data['supplier']->name_en . ' (' . $data['supplier']->row_no . ')',
                'Statement Period: ' . Carbon::parse($this->startDate)->format('d M Y')
                    . ' to ' . Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 6,
        ];

        $filename = 'SupplierStatement-' . $data['supplier']->row_no
            . '-' . $this->startDate . '_' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function voucherTypeLabel(?string $type): string
    {
        return match ($type) {
            'SI' => 'Supplier Invoice',
            'PV' => 'Payment Voucher',
            'DN' => 'Debit Note',
            'OB' => 'Opening Balance',
            'JV' => 'Journal Voucher',
            'EV' => 'Expense',
            default => (string)$type,
        };
    }

    /* ─────────────────────────────── data ─────────────────────────────── */

    private function getStatementData(): array
    {
        $empty = [
            'supplier'       => null,
            'openingBalance' => 0,
            'invoicedAmount' => 0,
            'paidAmount'     => 0,
            'closingBalance' => 0,
            'transactions'   => collect(),
        ];

        if (empty($this->supplierId)) {
            return $empty;
        }

        $companyId = auth()->user()->company_id ?? 1;

        $supplier = Supplier::where('id', $this->supplierId)
            ->select('id', 'row_no', 'name_en', 'company_id', 'email', 'phone', 'currency')
            ->first();

        if (!$supplier) {
            return $empty;
        }

        // ── Opening balance: AP entries before the start date ──
        $openingQuery = DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.supplier_id', $this->supplierId)
            ->where('fs.company_id', $companyId)
            ->where('fs.account_id', self::AP_ACCOUNT_ID)
            ->where('fs.reference_date', '<', $this->startDate)
            ->where('f.is_approved', 1);

        $openingDebit  = (clone $openingQuery)->sum('fs.base_debit');
        $openingCredit = (clone $openingQuery)->sum('fs.base_credit');
        // AP is a liability → CR = invoice posted, DR = payment made
        $openingBalance = (float) $openingCredit - (float) $openingDebit;

        // ── Period transactions: the payable (2110) lines, so every movement counts — invoices, payments, debit notes,
        //    opening balances, exchange differences — and the running balance is what is owed (the voucher headers used
        //    before have debit = credit, which made a debit note move nothing) ──
        $transactions = DB::table('finance_subs as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->leftJoin('jobs as j', 'fs.job_id', '=', 'j.id')
            ->where('fs.company_id', $companyId)
            ->where('fs.supplier_id', $this->supplierId)
            ->where('fs.account_id', self::AP_ACCOUNT_ID)
            ->where('f.is_approved', 1)
            ->whereBetween('fs.reference_date', [$this->startDate, $this->endDate])
            ->select('fs.id', 'fs.reference_date', 'fs.voucher_no', 'fs.voucher_type', 'fs.reference_no', 'j.row_no as job_number',
                DB::raw('COALESCE(fs.description, f.narration) as description'), 'fs.currency', 'fs.exchange_rate',
                'fs.debit', 'fs.credit', 'fs.base_debit', 'fs.base_credit')
            ->orderBy('fs.reference_date')
            ->orderBy('fs.id')
            ->get();

        $baseCurrency = optional(authUserCompany())->base_currency ?: 'SAR';

        // Running balance (what is owed: a credit raises it, a debit lowers it)
        $runningBalance = $openingBalance;
        $transactions = $transactions->map(function ($txn) use (&$runningBalance, $baseCurrency) {
            $runningBalance += (float) $txn->base_credit - (float) $txn->base_debit;
            $txn->balance = $runningBalance;
            // FCY column only when the line's own currency differs from the company's
            $isForeign = $txn->currency && $txn->currency !== $baseCurrency && ((float) $txn->credit + (float) $txn->debit) > 0;
            $txn->fcy_amount = $isForeign ? abs((float) $txn->credit - (float) $txn->debit) : null;

            return $txn;
        });

        $invoicedAmount = $transactions->sum('base_credit');
        $paidAmount     = $transactions->sum('base_debit');
        $closingBalance = $openingBalance + $invoicedAmount - $paidAmount;

        return [
            'supplier'       => $supplier,
            'openingBalance' => $openingBalance,
            'invoicedAmount' => $invoicedAmount,
            'paidAmount'     => $paidAmount,
            'closingBalance' => $closingBalance,
            'transactions'   => $transactions,
        ];
    }

    public function render()
    {
        $data = $this->getStatementData();

        return view('livewire.report.finance.supplier-statement', array_merge([
            'company' => authUserCompany(),
        ], $data));
    }
}
