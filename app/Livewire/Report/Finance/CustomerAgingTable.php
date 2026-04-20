<?php

namespace App\Livewire\Report\Finance;

use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CustomerAgingTable extends Component
{
    public $asOfDate;
    public $customerId;
    public $search = '';

    protected $listeners = [
        'asOfDateChanged' => 'updateAsOfDate',
        'customerChanged' => 'updateCustomer',
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

    public function updateCustomer($customerId)
    {
        $this->customerId = $customerId;
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function getAgingData()
    {
        if (empty($this->customerId)) {
            return [
                'invoices' => [],
                'totals' => [
                    'current' => 0,
                    'days_1_30' => 0,
                    'days_31_60' => 0,
                    'days_61_90' => 0,
                    'days_91_120' => 0,
                    'days_over_120' => 0,
                    'grand_total' => 0
                ]
            ];
        }

        // Get customer invoices that are not fully paid
        $invoices = CustomerInvoice::where('customer_id', $this->customerId)
            ->where('status', 3) // Approved invoices
            ->where(function ($query) {
                $query->whereRaw('grand_total > paid_amount')
                    ->orWhereNull('paid_amount');
            })
            ->when(!empty($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('row_no', 'like', '%' . $this->search . '%')
                        ->orWhere('invoice_number', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('invoice_date')
            ->get();

        $asOfDate = Carbon::parse($this->asOfDate);
        $agingInvoices = [];
        $totals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'days_91_120' => 0,
            'days_over_120' => 0,
            'grand_total' => 0
        ];

        foreach ($invoices as $invoice) {
            $dueDate = Carbon::parse($invoice->due_at);
            $balance = $invoice->grand_total - ($invoice->paid_amount ?? 0);

            if ($balance <= 0) {
                continue; // Skip fully paid invoices
            }

            // Calculate days overdue
            $daysOverdue = $dueDate->diffInDays($asOfDate, false);

            // Initialize aging buckets
            $current = 0;
            $days_1_30 = 0;
            $days_31_60 = 0;
            $days_61_90 = 0;
            $days_91_120 = 0;
            $days_over_120 = 0;

            // Assign balance to appropriate aging bucket
            if ($daysOverdue < 0) {
                // Not yet due
                $current = $balance;
                $totals['current'] += $balance;
            } elseif ($daysOverdue <= 30) {
                $days_1_30 = $balance;
                $totals['days_1_30'] += $balance;
            } elseif ($daysOverdue <= 60) {
                $days_31_60 = $balance;
                $totals['days_31_60'] += $balance;
            } elseif ($daysOverdue <= 90) {
                $days_61_90 = $balance;
                $totals['days_61_90'] += $balance;
            } elseif ($daysOverdue <= 120) {
                $days_91_120 = $balance;
                $totals['days_91_120'] += $balance;
            } else {
                $days_over_120 = $balance;
                $totals['days_over_120'] += $balance;
            }

            $agingInvoices[] = [
                'invoice_no' => $invoice->row_no,
                'invoice_date' => Carbon::parse($invoice->invoice_date)->format('d-m-Y'),
                'due_date' => $dueDate->format('d-m-Y'),
                'current' => $current,
                'days_1_30' => $days_1_30,
                'days_31_60' => $days_31_60,
                'days_61_90' => $days_61_90,
                'days_91_120' => $days_91_120,
                'days_over_120' => $days_over_120,
                'total' => $balance
            ];

            $totals['grand_total'] += $balance;
        }

        return [
            'invoices' => $agingInvoices,
            'totals' => $totals
        ];
    }

    public function render()
    {
        $agingData = $this->getAgingData();

        return view('livewire.report.finance.customer-aging-table', [
            'agingData' => $agingData
        ]);
    }
}
