<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class BalanceSheet extends Component
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
        // Reuse the child table component's query rather than duplicating
        // it — the Print/PDF/Excel buttons live here on the parent, but the
        // account-balance calculation lives on BalanceSheetTable.
        $child = new BalanceSheetTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->search = $this->search;
        $data = $child->getBalanceSheetData();

        $rows = [];
        foreach ($data['assets'] as $acc) {
            $rows[] = ['Asset', $acc['account_code'], $acc['account_name'], (float) $acc['balance']];
        }
        foreach ($data['liabilities'] as $acc) {
            $rows[] = ['Liability', $acc['account_code'], $acc['account_name'], (float) $acc['balance']];
        }
        foreach ($data['equity'] as $acc) {
            $rows[] = ['Equity', $acc['account_code'], $acc['account_name'], (float) $acc['balance']];
        }

        $columns = ['Section', 'Code', 'Account Name', 'Balance'];
        $totalsRow = ['', '', 'Total Assets: ' . number_format($data['total_assets'], 2) . '  |  Total Liabilities & Equity: ' . number_format($data['total_liabilities_equity'], 2), (float) $data['total_assets']];

        $meta = [
            'title' => 'BALANCE SHEET',
            'lines' => [
                'As of: ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 4,
        ];

        $filename = 'BalanceSheet-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        $accounts = Account::where('is_active', 1)
            ->whereIn('type', ['Asset', 'Liability', 'Equity'])
            ->get();

        $totalAssets      = 0;
        $totalLiabilities = 0;
        $totalEquity      = 0;

        foreach ($accounts as $account) {
            $financeSub = FinanceSub::where('account_id', $account->id)
                ->where('reference_date', '<=', $this->endDate)
                ->whereHas('finance', fn($q) => $q->where('is_approved', 1))
                ->select(DB::raw('SUM(base_debit) as total_debit'), DB::raw('SUM(base_credit) as total_credit'))
                ->first();

            $debit  = $financeSub->total_debit ?? 0;
            $credit = $financeSub->total_credit ?? 0;

            if ($account->type === 'Asset') {
                $totalAssets += $debit - $credit;
            } elseif ($account->type === 'Liability') {
                $totalLiabilities += $credit - $debit;
            } elseif ($account->type === 'Equity') {
                $totalEquity += $credit - $debit;
            }
        }

        // Net income from Income/Expense accounts
        $incomeIds  = Account::where('is_active', 1)->where('type', 'Income')->pluck('id');
        $expenseIds = Account::where('is_active', 1)->where('type', 'Expense')->pluck('id');

        $totalIncome = 0;
        if ($incomeIds->isNotEmpty()) {
            $d = FinanceSub::whereIn('account_id', $incomeIds)
                ->where('reference_date', '<=', $this->endDate)
                ->whereHas('finance', fn($q) => $q->where('is_approved', 1))
                ->selectRaw('SUM(base_credit) as total_credit, SUM(base_debit) as total_debit')
                ->first();
            $totalIncome = ($d->total_credit ?? 0) - ($d->total_debit ?? 0);
        }

        $totalExpenses = 0;
        if ($expenseIds->isNotEmpty()) {
            $d = FinanceSub::whereIn('account_id', $expenseIds)
                ->where('reference_date', '<=', $this->endDate)
                ->whereHas('finance', fn($q) => $q->where('is_approved', 1))
                ->selectRaw('SUM(base_debit) as total_debit, SUM(base_credit) as total_credit')
                ->first();
            $totalExpenses = ($d->total_debit ?? 0) - ($d->total_credit ?? 0);
        }

        $netIncome = $totalIncome - $totalExpenses;
        if ($netIncome != 0) {
            $totalEquity += $netIncome;
        }

        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesEquity) < 0.01;

        return view('livewire.report.finance.balance-sheet', [
            'summary' => [
                'total_assets'              => $totalAssets,
                'total_liabilities'         => $totalLiabilities,
                'total_equity'              => $totalEquity,
                'total_liabilities_equity'  => $totalLiabilitiesEquity,
                'net_income'                => $netIncome,
                'is_balanced'               => $isBalanced,
                'difference'                => abs($totalAssets - $totalLiabilitiesEquity),
            ],
        ]);
    }
}
