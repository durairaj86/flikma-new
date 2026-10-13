<?php

namespace App\Http\Controllers\Finance\Report;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceLedger;
use Illuminate\Http\Request;

/**
 * The postings behind one Profit & Loss or Balance Sheet figure: click an account balance and see its journal lines, both sides,
 * netting to exactly the number shown (Profit & Loss: the period; Balance Sheet: everything up to the end date).
 */
class AccountPostingsController extends Controller
{
    public function show(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20', 'to' => 'required|date', 'from' => 'nullable|date']);

        $account = Account::where('is_active', 1)->where('code', $request->input('code'))->firstOrFail();
        $from = $request->input('from');
        $to = $request->input('to');

        $q = FinanceLedger::query()->actual()->where('account_id', $account->id)->where('transaction_date', '<=', $to);
        if ($from) {
            $q->where('transaction_date', '>=', $from);
        }
        $lines = $q->orderBy('transaction_date')->orderBy('id')->get();

        $debit = (float) $lines->sum('base_debit');
        $credit = (float) $lines->sum('base_credit');
        // the same sign convention the reports use: assets and expenses are debit balances, the rest credit balances
        $net = in_array($account->type, ['Asset', 'Expense'], true) ? $debit - $credit : $credit - $debit;

        return view('modules.finance.reports.account-postings', compact('account', 'lines', 'from', 'to', 'debit', 'credit', 'net'));
    }
}
