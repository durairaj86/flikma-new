<?php

namespace App\Livewire\Report\Finance;

use App\Models\Customer\Customer;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GeneralLedgerTable extends Component
{
    public $startDate;
    public $endDate;
    public $customerId = 'all';
    public $search = '';

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'customerChanged' => 'updateCustomer',
        'searchChanged' => 'updateSearch'
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

    }

    public function updateDateRange($dateRange)
    {
        $this->startDate = $dateRange['startDate'];
        $this->endDate = $dateRange['endDate'];
    }

    public function updateCustomer($customerId)
    {
        $this->customerId = $customerId;
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function getGeneralLedgerData()
    {
        // Get customers to include in the report
        $customers = Customer::where('company_id', companyId());

        // Filter by specific customer if selected
        if ($this->customerId !== 'all') {
            $customers = $customers->where('id', $this->customerId);
        }

        // Apply search if provided
        if (!empty($this->search)) {
            $customers = $customers->where(function ($query) {
                $query->where('name_en', 'like', '%' . $this->search . '%')
                    ->orWhere('row_no', 'like', '%' . $this->search . '%');
            });
        }

        $customers = $customers->orderBy('name_en')->get();

        if ($customers->isEmpty()) {
            return [
                'customers' => [],
                'grand_total_debit' => 0,
                'grand_total_credit' => 0,
                'net_balance' => 0
            ];
        }

        $generalLedgerData = [];
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;
        $netBalance = 0;

        foreach ($customers as $customer) {
            // Get opening balance (transactions before start date)
            $openingBalance = $this->getOpeningBalance($customer->id, $this->startDate);

            // Get every ledger line for this customer within the date range,
            // across every account it touched (AR, Revenue, Tax, ...) — a
            // flat customer sub-ledger, not grouped by account.
            $transactions = $this->getCustomerTransactions($customer->id, $this->startDate, $this->endDate);

            // Calculate running balance and totals. Customers are always
            // debtors from the company's perspective: a debit (e.g. an
            // invoice) increases what they owe, a credit (e.g. a payment)
            // decreases it — regardless of which underlying account each
            // line posted to.
            $runningBalance = $openingBalance;
            $totalDebit = 0;
            $totalCredit = 0;

            $formattedTransactions = [];
            foreach ($transactions as $transaction) {
                $debit = $transaction->debit ?? 0;
                $credit = $transaction->credit ?? 0;

                $runningBalance += $debit - $credit;

                $formattedTransactions[] = [
                    'date'         => $transaction->reference_date,
                    'voucher_no'   => $transaction->voucher_no,
                    'voucher_type' => $transaction->voucher_type,
                    'reference_no' => $transaction->reference_no,
                    'description'  => $transaction->description,
                    'account_code' => $transaction->account->code ?? '',
                    'account_name' => $transaction->account->name ?? '',
                    'debit'        => $debit,
                    'credit'       => $credit,
                    'balance'      => $runningBalance,
                ];

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            $generalLedgerData[$customer->id] = [
                'customer_code' => $customer->row_no,
                'customer_name' => $customer->name_en,
                'opening_balance' => $openingBalance,
                'transactions' => $formattedTransactions,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'closing_balance' => $closingBalance
            ];

            $grandTotalDebit += $totalDebit;
            $grandTotalCredit += $totalCredit;
            $netBalance += $closingBalance;
        }

        return [
            'customers' => $generalLedgerData,
            'grand_total_debit' => $grandTotalDebit,
            'grand_total_credit' => $grandTotalCredit,
            'net_balance' => $netBalance
        ];
    }

    private function getOpeningBalance($customerId, $startDate)
    {
        $financeSub = FinanceSub::where('customer_id', $customerId)
            ->where('reference_date', '<', $startDate)
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1);
            })
            ->select(
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->first();

        $debit = $financeSub->total_debit ?? 0;
        $credit = $financeSub->total_credit ?? 0;

        return $debit - $credit;
    }

    private function getCustomerTransactions($customerId, $startDate, $endDate)
    {
        return FinanceSub::where('customer_id', $customerId)
            ->whereBetween('reference_date', [$startDate, $endDate])
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1);
            })
            ->with('account:id,code,name')
            ->orderBy('reference_date')
            ->orderBy('id')
            ->get();
    }

    public function render()
    {
        $generalLedgerData = $this->getGeneralLedgerData();

        return view('livewire.report.finance.general-ledger-table', [
            'generalLedgerData' => $generalLedgerData
        ]);
    }
}
