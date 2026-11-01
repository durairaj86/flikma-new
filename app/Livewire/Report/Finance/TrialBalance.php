<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class TrialBalance extends Component
{
    // A trial balance is "as of" one date: every entry up to $endDate. $startDate is not chosen by the user; it stays at the
    // beginning so the old (non ledger) query adds the opening balance and all activity up to the date.
    public $startDate = '2000-01-01';
    public $endDate;
    public $search = '';

    public function mount()
    {
        // Default to today
        $this->startDate = '2000-01-01';
        $this->endDate = now()->format('Y-m-d');
    }

    /** The Generate button: apply the date and the search to the table. */
    public function generate()
    {
        $this->dispatch('dateRangeChanged', ['startDate' => $this->startDate, 'endDate' => $this->endDate]);
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
                'As of: ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 4,
        ];

        $filename = 'TrialBalance-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        return view('livewire.report.finance.trial-balance');
    }
}
