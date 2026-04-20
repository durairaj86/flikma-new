<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Supplier\Supplier;
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

        // --- Supplier Account IDs (Accounts Payable) ---
        /*$supplierAccountIds = DB::table('accounts')
            ->where('company_id', $companyId)
            ->where('type', 'Accounts Payable')
            ->pluck('code')
            ->toArray();*/
        $supplierAccountIds = [2100];

        // --- Opening Balance before from_date ---
        $openingDebit = DB::table('finance_sub')
            ->where('supplier_id', $selectedSupplier->id)
            ->where('company_id', $companyId)
            ->whereIn('account_id', $supplierAccountIds)
            ->where('reference_date', '<', $fromDate)
            ->sum('base_debit');

        $openingCredit = DB::table('finance_sub')
            ->where('supplier_id', $selectedSupplier->id)
            ->where('company_id', $companyId)
            ->whereIn('account_id', $supplierAccountIds)
            ->where('reference_date', '<', $fromDate)
            ->sum('base_credit');

        $openingBalance = $openingCredit - $openingDebit; // Credit = liability

        // --- Transactions in date range ---
        $transactions = DB::table('finance as f')
            ->leftJoin('jobs as j', 'f.job_id', '=', 'j.id')
            ->where('f.company_id', $companyId)
            ->where('f.supplier_id', $selectedSupplier->id)
            ->whereIn('f.account_id', $supplierAccountIds)
            ->whereBetween('f.reference_date', [$fromDate, $toDate])
            ->select(
                'f.id',
                'f.reference_date',
                'f.voucher_no',
                'f.voucher_type',
                'f.reference_no',
                'j.row_no as job_number',
                'f.narration as description',
                'f.currency',
                'f.exchange_rate',
                'f.base_debit',
                'f.base_credit'
            )
            ->orderBy('f.reference_date')
            ->orderBy('f.id')
            ->get();

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
