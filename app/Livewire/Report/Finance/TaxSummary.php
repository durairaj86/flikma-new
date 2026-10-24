<?php

namespace App\Livewire\Report\Finance;

use App\Services\Finance\VatReturn;
use App\Exports\ReportTableExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TaxSummary extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';

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
    }

    public function resetFilter()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->search = '';

        $this->dispatch('dateRangeChanged', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
        $this->dispatch('searchChanged', '');
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

    public function exportExcel()
    {
        $vr = VatReturn::compute($this->startDate, $this->endDate);
        $rows = [];
        $add = function (string $no, string $label, array $b, bool $vat = true) use (&$rows) {
            $rows[] = [$no, $label, (float) $b['amount'], (float) $b['adjustment'], $vat ? (float) $b['vat'] : ''];
        };
        $rows[] = ['', __('VAT on Sales'), '', '', ''];
        $add('1', 'Standard rated sales', $vr['sales']['standard']);
        $add('2', 'Private healthcare / private education sales to citizens', $vr['sales']['citizens'], false);
        $add('3', 'Zero rated domestic sales', $vr['sales']['zero'], false);
        $add('4', 'Exports', $vr['sales']['exports'], false);
        $add('5', 'Exempt sales', $vr['sales']['exempt'], false);
        $add('6', 'Total sales', $vr['salesTotal']);
        $rows[] = ['', __('VAT on Purchases'), '', '', ''];
        $add('7', 'Standard rated domestic purchases', $vr['purchases']['standard']);
        $add('10', 'Zero rated purchases', $vr['purchases']['zero'], false);
        $add('11', 'Exempt purchases', $vr['purchases']['exempt'], false);
        $add('12', 'Total purchases', $vr['purchasesTotal']);
        $rows[] = ['13', 'Total VAT due for current period', '', '', (float) $vr['vatDue']];

        $columns = ['Box', 'Description', 'Amount (SAR)', 'Adjustment (SAR)', 'VAT Amount (SAR)'];
        $totalsRow = ['16', 'Net VAT ' . ($vr['vatDue'] >= 0 ? 'due' : 'claim (refund)'), '', '', (float) $vr['vatDue']];
        $meta = [
            'title' => 'TAX SUMMARY (VAT RETURN)',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 3,
        ];

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), 'TaxSummary-' . $this->startDate . '-' . $this->endDate . '.xlsx');
    }

    public function render()
    {
        return view('livewire.report.finance.tax-summary', ['vr' => VatReturn::compute($this->startDate, $this->endDate)]);
    }
}
