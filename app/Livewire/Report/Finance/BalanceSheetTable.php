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
        // Get all active accounts, filtered by search if provided
        $accounts = Account::where('is_active', 1);

        if (!empty($this->search)) {
            $accounts = $accounts->where(function($query) {
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
            // Get sum of debits and credits for this account within date range
            // Using account id as the account_id in finance_sub table
            $financeSub = FinanceSub::where('account_id', $account->id)
                ->whereHas('finance', function ($query) {
                    $query->where('is_approved', 1)
                        ->whereBetween('reference_date', [$this->startDate, $this->endDate]);
                })
                ->select(
                    DB::raw('SUM(debit) as total_debit'),
                    DB::raw('SUM(credit) as total_credit')
                )
                ->first();

            $debit = $financeSub->total_debit ?? 0;
            $credit = $financeSub->total_credit ?? 0;

            // Calculate balance based on account type
            if ($account->type === 'Asset') {
                // For assets: balance = debit - credit
                $balance = $debit - $credit;
                if ($balance != 0) {
                    $assetAccounts[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'balance' => $balance
                    ];
                    $totalAssets += $balance;
                }
            } elseif ($account->type === 'Liability') {
                // For liabilities: balance = credit - debit
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $liabilityAccounts[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'balance' => $balance
                    ];
                    $totalLiabilities += $balance;
                }
            } elseif ($account->type === 'Equity') {
                // For equity: balance = credit - debit
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $equityAccounts[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'balance' => $balance
                    ];
                    $totalEquity += $balance;
                }
            }
        }

        // Calculate retained earnings (net income/loss)
        $revenueAccounts = Account::where('is_active', 1)
            ->where('type', 'Revenue')
            ->pluck('id')
            ->toArray();

        $expenseAccounts = Account::where('is_active', 1)
            ->where('type', 'Expense')
            ->pluck('id')
            ->toArray();

        // Get total revenue
        $totalRevenue = FinanceSub::whereIn('account_id', $revenueAccounts)
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1)
                    ->whereBetween('reference_date', [$this->startDate, $this->endDate]);
            })
            ->sum('credit') - FinanceSub::whereIn('account_id', $revenueAccounts)
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1)
                    ->whereBetween('reference_date', [$this->startDate, $this->endDate]);
            })
            ->sum('debit');

        // Get total expenses
        $totalExpenses = FinanceSub::whereIn('account_id', $expenseAccounts)
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1)
                    ->whereBetween('reference_date', [$this->startDate, $this->endDate]);
            })
            ->sum('debit') - FinanceSub::whereIn('account_id', $expenseAccounts)
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1)
                    ->whereBetween('reference_date', [$this->startDate, $this->endDate]);
            })
            ->sum('credit');

        // Calculate net income/loss
        $netIncome = $totalRevenue - $totalExpenses;

        // Add net income to equity
        if ($netIncome != 0) {
            $equityAccounts[] = [
                'account_code' => '',
                'account_name' => 'Retained Earnings (Net Income/Loss)',
                'balance' => $netIncome
            ];
            $totalEquity += $netIncome;
        }

        return [
            'assets' => $assetAccounts,
            'liabilities' => $liabilityAccounts,
            'equity' => $equityAccounts,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_equity' => $totalLiabilities + $totalEquity
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
