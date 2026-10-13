<?php

namespace App\Console\Commands;

use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use App\Services\Finance\LedgerReports;
use Illuminate\Console\Command;

/**
 * Read-only: for every job, the figures the Job Balance report shows today (invoice totals, VAT and drafts included)
 * next to what the books say (posted income and cost accounts, VAT excluded). Use it to decide which definition the
 * job reports should use before they are switched to the ledger.
 */
class FinanceJobCompare extends Command
{
    protected $signature = 'finance:job-compare {--company=1}';

    protected $description = 'Compare the Job Balance report numbers with the ledger numbers per job (read-only)';

    public function handle(): int
    {
        session(['company_id' => (int) $this->option('company')]);
        $jobs = Job::orderBy('id')->get(['id', 'row_no']);
        $books = LedgerReports::jobFinancials($jobs->pluck('id')->all());

        $rows = [];
        foreach ($jobs as $job) {
            $ci = CustomerInvoice::where('job_id', $job->id)->get();
            $docIncome = $ci->sum('base_grand_total') - CreditNote::where('job_id', $job->id)->sum('base_grand_total');
            $docCost = SupplierInvoice::where('job_id', $job->id)->sum('base_grand_total');
            $b = $books[$job->id] ?? ['income' => 0, 'cost' => 0];
            if ($docIncome == 0 && $docCost == 0 && !$b['income'] && !$b['cost']) {
                continue;
            }
            $draft = $ci->where('status', 1)->sum('base_grand_total');
            $rows[] = [$job->row_no, round($docIncome, 2), round($b['income'], 2), round($docCost, 2), round($b['cost'], 2),
                round($docIncome - $b['income'], 2), round($docCost - $b['cost'], 2), round($draft, 2)];
        }
        $this->table(['Job', 'Report income', 'Books income', 'Report cost', 'Books cost', 'Income diff', 'Cost diff', 'Draft invoices (in report only)'], $rows);
        $this->line('Differences come from: VAT (the report includes it, the books do not), draft invoices and credit notes that are in the report but not posted, and expenses posted as cost without a supplier invoice.');

        return self::SUCCESS;
    }
}
