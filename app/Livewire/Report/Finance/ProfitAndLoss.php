<?php

namespace App\Livewire\Report\Finance;

use App\Exports\ReportTableExport;
use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ProfitAndLoss extends Component
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
        // account-balance calculation lives on ProfitAndLossTable.
        $child = new ProfitAndLossTable();
        $child->startDate = $this->startDate;
        $child->endDate = $this->endDate;
        $child->search = $this->search;
        $data = $child->getProfitAndLossData();

        $rows = [];
        foreach ($data['revenue'] as $acc) {
            $rows[] = ['Revenue', $acc['account_code'], $acc['account_name'], (float) $acc['balance']];
        }
        foreach ($data['expenses'] as $acc) {
            $rows[] = ['Expense', $acc['account_code'], $acc['account_name'], (float) $acc['balance']];
        }

        $columns = ['Section', 'Code', 'Account Name', 'Balance'];
        $totalsRow = ['', '', 'Net Income / (Loss)', (float) $data['net_income']];

        $meta = [
            'title' => 'PROFIT & LOSS STATEMENT',
            'lines' => [
                'Period: ' . \Carbon\Carbon::parse($this->startDate)->format('d M Y') . ' — ' . \Carbon\Carbon::parse($this->endDate)->format('d M Y'),
                'Total Revenue: ' . number_format($data['total_revenue'], 2) . '  |  Total Expenses: ' . number_format($data['total_expenses'], 2),
                'Generated on: ' . now()->format('d-m-Y H:i'),
            ],
            'numeric_from' => 4,
        ];

        $filename = 'ProfitAndLoss-' . $this->startDate . '-' . $this->endDate . '.xlsx';

        return Excel::download(new ReportTableExport($rows, $totalsRow, $columns, $meta), $filename);
    }

    public function render()
    {
        $accounts = Account::where('is_active', 1)
            ->whereIn('type', ['Income', 'Expense'])
            ->get();

        $totalRevenue = 0;
        $totalExpenses = 0;
        $revenueCount = 0;
        $expenseCount = 0;

        foreach ($accounts as $account) {
            $financeSub = FinanceSub::where('account_id', $account->id)
                ->whereBetween('reference_date', [$this->startDate, $this->endDate])
                ->whereHas('finance', fn($q) => $q->where('is_approved', 1))
                ->select(DB::raw('SUM(base_debit) as total_debit'), DB::raw('SUM(base_credit) as total_credit'))
                ->first();

            $debit  = $financeSub->total_debit ?? 0;
            $credit = $financeSub->total_credit ?? 0;

            if ($account->type === 'Income') {
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $totalRevenue += $balance;
                    $revenueCount++;
                }
            } elseif ($account->type === 'Expense') {
                $balance = $debit - $credit;
                if ($balance != 0) {
                    $totalExpenses += $balance;
                    $expenseCount++;
                }
            }
        }

        $netIncome = $totalRevenue - $totalExpenses;
        $margin    = $totalRevenue > 0 ? ($netIncome / $totalRevenue) * 100 : 0;

        return view('livewire.report.finance.profit-and-loss', [
            'summary' => [
                'total_revenue'  => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_income'     => $netIncome,
                'margin'         => $margin,
                'account_count'  => $revenueCount + $expenseCount,
            ],
        ]);
    }
}
