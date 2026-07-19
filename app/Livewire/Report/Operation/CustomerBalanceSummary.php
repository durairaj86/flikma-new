<?php

namespace App\Livewire\Report\Operation;

use App\Exports\ReportTableExport;
use App\Models\Customer\Customer;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class CustomerBalanceSummary extends Component
{
    public $startDate;
    public $endDate;
    public $customerId = '';
    public $customers = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->endOfMonth()->format('Y-m-d');
        $this->loadCustomers();
    }

    public function loadCustomers()
    {
        $this->customers = Customer::where('company_id', auth()->user()->company_id ?? 1)
            ->orderBy('name_en')
            ->select('id', 'row_no', 'name_en')
            ->get()
            ->toArray();
    }

    public function applyFilter()
    {
        // Triggers re-render
    }

    public function resetFilter()
    {
        $this->startDate  = now()->startOfMonth()->format('Y-m-d');
        $this->endDate    = now()->endOfMonth()->format('Y-m-d');
        $this->customerId = '';
    }

    protected function getReportData(): array
    {
        $companyId = auth()->user()->company_id ?? 1;

        $customers = Customer::where('company_id', $companyId);

        if (!empty($this->customerId)) {
            $customers->where('id', $this->customerId);
        }

        $customers = $customers->orderBy('name_en')->get();

        $rows   = [];
        $totals = [
            'opening' => 0.0,
            'invoiced' => 0.0,
            'received' => 0.0,
            'closing' => 0.0,
        ];

        foreach ($customers as $customer) {
            // Opening balance: everything before startDate — same formula
            // used by the Customer Statement report.
            $openingInvoiced = (float) CustomerInvoice::where('customer_id', $customer->id)
                ->where('company_id', $companyId)
                ->where('invoice_date', '<', $this->startDate)
                ->sum('grand_total');

            $openingReceived = (float) Collection::where('customer_id', $customer->id)
                ->where('company_id', $companyId)
                ->where('collection_date', '<', $this->startDate)
                ->sum('grand_total');

            $opening = $openingInvoiced - $openingReceived;

            $invoiced = (float) CustomerInvoice::where('customer_id', $customer->id)
                ->where('company_id', $companyId)
                ->whereBetween('invoice_date', [$this->startDate, $this->endDate])
                ->sum('grand_total');

            $received = (float) Collection::where('customer_id', $customer->id)
                ->where('company_id', $companyId)
                ->whereBetween('collection_date', [$this->startDate, $this->endDate])
                ->sum('grand_total');

            $closing = $opening + $invoiced - $received;

            // Skip customers with no activity at all in this period and no
            // carried-forward balance — keeps the report focused, matching
            // the Customer Activity Report's convention.
            if ($opening == 0.0 && $invoiced == 0.0 && $received == 0.0) {
                continue;
            }

            $rows[] = [
                'customer' => $customer,
                'opening'  => $opening,
                'invoiced' => $invoiced,
                'received' => $received,
                'closing'  => $closing,
            ];

            $totals['opening']  += $opening;
            $totals['invoiced'] += $invoiced;
            $totals['received'] += $received;
            $totals['closing']  += $closing;
        }

        return [$rows, $totals];
    }

    public function exportExcel()
    {
        [$rows, $totals] = $this->getReportData();

        $columns = ['Customer', 'Code', 'Opening Balance', 'Invoiced', 'Received', 'Closing Balance'];

        $exportRows = [];
        foreach ($rows as $row) {
            $exportRows[] = [
                $row['customer']->name_en, $row['customer']->row_no,
                (float) $row['opening'], (float) $row['invoiced'], (float) $row['received'], (float) $row['closing'],
            ];
        }

        $totalsRow = [
            '', 'TOTAL',
            (float) $totals['opening'], (float) $totals['invoiced'], (float) $totals['received'], (float) $totals['closing'],
        ];

        $meta = [
            'title' => 'CUSTOMER BALANCE SUMMARY',
            'lines' => [
                'Period: ' . Carbon::parse($this->startDate)->format('d M Y') . ' — ' . Carbon::parse($this->endDate)->format('d M Y'),
                'Total Customers: ' . count($rows),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 3,
        ];

        $filename = 'CustomerBalanceSummary-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($exportRows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        [$rows, $totals] = $this->getReportData();

        return view('livewire.report.operation.customer-balance-summary', [
            'rows'   => $rows,
            'totals' => $totals,
        ]);
    }
}
