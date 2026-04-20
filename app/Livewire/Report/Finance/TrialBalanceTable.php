<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TrialBalanceTable extends Component
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

    public function getTrialBalanceData()
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

        $accounts = $accounts->orderBy('code')
            ->get();

        $trialBalanceData = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            // Get opening balance (transactions before start date)
            $openingBalanceData = FinanceSub::where('account_id', $account->id)
                ->whereHas('finance', function ($query) {
                    $query->where('is_approved', 1)
                        ->where('posted_at', '<', $this->startDate);
                })
                ->select(
                    DB::raw('SUM(debit) as total_debit'),
                    DB::raw('SUM(credit) as total_credit')
                )
                ->first();

            $openingDebit = $openingBalanceData->total_debit ?? 0;
            $openingCredit = $openingBalanceData->total_credit ?? 0;

            // Get sum of debits and credits for this account within date range
            $periodData = FinanceSub::where('account_id', $account->id)
                ->whereHas('finance', function ($query) {
                    $query->where('is_approved', 1)
                        ->whereBetween('posted_at', [$this->startDate, $this->endDate]);
                })
                ->select(
                    DB::raw('SUM(debit) as total_debit'),
                    DB::raw('SUM(credit) as total_credit')
                )
                ->first();

            $periodDebit = $periodData->total_debit ?? 0;
            $periodCredit = $periodData->total_credit ?? 0;

            // Calculate total debits and credits (opening balance + period activity)
            $debit = $openingDebit + $periodDebit;
            $credit = $openingCredit + $periodCredit;

            // Only include accounts with activity
            if ($debit > 0 || $credit > 0) {
                $trialBalanceData[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'account_type' => $account->type,
                    'debit' => $debit,
                    'credit' => $credit
                ];

                $totalDebit += $debit;
                $totalCredit += $credit;
            }
        }

        return [
            'accounts' => $trialBalanceData,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit
        ];
    }

    public function render()
    {
        $trialBalanceData = $this->getTrialBalanceData();

        return view('livewire.report.finance.trial-balance-table', [
            'trialBalanceData' => $trialBalanceData
        ]);
    }
}
