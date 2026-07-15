<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Models\Finance\Account\Account;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class GeneralLedger extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $accountId = 'all';
    public $accounts = [];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

        // Load accounts for dropdown
        $this->loadAccounts();
    }

    public function loadAccounts()
    {
        $this->accounts = Account::where('is_active', 1)
            ->orderBy('code')
            ->select('id', 'code', 'name', 'type')
            ->get()
            ->toArray();
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

    public function updatedAccountId($value)
    {
        $this->dispatch('accountChanged', $this->accountId);
    }

    public function exportExcel()
    {
        // Reuse the child table component's query rather than duplicating
        // it — the Print/PDF/Excel buttons live here on the parent, but the
        // ledger calculation lives on GeneralLedgerTable.
        $child = new GeneralLedgerTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->accountId = $this->accountId;
        $child->search = $this->search;
        $data = $child->getGeneralLedgerData();

        $rows = [];
        foreach ($data['accounts'] as $acc) {
            if (abs($acc['opening_balance'] ?? 0) < 0.001 && count($acc['transactions'] ?? []) === 0 && abs($acc['closing_balance'] ?? 0) < 0.001) {
                continue;
            }
            $rows[] = ['', $acc['account_code'] . ' — ' . $acc['account_name'], '', 'Opening Balance', null, null, (float) $acc['opening_balance']];
            foreach ($acc['transactions'] as $txn) {
                $rows[] = [
                    $txn['date'], '', $txn['voucher_no'], $txn['description'],
                    $txn['debit'] > 0 ? (float) $txn['debit'] : null,
                    $txn['credit'] > 0 ? (float) $txn['credit'] : null,
                    (float) $txn['balance'],
                ];
            }
            $rows[] = ['', '', '', 'Closing Balance', (float) $acc['total_debit'], (float) $acc['total_credit'], (float) $acc['closing_balance']];
        }

        $columns = ['Date', 'Account', 'Voucher No', 'Description', 'Debit', 'Credit', 'Balance'];
        $totalsRow = ['', '', '', 'GRAND TOTAL', (float) $data['grand_total_debit'], (float) $data['grand_total_credit'], (float) $data['net_balance']];

        $meta = [
            'title' => 'GENERAL LEDGER',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 5,
        ];

        $filename = 'GeneralLedger-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        return view('livewire.report.finance.general-ledger');
    }
}
