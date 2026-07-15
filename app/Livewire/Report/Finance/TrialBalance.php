<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TrialBalance extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
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
        // Reuse the child table component's query rather than duplicating
        // it — the Print/PDF/Excel buttons live here on the parent, but the
        // account-balance calculation lives on TrialBalanceTable.
        $child = new TrialBalanceTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->search = $this->search;
        $data = $child->getTrialBalanceData();

        $rows = [];
        foreach ($data['accounts'] as $acc) {
            $rows[] = [$acc['account_code'], $acc['account_name'], $acc['account_type'], (float) $acc['debit'], (float) $acc['credit']];
        }

        $columns = ['Code', 'Account Name', 'Type', 'Debit', 'Credit'];
        $totalsRow = ['', '', 'TOTAL', (float) $data['total_debit'], (float) $data['total_credit']];

        $meta = [
            'title' => 'TRIAL BALANCE',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 4,
        ];

        $filename = 'TrialBalance-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        return view('livewire.report.finance.trial-balance');
    }
}
