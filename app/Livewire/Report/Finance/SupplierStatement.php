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

        if (count($this->suppliers) > 0 && !$this->supplierId) {
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

        $this->dispatch('statement-dates-reset', startDate: $this->startDate, endDate: $this->endDate);
    }

    public function exportExcel()
    {
        $data = $this->getStatementData();

        if (!$data['supplier']) {
            return;
        }

        $columns = ['Date', 'Voucher No', 'Type', 'Description', 'Invoiced', 'Paid', 'Balance'];

        $rows = [[
            '', 'Balance Brought Forward', '', '', '', '', (float)$data['openingBalance'],
        ]];

        foreach ($data['transactions'] as $txn) {
            $rows[] = [
                Carbon::parse($txn->reference_date)->format('d M Y'),
                $txn->voucher_no,
                $this->voucherTypeLabel($txn->voucher_type),
                $txn->description,
                $txn->voucher_type === 'SI' ? (float)$txn->base_credit : '',
                $txn->voucher_type === 'PV' ? (float)$txn->base_debit : '',
                (float)$txn->balance,
            ];
        }

        $totalsRow = [
            '', '', '', 'CLOSING TOTALS',
            (float)$data['invoicedAmount'],
            (float)$data['paidAmount'],
            (float)$data['closingBalance'],
        ];

        $meta = [
            'title' => 'SUPPLIER STATEMENT',
            'lines' => [
                'Supplier: ' . $data['supplier']->name_en . ' (' . $data['supplier']->row_no . ')',
                'Statement Period: ' . Carbon::parse($this->startDate)->format('d M Y')
                    . ' to ' . Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 5,
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
        $openingQuery = DB::table('finance_sub as fs')
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

        // ── Period transactions ──
        $transactions = DB::table('finance as f')
            ->leftJoin('jobs as j', 'f.job_id', '=', 'j.id')
            ->where('f.company_id', $companyId)
            ->where('f.supplier_id', $this->supplierId)
            ->where('f.is_approved', 1)
            ->whereBetween('f.reference_date', [$this->startDate, $this->endDate])
            ->select(
                'f.id',
                'f.reference_date',
                'f.voucher_no',
                'f.voucher_type',
                'f.reference_no',
                'j.row_no as job_number',
                'f.narration as description',
                'f.currency',
                'f.exchange_rate',
                'f.base_total_credit as base_credit',  // SI: AP credited (invoice)
                'f.base_total_debit  as base_debit'    // PV: AP debited  (payment)
            )
            ->orderBy('f.reference_date')
            ->orderBy('f.id')
            ->get();

        // Running balance (supplier AP perspective: credit increases, debit decreases)
        $runningBalance = $openingBalance;
        $transactions = $transactions->map(function ($txn) use (&$runningBalance) {
            if ($txn->voucher_type === 'SI') {
                // Invoice: we owe more to supplier → balance goes up
                $runningBalance += (float) $txn->base_credit;
            } elseif ($txn->voucher_type === 'PV') {
                // Payment: we pay supplier → balance goes down
                $runningBalance -= (float) $txn->base_debit;
            } else {
                $runningBalance += (float) $txn->base_credit - (float) $txn->base_debit;
            }
            $txn->balance = $runningBalance;
            return $txn;
        });

        $invoicedAmount = $transactions->where('voucher_type', 'SI')->sum('base_credit');
        $paidAmount     = $transactions->where('voucher_type', 'PV')->sum('base_debit');
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
