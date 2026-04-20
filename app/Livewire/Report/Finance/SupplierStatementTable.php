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

        // Supplier Account IDs (Accounts Payable)
        $supplierAccountIds = [2110]; // Using 2110 as the Accounts Payable account

        // Opening Balance before from_date
        $openingDebit = DB::table('finance_sub')
            ->where('supplier_id', $this->supplierId)
            ->where('company_id', $companyId)
            ->whereIn('account_id', $supplierAccountIds)
            ->where('reference_date', '<', $this->startDate)
            ->sum('base_debit');

        $openingCredit = DB::table('finance_sub')
            ->where('supplier_id', $this->supplierId)
            ->where('company_id', $companyId)
            ->whereIn('account_id', $supplierAccountIds)
            ->where('reference_date', '<', $this->startDate)
            ->sum('base_credit');

        $openingBalance = $openingCredit - $openingDebit; // Credit = liability

        // Transactions in date range
        $transactions = DB::table('finance as f')
            ->leftJoin('jobs as j', 'f.job_id', '=', 'j.id')
            ->where('f.company_id', $companyId)
            ->where('f.supplier_id', $this->supplierId)
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
                'f.base_total_debit as base_debit',
                'f.base_total_credit as base_credit'
            )
            ->orderBy('f.reference_date')
            ->orderBy('f.id')
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

        $closingBalance = $openingBalance + $invoicedAmount - $paidAmount;

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
