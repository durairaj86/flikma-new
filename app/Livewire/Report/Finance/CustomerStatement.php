<?php

namespace App\Livewire\Report\Finance;

use App\Exports\Customer\CustomerStatementReportExport;
use App\Models\Customer\Customer;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CustomerStatement extends Component
{
    public string $startDate = '';
    public string $endDate = '';
    public string $search = '';
    public string|int $customerId = '';
    public ?string $currency = null;
    public ?float $currency_rate = null;
    public array $customers = [];

    public function mount()
    {
        $this->startDate = now()->subMonth(3)->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');

        $this->loadCustomers();

        // Deep-linked from a customer's "Statement" action — preselect that
        // customer instead of defaulting to the first one in the list.
        $requestedCustomerId = request()->query('customer');
        if ($requestedCustomerId && collect($this->customers)->contains('id', (int) $requestedCustomerId)) {
            $this->customerId = (string) $requestedCustomerId;
        } elseif (count($this->customers) > 0 && !$this->customerId) {
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
        $this->loadCustomers();
        if (count($this->customers) > 0) {
            $this->customerId = $this->customers[0]['id'];
        }

        $this->dispatch('statement-dates-reset', startDate: $this->startDate, endDate: $this->endDate);
    }

    public function exportExcel()
    {
        $data = $this->buildStatement();

        if (!$data['selectedCustomer']) {
            return;
        }

        $summary = [
            'name'          => $data['selectedCustomer']->name,
            'customer_code' => $data['selectedCustomer']->code,
            'start_date'    => $this->startDate,
            'end_date'      => $this->endDate,
            'opening'       => $data['openingBalance'],
            'total_debit'   => $data['totalDebit'],
            'total_credit'  => $data['totalCredit'],
            'closing'       => $data['closingBalance'],
            'base_currency' => optional(authUserCompany())->base_currency ?: 'SAR',
        ];

        $filename = 'CustomerStatement-' . $data['selectedCustomer']->code
            . '-' . $this->startDate . '_' . $this->endDate . '.xlsx';

        return Excel::download(new CustomerStatementReportExport($data['transactions'], $summary), $filename);
    }

    protected function buildStatement(): array
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
                $baseCurrency = optional(authUserCompany())->base_currency ?: 'SAR';

                // Opening balance: everything before startDate. Uses
                // base_grand_total (company-currency amount), not
                // grand_total (the invoice's own currency) — a foreign
                // currency invoice would otherwise silently corrupt the
                // running balance by mixing currencies together.
                $openingInvoices    = \App\Models\Finance\CustomerInvoice\CustomerInvoice::where('customer_id', $this->customerId)
                    ->where('company_id', $companyId)
                    ->where('invoice_date', '<', $this->startDate)
                    ->sum('base_grand_total');

                $openingCollections = \App\Models\Finance\Collection\Collection::where('customer_id', $this->customerId)
                    ->where('company_id', $companyId)
                    ->where('collection_date', '<', $this->startDate)
                    ->sum('base_grand_total');

                // Credit notes reduce AR exactly like a collection does — a
                // statement that omits them overstates what the customer
                // still owes by the full credited amount.
                $openingCreditNotes = \App\Models\Finance\Adjustment\CreditNote::where('customer_id', $this->customerId)
                    ->where('company_id', $companyId)
                    ->where('posted_at', '<', $this->startDate)
                    ->sum('base_grand_total');

                $openingBalance = (float)$openingInvoices - (float)$openingCollections - (float)$openingCreditNotes;

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

                // Period credit notes (credit)
                $creditNotes = \App\Models\Finance\Adjustment\CreditNote::where('customer_id', $this->customerId)
                    ->where('company_id', $companyId)
                    ->whereBetween('posted_at', [$this->startDate, $this->endDate])
                    ->orderBy('posted_at')
                    ->get();

                $totalDebit  = (float)$invoices->sum('base_grand_total');
                $totalCredit = (float)$collections->sum('base_grand_total') + (float)$creditNotes->sum('base_grand_total');

                foreach ($invoices as $inv) {
                    $isForeign = $inv->currency && $inv->currency !== $baseCurrency;

                    // Overdue = past due date with an outstanding balance —
                    // a fully-paid invoice past its due date isn't overdue.
                    $daysOverdue = 0;
                    $outstanding = (float)($inv->base_grand_total ?? 0) - (float)($inv->base_paid_amount ?? 0);
                    if ($inv->due_date && $outstanding > 0.01) {
                        $dueDate = \Carbon\Carbon::parse($inv->due_date)->startOfDay();
                        if ($dueDate->lessThan(now()->startOfDay())) {
                            $daysOverdue = $dueDate->diffInDays(now()->startOfDay());
                        }
                    }

                    $transactions[] = (object)[
                        'date'          => $inv->getRawOriginal('invoice_date') ?? $inv->invoice_date,
                        'display_date'  => \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y'),
                        'type'          => 'invoice',
                        'reference'     => $inv->row_no ?? $inv->invoice_no,
                        'description'   => 'Customer Invoice',
                        'debit'         => (float)($inv->base_grand_total ?? $inv->grand_total ?? 0),
                        'credit'        => 0,
                        'currency'      => $inv->currency,
                        'fcy_amount'    => $isForeign ? (float)($inv->grand_total ?? 0) : null,
                        'currency_rate' => $isForeign ? (float)($inv->currency_rate ?? 0) : null,
                        'days_overdue'  => $daysOverdue,
                    ];
                }

                foreach ($collections as $col) {
                    $isForeign = $col->currency && $col->currency !== $baseCurrency;
                    $transactions[] = (object)[
                        'date'          => $col->getRawOriginal('collection_date') ?? $col->collection_date,
                        'display_date'  => \Carbon\Carbon::parse($col->collection_date)->format('d-m-Y'),
                        'type'          => 'payment',
                        'reference'     => $col->row_no ?? $col->reference_no ?? 'COL-' . $col->id,
                        'description'   => 'Payment Received',
                        'debit'         => 0,
                        'credit'        => (float)($col->base_grand_total ?? $col->grand_total ?? 0),
                        'currency'      => $col->currency,
                        'fcy_amount'    => $isForeign ? (float)($col->grand_total ?? 0) : null,
                        'currency_rate' => $isForeign ? (float)($col->currency_rate ?? 0) : null,
                        'days_overdue'  => 0, // payments have no due date of their own
                    ];
                }

                foreach ($creditNotes as $cn) {
                    $isForeign = $cn->currency && $cn->currency !== $baseCurrency;
                    $transactions[] = (object)[
                        'date'          => $cn->getRawOriginal('posted_at') ?? $cn->posted_at,
                        'display_date'  => \Carbon\Carbon::parse($cn->posted_at)->format('d-m-Y'),
                        'type'          => 'credit_note',
                        'reference'     => $cn->row_no,
                        'description'   => 'Credit Note',
                        'debit'         => 0,
                        'credit'        => (float)($cn->base_grand_total ?? $cn->grand_total ?? 0),
                        'currency'      => $cn->currency,
                        'fcy_amount'    => $isForeign ? (float)($cn->grand_total ?? 0) : null,
                        'currency_rate' => $isForeign ? (float)($cn->currency_rate ?? 0) : null,
                        'days_overdue'  => 0,
                    ];
                }

                // Sort all transactions by date ascending
                usort($transactions, fn($a, $b) => strcmp($a->date ?? '', $b->date ?? ''));

                $closingBalance = $openingBalance + $totalDebit - $totalCredit;
            }
        }

        return [
            'selectedCustomer' => $selectedCustomer,
            'openingBalance'   => $openingBalance,
            'totalDebit'       => $totalDebit,
            'totalCredit'      => $totalCredit,
            'closingBalance'   => $closingBalance,
            'transactions'     => $transactions,
        ];
    }

    public function render()
    {
        $data = $this->buildStatement();

        return view('livewire.report.finance.customer-statement', [
            'company'         => authUserCompany(),
            'customers'       => $this->customers,
            'selectedCustomer'=> $data['selectedCustomer'],
            'openingBalance'  => $data['openingBalance'],
            'totalDebit'      => $data['totalDebit'],
            'totalCredit'     => $data['totalCredit'],
            'closingBalance'  => $data['closingBalance'],
            'transactions'    => $data['transactions'],
        ]);
    }
}
