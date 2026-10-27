<?php

namespace App\Services\Onboarding;

use App\Support\CompanyDefaults;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gives a new company its own copy of the default data.
 *
 * The defaults live in the shared masters database (connection `masters`):
 *   default_onboarding_tables : the parent list. One row per default table (source_table = default_x in masters, target_table =
 *                               the app table), sort_order, is_active, parent_column (the column that points at the row's own
 *                               parent, e.g. accounts.parent_id) and reference_columns ({column: code of another listed table}).
 *   default_x                 : the default rows, with the ids their parent / reference columns point at.
 *
 * seed() loops the active rows of the parent list in sort_order and, for each, inserts every default row into the target table
 * under the company, then makes the links again with the new ids (parent_id of the copy points at the copied parent, department_id of a
 * permission at the copied department, ...). A reference to a table that is not part of this run (e.g. accounts while they are switched
 * off) is left as it is, pointing at the shared row.
 *
 * Each (company, table) is copied once: it is recorded in company_default_seeds, which also tells the shared-rows scopes to stop
 * showing that table's shared rows to this company (App\Support\CompanyDefaults). Everything is one transaction on the app database.
 */
class CompanyDefaultsSeeder
{
    /**
     * @return array<string,array{table:string,status:string,rows:int}> per listed table, what happened
     */
    public function seed(int $companyId, bool $dryRun = false): array
    {
        $registry = DB::connection('masters')->table('default_onboarding_tables')
            ->where('is_active', 1)->orderBy('sort_order')->orderBy('id')->get();

        $run = function () use ($registry, $companyId, $dryRun) {
            $report = [];
            $maps = [];   // list code => [default row id => id of the copy], so later tables can re-make their links

            foreach ($registry as $entry) {
                $already = DB::table('company_default_seeds')->where('company_id', $companyId)->where('target_table', $entry->target_table)->exists();
                if ($already) {
                    $report[$entry->code] = ['table' => $entry->target_table, 'status' => 'already copied', 'rows' => 0];
                    continue;
                }
                $source = DB::connection('masters')->table($entry->source_table)->orderBy('id')->get();
                if ($dryRun) {
                    $report[$entry->code] = ['table' => $entry->target_table, 'status' => 'would copy', 'rows' => $source->count()];
                    continue;
                }

                $maps[$entry->code] = $this->copy($entry, $source, $companyId, $maps);
                DB::table('company_default_seeds')->insert([
                    'company_id' => $companyId, 'target_table' => $entry->target_table,
                    'rows_copied' => count($maps[$entry->code]), 'seeded_at' => now(),
                ]);
                $report[$entry->code] = ['table' => $entry->target_table, 'status' => 'copied', 'rows' => count($maps[$entry->code])];
            }

            return $report;
        };

        $report = $dryRun ? $run() : DB::transaction($run);
        CompanyDefaults::forget();

        return $report;
    }

    /** Insert the default rows of one listed table for the company; returns [default id => new id]. */
    private function copy(object $entry, $rows, int $companyId, array $maps): array
    {
        $target = $entry->target_table;
        $targetCols = Schema::getColumnListing($target);
        $columns = array_values(array_diff(array_intersect(Schema::connection('masters')->getColumnListing($entry->source_table), $targetCols), ['id']));
        $refs = $entry->reference_columns ? (array) json_decode($entry->reference_columns, true) : [];
        $parent = $entry->parent_column;
        $stamp = now();

        $map = [];
        $parents = [];   // new id => the default id of its parent, set once every row has its new id
        foreach ($rows as $row) {
            $data = Arr::only((array) $row, $columns);
            $data['company_id'] = $companyId;
            foreach (['created_at', 'updated_at'] as $col) {
                if (in_array($col, $targetCols, true)) {
                    $data[$col] = $stamp;
                }
            }
            foreach ($refs as $column => $listCode) {
                // only a table copied in this run has new ids to point at; otherwise the shared row stays the target
                if (isset($maps[$listCode]) && ($data[$column] ?? null) !== null) {
                    $data[$column] = $maps[$listCode][$data[$column]] ?? null;
                }
            }
            $oldParent = null;
            if ($parent && ($data[$parent] ?? null) !== null) {
                $oldParent = $data[$parent];
                $data[$parent] = null;
            }

            $newId = (int) DB::table($target)->insertGetId($data);
            if ($newId <= 0) {
                throw new \RuntimeException("{$target}: the copy of default row {$row->id} did not get an id (is id AUTO_INCREMENT?)");
            }
            $map[$row->id] = $newId;
            if ($oldParent !== null) {
                $parents[$newId] = $oldParent;
            }
        }

        foreach ($parents as $newId => $oldParent) {
            DB::table($target)->where('id', $newId)->update([$parent => $map[$oldParent] ?? null]);
        }

        return $map;
    }
}
