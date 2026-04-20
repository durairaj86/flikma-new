<?php

namespace App\Livewire\Report\Finance;

use App\Models\Supplier\Supplier;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SupplierAgingSummary extends Component
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
        // Get all suppliers
        $suppliers = Supplier::when(!empty($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('name_en', 'like', '%' . $this->search . '%')
                        ->orWhere('row_no', 'like', '%' . $this->search . '%');
                });
            })
            /*->where('is_active', 1)*/
            ->orderBy('name_en')
            ->get();

        $asOfDate = Carbon::parse($this->asOfDate);
        $agingSuppliers = [];
        $totals = [
            'current' => 0,
            'days_1_30' => 0,
            'days_31_60' => 0,
            'days_61_90' => 0,
            'days_91_120' => 0,
            'days_over_120' => 0,
            'grand_total' => 0
        ];

        foreach ($suppliers as $supplier) {
            // Get supplier invoices that are not fully paid
            $invoices = SupplierInvoice::where('supplier_id', $supplier->id)
                ->where('status', 3) // Approved invoices
                ->where(function ($query) {
                    $query->whereRaw('grand_total > paid_amount')
                        ->orWhereNull('paid_amount');
                })
                ->orderBy('invoice_date')
                ->get();

            if ($invoices->isEmpty()) {
                continue; // Skip suppliers with no outstanding invoices
            }

            // Initialize supplier aging buckets
            $supplierTotals = [
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
                    $supplierTotals['current'] += $balance;
                    $totals['current'] += $balance;
                } elseif ($daysOverdue <= 30) {
                    $supplierTotals['days_1_30'] += $balance;
                    $totals['days_1_30'] += $balance;
                } elseif ($daysOverdue <= 60) {
                    $supplierTotals['days_31_60'] += $balance;
                    $totals['days_31_60'] += $balance;
                } elseif ($daysOverdue <= 90) {
                    $supplierTotals['days_61_90'] += $balance;
                    $totals['days_61_90'] += $balance;
                } elseif ($daysOverdue <= 120) {
                    $supplierTotals['days_91_120'] += $balance;
                    $totals['days_91_120'] += $balance;
                } else {
                    $supplierTotals['days_over_120'] += $balance;
                    $totals['days_over_120'] += $balance;
                }

                $supplierTotals['total'] += $balance;
                $totals['grand_total'] += $balance;
            }

            // Only add suppliers with outstanding balances
            if ($supplierTotals['total'] > 0) {
                $agingSuppliers[] = [
                    'supplier_id' => $supplier->id,
                    'supplier_name' => $supplier->name_en,
                    'supplier_code' => $supplier->row_no,
                    'current' => $supplierTotals['current'],
                    'days_1_30' => $supplierTotals['days_1_30'],
                    'days_31_60' => $supplierTotals['days_31_60'],
                    'days_61_90' => $supplierTotals['days_61_90'],
                    'days_91_120' => $supplierTotals['days_91_120'],
                    'days_over_120' => $supplierTotals['days_over_120'],
                    'total' => $supplierTotals['total']
                ];
            }
        }

        return [
            'suppliers' => $agingSuppliers,
            'totals' => $totals
        ];
    }

    public function render()
    {
        $agingData = $this->getAgingData();

        return view('livewire.report.finance.supplier-aging-summary-table', [
            'agingData' => $agingData
        ]);
    }
}
