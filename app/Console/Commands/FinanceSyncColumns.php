<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Phase 1 of the finance redesign added reporting columns (entry date, partner, tax, source...) to finance / finance_subs.
 * Until PostingService (phase 6) writes them directly, this fills them for newly posted entries. Safe to run any time:
 * it only fills what is still empty.
 */
class FinanceSyncColumns extends Command
{
    protected $signature = 'finance:sync-columns';

    protected $description = 'Fill the new ledger reporting columns for entries posted since the last run';

    public function handle(): int
    {
        $migration = require database_path('migrations/2026_10_22_000001_finance_ledger_foundation.php');
        $migration->up();
        $this->info('Ledger reporting columns are up to date.');

        return self::SUCCESS;
    }
}
