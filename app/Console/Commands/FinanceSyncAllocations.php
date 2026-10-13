<?php

namespace App\Console\Commands;

use App\Services\Finance\AllocationSync;
use Illuminate\Console\Command;

/** Adds the finance_allocations rows that are still missing for collections, payments, credit notes and debit notes. */
class FinanceSyncAllocations extends Command
{
    protected $signature = 'finance:sync-allocations';

    protected $description = 'Create missing finance_allocations rows from settlements already recorded (idempotent)';

    public function handle(AllocationSync $sync): int
    {
        foreach ($sync->run() as $source => $added) {
            $this->line(str_pad($source, 14) . ": $added added");
        }

        return self::SUCCESS;
    }
}
