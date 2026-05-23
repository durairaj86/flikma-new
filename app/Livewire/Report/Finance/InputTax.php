<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\FinanceSub;
use Livewire\Component;

class InputTax extends Component
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
        $inputVatAccountId = 7;

        $query = FinanceSub::where('is_tax_line', 1)
            ->where('account_id', $inputVatAccountId)
            ->whereBetween('reference_date', [$this->startDate, $this->endDate])
            ->whereHas('finance', fn($q) => $q->where('is_approved', 1));

        $totalInputTax = (clone $query)->sum('debit') - (clone $query)->sum('credit');
        $count = (clone $query)->where('debit', '>', 0)->count();

        return view('livewire.report.finance.input-tax', [
            'summary' => [
                'total_input_tax' => $totalInputTax > 0 ? $totalInputTax : 0,
                'transaction_count' => $count,
            ],
        ]);
    }
}
