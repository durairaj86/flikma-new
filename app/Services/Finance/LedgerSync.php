<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\Cache;

/**
 * Until the posting service writes the reporting columns and the allocations itself (design phase 6), anything that must see
 * entries posted a moment ago (aging as of a date) calls ensureFresh(): at most once a minute it fills the new journal columns
 * and creates the missing allocation rows. Both steps only fill what is still empty, so this is safe and cheap.
 */
class LedgerSync
{
    public static function ensureFresh(): void
    {
        if (!Cache::add('finance-ledger-sync-fresh', 1, 60)) {
            return;
        }
        $migration = require database_path('migrations/2026_10_22_000001_finance_ledger_foundation.php');
        $migration->up();
        app(AllocationSync::class)->run();
    }
}
