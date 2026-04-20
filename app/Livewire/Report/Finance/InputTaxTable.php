<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InputTaxTable extends Component
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

    public function getInputTaxData()
    {
        // Get all finance sub entries that are tax lines
        $transactions = FinanceSub::where('is_tax_line', 1)
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1)
                    ->whereBetween('reference_date', [$this->startDate, $this->endDate]);
            })
            ->with(['finance' => function ($query) {
                $query->select('id', 'reference_no', 'reference_date', 'narration');
            }]);

        // Apply search filter if provided
        if (!empty($this->search)) {
            $transactions = $transactions->where(function ($query) {
                $query->whereHas('finance', function ($subQuery) {
                    $subQuery->where('reference_no', 'like', '%' . $this->search . '%')
                        ->orWhere('narration', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Get the transactions
        $transactions = $transactions->get();

        // Get account details for the accounts with tax entries
        $accountIds = $transactions->pluck('account_id')->unique()->toArray();
        $accounts = Account::whereIn('id', $accountIds)->get()->keyBy('id');

        $inputTaxData = [];
        $totalInputTax = 0;

        foreach ($transactions as $transaction) {
            if (!isset($accounts[$transaction->account_id])) {
                continue;
            }

            $account = $accounts[$transaction->account_id];

            // Check if this is an input tax account
            $isInputTax = stripos($account->name, 'input') !== false || stripos($account->code, 'input') !== false;

            if (!$isInputTax) {
                continue;
            }

            $amount = $transaction->debit - $transaction->credit;

            if ($amount != 0) {
                $inputTaxData[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'reference_no' => $transaction->finance->reference_no,
                    'reference_date' => $transaction->finance->reference_date,
                    'description' => $transaction->finance->narration,
                    'amount' => $amount
                ];

                $totalInputTax += $amount;
            }
        }

        return [
            'input_tax_transactions' => $inputTaxData,
            'total_input_tax' => $totalInputTax
        ];
    }

    public function render()
    {
        $inputTaxData = $this->getInputTaxData();

        return view('livewire.report.finance.input-tax-table', [
            'inputTaxData' => $inputTaxData
        ]);
    }
}
