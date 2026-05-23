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

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function applyFilter()
    {
        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
        $this->dispatch('searchChanged', $this->search);
        $this->dispatch('statusChanged', $this->status);
        $this->dispatch('customerChanged', $this->customerId);
    }

    public function resetFilter()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->search = '';
        $this->status = '';
        $this->customerId = '';

        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
        $this->dispatch('searchChanged', '');
        $this->dispatch('statusChanged', '');
        $this->dispatch('customerChanged', '');
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
        $sales = CustomerInvoice::whereBetween(DB::raw('DATE(invoice_date)'), [$this->startDate, $this->endDate]);

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

        if (!empty($this->customerId)) {
            $sales = $sales->where('customer_id', $this->customerId);
        }

        $totalCount = $sales->count();
        $draftCount = (clone $sales)->where('status', 1)->count();
        $approvedCount = (clone $sales)->where('status', 3)->count();
        $cancelledCount = (clone $sales)->where('status', 4)->count();

        $totalAmount = (clone $sales)->sum('sub_total');
        $totalTax = (clone $sales)->sum('tax_total');
        $totalGrand = (clone $sales)->sum('grand_total');

        $draftAmount = (clone $sales)->where('status', 1)->sum('sub_total');
        $draftTax = (clone $sales)->where('status', 1)->sum('tax_total');
        $draftGrand = (clone $sales)->where('status', 1)->sum('grand_total');

        $approvedAmount = (clone $sales)->where('status', 3)->sum('sub_total');
        $approvedTax = (clone $sales)->where('status', 3)->sum('tax_total');
        $approvedGrand = (clone $sales)->where('status', 3)->sum('grand_total');

        $cancelledAmount = (clone $sales)->where('status', 4)->sum('sub_total');
        $cancelledTax = (clone $sales)->where('status', 4)->sum('tax_total');
        $cancelledGrand = (clone $sales)->where('status', 4)->sum('grand_total');

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

            'cancelled_amount' => $cancelledAmount,
            'cancelled_tax' => $cancelledTax,
            'cancelled_grand' => $cancelledGrand,
        ];
    }

    public function render()
    {
        $summary = $this->getSaleReportSummary();

        $customers = Customer::select('id', 'name_en', 'name_ar', 'row_no')
            ->where('status', 3)
            ->orderBy('name_en')
            ->get()
            ->toArray();

        return view('livewire.report.sale.sale-report', [
            'summary' => $summary,
            'customers' => $customers,
        ]);
    }
}
