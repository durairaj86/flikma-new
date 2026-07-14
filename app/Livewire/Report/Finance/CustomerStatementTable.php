<?php

namespace App\Livewire\Report\Finance;

use App\Exports\Customer\CustomerStatementExport;
use App\Models\Customer\Customer;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CustomerStatementTable extends Component
{
    public $startDate;
    public $endDate;
    public $customerId;
    public $currency;
    public $currency_rate;
    public $search = '';

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'customerChanged' => 'updateCustomer',
        'currencyChanged' => 'updateCurrency',
        'searchChanged' => 'updateSearch',
        'exportAsExcel' => 'exportAsExcel'
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->subMonth(3)->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        $company = authUserCompany();
        $this->currency = $company ? $company->currency : 'SAR';
        $this->currency_rate = 1;//default
    }

    public function updateDateRange($dateRange)
    {
        if (filled($dateRange['startDate'])) {
            $this->startDate = Carbon::parse($dateRange['startDate'])->format('Y-m-d');
        }
        if (filled($dateRange['endDate'])) {
            $this->endDate = Carbon::parse($dateRange['endDate'])->format('Y-m-d');
        }
    }

    public function updateCustomer($customerId)
    {
        $this->customerId = $customerId;
    }

    public function updateCurrency($data)
    {
        $this->currency = $data['currency'];
        $this->currency_rate = $data['currency_rate'];
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function exportAsExcel()
    {
        $data = $this->getCustomerStatementData();

        $summary = [
            'name' => $data['customer']['name_en'],
            'customer_code' => $data['customer']['row_no'],
            'opening' => $data['openingBalance'],
            'closing' => $data['closingBalance'],
        ];

        return Excel::download(new CustomerStatementExport($data['transactions'], $summary), 'CustomerStatement.xlsx');
    }

    public function getCustomerStatementData()
    {
        if (empty($this->customerId)) {
            return [
                'customer' => null,
                'transactions' => [],
                'openingBalance' => 0,
                'invoicedAmount' => 0,
                'paidAmount' => 0,
                'closingBalance' => 0
            ];
        }

        $companyId = auth()->user()->company_id ?? 1;

        // Get customer details
        $customer = Customer::where('id', $this->customerId)
            ->select('id', 'row_no', 'name_en', 'company_id', 'email', 'phone', 'currency')
            ->first();

        if (!$customer) {
            return [
                'customer' => null,
                'transactions' => [],
                'openingBalance' => 0,
                'invoicedAmount' => 0,
                'paidAmount' => 0,
                'closingBalance' => 0
            ];
        }

        // Customer Account IDs (Accounts Receivable) — resolve by account code
        $customerAccountIds = DB::table('accounts')->where('code', '1130')->pluck('id')->all() ?: [5];

        // Opening Balance before from_date
        $openingQuery = DB::table('finance_sub as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->where('fs.customer_id', $this->customerId)
            ->where('fs.company_id', $companyId)
            ->whereIn('fs.account_id', $customerAccountIds)
            ->where('fs.reference_date', '<', $this->startDate)
            ->where('f.is_approved', 1);

        $openingDebit = (clone $openingQuery)->sum('fs.base_debit');
        $openingCredit = (clone $openingQuery)->sum('fs.base_credit');

        $openingBalance = $openingDebit - $openingCredit; // Debit = asset

        // Transactions in date range — taken from the AR sub-ledger lines.
        // (Finance headers always have total_debit == total_credit, so they
        // can never move a running balance.)
        $transactions = DB::table('finance_sub as fs')
            ->join('finance as f', 'fs.finance_id', '=', 'f.id')
            ->leftJoin('jobs as j', 'fs.job_id', '=', 'j.id')
            ->where('fs.company_id', $companyId)
            ->where('fs.customer_id', $this->customerId)
            ->whereIn('fs.account_id', $customerAccountIds)
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
            $runningBalance += $txn->base_debit - $txn->base_credit;
            $txn->balance = $runningBalance;
            return $txn;
        });

        // Summary Totals
        $invoicedAmount = $transactions
            ->where('voucher_type', 'CI') // Customer Invoice
            ->sum('base_debit');

        $paidAmount = $transactions
            ->where('voucher_type', 'CV') // Receipt Voucher
            ->sum('base_credit');

        // Closing balance must reflect every AR movement (incl. credit notes),
        // i.e. the final running balance, not just invoices minus receipts.
        $closingBalance = $openingBalance + $transactions->sum('base_debit') - $transactions->sum('base_credit');

        return [
            'customer' => $customer,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'invoicedAmount' => $invoicedAmount,
            'paidAmount' => $paidAmount,
            'closingBalance' => $closingBalance
        ];
    }

    public function render()
    {
        $customerStatementData = $this->getCustomerStatementData();

        return view('livewire.report.finance.customer-statement-table', [
            'customerStatementData' => $customerStatementData
        ]);
    }
}
