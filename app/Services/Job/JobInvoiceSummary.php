<?php

namespace App\Services\Job;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Keeps jobs.invoice_json up to date:
 * {"si":{"created":4,"approved":2,"draft":1,"cancelled":1},"ci":{...},"cn":{...}}
 * si = supplier invoices, ci = customer invoices, cn = credit notes.
 */
class JobInvoiceSummary
{
    /** key => doc_type in job_links */
    private const TYPES = ['si' => 'supplier_invoice', 'ci' => 'customer_invoice', 'cn' => 'credit_note'];

    public static function build(int $jobId): array
    {
        // One query on the common table.
        $rows = \App\Models\Job\JobLink::withoutGlobalScopes()->where('job_id', $jobId)->whereIn('doc_type', array_values(self::TYPES))
            ->selectRaw('doc_type, state, COUNT(*) c')->groupBy('doc_type', 'state')->get();
        $out = [];
        foreach (self::TYPES as $key => $docType) {
            $r = $rows->where('doc_type', $docType);
            $count = fn($state) => (int) $r->where('state', $state)->sum('c');
            $out[$key] = ['created' => (int) $r->sum('c'), 'approved' => $count('approved'), 'draft' => $count('draft'), 'cancelled' => $count('cancelled')];
        }

        return $out;
    }

    /** Recalculate and store; written straight to the table so no job events or timestamps are touched. */
    public static function refresh(?int $jobId): void
    {
        if (!$jobId || !Schema::hasColumn('jobs', 'invoice_json')) {
            return;
        }
        $data = self::build($jobId);
        // Money for the job list (same figures as the Financials tab): approved documents only, company currency.
        $job = \App\Models\Job\Job::withoutGlobalScopes()->withTrashed()->find($jobId);
        if ($job) {
            if ($job->company_id && !session('company_id')) {
                session(['company_id' => $job->company_id]);
            }
            $m = \App\Services\Job\JobFinancials::collect($job)['summary'];
            $data['money'] = ['invoiced' => round($m['invoiced'], 2), 'credits' => round($m['credits'], 2), 'cost' => round($m['cost'], 2),
                'collected' => round($m['collected'], 2), 'profit' => round($m['profit'], 2), 'due' => round($m['outstanding'], 2)];
        }
        DB::table('jobs')->where('id', $jobId)->update(['invoice_json' => json_encode($data)]);
    }

    public static function refreshAll(): int
    {
        $n = 0;
        foreach (DB::table('jobs')->get(['id', 'company_id']) as $j) {
            session(['company_id' => $j->company_id]);
            self::refresh((int) $j->id);
            $n++;
        }

        return $n;
    }
}
