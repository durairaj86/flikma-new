<?php

namespace App\Livewire\Report\Finance;

use App\Models\Supplier\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SupplierAging extends Component
{
    public $asOfDate;
    public $supplierId;
    public $search = '';
    public $suppliers = [];

    public function mount()
    {
        // Default to current date
        $this->asOfDate = now()->format('Y-m-d');

        // Load suppliers
        $this->loadSuppliers();

        // Set default supplier if available
        if (count($this->suppliers) > 0) {
            $this->supplierId = $this->suppliers[0]['id'];
        }
    }

    public function loadSuppliers()
    {
        $query = Supplier::select('id', 'name_en', 'name_ar', 'row_no')
            ->where('status', 3) // Assuming 3 is confirmed status
            ->orderBy('name_en');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name_en', 'like', '%' . $this->search . '%')
                  ->orWhere('name_ar', 'like', '%' . $this->search . '%')
                  ->orWhere('row_no', 'like', '%' . $this->search . '%');
            });
        }

        $this->suppliers = $query->get()->toArray();
    }

    public function updatedAsOfDate()
    {
        $this->dispatch('asOfDateChanged', $this->asOfDate);
    }

    public function updatedSupplierId()
    {
        $this->dispatch('supplierChanged', $this->supplierId);
    }

    public function updatedSearch()
    {
        $this->loadSuppliers();
        $this->dispatch('searchChanged', $this->search);
    }

    public function exportExcel()
    {
        $this->dispatch('exportAsExcel');
    }

    public function render()
    {
        return view('livewire.report.finance.supplier-aging');
    }
}
