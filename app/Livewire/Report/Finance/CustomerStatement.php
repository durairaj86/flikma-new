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
        // Triggers re-render with current filter values
    }

    public function resetFilter()
    {
        $this->startDate = now()->subMonth(3)->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->search = '';
        $this->customerId = null;
        $this->loadCustomers();
        if (count($this->customers) > 0) {
            $this->customerId = (string)$this->customers[0]['id'];
        }
    }

    public function exportExcel()
    {
        $this->dispatch('exportAsExcel');
    }

    public function render()
    {
        $selectedCustomer = null;
        $openingBalance   = 0;
        $totalDebit       = 0;
        $totalCredit      = 0;
        $closingBalance   = 0;
        $transactions     = [];

        if (!empty($this->customerId)) {
            // Query directly — do NOT rely on looping $this->customers
            $customerData = \App\Models\Customer\Customer::find($this->customerId);

            if ($customerData) {
                $selectedCustomer = (object)[
                    'id'      => $customerData->id,
                    'code'    => $customerData->row_no ?? 'CUST-' . str_pad($customerData->id, 3, '0', STR_PAD_LEFT),
                    'name'    => $customerData->name_en,
                    'email'   => $customerData->email ?? '',
                    'phone'   => $customerData->phone ?? '',
                    'address' => implode(', ', array_filter([
                        $customerData->address1_en ?? null,
                        $customerData->city_en     ?? null,
                        $customerData->country_en  ?? null,
                    ])) ?: 'N/A',
                ];

                $companyId = auth()->user()->company_id ?? 1;

                // Opening balance: everything before startDate
                $openingInvoices    = \App\Models\Finance\CustomerInvoice\CustomerInvoice::where('customer_id', $this->customerId)
                    ->where('company_id', $companyId)
                    ->where('invoice_date', '<', $this->startDate)
                    ->sum('grand_total');

                $openingCollections = \App\Models\Finance\Collection\Collection::where('customer_id', $this->customerId)
                    ->where('company_id', $companyId)
                    ->where('collection_date', '<', $this->startDate)
                    ->sum('grand_total');

                $openingBalance = (float)$openingInvoices - (float)$openingCollections;

                // Period invoices (debit)
                $invoices = \App\Models\Finance\CustomerInvoice\CustomerInvoice::where('customer_id', $this->customerId)
                    ->where('company_id', $companyId)
                    ->whereBetween('invoice_date', [$this->startDate, $this->endDate])
                    ->orderBy('invoice_date')
                    ->get();

                // Period collections (credit)
                $collections = \App\Models\Finance\Collection\Collection::where('customer_id', $this->customerId)
                    ->where('company_id', $companyId)
                    ->whereBetween('collection_date', [$this->startDate, $this->endDate])
                    ->orderBy('collection_date')
                    ->get();

                $totalDebit  = (float)$invoices->sum('grand_total');
                $totalCredit = (float)$collections->sum('grand_total');

                foreach ($invoices as $inv) {
                    $transactions[] = (object)[
                        'date'         => $inv->getRawOriginal('invoice_date') ?? $inv->invoice_date,
                        'display_date' => \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y'),
                        'type'         => 'invoice',
                        'reference'    => $inv->row_no ?? $inv->invoice_no,
                        'description'  => 'Customer Invoice',
                        'debit'        => (float)($inv->grand_total ?? 0),
                        'credit'       => 0,
                    ];
                }

                foreach ($collections as $col) {
                    $transactions[] = (object)[
                        'date'         => $col->getRawOriginal('collection_date') ?? $col->collection_date,
                        'display_date' => \Carbon\Carbon::parse($col->collection_date)->format('d M Y'),
                        'type'         => 'payment',
                        'reference'    => $col->row_no ?? $col->reference_no ?? 'COL-' . $col->id,
                        'description'  => 'Payment Received',
                        'debit'        => 0,
                        'credit'       => (float)($col->grand_total ?? 0),
                    ];
                }

                // Sort all transactions by date ascending
                usort($transactions, fn($a, $b) => strcmp($a->date ?? '', $b->date ?? ''));

                $closingBalance = $openingBalance + $totalDebit - $totalCredit;
            }
        }

        return view('livewire.report.finance.customer-statement', [
            'customers'       => $this->customers,
            'selectedCustomer'=> $selectedCustomer,
            'openingBalance'  => $openingBalance,
            'totalDebit'      => $totalDebit,
            'totalCredit'     => $totalCredit,
            'closingBalance'  => $closingBalance,
            'transactions'    => $transactions,
        ]);
    }
}
