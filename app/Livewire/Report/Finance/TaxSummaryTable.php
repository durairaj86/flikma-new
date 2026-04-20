<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TaxSummaryTable extends Component
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

    public function getTaxSummaryData()
    {
        // Get all finance sub entries that are tax lines
        $financeSubs = FinanceSub::where('is_tax_line', 1)
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1)
                    ->whereBetween('reference_date', [$this->startDate, $this->endDate]);
            })
            ->with(['finance' => function ($query) {
                $query->select('id', 'reference_no', 'reference_date', 'narration');
            }]);

        // Apply search filter if provided
        if (!empty($this->search)) {
            $financeSubs = $financeSubs->where(function ($query) {
                $query->whereHas('finance', function ($subQuery) {
                    $subQuery->where('reference_no', 'like', '%' . $this->search . '%')
                        ->orWhere('narration', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Get the finance sub entries
        $financeSubs = $financeSubs->get();

        // Group by account_id and calculate totals
        $accountTotals = [];
        $accountIds = [];

        foreach ($financeSubs as $financeSub) {
            $accountId = $financeSub->account_id;
            $accountIds[] = $accountId;

            if (!isset($accountTotals[$accountId])) {
                $accountTotals[$accountId] = [
                    'debit' => 0,
                    'credit' => 0
                ];
            }

            $accountTotals[$accountId]['debit'] += $financeSub->debit;
            $accountTotals[$accountId]['credit'] += $financeSub->credit;
        }

        // Get account details for the accounts with tax entries
        $accounts = Account::whereIn('id', array_unique($accountIds))->get()->keyBy('id');

        $taxData = [];
        $totalInputTax = 0;
        $totalOutputTax = 0;

        foreach ($accountTotals as $accountId => $totals) {
            if (!isset($accounts[$accountId])) {
                continue;
            }

            $account = $accounts[$accountId];
            $debit = $totals['debit'];
            $credit = $totals['credit'];

            // Determine if this is input or output tax based on account name/code
            $isInputTax = stripos($account->name, 'input') !== false || stripos($account->code, 'input') !== false;
            $isOutputTax = stripos($account->name, 'output') !== false || stripos($account->code, 'output') !== false;

            // Determine balance based on account type
            // For input tax (asset/debit account), balance = debit - credit
            // For output tax (liability/credit account), balance = credit - debit
            $balance = 0;
            $type = 'Other';

            if ($isInputTax) {
                $type = 'Input Tax';
                $balance = $debit - $credit; // Input tax is typically a debit balance
                $totalInputTax += $balance;
            } elseif ($isOutputTax) {
                $type = 'Output Tax';
                $balance = $credit - $debit; // Output tax is typically a credit balance
                $totalOutputTax += $balance;
            } else {
                // For other tax accounts, determine based on account type
                $accountType = $account->type ?? '';
                if (in_array($accountType, ['Asset', 'Expense'])) {
                    $balance = $debit - $credit;
                } else {
                    $balance = $credit - $debit;
                }
            }

            if ($balance != 0) {
                $taxData[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'type' => $type,
                    'balance' => $balance
                ];
            }
        }

        // Calculate net tax (output + input)
        // Since we've already calculated the balances with the correct sign,
        // we can simply add them together to get the net tax
        $netTax = $totalOutputTax + $totalInputTax;

        return [
            'tax_accounts' => $taxData,
            'total_input_tax' => $totalInputTax,
            'total_output_tax' => $totalOutputTax,
            'net_tax' => $netTax
        ];
    }

    public function render()
    {
        $taxSummaryData = $this->getTaxSummaryData();

        return view('livewire.report.finance.tax-summary-table', [
            'taxSummaryData' => $taxSummaryData
        ]);
    }
}
