<?php

namespace App\Http\Controllers\Finance\OpeningBalance;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Finance\JournalVoucher\JournalVoucher;
use App\Models\Finance\JournalVoucher\JournalVoucherItem;
use App\Models\Supplier\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpeningBalanceController extends Controller
{
    // AR/AP account IDs — auto-assigned for customer/supplier rows
    private int $arAccountId = 5;
    private int $apAccountId = 18;

    // ──────────────────────────────────────────────────────────────
    // INDEX  — list individual finance_sub entries
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = FinanceSub::query()
            ->where('voucher_type', 'OB')
            ->where('company_id', companyId())
            ->with(['finance', 'account', 'customer', 'supplier'])
            ->orderBy('id', 'desc');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('date_from')) {
            $query->whereHas('finance', fn($q) => $q->whereDate('posted_at', '>=', $request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereHas('finance', fn($q) => $q->whereDate('posted_at', '<=', $request->date_to));
        }

        $openingBalances = $query->paginate(25)->withQueryString();

        $accounts  = Account::where('is_last', 1)->orderBy('name')->get(['id', 'code', 'name']);
        $customers = Customer::orderBy('name_en')->get(['id', 'name_en']);
        $suppliers = Supplier::orderBy('name_en')->get(['id', 'name_en']);

        return view('modules.finance.opening-balance.index',
            compact('openingBalances', 'accounts', 'customers', 'suppliers'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        $existingOb = FinanceSub::where('voucher_type', 'OB')
            ->where('company_id', companyId())
            ->with('finance')
            ->first();

        $lockedDate = $existingOb?->finance?->posted_at
            ? \Carbon\Carbon::parse($existingOb->finance->posted_at)->format('Y-m-d')
            : null;

        // Exclude entities that already have an OB entry
        $existingCustomerIds = FinanceSub::where('voucher_type', 'OB')
            ->where('company_id', companyId())
            ->whereNotNull('customer_id')
            ->pluck('customer_id');

        $existingSupplierIds = FinanceSub::where('voucher_type', 'OB')
            ->where('company_id', companyId())
            ->whereNotNull('supplier_id')
            ->pluck('supplier_id');

        $existingAccountIds = FinanceSub::where('voucher_type', 'OB')
            ->where('company_id', companyId())
            ->whereNull('customer_id')
            ->whereNull('supplier_id')
            ->pluck('account_id');

        $accounts  = Account::where('is_last', 1)
            ->whereNotIn('id', $existingAccountIds)
            ->orderBy('name')->get(['id', 'code', 'name']);

        $customers = Customer::whereNotIn('id', $existingCustomerIds)
            ->orderBy('name_en')->get(['id', 'name_en']);

        $suppliers = Supplier::whereNotIn('id', $existingSupplierIds)
            ->orderBy('name_en')->get(['id', 'name_en']);

        return view('modules.finance.opening-balance.create',
            compact('accounts', 'customers', 'suppliers', 'lockedDate'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate(['posted_at' => 'required|date']);

        $rows = collect($request->input('balances', []))->filter(function ($row) {
            return ((float)($row['debit'] ?? 0) + (float)($row['credit'] ?? 0)) > 0;
        })->values();

        if ($rows->isEmpty()) {
            return back()->withErrors(['balances' => 'Enter at least one amount before saving.'])->withInput();
        }

        $totalDebit  = $rows->sum(fn($r) => (float)($r['debit'] ?? 0));
        $totalCredit = $rows->sum(fn($r) => (float)($r['credit'] ?? 0));

        // A one-sided (or otherwise unbalanced) opening balance voucher
        // corrupts the trial balance permanently, since nothing else in the
        // system ever revisits it — reject it here rather than silently
        // saving broken books.
        if (round($totalDebit - $totalCredit, 2) !== 0.0) {
            return back()->withErrors([
                'balances' => sprintf(
                    'Opening balance entries must balance — total debit (%s) does not equal total credit (%s). Add a contra entry (e.g. to Owner Capital / Retained Earnings) for the difference of %s.',
                    number_format($totalDebit, 2),
                    number_format($totalCredit, 2),
                    number_format(abs($totalDebit - $totalCredit), 2)
                ),
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            $voucherNo   = 'OB-' . date('YmdHis');

            $finance = Finance::create([
                'voucher_no'        => $voucherNo,
                'voucher_type'      => 'OB',
                'posted_at'         => $request->posted_at,
                'reference_date'    => $request->posted_at,
                'narration'         => 'Opening Balance',
                'total_debit'       => $totalDebit,
                'total_credit'      => $totalCredit,
                'base_total_debit'  => $totalDebit,
                'base_total_credit' => $totalCredit,
                'is_approved'       => 1,
                'company_id'        => companyId(),
                'user_id'           => auth()->id(),
            ]);

            foreach ($rows as $row) {
                $customerId = $row['customer_id'] ?? null;
                $supplierId = $row['supplier_id'] ?? null;
                $accountId  = $customerId ? $this->arAccountId
                            : ($supplierId ? $this->apAccountId
                            : ($row['account_id'] ?? null));

                FinanceSub::create([
                    'finance_id'     => $finance->id,
                    'voucher_no'     => $voucherNo,
                    'voucher_type'   => 'OB',
                    'account_id'     => $accountId,
                    'customer_id'    => $customerId ?: null,
                    'supplier_id'    => $supplierId ?: null,
                    'reference_date' => $request->posted_at,
                    'debit'          => (float)($row['debit']  ?? 0),
                    'credit'         => (float)($row['credit'] ?? 0),
                    'base_debit'     => (float)($row['debit']  ?? 0),
                    'base_credit'    => (float)($row['credit'] ?? 0),
                    'description'    => $row['notes'] ?? null,
                    'company_id'     => companyId(),
                    'user_id'        => auth()->id(),
                ]);
            }

            DB::commit();
            return redirect()->route('finance.opening-balance')
                ->with('success', $rows->count() . ' opening balance(s) saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT  — single finance_sub entry
    // ──────────────────────────────────────────────────────────────
    public function edit($id)
    {
        $sub = FinanceSub::where('company_id', companyId())
            ->where('voucher_type', 'OB')
            ->with('finance')
            ->findOrFail($id);

        $sub->party_type  = $sub->customer_id ? 'customer' : ($sub->supplier_id ? 'supplier' : null);
        $sub->balance_date = $sub->finance?->posted_at
            ? \Carbon\Carbon::parse($sub->finance->posted_at)->format('Y-m-d')
            : null;
        $sub->notes = $sub->description;

        $accounts  = Account::where('is_last', 1)->orderBy('name')->get(['id', 'code', 'name']);
        $customers = Customer::orderBy('name_en')->get(['id', 'name_en']);
        $suppliers = Supplier::orderBy('name_en')->get(['id', 'name_en']);

        return view('modules.finance.opening-balance.edit', [
            'balance'   => $sub,
            'accounts'  => $accounts,
            'customers' => $customers,
            'suppliers' => $suppliers,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE  — single finance_sub entry
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $sub = FinanceSub::where('company_id', companyId())
            ->where('voucher_type', 'OB')
            ->findOrFail($id);

        $request->validate([
            'debit'  => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'notes'  => 'nullable|string|max:500',
        ]);

        // Determine account — always auto-assign for party rows
        $accountId = $sub->customer_id ? $this->arAccountId
                   : ($sub->supplier_id ? $this->apAccountId
                   : ($request->account_id ?? $sub->account_id));

        // For GL rows allow changing customer/supplier
        $customerId = $sub->customer_id ?: ($request->customer_id ?: null);
        $supplierId = $sub->supplier_id ?: ($request->supplier_id ?: null);

        $debit  = (float)($request->debit  ?? 0);
        $credit = (float)($request->credit ?? 0);

        // Check the whole voucher balances after this row's change, not just
        // this row in isolation — same reasoning as the store() guard above.
        if ($sub->finance_id) {
            $otherRowsDebit  = FinanceSub::where('finance_id', $sub->finance_id)->where('id', '!=', $sub->id)->sum('debit');
            $otherRowsCredit = FinanceSub::where('finance_id', $sub->finance_id)->where('id', '!=', $sub->id)->sum('credit');
            $newTotalDebit   = $otherRowsDebit + $debit;
            $newTotalCredit  = $otherRowsCredit + $credit;

            if (round($newTotalDebit - $newTotalCredit, 2) !== 0.0) {
                return back()->withErrors([
                    'balances' => sprintf(
                        'This change would leave the opening balance voucher unbalanced — total debit (%s) would not equal total credit (%s).',
                        number_format($newTotalDebit, 2),
                        number_format($newTotalCredit, 2)
                    ),
                ])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $sub->update([
                'account_id'  => $accountId,
                'customer_id' => $customerId,
                'supplier_id' => $supplierId,
                'debit'       => $debit,
                'credit'      => $credit,
                'base_debit'  => $debit,
                'base_credit' => $credit,
                'description' => $request->notes,
            ]);

            // Refresh parent Finance totals
            if ($sub->finance_id) {
                $subs = FinanceSub::where('finance_id', $sub->finance_id);
                Finance::find($sub->finance_id)?->update([
                    'total_debit'       => $subs->sum('debit'),
                    'total_credit'      => $subs->sum('credit'),
                    'base_total_debit'  => $subs->sum('base_debit'),
                    'base_total_credit' => $subs->sum('base_credit'),
                ]);
            }

            DB::commit();
            return redirect()->route('finance.opening-balance')
                ->with('success', 'Opening balance updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DELETE  — single finance_sub entry
    // ──────────────────────────────────────────────────────────────
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $sub       = FinanceSub::findOrFail($id);
            $financeId = $sub->finance_id;
            $sub->delete();

            if ($financeId) {
                $remaining = FinanceSub::where('finance_id', $financeId)->count();
                if ($remaining === 0) {
                    Finance::find($financeId)?->delete();
                } else {
                    $subs = FinanceSub::where('finance_id', $financeId);
                    Finance::find($financeId)?->update([
                        'total_debit'       => $subs->sum('debit'),
                        'total_credit'      => $subs->sum('credit'),
                        'base_total_debit'  => $subs->sum('base_debit'),
                        'base_total_credit' => $subs->sum('base_credit'),
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Legacy view method (kept for backward compat)
    public function view($id)
    {
        $finance = Finance::with('financeSubs')->findOrFail($id);
        return view('modules.finance.opening-balance.view', compact('finance'));
    }
}
