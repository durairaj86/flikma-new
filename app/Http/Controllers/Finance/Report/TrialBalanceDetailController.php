<?php

namespace App\Http\Controllers\Finance\Report;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceLedger;
use Illuminate\Http\Request;

/** The postings behind one Trial Balance figure: click a debit or credit amount and see the journal lines that add up to it. */
class TrialBalanceDetailController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'account_id' => 'required|integer',
            'to' => 'required|date',
            'type' => 'required|in:debit,credit',
        ]);

        $account = Account::findOrFail($request->integer('account_id'));
        $type = $request->input('type');
        $to = $request->input('to');

        // a parent account shows the postings of all its sub-accounts too (its figure is their roll-up)
        $ids = [$account->id];
        $all = Account::where('is_active', 1)->get(['id', 'parent_id']);
        $queue = [$account->id];
        while ($queue) {
            $parent = array_shift($queue);
            foreach ($all->where('parent_id', $parent) as $child) {
                $ids[] = $child->id;
                $queue[] = $child->id;
            }
        }

        // same basis as the Trial Balance: posted entries up to the end date, company currency, one side of the entry
        $lines = FinanceLedger::query()->actual()
            ->whereIn('account_id', $ids)
            ->where('transaction_date', '<=', $to)
            ->where('base_' . $type, '>', 0)
            ->orderBy('transaction_date')->orderBy('id')
            ->get();

        return view('modules.finance.reports.trial-balance-details', [
            'account' => $account,
            'type' => $type,
            'to' => $to,
            'lines' => $lines,
            'total' => $lines->sum('base_' . $type),
            'multiple' => count($ids) > 1,
        ]);
    }
}
