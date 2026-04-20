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

        // Customer Account IDs (Accounts Receivable)
        $customerAccountIds = [1130]; // Assuming 1130 is the Accounts Receivable account

        // Opening Balance before from_date
        $openingDebit = DB::table('finance_sub')
            ->where('customer_id', $this->customerId)
            ->where('company_id', $companyId)
            ->whereIn('account_id', $customerAccountIds)
            ->where('reference_date', '<', $this->startDate)
            ->sum('base_debit');

        $openingCredit = DB::table('finance_sub')
            ->where('customer_id', $this->customerId)
            ->where('company_id', $companyId)
            ->whereIn('account_id', $customerAccountIds)
            ->where('reference_date', '<', $this->startDate)
            ->sum('base_credit');

        $openingBalance = $openingDebit - $openingCredit; // Debit = asset

        // Transactions in date range
        $transactions = DB::table('finance as f')
            ->leftJoin('jobs as j', 'f.job_id', '=', 'j.id')
            ->where('f.company_id', $companyId)
            ->where('f.customer_id', $this->customerId)
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

        $closingBalance = $openingBalance + $invoicedAmount - $paidAmount;

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
