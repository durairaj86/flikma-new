<?php

namespace App\Livewire\Report\Finance;

use App\Models\Supplier\Supplier;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SupplierStatementTable extends Component
{
    public $startDate;
    public $endDate;
    public $supplierId;
    public $currency;
    public $currency_rate;
    public $search = '';

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'supplierChanged' => 'updateSupplier',
        'currencyChanged' => 'updateCurrency',
        'searchChanged' => 'updateSearch',
        'exportAsExcel' => 'exportAsExcel'
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->currency = authUserCompany()->currency;
        $this->currency_rate = 1;//default
    }

    public function updateDateRange($dateRange)
    {
        $this->startDate = $dateRange['startDate'];
        $this->endDate = $dateRange['endDate'];
    }

    public function updateSupplier($supplierId)
    {
        $this->supplierId = $supplierId;
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function updateCurrency($data)
    {
        $this->currency = $data['currency'];
        $this->currency_rate = $data['currency_rate'];
    }

    public function exportAsExcel()
    {
        $data = $this->getSupplierStatementData();

        $summary = [
            'name' => $data['supplier']['name_en'],
            'supplier_code' => $data['supplier']['row_no'],
            'opening' => $data['openingBalance'],
            'closing' => $data['closingBalance'],
        ];

        return Excel::download(new \App\Exports\Supplier\SupplierStatementExport($data['transactions'], $summary), 'SupplierStatement.xlsx');
    }

    public function getSupplierStatementData()
    {
        if (empty($this->supplierId)) {
            return [
                'supplier' => null,
                'transactions' => [],
                'openingBalance' => 0,
                'invoicedAmount' => 0,
                'paidAmount' => 0,
                'closingBalance' => 0
            ];
        }

        $companyId = auth()->user()->company_id ?? 1;

        // Get supplier details
        $supplier = Supplier::where('id', $this->supplierId)
            ->select('id', 'row_no', 'name_en', 'company_id', 'email', 'phone', 'currency')
            ->first();

        if (!$supplier) {
            return [
                'supplier' => null,
                'transactions' => [],
                'openingBalance' => 0,
                'invoicedAmount' => 0,
                'paidAmount' => 0,
                'closingBalance' => 0
            ];
        }

        // Supplier Account IDs (Accounts Payable) — resolve by account code
        $supplierAccountIds = DB::table('accounts')->where('code', '2110')->pluck('id')->all() ?: [18];

        // Opening Balance before from_date (AP sub-ledger entries before period)
        $openingQuery = DB::table('finance_sub as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.supplier_id', $this->supplierId)
            ->where('fs.company_id', $companyId)
            ->whereIn('fs.account_id', $supplierAccountIds)
            ->where('fs.reference_date', '<', $this->startDate)
            ->where('f.is_approved', 1);

        $openingDebit  = (clone $openingQuery)->sum('fs.base_debit');
        $openingCredit = (clone $openingQuery)->sum('fs.base_credit');

        $openingBalance = $openingCredit - $openingDebit; // Credit balance = amount owed to supplier

        // Transactions in date range — taken from the AP sub-ledger lines.
        // (Finance headers always have total_debit == total_credit, so they
        // can never move a running balance.)
        $transactions = DB::table('finance_sub as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->leftJoin('jobs as j', 'fs.job_id', '=', 'j.id')
            ->where('fs.company_id', $companyId)
            ->where('fs.supplier_id', $this->supplierId)
            ->whereIn('fs.account_id', $supplierAccountIds)
            ->where('f.is_approved', 1)
            ->whereBetween('fs.reference_date', [$this->startDate, $this->endDate])
            ->select(
                'fs.id',
                'fs.reference_date',
                'fs.voucher_no',
                'fs.voucher_type',
                'fs.reference_no',
                'j.row_no as job_number',
                'f.narration as description',
                'fs.currency',
                'fs.exchange_rate',
                'fs.base_debit',
                'fs.base_credit'
            )
            ->orderBy('fs.reference_date')
            ->orderBy('fs.id')
            ->get();

        // Running Balance
        $runningBalance = $openingBalance;
        $transactions = $transactions->map(function ($txn) use (&$runningBalance) {
            $runningBalance += $txn->base_credit - $txn->base_debit;
            $txn->balance = $runningBalance;
            return $txn;
        });

        // Summary Totals
        $invoicedAmount = $transactions
            ->where('voucher_type', 'SI') // Supplier Invoice
            ->sum('base_credit');

        $paidAmount = $transactions
            ->where('voucher_type', 'PV') // Payment Voucher
            ->sum('base_debit');

        // Closing balance must reflect every AP movement (incl. debit notes),
        // i.e. the final running balance, not just invoices minus payments.
        $closingBalance = $openingBalance + $transactions->sum('base_credit') - $transactions->sum('base_debit');

        return [
            'supplier' => $supplier,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'invoicedAmount' => $invoicedAmount,
            'paidAmount' => $paidAmount,
            'closingBalance' => $closingBalance
        ];
    }

    public function render()
    {
        $supplierStatementData = $this->getSupplierStatementData();

        return view('livewire.report.finance.supplier-statement-table', [
            'supplierStatementData' => $supplierStatementData
        ]);
    }
}
