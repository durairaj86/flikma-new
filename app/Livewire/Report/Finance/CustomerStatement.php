<?php

namespace App\Livewire\Report\Finance;

use App\Models\Customer\Customer;
use Livewire\Component;

class CustomerStatement extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $customerId;
    public $currency;
    public $currency_rate;
    public $customers = [];

    public function mount()
    {
        // Default to current month
        /*$this->startDate = now()->subMonth(6)->startOfMonth()->format('d-m-Y');
        $this->endDate = now()->subMonth(6)->endOfMonth()->format('d-m-Y');*/

        // Load customers for dropdown
        $this->loadCustomers();

        // Set default customer if available
        if (count($this->customers) > 0) {
            $this->customerId = $this->customers[0]['id'];
        }
    }

    public function loadCustomers()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $this->customers = Customer::where('company_id', $companyId)
            ->when(!empty($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('name_en', 'like', '%' . $this->search . '%')
                        ->orWhere('name_ar', 'like', '%' . $this->search . '%')/*->orWhere('code', 'like', '%' . $this->search . '%')*/
                    ;
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

    public function updatedCurrency($value)
    {
        $this->dispatch('currencyChanged', [
            'currency' => $this->currency,
            'currency_rate' => $this->currency_rate
        ]);
    }

    public function updatedSearch($value)
    {
        $this->loadCustomers();
    }

    public function updatedCustomerId($value)
    {
        $this->dispatch('customerChanged', $this->customerId);
    }

    public function exportExcel()
    {
        $this->dispatch('exportAsExcel');
    }

    public function render()
    {
        return view('livewire.report.finance.customer-statement');
    }
}
