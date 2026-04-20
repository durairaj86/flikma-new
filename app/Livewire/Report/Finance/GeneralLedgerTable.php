<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GeneralLedgerTable extends Component
{
    public $startDate;
    public $endDate;
    public $accountId = 'all';
    public $search = '';

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'accountChanged' => 'updateAccount',
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

    public function updateAccount($accountId)
    {
        $this->accountId = $accountId;
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function getGeneralLedgerData()
    {
        // Get accounts to include in the report
        $accounts = Account::where('is_active', 1);

        // Filter by specific account if selected
        if ($this->accountId !== 'all') {
            $accounts = $accounts->where('id', $this->accountId);
        }

        // Apply search if provided
        if (!empty($this->search)) {
            $accounts = $accounts->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        $accounts = $accounts->orderBy('code')->get();

        if ($accounts->isEmpty()) {
            return [
                'accounts' => [],
                'grand_total_debit' => 0,
                'grand_total_credit' => 0,
                'net_balance' => 0
            ];
        }

        $generalLedgerData = [];
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;
        $netBalance = 0;

        foreach ($accounts as $account) {
            // Get opening balance (transactions before start date)
            $openingBalance = $this->getOpeningBalance($account->code, $this->startDate);

            // Get transactions for this account within date range
            $transactions = $this->getAccountTransactions($account->code, $this->startDate, $this->endDate);

            // Calculate running balance and totals
            $runningBalance = $openingBalance;
            $totalDebit = 0;
            $totalCredit = 0;

            $formattedTransactions = [];
            foreach ($transactions as $transaction) {
                $debit = $transaction->debit ?? 0;
                $credit = $transaction->credit ?? 0;

                // Update running balance based on account type
                if (in_array($account->type, ['Asset', 'Expense'])) {
                    // For assets and expenses: debit increases, credit decreases
                    $runningBalance += $debit - $credit;
                } else {
                    // For liabilities, equity, and revenue: credit increases, debit decreases
                    $runningBalance += $credit - $debit;
                }

                $formattedTransactions[] = [
                    'date' => $transaction->reference_date,
                    'voucher_no' => $transaction->voucher_no,
                    'voucher_type' => $transaction->voucher_type,
                    'reference_no' => $transaction->reference_no,
                    'description' => $transaction->narration,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $runningBalance
                ];

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            // Calculate closing balance
            $closingBalance = $openingBalance;
            if (in_array($account->type, ['Asset', 'Expense'])) {
                $closingBalance += $totalDebit - $totalCredit;
            } else {
                $closingBalance += $totalCredit - $totalDebit;
            }

            $generalLedgerData[$account->id] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'opening_balance' => $openingBalance,
                'transactions' => $formattedTransactions,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'closing_balance' => $closingBalance
            ];

            $grandTotalDebit += $totalDebit;
            $grandTotalCredit += $totalCredit;
            // Add to net balance based on account type
            // For assets and expenses, a positive balance increases net balance
            // For liabilities, equity, and revenue, a positive balance decreases net balance
            if (in_array($account->type, ['Asset', 'Expense'])) {
                $netBalance += $closingBalance;
            } else {
                // For liability, equity, and revenue accounts, we need to reverse the sign
                // because a positive balance in these accounts is a credit (negative in accounting equation)
                $netBalance -= $closingBalance;
            }
        }

        return [
            'accounts' => $generalLedgerData,
            'grand_total_debit' => $grandTotalDebit,
            'grand_total_credit' => $grandTotalCredit,
            'net_balance' => $netBalance
        ];
    }

    private function getOpeningBalance($accountCode, $startDate)
    {
        // Get account details to determine balance calculation
        $account = Account::where('code', $accountCode)->first();

        if (!$account) {
            return 0; // Account not found
        }

        $accountType = $account->type;
        $accountId = $account->id;

        // Get sum of debits and credits before start date
        $financeSub = FinanceSub::where('account_id', $accountId)
            ->where('reference_date', '<', $startDate)
            ->select(
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->first();

        $debit = $financeSub->total_debit ?? 0;
        $credit = $financeSub->total_credit ?? 0;

        // Calculate opening balance based on account type
        if (in_array($accountType, ['Asset', 'Expense'])) {
            // For assets and expenses: balance = debit - credit
            return $debit - $credit;
        } else {
            // For liabilities, equity, and revenue: balance = credit - debit
            return $credit - $debit;
        }
    }

    private function getAccountTransactions($accountCode, $startDate, $endDate)
    {
        // Get account ID from code
        $accountId = Account::where('code', $accountCode)->value('id');

        if (!$accountId) {
            return collect(); // Return empty collection if account not found
        }

        return FinanceSub::where('account_id', $accountId)
            ->whereBetween('reference_date', [$startDate, $endDate])
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
