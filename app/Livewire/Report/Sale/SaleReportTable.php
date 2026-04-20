<?php

namespace App\Livewire\Report\Sale;

use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SaleReportTable extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $status = '';
    public $customerId = '';

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'searchChanged' => 'updateSearch',
        'statusChanged' => 'updateStatus',
        'customerChanged' => 'updateCustomer'
    ];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updateDateRange($dateRange)
    {
        $this->startDate = $dateRange['startDate'];
        $this->endDate = $dateRange['endDate'];
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function updateStatus($status)
    {
        $this->status = $status;
    }

    public function updateCustomer($customerId)
    {
        $this->customerId = $customerId;
    }

    public function getSaleReportData()
    {
        // Get sales (customer invoices) within date range
        $sales = CustomerInvoice::whereBetween(DB::raw('DATE(invoice_date)'), [$this->startDate, $this->endDate]);

        // Apply search filter if provided
        if (!empty($this->search)) {
            $sales = $sales->where(function ($query) {
                $query->where('row_no', 'like', '%' . $this->search . '%')
                    ->orWhere('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('name_en', 'like', '%' . $this->search . '%')
                            ->orWhere('name_ar', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('job', function ($q) {
                        $q->where('row_no', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Apply status filter if provided
        if (!empty($this->status)) {
            $sales = $sales->where('status', $this->status);
        }

        // Apply customer filter if provided
        if (!empty($this->customerId)) {
            $sales = $sales->where('customer_id', $this->customerId);
        }

        // Get sales with related data
        $sales = $sales->with(['customer', 'job', 'customerInvoiceSubs.description'])
            ->orderBy('invoice_date', 'desc')
            ->get();

        $totalAmount = 0;
        $totalTax = 0;
        $totalGrand = 0;

        foreach ($sales as $sale) {
            $totalAmount += $sale->sub_total ?? 0;
            $totalTax += $sale->tax_total ?? 0;
            $totalGrand += $sale->grand_total ?? 0;
        }

        return [
            'sales' => $sales,
            'total_amount' => $totalAmount,
            'total_tax' => $totalTax,
            'total_grand' => $totalGrand
        ];
    }

    public function render()
    {
        $saleReportData = $this->getSaleReportData();

        return view('livewire.report.sale.sale-report-table', [
            'saleReportData' => $saleReportData
        ]);
    }
}
