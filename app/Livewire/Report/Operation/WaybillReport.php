<?php

namespace App\Livewire\Report\Operation;

use App\Exports\ReportTableExport;
use App\Models\BL\Waybill;
use App\Models\Customer\Customer;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class WaybillReport extends Component
{
    public $startDate;
    public $endDate;
    public $customerId = '';
    public $status = '';
    public $customers = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->endOfMonth()->format('Y-m-d');
        $this->loadCustomers();
    }

    public function loadCustomers()
    {
        $this->customers = Customer::where('company_id', companyId())
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
        $this->status     = '';
    }

    protected function getReportData()
    {
        $companyId = companyId();

        $waybills = Waybill::with(['customer:id,name_en,row_no', 'job:id,row_no'])
            ->where('company_id', $companyId)
            ->whereBetween(DB::raw('DATE(waybill_date)'), [$this->startDate, $this->endDate]);

        if (!empty($this->customerId)) {
            $waybills->where('customer_id', $this->customerId);
        }

        if (!empty($this->status)) {
            $waybills->where('status', $this->status);
        }

        $waybills = $waybills->orderByDesc('waybill_date')->get();

        $totals = [
            'total'      => $waybills->count(),
            'pending'    => $waybills->where('status', 'pending')->count(),
            'in_transit' => $waybills->where('status', 'in_transit')->count(),
            'delivered'  => $waybills->where('status', 'delivered')->count(),
        ];

        return [$waybills, $totals];
    }

    public function exportExcel()
    {
        [$waybills, $totals] = $this->getReportData();

        $columns = ['Waybill No', 'Date', 'Job', 'Customer', 'Delivery Date', 'Delivery Address', 'Contact Person', 'Contact Phone', 'Status'];

        $exportRows = [];
        foreach ($waybills as $wb) {
            $exportRows[] = [
                $wb->row_no ?? $wb->waybill_no,
                $wb->waybill_date,
                $wb->job->row_no ?? '—',
                $wb->customer->name_en ?? '—',
                $wb->delivery_date ?? '—',
                $wb->delivery_address ?? '—',
                $wb->contact_person ?? '—',
                $wb->contact_phone ?? '—',
                ucfirst(str_replace('_', ' ', $wb->status)),
            ];
        }

        $totalsRow = ['', '', '', '', '', '', '', '', $totals['total'] . ' Waybill(s)'];

        $meta = [
            'title' => 'WAYBILL REPORT',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Total Waybills: ' . $totals['total'],
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 9,
        ];

        $filename = 'WaybillReport-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($exportRows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        [$waybills, $totals] = $this->getReportData();

        return view('livewire.report.operation.waybill-report', [
            'waybills' => $waybills,
            'totals'   => $totals,
        ]);
    }
}
