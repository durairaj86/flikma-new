<?php

namespace App\Livewire\Report\Finance;

use App\Models\Supplier\Supplier;
use Livewire\Component;

class SupplierStatement extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $supplierId;
    public $currency;
    public $currency_rate;
    public $suppliers = [];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->currency = authUserCompany()->currency;
        $this->currency_rate = 1;//default

        // Load suppliers for dropdown
        $this->loadSuppliers();

        // Set default supplier if available
        if (count($this->suppliers) > 0) {
            $this->supplierId = $this->suppliers[0]['id'];
        }
    }

    public function loadSuppliers()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $this->suppliers = Supplier::where('company_id', $companyId)
            ->when(!empty($this->search), function($query) {
                $query->where(function($q) {
                    $q->where('name_en', 'like', '%' . $this->search . '%')
                      ->orWhere('name_ar', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->select('id', 'row_no', 'name_en', 'company_id', 'email', 'phone', 'currency')
            ->get()
            ->toArray();
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
        $this->loadSuppliers();
    }

    public function updatedSupplierId($value)
    {
        $this->dispatch('supplierChanged', $this->supplierId);
    }

    public function updatedCurrency($value)
    {
        $this->dispatch('currencyChanged', [
            'currency' => $this->currency,
            'currency_rate' => $this->currency_rate
        ]);
    }

    public function exportExcel()
    {
        $this->dispatch('exportAsExcel');
    }

    public function render()
    {
        return view('livewire.report.finance.supplier-statement');
    }
}
