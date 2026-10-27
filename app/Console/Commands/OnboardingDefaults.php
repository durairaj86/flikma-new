<?php

namespace App\Console\Commands;

use App\Services\Onboarding\CompanyDefaultsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The onboarding defaults (masters database: default_onboarding_tables + the default_* tables it lists).
 *
 *   php artisan onboarding:defaults                    the parent list, and which companies already own a copy of what
 *   php artisan onboarding:defaults 7 --dry-run        what giving company 7 its copies would do
 *   php artisan onboarding:defaults 7                  give company 7 its copies (tables it already has are skipped)
 */
class OnboardingDefaults extends Command
{
    protected $signature = 'onboarding:defaults {company? : company id to copy the defaults into} {--dry-run : show what would be copied, write nothing}';

    protected $description = 'Show the onboarding default tables, or copy them into a company';

    public function handle(CompanyDefaultsSeeder $seeder): int
    {
        $list = DB::connection('masters')->table('default_onboarding_tables')->orderBy('sort_order')->orderBy('id')->get();

        if (!$this->argument('company')) {
            $this->table(['#', 'Code', 'Default table (masters)', 'Copied into', 'Active', 'Default rows', 'Parent col', 'Links'], $list->map(fn ($r) => [
                $r->sort_order, $r->code, $r->source_table, $r->target_table, $r->is_active ? 'yes' : 'OFF',
                DB::connection('masters')->table($r->source_table)->count(), $r->parent_column ?: '-', $r->reference_columns ?: '-',
            ])->all());
            $owned = DB::table('company_default_seeds')->selectRaw('company_id, COUNT(*) AS tables, SUM(rows_copied) AS rows_copied')->groupBy('company_id')->get();
            $this->line($owned->isEmpty() ? 'No company owns copies yet: all of them use the shared rows.' : 'Companies that own copies:');
            foreach ($owned as $o) {
                $this->line("  company {$o->company_id}: {$o->tables} tables, {$o->rows_copied} rows");
            }
            foreach ($list->where('is_active', 0) as $off) {
                $this->warn("{$off->code} is OFF. " . $off->notes);
            }

            return self::SUCCESS;
        }

        $companyId = (int) $this->argument('company');
        if (!DB::table('companies')->where('id', $companyId)->exists()) {
            $this->error("There is no company {$companyId}.");

            return self::FAILURE;
        }
        $report = $seeder->seed($companyId, (bool) $this->option('dry-run'));
        $this->table(['Table', 'Result', 'Rows'], collect($report)->map(fn ($r) => [$r['table'], $r['status'], $r['rows']])->values()->all());

        return self::SUCCESS;
    }
}
