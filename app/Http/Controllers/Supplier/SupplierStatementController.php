<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Supplier\Supplier;
use App\Services\Finance\LedgerReports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupplierStatementController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id ?? 1;
        $supplierId = $request->supplier_id ?? 12;
        $fromDate = $request->from_date ?? Carbon::now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? Carbon::now()->format('Y-m-d');

        // --- Fetch suppliers for dropdown ---
        $suppliers = Supplier::where('company_id', $companyId)
            ->select('id', 'row_no', 'name_en as name', 'company_id', 'email', 'phone', 'currency')
            ->get();

        $selectedSupplier = $suppliers->firstWhere('id', $supplierId) ?? $suppliers->first();

        if (!$selectedSupplier) {
            return view('modules.supplier.statement', [
                'suppliers' => [],
                'selectedSupplier' => null,
                'transactions' => [],
                'openingBalance' => 0,
                'invoicedAmount' => 0,
                'paidAmount' => 0,
                'closingBalance' => 0,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
            ]);
        }

        // --- Opening balance and period lines: payable control account of this supplier, read from the ledger view
        // (same source as Reports > Supplier Statement) ---
        $opening = LedgerReports::statementOpening('supplier', (int) $selectedSupplier->id, (int) $companyId, $fromDate);
        $openingBalance = $opening->credit - $opening->debit; // Credit = liability

        $transactions = LedgerReports::statementLines('supplier', (int) $selectedSupplier->id, (int) $companyId, $fromDate, $toDate);

        // --- Running Balance ---
        $runningBalance = $openingBalance;
        $transactions = $transactions->map(function ($txn) use (&$runningBalance) {
            $runningBalance += $txn->base_credit - $txn->base_debit;
            $txn->balance = $runningBalance;
            return $txn;
        });

        // --- Summary Totals ---
        $invoicedAmount = $transactions
            ->where('voucher_type', 'PI') // Supplier Bill
            ->sum('base_credit');

        $paidAmount = $transactions
            ->where('voucher_type', 'PV') // Payment Voucher
            ->sum('base_debit');

        $closingBalance = $openingBalance + $invoicedAmount - $paidAmount;

        return view('modules.supplier.statement', compact(
            'suppliers',
            'selectedSupplier',
            'transactions',
            'openingBalance',
            'invoicedAmount',
            'paidAmount',
            'closingBalance',
            'fromDate',
            'toDate'
        ));
    }
}
