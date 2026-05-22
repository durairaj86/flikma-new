<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BalanceSheetTable extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

        // Listen for date range and search changes from parent component
        $this->listeners = [
            'dateRangeChanged' => 'updateDateRange',
            'searchChanged' => 'updateSearch'
        ];
    }

    public function updateDateRange($dateRange)
    {
        $this->startDate = $dateRange['startDate'];
        $this->endDate = $dateRange['endDate'];
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function getBalanceSheetData()
    {
        // Balance Sheet is CUMULATIVE — it shows balances as of the end date,
        // including ALL transactions from the beginning up to endDate.
        // It does NOT filter by a date range like P&L does.

        $accounts = Account::where('is_active', 1);

        if (!empty($this->search)) {
            $accounts = $accounts->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('type', 'like', '%' . $this->search . '%');
            });
        }

        // Get only Asset, Liability, and Equity accounts
        $accounts = $accounts->whereIn('type', ['Asset', 'Liability', 'Equity'])
            ->orderBy('type')
            ->orderBy('code')
            ->get();

        $assetAccounts = [];
        $liabilityAccounts = [];
        $equityAccounts = [];

        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;

        foreach ($accounts as $account) {
            // Cumulative balance up to endDate using reference_date on finance_sub
            $financeSub = FinanceSub::where('account_id', $account->id)
                ->where('reference_date', '<=', $this->endDate)
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

            if ($account->type === 'Asset') {
                // Assets have normal DEBIT balance
                $balance = $debit - $credit;
                if ($balance != 0) {
                    $assetAccounts[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'balance'      => $balance,
                    ];
                    $totalAssets += $balance;
                }
            } elseif ($account->type === 'Liability') {
                // Liabilities have normal CREDIT balance
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $liabilityAccounts[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'balance'      => $balance,
                    ];
                    $totalLiabilities += $balance;
                }
            } elseif ($account->type === 'Equity') {
                // Equity has normal CREDIT balance
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $equityAccounts[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'balance'      => $balance,
                    ];
                    $totalEquity += $balance;
                }
            }
        }

        // Net Income/Loss from P&L (Income - Expense) up to endDate
        // Account type is 'Income' (not 'Revenue') — matches chart of accounts
        $incomeAccountIds = Account::where('is_active', 1)
            ->where('type', 'Income')
            ->pluck('id')
            ->toArray();

        $expenseAccountIds = Account::where('is_active', 1)
            ->where('type', 'Expense')
            ->pluck('id')
            ->toArray();

        $totalIncome = 0;
        if (!empty($incomeAccountIds)) {
            $incomeData = FinanceSub::whereIn('account_id', $incomeAccountIds)
                ->where('reference_date', '<=', $this->endDate)
                ->whereHas('finance', function ($query) {
                    $query->where('is_approved', 1);
                })
                ->selectRaw('SUM(credit) as total_credit, SUM(debit) as total_debit')
                ->first();
            $totalIncome = ($incomeData->total_credit ?? 0) - ($incomeData->total_debit ?? 0);
        }

        $totalExpenses = 0;
        if (!empty($expenseAccountIds)) {
            $expenseData = FinanceSub::whereIn('account_id', $expenseAccountIds)
                ->where('reference_date', '<=', $this->endDate)
                ->whereHas('finance', function ($query) {
                    $query->where('is_approved', 1);
                })
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();
            $totalExpenses = ($expenseData->total_debit ?? 0) - ($expenseData->total_credit ?? 0);
        }

        $netIncome = $totalIncome - $totalExpenses;

        // Add current-year net income as a component of equity
        if ($netIncome != 0) {
            $equityAccounts[] = [
                'account_code' => '',
                'account_name' => 'Current Year Net Income / (Loss)',
                'balance'      => $netIncome,
            ];
            $totalEquity += $netIncome;
        }

        return [
            'assets'                   => $assetAccounts,
            'liabilities'              => $liabilityAccounts,
            'equity'                   => $equityAccounts,
            'total_assets'             => $totalAssets,
            'total_liabilities'        => $totalLiabilities,
            'total_equity'             => $totalEquity,
            'total_liabilities_equity' => $totalLiabilities + $totalEquity,
        ];
    }

    public function render()
    {
        $balanceSheetData = $this->getBalanceSheetData();

        return view('livewire.report.finance.balance-sheet-table', [
            'balanceSheetData' => $balanceSheetData
        ]);
    }
}
