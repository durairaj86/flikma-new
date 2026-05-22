<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\FinanceSub;
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
        // Input VAT account ID = 7 (code 1150 — "Input VAT", type Asset)
        // Input tax lines are DEBITS on the supplier invoice (DR Input VAT / CR AP)
        // We filter directly on finance_sub.reference_date (the actual transaction date)

        $inputVatAccountId = 7; // 1150 Input VAT

        $query = FinanceSub::where('is_tax_line', 1)
            ->where('account_id', $inputVatAccountId)
            ->whereBetween('reference_date', [$this->startDate, $this->endDate])
            ->whereHas('finance', function ($q) {
                $q->where('is_approved', 1);
            })
            ->with(['finance:id,voucher_no,voucher_type,narration,supplier_id']);

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reference_no', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('finance', function ($sq) {
                      $sq->where('narration', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $transactions = $query->orderBy('reference_date')->get();

        $inputTaxData = [];
        $totalInputTax = 0;

        foreach ($transactions as $transaction) {
            // Input VAT = debit amount (DR Input VAT on purchase invoice)
            $amount = $transaction->debit - $transaction->credit;

            if ($amount == 0) {
                continue;
            }

            $inputTaxData[] = [
                'account_code'   => '1150',
                'account_name'   => 'Input VAT',
                'voucher_no'     => $transaction->voucher_no,
                'voucher_type'   => $transaction->voucher_type,
                'reference_no'   => $transaction->reference_no,
                'reference_date' => $transaction->reference_date, // from finance_sub
                'description'    => $transaction->description,    // from finance_sub
                'amount'         => $amount,
            ];

            $totalInputTax += $amount;
        }

        return [
            'input_tax_transactions' => $inputTaxData,
            'total_input_tax'        => $totalInputTax,
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
