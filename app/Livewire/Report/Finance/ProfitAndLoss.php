<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProfitAndLoss extends Component
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
            ->whereIn('type', ['Income', 'Expense'])
            ->get();

        $totalRevenue = 0;
        $totalExpenses = 0;
        $revenueCount = 0;
        $expenseCount = 0;

        foreach ($accounts as $account) {
            $financeSub = FinanceSub::where('account_id', $account->id)
                ->whereBetween('reference_date', [$this->startDate, $this->endDate])
                ->whereHas('finance', fn($q) => $q->where('is_approved', 1))
                ->select(DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
                ->first();

            $debit  = $financeSub->total_debit ?? 0;
            $credit = $financeSub->total_credit ?? 0;

            if ($account->type === 'Income') {
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $totalRevenue += $balance;
                    $revenueCount++;
                }
            } elseif ($account->type === 'Expense') {
                $balance = $debit - $credit;
                if ($balance != 0) {
                    $totalExpenses += $balance;
                    $expenseCount++;
                }
            }
        }

        $netIncome = $totalRevenue - $totalExpenses;
        $margin    = $totalRevenue > 0 ? ($netIncome / $totalRevenue) * 100 : 0;

        return view('livewire.report.finance.profit-and-loss', [
            'summary' => [
                'total_revenue'  => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_income'     => $netIncome,
                'margin'         => $margin,
                'account_count'  => $revenueCount + $expenseCount,
            ],
        ]);
    }
}
