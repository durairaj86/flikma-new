<?php

namespace App\Livewire\Report\Finance;

use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CustomerAgingSummary extends Component
{
    public $asOfDate;
    public $search = '';

    protected $listeners = [
        'asOfDateChanged' => 'updateAsOfDate',
        'searchChanged' => 'updateSearch'
    ];

    public function mount()
    {
        // Default to current date
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function updateAsOfDate($asOfDate)
    {
        $this->asOfDate = $asOfDate;
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function getAgingData()
    {
        // Get all customers
        $customers = Customer::when(!empty($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('name_en', 'like', '%' . $this->search . '%')
                        ->orWhere('row_no', 'like', '%' . $this->search . '%');
                });
            })
            /*->where('is_active', 1)*/
            ->orderBy('name_en')
            ->get();

        $asOfDate = Carbon::parse($this->asOfDate);
        $agingCustomers = [];
        $totals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'days_91_120' => 0,
            'days_over_120' => 0,
            'grand_total' => 0
        ];

        foreach ($customers as $customer) {
            // Get customer invoices that are not fully paid
            $invoices = CustomerInvoice::where('customer_id', $customer->id)
                ->where('status', 3) // Approved invoices
                ->where(function ($query) {
                    $query->whereRaw('grand_total > paid_amount')
                        ->orWhereNull('paid_amount');
                })
                ->orderBy('invoice_date')
                ->get();

            if ($invoices->isEmpty()) {
                continue; // Skip customers with no outstanding invoices
            }

            // Initialize customer aging buckets
            $customerTotals = [
                'current' => 0,
                'days_1_30' => 0,
                'days_31_60' => 0,
                'days_61_90' => 0,
                'days_91_120' => 0,
                'days_over_120' => 0,
                'total' => 0
            ];

            foreach ($invoices as $invoice) {
                $dueDate = Carbon::parse($invoice->due_at);
                $balance = $invoice->grand_total - ($invoice->paid_amount ?? 0);

                if ($balance <= 0) {
                    continue; // Skip fully paid invoices
                }

                // Calculate days overdue
                $daysOverdue = $dueDate->diffInDays($asOfDate, false);

                // Assign balance to appropriate aging bucket
                if ($daysOverdue < 0) {
                    // Not yet due
                    $customerTotals['current'] += $balance;
                    $totals['current'] += $balance;
                } elseif ($daysOverdue <= 30) {
                    $customerTotals['days_1_30'] += $balance;
                    $totals['days_1_30'] += $balance;
                } elseif ($daysOverdue <= 60) {
                    $customerTotals['days_31_60'] += $balance;
                    $totals['days_31_60'] += $balance;
                } elseif ($daysOverdue <= 90) {
                    $customerTotals['days_61_90'] += $balance;
                    $totals['days_61_90'] += $balance;
                } elseif ($daysOverdue <= 120) {
                    $customerTotals['days_91_120'] += $balance;
                    $totals['days_91_120'] += $balance;
                } else {
                    $customerTotals['days_over_120'] += $balance;
                    $totals['days_over_120'] += $balance;
                }

                $customerTotals['total'] += $balance;
                $totals['grand_total'] += $balance;
            }

            // Only add customers with outstanding balances
            if ($customerTotals['total'] > 0) {
                $agingCustomers[] = [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name_en,
                    'customer_code' => $customer->row_no,
                    'current' => $customerTotals['current'],
                    'days_1_30' => $customerTotals['days_1_30'],
                    'days_31_60' => $customerTotals['days_31_60'],
                    'days_61_90' => $customerTotals['days_61_90'],
                    'days_91_120' => $customerTotals['days_91_120'],
                    'days_over_120' => $customerTotals['days_over_120'],
                    'total' => $customerTotals['total']
                ];
            }
        }

        return [
            'customers' => $agingCustomers,
            'totals' => $totals
        ];
    }

    public function render()
    {
        $agingData = $this->getAgingData();

        return view('livewire.report.finance.customer-aging-summary-table', [
            'agingData' => $agingData
        ]);
    }
}
