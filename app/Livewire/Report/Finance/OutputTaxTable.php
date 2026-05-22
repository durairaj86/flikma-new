<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\FinanceSub;
use Livewire\Component;

class OutputTaxTable extends Component
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

    public function getOutputTaxData()
    {
        // Output VAT account ID = 20 (code 2130 — "Output VAT Payable", type Liability)
        // Output tax lines are CREDITS on the customer invoice (DR AR / CR Sales / CR Output VAT)
        // We filter directly on finance_sub.reference_date (the actual transaction date)

        $outputVatAccountId = 20; // 2130 Output VAT Payable

        $query = FinanceSub::where('is_tax_line', 1)
            ->where('account_id', $outputVatAccountId)
            ->whereBetween('reference_date', [$this->startDate, $this->endDate])
            ->whereHas('finance', function ($q) {
                $q->where('is_approved', 1);
            })
            ->with(['finance:id,voucher_no,voucher_type,narration,customer_id']);

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

        $outputTaxData = [];
        $totalOutputTax = 0;

        foreach ($transactions as $transaction) {
            // Output VAT = credit amount (CR Output VAT Payable on customer invoice)
            $amount = $transaction->credit - $transaction->debit;

            if ($amount == 0) {
                continue;
            }

            $outputTaxData[] = [
                'account_code'   => '2130',
                'account_name'   => 'Output VAT Payable',
                'voucher_no'     => $transaction->voucher_no,
                'voucher_type'   => $transaction->voucher_type,
                'reference_no'   => $transaction->reference_no,
                'reference_date' => $transaction->reference_date, // from finance_sub
                'description'    => $transaction->description,    // from finance_sub
                'amount'         => $amount,
            ];

            $totalOutputTax += $amount;
        }

        return [
            'output_tax_transactions' => $outputTaxData,
            'total_output_tax'        => $totalOutputTax,
        ];
    }

    public function render()
    {
        $outputTaxData = $this->getOutputTaxData();

        return view('livewire.report.finance.output-tax-table', [
            'outputTaxData' => $outputTaxData
        ]);
    }
}
