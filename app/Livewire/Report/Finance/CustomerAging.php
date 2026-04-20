<?php

namespace App\Livewire\Report\Finance;

use App\Models\Customer\Customer;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CustomerAging extends Component
{
    public $asOfDate;
    public $customerId;
    public $search = '';
    public $customers = [];

    public function mount()
    {
        // Default to current date
        $this->asOfDate = now()->format('Y-m-d');

        // Load customers
        $this->loadCustomers();

        // Set default customer if available
        if (count($this->customers) > 0) {
            $this->customerId = $this->customers[0]['id'];
        }
    }

    public function loadCustomers()
    {
        $query = Customer::select('id', 'name_en', 'name_ar', 'row_no')
            ->where('status', 3) // Assuming 3 is confirmed status
            ->orderBy('name_en');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name_en', 'like', '%' . $this->search . '%')
                  ->orWhere('name_ar', 'like', '%' . $this->search . '%')
                  ->orWhere('row_no', 'like', '%' . $this->search . '%');
            });
        }

        $this->customers = $query->get()->toArray();
    }

    public function updatedAsOfDate()
    {
        $this->dispatch('asOfDateChanged', $this->asOfDate);
    }

    public function updatedCustomerId()
    {
        $this->dispatch('customerChanged', $this->customerId);
    }

    public function updatedSearch()
    {
        $this->loadCustomers();
        $this->dispatch('searchChanged', $this->search);
    }

    public function render()
    {
        return view('livewire.report.finance.customer-aging');
    }
}
