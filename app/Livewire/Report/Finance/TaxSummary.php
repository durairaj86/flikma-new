<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\FinanceSub;
use Livewire\Component;

class TaxSummary extends Component
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
        $inputVatAccountId  = 7;
        $outputVatAccountId = 20;

        $baseQuery = FinanceSub::where('is_tax_line', 1)
            ->whereBetween('reference_date', [$this->startDate, $this->endDate])
            ->whereHas('finance', fn($q) => $q->where('is_approved', 1));

        $inputData = (clone $baseQuery)
            ->where('account_id', $inputVatAccountId)
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalInputTax = ($inputData->total_debit ?? 0) - ($inputData->total_credit ?? 0);

        $outputData = (clone $baseQuery)
            ->where('account_id', $outputVatAccountId)
            ->selectRaw('SUM(credit) as total_credit, SUM(debit) as total_debit')
            ->first();

        $totalOutputTax = ($outputData->total_credit ?? 0) - ($outputData->total_debit ?? 0);

        $netTax = $totalOutputTax - $totalInputTax;

        return view('livewire.report.finance.tax-summary', [
            'summary' => [
                'total_input_tax'  => $totalInputTax,
                'total_output_tax' => $totalOutputTax,
                'net_tax'          => $netTax,
                'is_payable'       => $netTax >= 0,
            ],
        ]);
    }
}
