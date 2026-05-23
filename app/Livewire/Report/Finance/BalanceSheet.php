<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BalanceSheet extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function applyFilter()
    {
        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
        $this->dispatch('searchChanged', $this->search);
    }

    public function resetFilter()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->search = '';

        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
        $this->dispatch('searchChanged', '');
    }

    public function updatedStartDate($value)
    {
        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }

    public function updatedEndDate($value)
    {
        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }

    public function updatedSearch($value)
    {
        $this->dispatch('searchChanged', $value);
    }

    public function render()
    {
        $accounts = Account::where('is_active', 1)
            ->whereIn('type', ['Asset', 'Liability', 'Equity'])
            ->get();

        $totalAssets      = 0;
        $totalLiabilities = 0;
        $totalEquity      = 0;

        foreach ($accounts as $account) {
            $financeSub = FinanceSub::where('account_id', $account->id)
                ->where('reference_date', '<=', $this->endDate)
                ->whereHas('finance', fn($q) => $q->where('is_approved', 1))
                ->select(DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
                ->first();

            $debit  = $financeSub->total_debit ?? 0;
            $credit = $financeSub->total_credit ?? 0;

            if ($account->type === 'Asset') {
                $totalAssets += $debit - $credit;
            } elseif ($account->type === 'Liability') {
                $totalLiabilities += $credit - $debit;
            } elseif ($account->type === 'Equity') {
                $totalEquity += $credit - $debit;
            }
        }

        // Net income from Income/Expense accounts
        $incomeIds  = Account::where('is_active', 1)->where('type', 'Income')->pluck('id');
        $expenseIds = Account::where('is_active', 1)->where('type', 'Expense')->pluck('id');

        $totalIncome = 0;
        if ($incomeIds->isNotEmpty()) {
            $d = FinanceSub::whereIn('account_id', $incomeIds)
                ->where('reference_date', '<=', $this->endDate)
                ->whereHas('finance', fn($q) => $q->where('is_approved', 1))
                ->selectRaw('SUM(credit) as total_credit, SUM(debit) as total_debit')
                ->first();
            $totalIncome = ($d->total_credit ?? 0) - ($d->total_debit ?? 0);
        }

        $totalExpenses = 0;
        if ($expenseIds->isNotEmpty()) {
            $d = FinanceSub::whereIn('account_id', $expenseIds)
                ->where('reference_date', '<=', $this->endDate)
                ->whereHas('finance', fn($q) => $q->where('is_approved', 1))
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();
            $totalExpenses = ($d->total_debit ?? 0) - ($d->total_credit ?? 0);
        }

        $netIncome = $totalIncome - $totalExpenses;
        if ($netIncome != 0) {
            $totalEquity += $netIncome;
        }

        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesEquity) < 0.01;

        return view('livewire.report.finance.balance-sheet', [
            'summary' => [
                'total_assets'              => $totalAssets,
                'total_liabilities'         => $totalLiabilities,
                'total_equity'              => $totalEquity,
                'total_liabilities_equity'  => $totalLiabilitiesEquity,
                'net_income'                => $netIncome,
                'is_balanced'               => $isBalanced,
                'difference'                => abs($totalAssets - $totalLiabilitiesEquity),
            ],
        ]);
    }
}
