<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\FinanceSub;
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
        // Fixed account IDs (from chart of accounts):
        //   Input VAT  = account 7  (code 1150, type Asset  — DR balance)
        //   Output VAT = account 20 (code 2130, type Liability — CR balance)
        //
        // VAT payable to authority = Output VAT collected − Input VAT reclaimable
        //
        // Date filter is on finance_sub.reference_date (actual transaction date)

        $inputVatAccountId  = 7;
        $outputVatAccountId = 20;

        $baseQuery = FinanceSub::where('is_tax_line', 1)
            ->whereBetween('reference_date', [$this->startDate, $this->endDate])
            ->whereHas('finance', function ($q) {
                $q->where('is_approved', 1);
            });

        // Apply search
        if (!empty($this->search)) {
            $baseQuery->where(function ($q) {
                $q->where('reference_no', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('finance', function ($sq) {
                      $sq->where('narration', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // --- Input VAT ---
        $inputData = (clone $baseQuery)
            ->where('account_id', $inputVatAccountId)
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalInputTax = ($inputData->total_debit ?? 0) - ($inputData->total_credit ?? 0);

        // --- Output VAT ---
        $outputData = (clone $baseQuery)
            ->where('account_id', $outputVatAccountId)
            ->selectRaw('SUM(credit) as total_credit, SUM(debit) as total_debit')
            ->first();

        $totalOutputTax = ($outputData->total_credit ?? 0) - ($outputData->total_debit ?? 0);

        // Build summary rows
        $taxData = [];

        if ($totalInputTax != 0) {
            $taxData[] = [
                'account_code' => '1150',
                'account_name' => 'Input VAT',
                'type'         => 'Input Tax',
                'balance'      => $totalInputTax,
            ];
        }

        if ($totalOutputTax != 0) {
            $taxData[] = [
                'account_code' => '2130',
                'account_name' => 'Output VAT Payable',
                'type'         => 'Output Tax',
                'balance'      => $totalOutputTax,
            ];
        }

        // Net VAT payable = Output VAT collected − Input VAT reclaimable
        // Positive = payable to tax authority; Negative = refundable
        $netTax = $totalOutputTax - $totalInputTax;

        return [
            'tax_accounts'     => $taxData,
            'total_input_tax'  => $totalInputTax,
            'total_output_tax' => $totalOutputTax,
            'net_tax'          => $netTax,
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
