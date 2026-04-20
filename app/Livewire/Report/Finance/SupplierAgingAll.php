<?php

namespace App\Livewire\Report\Finance;

use Livewire\Component;

class SupplierAgingAll extends Component
{
    public $asOfDate;
    public $search = '';

    public function mount()
    {
        // Default to current date
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function updatedAsOfDate()
    {
        $this->dispatch('asOfDateChanged', $this->asOfDate);
    }

    public function updatedSearch()
    {
        $this->dispatch('searchChanged', $this->search);
    }

    public function render()
    {
        return view('livewire.report.finance.supplier-aging-summary');
    }
}
