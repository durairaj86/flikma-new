<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Models\Customer\Customer;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class GeneralLedger extends Component
{
    public $startDate;
    public $endDate;
    public $search = '';
    public $customerId = 'all';
    public $customers = [];

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

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

    public function updatedCustomerId($value)
    {
        $this->dispatch('customerChanged', $this->customerId);
    }

    public function exportExcel()
    {
        // Reuse the child table component's query rather than duplicating
        // it — the Print/PDF/Excel buttons live here on the parent, but the
        // ledger calculation lives on GeneralLedgerTable.
        $child = new GeneralLedgerTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->customerId = $this->customerId;
        $child->search = $this->search;
        $data = $child->getGeneralLedgerData();

        $rows = [];
        foreach ($data['customers'] as $cust) {
            if (abs($cust['opening_balance'] ?? 0) < 0.001 && count($cust['transactions'] ?? []) === 0 && abs($cust['closing_balance'] ?? 0) < 0.001) {
                continue;
            }
            $rows[] = ['', $cust['customer_code'] . ' — ' . $cust['customer_name'], '', 'Opening Balance', null, null, (float) $cust['opening_balance']];
            foreach ($cust['transactions'] as $txn) {
                $rows[] = [
                    $txn['date'], '', $txn['voucher_no'], $txn['description'],
                    $txn['debit'] > 0 ? (float) $txn['debit'] : null,
                    $txn['credit'] > 0 ? (float) $txn['credit'] : null,
                    (float) $txn['balance'],
                ];
            }
            $rows[] = ['', '', '', 'Closing Balance', (float) $cust['total_debit'], (float) $cust['total_credit'], (float) $cust['closing_balance']];
        }

        $columns = ['Date', 'Customer', 'Voucher No', 'Description', 'Debit', 'Credit', 'Balance'];
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
