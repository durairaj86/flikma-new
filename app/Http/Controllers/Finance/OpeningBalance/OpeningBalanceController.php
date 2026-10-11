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
use App\Services\Finance\OpeningBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpeningBalanceController extends Controller
{
    // AR/AP account IDs — auto-assigned for customer/supplier rows
    private int $arAccountId = 5;
    private int $apAccountId = 18;

    // ──────────────────────────────────────────────────────────────
    // INDEX  — list individual finance_subs entries
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
            $query->whereHas('finance', fn($q) => $q->whereDate('posted_at', '>=', formDate($request->date_from)));
        }
        if ($request->filled('date_to')) {
            $query->whereHas('finance', fn($q) => $q->whereDate('posted_at', '<=', formDate($request->date_to)));
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
        // The calendar sends dd-mm-yyyy; the rest of this method works with yyyy-mm-dd.
        $request->merge(['posted_at' => formDate($request->posted_at)]);
        $request->validate(['posted_at' => 'required|date']);
        $date = $request->posted_at;
        $svc = app(OpeningBalanceService::class);

        $rows = collect($request->input('balances', []))->filter(function ($row) {
            return ((float)($row['debit'] ?? 0) + (float)($row['credit'] ?? 0)) > 0;
        })->values();

        if ($rows->isEmpty()) {
            return back()->withErrors(['balances' => 'Enter at least one amount before saving.'])->withInput();
        }

        // Every opening balance shares one date, and it must be in an open period.
        $locked = $svc->lockedDate();
        if ($locked && $locked !== $date) {
            return back()->withErrors(['posted_at' => __('Opening balances already exist on :d. Use the same date.', ['d' => \Carbon\Carbon::parse($locked)->format('d-m-Y')])])->withInput();
        }
        $this->assertPeriodOpen($date, 'posted_at');

        // Check every row on the server (the form only hides what is already used).
        $haveCust = FinanceSub::where('voucher_type', 'OB')->where('company_id', companyId())->whereNotNull('customer_id')->pluck('customer_id')->flip();
        $haveSupp = FinanceSub::where('voucher_type', 'OB')->where('company_id', companyId())->whereNotNull('supplier_id')->pluck('supplier_id')->flip();
        $haveAcct = FinanceSub::where('voucher_type', 'OB')->where('company_id', companyId())->whereNull('customer_id')->whereNull('supplier_id')->where('is_auto_generated', 0)->pluck('account_id')->flip();
        $seen = [];
        $lines = [];
        foreach ($rows as $i => $row) {
            $n = $i + 1;
            $dr = round((float)($row['debit'] ?? 0), 2);
            $cr = round((float)($row['credit'] ?? 0), 2);
            if ($dr < 0 || $cr < 0) {
                return back()->withErrors(['balances' => "Row {$n}: amounts cannot be negative."])->withInput();
            }
            if ($dr > 0 && $cr > 0) {
                return back()->withErrors(['balances' => "Row {$n}: enter either a debit or a credit, not both."])->withInput();
            }
            $customerId = !empty($row['customer_id']) ? (int) $row['customer_id'] : null;
            $supplierId = !empty($row['supplier_id']) ? (int) $row['supplier_id'] : null;
            $accountId = !empty($row['account_id']) ? (int) $row['account_id'] : null;
            if (count(array_filter([$customerId, $supplierId, $accountId])) !== 1) {
                return back()->withErrors(['balances' => "Row {$n}: choose exactly one account, customer or supplier."])->withInput();
            }
            if ($customerId) {
                if (!Customer::whereKey($customerId)->exists()) return back()->withErrors(['balances' => "Row {$n}: customer not found."])->withInput();
                if (isset($haveCust[$customerId])) return back()->withErrors(['balances' => "Row {$n}: this customer already has an opening balance."])->withInput();
                $key = "c{$customerId}"; $accountId = $svc->arId();
            } elseif ($supplierId) {
                if (!Supplier::whereKey($supplierId)->exists()) return back()->withErrors(['balances' => "Row {$n}: supplier not found."])->withInput();
                if (isset($haveSupp[$supplierId])) return back()->withErrors(['balances' => "Row {$n}: this supplier already has an opening balance."])->withInput();
                $key = "s{$supplierId}"; $accountId = $svc->apId();
            } else {
                if (!$svc->isDirectAccount($accountId)) return back()->withErrors(['balances' => "Row {$n}: that account cannot take an opening balance here (receivables and payables are entered per customer or supplier)."])->withInput();
                if (isset($haveAcct[$accountId])) return back()->withErrors(['balances' => "Row {$n}: this account already has an opening balance."])->withInput();
                $key = "a{$accountId}";
            }
            if (isset($seen[$key])) {
                return back()->withErrors(['balances' => "Row {$n}: listed twice."])->withInput();
            }
            $seen[$key] = true;
            $lines[] = ['account_id' => $accountId, 'customer_id' => $customerId, 'supplier_id' => $supplierId, 'debit' => $dr, 'credit' => $cr, 'description' => $row['notes'] ?? null];
        }

        $totalDebit = round(array_sum(array_column($lines, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);

        // A one-sided (or otherwise unbalanced) opening balance voucher corrupts the trial balance permanently.
        if ($totalDebit !== $totalCredit) {
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
            $svc->post($date, $lines);
            DB::commit();
            return redirect()->route('finance.opening-balance')
                ->with('success', count($lines) . ' opening balance(s) saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT  — single finance_subs entry
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
    // UPDATE  — single finance_subs entry
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $sub = FinanceSub::where('company_id', companyId())->where('voucher_type', 'OB')->with('finance')->findOrFail($id);
        $svc = app(OpeningBalanceService::class);

        if ($sub->is_auto_generated) {
            return back()->withErrors(['error' => __('This is the automatic balancing line. Change the other lines and it adjusts itself.')])->withInput();
        }
        $request->validate([
            'debit'  => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'notes'  => 'nullable|string|max:500',
        ]);
        $debit  = round((float)($request->debit ?? 0), 2);
        $credit = round((float)($request->credit ?? 0), 2);
        if ($debit > 0 && $credit > 0) {
            return back()->withErrors(['balances' => __('Enter either a debit or a credit, not both.')])->withInput();
        }
        if ($debit == 0 && $credit == 0) {
            return back()->withErrors(['balances' => __('Enter an amount, or delete this entry instead.')])->withInput();
        }
        $this->assertPeriodOpen($sub->finance?->posted_at ?? $sub->reference_date, 'debit');

        // Party rows keep their customer / supplier and the receivable / payable account. Only a plain account row may change account.
        $accountId = $sub->customer_id ? $svc->arId() : ($sub->supplier_id ? $svc->apId() : $sub->account_id);
        if (!$sub->customer_id && !$sub->supplier_id && $request->filled('account_id') && (int) $request->account_id !== (int) $sub->account_id) {
            $new = (int) $request->account_id;
            $taken = FinanceSub::where('voucher_type', 'OB')->where('company_id', companyId())->whereNull('customer_id')->whereNull('supplier_id')->where('account_id', $new)->where('id', '!=', $sub->id)->exists();
            if (!$svc->isDirectAccount($new) || $taken) {
                return back()->withErrors(['account_id' => __('That account cannot be used here, or it already has an opening balance.')])->withInput();
            }
            $accountId = $new;
        }

        DB::beginTransaction();
        try {
            $sub->update([
                'account_id' => $accountId, 'debit' => $debit, 'credit' => $credit,
                'base_debit' => $debit, 'base_credit' => $credit, 'description' => $request->notes,
            ]);
            // The entry must stay balanced: any difference goes to the automatic balancing line.
            if ($sub->finance_id && ($err = $svc->rebalance($sub->finance_id))) {
                DB::rollBack();
                return back()->withErrors(['error' => $err])->withInput();
            }
            DB::commit();
            return redirect()->route('finance.opening-balance')
                ->with('success', __('Opening balance updated.') . ' ' . __('Any difference was carried to the balancing line (Owner Capital).'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ──────────────────────────────────────────────────────────────
    // DELETE  — single finance_subs entry
    // ──────────────────────────────────────────────────────────────
    public function delete($id)
    {
        $svc = app(OpeningBalanceService::class);
        // Only opening balance lines of this company can be deleted here.
        $sub = FinanceSub::where('company_id', companyId())->where('voucher_type', 'OB')->with('finance')->find($id);
        if (!$sub) {
            return response()->json(['status' => 'error', 'message' => __('Opening balance entry not found.')], 404);
        }
        if ($sub->is_auto_generated) {
            return response()->json(['status' => 'error', 'message' => __('This is the automatic balancing line. Delete the other lines and it goes away by itself.')], 422);
        }
        $this->assertPeriodOpen($sub->finance?->posted_at ?? $sub->reference_date, 'date');

        DB::beginTransaction();
        try {
            $financeId = $sub->finance_id;
            $sub->delete();
            if ($financeId && ($err = $svc->rebalance($financeId))) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $err], 422);
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => __('Deleted successfully.')]);
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
