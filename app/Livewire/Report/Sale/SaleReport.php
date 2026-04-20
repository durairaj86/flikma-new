<?php

namespace App\Livewire\Report\Sale;

use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SaleReport extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $status = '';
    public $customerId = '';
    public $customers = [];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

        // Load customers
        $this->loadCustomers();
    }

    public function loadCustomers()
    {
        $query = Customer::select('id', 'name_en', 'name_ar', 'row_no')
            ->where('status', 3) // Assuming 3 is confirmed status
            ->orderBy('name_en');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name_en', 'like', '%' . $this->search . '%')
                  ->orWhere('name_ar', 'like', '%' . $this->search . '%')
                  ->orWhere('row_no', 'like', '%' . $this->search . '%');
            });
        }

        $this->customers = $query->get()->toArray();
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

    public function updatedStatus($value)
    {
        $this->dispatch('statusChanged', $value);
    }

    public function updatedCustomerId($value)
    {
        $this->dispatch('customerChanged', $value);
    }

    public function getSaleReportSummary()
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

        // Apply customer filter if provided
        if (!empty($this->customerId)) {
            $sales = $sales->where('customer_id', $this->customerId);
        }

        // Get counts by status
        $totalCount = $sales->count();
        $draftCount = (clone $sales)->where('status', 1)->count();
        $approvedCount = (clone $sales)->where('status', 3)->count();
        $cancelledCount = (clone $sales)->where('status', 4)->count();

        // Get totals by status
        $totalAmount = (clone $sales)->sum('sub_total');
        $totalTax = (clone $sales)->sum('tax_total');
        $totalGrand = (clone $sales)->sum('grand_total');

        $draftAmount = (clone $sales)->where('status', 1)->sum('sub_total');
        $draftTax = (clone $sales)->where('status', 1)->sum('tax_total');
        $draftGrand = (clone $sales)->where('status', 1)->sum('grand_total');

        $approvedAmount = (clone $sales)->where('status', 3)->sum('sub_total');
        $approvedTax = (clone $sales)->where('status', 3)->sum('tax_total');
        $approvedGrand = (clone $sales)->where('status', 3)->sum('grand_total');

        return [
            'total_count' => $totalCount,
            'draft_count' => $draftCount,
            'approved_count' => $approvedCount,
            'cancelled_count' => $cancelledCount,

            'total_amount' => $totalAmount,
            'total_tax' => $totalTax,
            'total_grand' => $totalGrand,

            'draft_amount' => $draftAmount,
            'draft_tax' => $draftTax,
            'draft_grand' => $draftGrand,

            'approved_amount' => $approvedAmount,
            'approved_tax' => $approvedTax,
            'approved_grand' => $approvedGrand,
        ];
    }

    public function render()
    {
        $summary = $this->getSaleReportSummary();

        return view('livewire.report.sale.sale-report', [
            'summary' => $summary
        ]);
    }
}
