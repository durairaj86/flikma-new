<?php

namespace App\Livewire\Report\Finance;

use App\Models\Finance\Account\Account;
use Livewire\Component;

class GeneralLedger extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $accountId = 'all';
    public $accounts = [];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

        // Load accounts for dropdown
        $this->loadAccounts();
    }

    public function loadAccounts()
    {
        $this->accounts = Account::where('is_active', 1)
            ->orderBy('code')
            ->select('id', 'code', 'name', 'type')
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
        $this->dispatch('searchChanged', $value);
    }

    public function updatedAccountId($value)
    {
        $this->dispatch('accountChanged', $this->accountId);
    }

    public function render()
    {
        return view('livewire.report.finance.general-ledger');
    }
}
