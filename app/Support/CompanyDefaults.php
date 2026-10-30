<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Which companies own their copy of a default table.
 *
 * Master data tables (descriptions, units, departments...) can hold shared rows (company_id empty) that every company sees, next to rows
 * a company added itself. A company onboarded from the masters defaults (App\Services\Onboarding\CompanyDefaultsSeeder) gets its own copy
 * of those rows and is recorded in company_default_seeds; for such a table it must see only its own rows, otherwise every default would
 * show twice (its copy and the shared original). Companies without that record keep seeing the shared rows exactly as before.
 */
class CompanyDefaults
{
    /** @var array<int,array<string,bool>> company id => [table => true], read once per request */
    private static array $owned = [];

    /** True when this company has its own copy of $table, so the shared rows of that table are not for it. */
    public static function hidesShared(string $table, int $companyId): bool
    {
        if (!array_key_exists($companyId, self::$owned)) {
            try {
                self::$owned[$companyId] = DB::table('company_default_seeds')->where('company_id', $companyId)->pluck('target_table')->flip()->map(fn () => true)->all();
            } catch (\Throwable $e) {
                // the table is not there yet (an older database): nobody owns a copy, so everything behaves as it always did
                self::$owned[$companyId] = [];
            }
        }

        return isset(self::$owned[$companyId][$table]);
    }

    /** @var array<int,array<string,int[]>> company id => [table => ids of shared rows it switched off], read once per request */
    private static array $off = [];

    /** Ids of the shared rows of $table (company_id empty) that this company has switched Off for itself. */
    public static function switchedOff(string $table, int $companyId): array
    {
        if (!isset(self::$off[$companyId])) {
            try {
                self::$off[$companyId] = DB::table('company_inactive_defaults')->where('company_id', $companyId)->get(['target_table', 'row_id'])
                    ->groupBy('target_table')->map(fn ($rows) => $rows->pluck('row_id')->map(fn ($id) => (int) $id)->all())->all();
            } catch (\Throwable $e) {
                self::$off[$companyId] = [];   // an older database without the table: nothing is switched off
            }
        }

        return self::$off[$companyId][$table] ?? [];
    }

    /** Switch a shared row On / Off for one company only (the row itself is not touched). */
    public static function setSharedActive(string $table, int $companyId, int $rowId, bool $active): void
    {
        $key = ['company_id' => $companyId, 'target_table' => $table, 'row_id' => $rowId];
        $active ? DB::table('company_inactive_defaults')->where($key)->delete() : DB::table('company_inactive_defaults')->insertOrIgnore($key + ['created_at' => now()]);
        unset(self::$off[$companyId]);
    }

    /** Forget what was read (after a company has just been given its copies, and in tests). */
    public static function forget(): void
    {
        self::$owned = [];
        self::$off = [];
    }
}
