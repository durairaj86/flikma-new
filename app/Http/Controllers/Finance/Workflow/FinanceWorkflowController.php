<?php

namespace App\Http\Controllers\Finance\Workflow;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Documentation page: how every finance entry is saved, which tables it touches, and what the finance redesign changed. */
class FinanceWorkflowController extends Controller
{
    public function index()
    {
        $companyId = companyId();

        // live row counts, so the page always shows the real state of the books
        $counts = [];
        foreach ([
            'finance' => 'Journal entry headers',
            'finance_subs' => 'Journal lines',
            'finance_allocations' => 'Settlements (allocations)',
            'job_links' => 'Job document links',
            'accounts' => 'Chart of accounts',
        ] as $table => $label) {
            $q = Schema::hasTable($table) ? DB::table($table) : null;
            if ($q && Schema::hasColumn($table, 'company_id')) {
                $q->where(fn ($w) => $w->where('company_id', $companyId)->when($table === 'accounts', fn ($x) => $x->orWhereNull('company_id')));
            }
            $counts[$table] = ['label' => $label, 'rows' => $q ? $q->count() : null];
        }
        $counts['finance_ledgers'] = [
            'label' => 'Ledger view (posted lines)',
            'rows' => Schema::hasView('finance_ledgers') ? DB::table('finance_ledgers')->where('company_id', $companyId)->count() : null,
        ];

        $byType = DB::table('finance')->where('company_id', $companyId)
            ->selectRaw('voucher_type, count(*) entries, sum(is_approved = 1) posted, round(sum(base_total_debit), 2) amount')
            ->groupBy('voucher_type')->orderBy('voucher_type')->get();

        return view('modules.finance.workflow.index', [
            'counts' => $counts,
            'byType' => $byType,
            'useLedger' => (bool) config('finance.reports.use_ledger', true),
        ]);
    }
}
