<?php

namespace App\Livewire\Report\Finance;

use App\Models\Supplier\Supplier;
use App\Models\Finance\FinanceSub;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SupplierLedgerTable extends Component
{
    public $startDate;
    public $endDate;
    public $supplierId = '';
    public $search = '';

    protected $listeners = [
        'dateRangeChanged' => 'updateDateRange',
        'supplierChanged' => 'updateSupplier',
        'searchChanged' => 'updateSearch'
    ];

    public function mount($supplierId = '')
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

        $this->supplierId = $supplierId;
    }

    public function updateDateRange($dateRange)
    {
        $this->startDate = $dateRange['startDate'];
        $this->endDate = $dateRange['endDate'];
    }

    public function updateSupplier($supplierId)
    {
        $this->supplierId = $supplierId;
    }

    public function updateSearch($search)
    {
        $this->search = $search;
    }

    public function getSupplierLedgerData()
    {
        // A supplier is always required — with a large supplier base,
        // pulling every supplier's ledger at once is too expensive, so
        // there is no "all suppliers" mode to fall back to here.
        if (empty($this->supplierId)) {
            return [
                'suppliers' => [],
                'grand_total_debit' => 0,
                'grand_total_credit' => 0,
                'net_balance' => 0
            ];
        }

        $suppliers = Supplier::where('company_id', companyId())
            ->where('id', $this->supplierId);

        // Apply search if provided
        if (!empty($this->search)) {
            $suppliers = $suppliers->where(function ($query) {
                $query->where('name_en', 'like', '%' . $this->search . '%')
                    ->orWhere('row_no', 'like', '%' . $this->search . '%');
            });
        }

        $suppliers = $suppliers->orderBy('name_en')->get();

        if ($suppliers->isEmpty()) {
            return [
                'suppliers' => [],
                'grand_total_debit' => 0,
                'grand_total_credit' => 0,
                'net_balance' => 0
            ];
        }

        $supplierLedgerData = [];
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;
        $netBalance = 0;

        foreach ($suppliers as $supplier) {
            // Get opening balance (transactions before start date)
            $openingBalance = $this->getOpeningBalance($supplier->id, $this->startDate);

            // Get every ledger line for this supplier within the date range,
            // across every account it touched (AP, Expense, Tax, ...) — a
            // flat supplier sub-ledger, not grouped by account.
            $transactions = $this->getSupplierTransactions($supplier->id, $this->startDate, $this->endDate);

            // Calculate running balance and totals. Suppliers are always
            // creditors from the company's perspective: a credit (e.g. a
            // supplier invoice) increases what we owe them, a debit (e.g. a
            // payment) decreases it — but the running balance itself is
            // still just opening + debit - credit, matching the underlying
            // account postings regardless of which side each line hit.
            $runningBalance = $openingBalance;
            $totalDebit = 0;
            $totalCredit = 0;

            $formattedTransactions = [];
            foreach ($transactions as $transaction) {
                $debit = $transaction->debit ?? 0;
                $credit = $transaction->credit ?? 0;

                $runningBalance += $debit - $credit;

                $formattedTransactions[] = [
                    'date'         => $transaction->reference_date,
                    'voucher_no'   => $transaction->voucher_no,
                    'voucher_type' => $transaction->voucher_type,
                    'reference_no' => $transaction->reference_no,
                    'description'  => $transaction->description,
                    'account_code' => $transaction->account->code ?? '',
                    'account_name' => $transaction->account->name ?? '',
                    'debit'        => $debit,
                    'credit'       => $credit,
                    'balance'      => $runningBalance,
                ];

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            $supplierLedgerData[$supplier->id] = [
                'supplier_code' => $supplier->row_no,
                'supplier_name' => $supplier->name_en,
                'opening_balance' => $openingBalance,
                'transactions' => $formattedTransactions,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'closing_balance' => $closingBalance
            ];

            $grandTotalDebit += $totalDebit;
            $grandTotalCredit += $totalCredit;
            $netBalance += $closingBalance;
        }

        return [
            'suppliers' => $supplierLedgerData,
            'grand_total_debit' => $grandTotalDebit,
            'grand_total_credit' => $grandTotalCredit,
            'net_balance' => $netBalance
        ];
    }

    private function getOpeningBalance($supplierId, $startDate)
    {
        $financeSub = FinanceSub::where('supplier_id', $supplierId)
            ->where('reference_date', '<', $startDate)
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1);
            })
            ->select(
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->first();

        $debit = $financeSub->total_debit ?? 0;
        $credit = $financeSub->total_credit ?? 0;

        return $debit - $credit;
    }

    private function getSupplierTransactions($supplierId, $startDate, $endDate)
    {
        return FinanceSub::where('supplier_id', $supplierId)
            ->whereBetween('reference_date', [$startDate, $endDate])
            ->whereHas('finance', function ($query) {
                $query->where('is_approved', 1);
            })
            ->with('account:id,code,name')
            ->orderBy('reference_date')
            ->orderBy('id')
            ->get();
    }

    public function render()
    {
        $supplierLedgerData = $this->getSupplierLedgerData();

        return view('livewire.report.finance.supplier-ledger-table', [
            'supplierLedgerData' => $supplierLedgerData
        ]);
    }
}
