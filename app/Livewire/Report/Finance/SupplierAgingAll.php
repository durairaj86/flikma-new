<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class SupplierAgingAll extends Component
{
    public $asOfDate;
    public $search = '';

    public function mount()
    {
        // Default to current date
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function updatedAsOfDate()
    {
        $this->dispatch('asOfDateChanged', $this->asOfDate);
    }

    public function updatedSearch()
    {
        $this->dispatch('searchChanged', $this->search);
    }

    public function exportExcel()
    {
        $child = new SupplierAgingSummary();
        $child->asOfDate = $this->asOfDate;
        $child->search = $this->search;
        $data = $child->getAgingData();

        $columns = ['Supplier Code', 'Supplier Name', 'Current', '1-30 Days', '31-60 Days', '61-90 Days', '91-120 Days', 'Over 120 Days', 'Total'];

        $rows = [];
        foreach ($data['suppliers'] as $s) {
            $rows[] = [
                $s['supplier_code'], $s['supplier_name'],
                (float) $s['current'] ?: '', (float) $s['days_1_30'] ?: '', (float) $s['days_31_60'] ?: '',
                (float) $s['days_61_90'] ?: '', (float) $s['days_91_120'] ?: '', (float) $s['days_over_120'] ?: '',
                (float) $s['total'],
            ];
        }

        $totalsRow = [
            '', 'TOTAL',
            (float) $data['totals']['current'], (float) $data['totals']['days_1_30'], (float) $data['totals']['days_31_60'],
            (float) $data['totals']['days_61_90'], (float) $data['totals']['days_91_120'], (float) $data['totals']['days_over_120'],
            (float) $data['totals']['grand_total'],
        ];

        $meta = [
            'title' => 'SUPPLIER AGING SUMMARY',
            'lines' => [
                'As of: ' . \Carbon\Carbon::parse($this->asOfDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 3,
        ];

        $filename = 'SupplierAgingSummary-' . $this->asOfDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        return view('livewire.report.finance.supplier-aging-summary');
    }
}
