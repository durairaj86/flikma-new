<?php

namespace App\Livewire\Report\Finance;

use App\Models\Supplier\Supplier;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SupplierAgingTable extends Component
{
    public $asOfDate;
    public $supplierId;
    public $search = '';

    protected $listeners = [
        'asOfDateChanged' => 'updateAsOfDate',
        'supplierChanged' => 'updateSupplier',
        'searchChanged' => 'updateSearch',
        'exportAsExcel' => 'exportAsExcel'
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

    public function updateSupplier($supplierId)
    {
        $this->supplierId = $supplierId;
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function exportAsExcel()
    {
        $agingData = $this->getAgingData();

        // Get supplier name
        $supplierName = '';
        if (!empty($this->supplierId)) {
            $supplier = Supplier::find($this->supplierId);
            if ($supplier) {
                $supplierName = $supplier->name_en;
            }
        }

        return Excel::download(
            new \App\Exports\Supplier\SupplierAgingExport(
                $agingData['invoices'],
                $agingData['totals'],
                $supplierName
            ),
            'SupplierAging.xlsx'
        );
    }

    public function getAgingData()
    {
        if (empty($this->supplierId)) {
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

        // Get supplier invoices that are not fully paid
        $invoices = SupplierInvoice::where('supplier_id', $this->supplierId)
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

        return view('livewire.report.finance.supplier-aging-table', [
            'agingData' => $agingData
        ]);
    }
}
