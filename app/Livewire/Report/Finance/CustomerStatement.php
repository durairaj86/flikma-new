<?php

namespace App\Livewire\Report\Finance;

use Illuminate\Support\Facades\DB;

use App\Exports\Customer\CustomerStatementReportExport;
use App\Models\Customer\Customer;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CustomerStatement extends Component
{
    private const AR_ACCOUNT_ID = 5; // 1130 Accounts Receivable

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

    /** The shared customer dropdown posts hashed ids; keep the property as the plain id. */
    public function updatedCustomerId($value)
    {
        $this->customerId = $value ? (string) decodeId((string) $value) : '';
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

        $this->dispatch('statement-dates-reset', startDate: $this->startDate, endDate: $this->endDate,
            customer: $this->customerId ? encodeId((int) $this->customerId) : '');
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

                $companyId = companyId();
                $baseCurrency = optional(authUserCompany())->base_currency ?: 'SAR';

                // From the receivable (1130) lines of approved vouchers — the books, like the trial balance: drafts and
                // cancelled documents never appear, opening balances, journal vouchers and exchange differences do.
                $ar = fn () => DB::table('finance_subs as fs')->join('finance as f', 'fs.finance_id', '=', 'f.id')
                    ->where('fs.company_id', $companyId)->where('fs.customer_id', $this->customerId)
                    ->where('fs.account_id', self::AR_ACCOUNT_ID)->where('f.is_approved', 1);
                $opening = $ar()->where('fs.reference_date', '<', $this->startDate)->selectRaw('COALESCE(SUM(fs.base_debit - fs.base_credit), 0) n')->value('n');
                $openingBalance = (float) $opening;

                $lines = $ar()->whereBetween('fs.reference_date', [$this->startDate, $this->endDate])
                    ->orderBy('fs.reference_date')->orderBy('fs.id')
                    ->get(['fs.reference_date', 'fs.voucher_no', 'fs.voucher_type', 'fs.reference_no', 'fs.description', 'fs.currency', 'fs.exchange_rate',
                        'fs.debit', 'fs.credit', 'fs.base_debit', 'fs.base_credit']);

                // overdue days for invoices still open
                $invoices = \App\Models\Finance\CustomerInvoice\CustomerInvoice::where('customer_id', $this->customerId)
                    ->whereIn('row_no', $lines->where('voucher_type', 'CI')->pluck('voucher_no'))->get()->keyBy('row_no');
                $types = ['CI' => ['invoice', 'Customer Invoice'], 'CV' => ['payment', 'Payment Received'], 'CN' => ['credit_note', 'Credit Note'],
                    'OB' => ['opening_balance', 'Opening balance'], 'JV' => ['journal', 'Journal Voucher']];

                foreach ($lines as $l) {
                    $isForeign = $l->currency && $l->currency !== $baseCurrency && ((float) $l->debit + (float) $l->credit) > 0;
                    $daysOverdue = 0;
                    if ($l->voucher_type === 'CI' && ($inv = $invoices[$l->voucher_no] ?? null)) {
                        $outstanding = (float) $inv->grand_total - (float) $inv->paid_amount;
                        $due = $inv->due_at ?? $inv->due_date;
                        if ($due && $outstanding > 0.01 && \Carbon\Carbon::parse($due)->startOfDay()->lessThan(now()->startOfDay())) {
                            $daysOverdue = \Carbon\Carbon::parse($due)->startOfDay()->diffInDays(now()->startOfDay());
                        }
                    }
                    [$type, $label] = $types[$l->voucher_type] ?? [strtolower((string) $l->voucher_type), $l->description ?: (string) $l->voucher_type];
                    $transactions[] = (object) [
                        'date'          => $l->reference_date,
                        'display_date'  => \Carbon\Carbon::parse($l->reference_date)->format('d-m-Y'),
                        'type'          => $type,
                        'reference'     => $l->voucher_no ?: $l->reference_no,
                        'description'   => $l->voucher_type === 'CV' && $l->reference_no ? 'Payment Received' : $label,
                        'debit'         => (float) $l->base_debit,
                        'credit'        => (float) $l->base_credit,
                        'currency'      => $l->currency,
                        'fcy_amount'    => $isForeign ? abs((float) $l->debit - (float) $l->credit) : null,
                        'currency_rate' => $isForeign ? (float) $l->exchange_rate : null,
                        'days_overdue'  => $daysOverdue,
                    ];
                }
                $totalDebit = (float) $lines->sum('base_debit');
                $totalCredit = (float) $lines->sum('base_credit');

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
