<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Models\Finance\FinanceSub;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class OutputTax extends Component
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
        $child = new OutputTaxTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->search = $this->search;
        $data = $child->getOutputTaxData();

        $rows = [];
        foreach ($data['output_tax_transactions'] as $t) {
            $rows[] = [$t['account_code'] . ' ' . $t['account_name'], $t['reference_no'], $t['reference_date'], $t['description'], (float) $t['amount']];
        }

        $columns = ['Account', 'Reference No', 'Date', 'Description', 'Tax Amount'];
        $totalsRow = ['', '', '', 'TOTAL', (float) $data['total_output_tax']];

        $meta = [
            'title' => 'OUTPUT TAX REPORT',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 5,
        ];

        $filename = 'OutputTax-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        $outputVatAccountId = 20;

        $query = FinanceSub::where('is_tax_line', 1)
            ->where('account_id', $outputVatAccountId)
            ->whereBetween('reference_date', [$this->startDate, $this->endDate])
            ->whereHas('finance', fn($q) => $q->where('is_approved', 1));

        $totalOutputTax = (clone $query)->sum('credit') - (clone $query)->sum('debit');
        $count = (clone $query)->where('credit', '>', 0)->count();

        return view('livewire.report.finance.output-tax', [
            'summary' => [
                'total_output_tax' => $totalOutputTax > 0 ? $totalOutputTax : 0,
                'transaction_count' => $count,
            ],
        ]);
    }
}
