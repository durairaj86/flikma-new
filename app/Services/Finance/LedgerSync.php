<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Until the posting service writes the reporting columns and the allocations itself (design phase 6), anything that must see
 * entries posted a moment ago (aging as of a date) calls ensureFresh(): at most once a minute it fills the new journal columns
 * and creates the missing allocation rows. Both steps only fill what is still empty, so this is safe and cheap.
 */
class LedgerSync
{
    public static function ensureFresh(): void
    {
        // one company at a time: a report page only fills its own company's rows (the hourly commands do all companies)
        $companyId = (int) companyId() ?: null;
        if (!Cache::add('finance-ledger-sync-fresh:' . ($companyId ?? 'all'), 1, 60)) {
            return;
        }
        // The foundation migration is a local-only step (migration files are not always deployed): run it when the file is
        // there, and never fail a report page because it is not. Without the allocations table there is nothing to sync.
        $file = database_path('migrations/2026_10_22_000001_finance_ledger_foundation.php');
        if (is_file($file)) {
            (require $file)->backfill($companyId);
        }
        if (!Schema::hasTable('finance_allocations')) {
            return;
        }
        app(AllocationSync::class)->run($companyId);
    }
}
