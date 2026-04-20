<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProfitAndLossTable extends Component
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

    public function getProfitAndLossData()
    {
        // Get all active accounts, filtered by search if provided
        $accounts = Account::where('is_active', 1);

        if (!empty($this->search)) {
            $accounts = $accounts->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('type', 'like', '%' . $this->search . '%');
            });
        }

        // Get only Revenue and Expense accounts
        $accounts = $accounts->whereIn('type', ['Income', 'Expense'])
            ->orderBy('type')
            ->orderBy('code')
            ->get();

        $revenueAccounts = [];
        $expenseAccounts = [];

        $totalRevenue = 0;
        $totalExpenses = 0;

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
            if ($account->type === 'Income') {
                // For revenue: balance = credit - debit
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $revenueAccounts[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'balance' => $balance
                    ];
                    $totalRevenue += $balance;
                }
            } elseif ($account->type === 'Expense') {
                // For expenses: balance = debit - credit
                $balance = $debit - $credit;
                if ($balance != 0) {
                    $expenseAccounts[] = [
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'balance' => $balance
                    ];
                    $totalExpenses += $balance;
                }
            }
        }

        // Calculate net income/loss
        $netIncome = $totalRevenue - abs($totalExpenses);
        return [
            'revenue' => $revenueAccounts,
            'expenses' => $expenseAccounts,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome
        ];
    }

    public function render()
    {
        $profitAndLossData = $this->getProfitAndLossData();

        return view('livewire.report.finance.profit-and-loss-table', [
            'profitAndLossData' => $profitAndLossData
        ]);
    }
}
