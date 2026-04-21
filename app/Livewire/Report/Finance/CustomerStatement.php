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
        $this->startDate = now()->subMonth(3)->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');

        $this->loadCustomers();

        if (count($this->customers) > 0 && !$this->customerId) {
            $this->customerId = (string)$this->customers[0]['id'];
        }
    }

    public function loadCustomers()
    {
        $companyId = auth()->user()->company_id ?? 1;

        $query = Customer::where('company_id', $companyId);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name_en', 'like', '%' . $this->search . '%')
                    ->orWhere('name_ar', 'like', '%' . $this->search . '%');
            });
        }

        $this->customers = $query->select('id', 'row_no', 'name_en', 'company_id', 'email', 'phone', 'currency')
            ->get()
            ->toArray();

        // If customerId is set but not in loaded customers, clear it
        if ($this->customerId) {
            $found = false;
            foreach ($this->customers as $c) {
                if ($c['id'] == $this->customerId) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // $this->customerId = null; // Don't clear it immediately to avoid losing selection on search
            }
        }
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

    public function applyFilter()
    {
        $this->loadCustomers();
    }

    public function resetFilter()
    {
        $this->startDate = now()->subMonth(3)->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->search = '';
        $this->customerId = null;
        $this->loadCustomers();
    }

    public function exportExcel()
    {
        $this->dispatch('exportAsExcel');
    }

    public function render()
    {
        $selectedCustomer = null;
        $openingBalance = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        $closingBalance = 0;
        $transactions = [];

        // Find selected customer from loaded customers
        if ($this->customerId && count($this->customers) > 0) {
            foreach ($this->customers as $c) {
                if ($c['id'] == $this->customerId) {
                    $selectedCustomer = (object)[
                        'id' => $c['id'],
                        'code' => $c['row_no'] ?? 'CUST-'.str_pad($c['id'], 3, '0', STR_PAD_LEFT),
                        'name' => $c['name_en'],
                        'company' => $c['name_en'] ?? 'N/A',
                        'email' => $c['email'] ?? '',
                        'phone' => $c['phone'] ?? '',
                        'address' => 'N/A'
                    ];

                    // Get real customer data from database
                    $customerData = \App\Models\Customer\Customer::find($this->customerId);
                    if ($customerData) {
                        $selectedCustomer->name = $customerData->name_en;
                        $selectedCustomer->email = $customerData->email;
                        $selectedCustomer->phone = $customerData->phone;
                        $selectedCustomer->code = $customerData->row_no ?? 'CUST-'.str_pad($customerData->id, 3, '0', STR_PAD_LEFT);
                        $selectedCustomer->address = implode(', ', array_filter([
                            $customerData->address1_en,
                            $customerData->city_en,
                            $customerData->country_en
                        ])) ?: 'N/A';

                        // Calculate opening balance from transactions before startDate
                        $companyId = auth()->user()->company_id ?? 1;

                        $openingInvoices = \App\Models\Finance\CustomerInvoice\CustomerInvoice::where('customer_id', $this->customerId)
                            ->where('company_id', $companyId)
                            ->where('invoice_date', '<', $this->startDate)
                            ->sum('grand_total');

                        $openingCollections = \App\Models\Finance\Collection\Collection::where('customer_id', $this->customerId)
                            ->where('company_id', $companyId)
                            ->where('collection_date', '<', $this->startDate)
                            ->sum('grand_total');

                        $openingBalance = $openingInvoices - $openingCollections;

                        // Get invoices (debit) for the period
                        $invoices = \App\Models\Finance\CustomerInvoice\CustomerInvoice::where('customer_id', $this->customerId)
                            ->where('company_id', $companyId)
                            ->whereBetween('invoice_date', [$this->startDate, $this->endDate])
                            ->get();

                        // Get collections (credit) for the period
                        $collections = \App\Models\Finance\Collection\Collection::where('customer_id', $this->customerId)
                            ->where('company_id', $companyId)
                            ->whereBetween('collection_date', [$this->startDate, $this->endDate])
                            ->get();

                        $totalDebit = $invoices->sum('grand_total');
                        $totalCredit = $collections->sum('grand_total');

                        // Build transactions
                        foreach ($invoices as $inv) {
                            $transactions[] = (object)[
                                'date' => $inv->getRawOriginal('invoice_date') ?? $inv->invoice_date, // Get Y-m-d for sorting
                                'display_date' => $inv->invoice_date,
                                'type' => 'invoice',
                                'reference' => $inv->invoice_no,
                                'description' => 'Invoice',
                                'debit' => $inv->grand_total ?? 0,
                                'credit' => 0
                            ];
                        }

                        foreach ($collections as $col) {
                            $transactions[] = (object)[
                                'date' => $col->getRawOriginal('collection_date') ?? $col->collection_date, // Get Y-m-d for sorting
                                'display_date' => $col->collection_date,
                                'type' => 'payment',
                                'reference' => $col->reference_no ?? 'COL-'.$col->id,
                                'description' => 'Payment Received',
                                'debit' => 0,
                                'credit' => $col->grand_total ?? 0
                            ];
                        }

                        // Sort by date
                        usort($transactions, function($a, $b) {
                            return strcmp($a->date ?? '', $b->date ?? '');
                        });

                        $closingBalance = $openingBalance + $totalDebit - $totalCredit;
                    }
                    break;
                }
            }
        }

        return view('livewire.report.finance.customer-statement', [
            'selectedCustomer' => $selectedCustomer,
            'openingBalance' => $openingBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'closingBalance' => $closingBalance,
            'transactions' => $transactions,
        ]);
    }
}
