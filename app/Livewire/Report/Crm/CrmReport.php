<?php

namespace App\Livewire\Report\Crm;

use App\Livewire\Report\Pack\PackReport;
use Illuminate\Support\Facades\DB;

/**
 * Base of the CRM sales reports (funnel, win / loss, pipeline value, salesperson performance). Same page, filters and
 * Print / PDF / Excel as the other small grouped reports (PackReport). Each report counts several sources
 * (enquiries, quotations, jobs, activities ...) grouped by the same key, and merges them into one row per group.
 */
abstract class CrmReport extends PackReport
{
    /** Columns that are percentages: the total row recomputes them instead of adding them up. [column index => fn(totals)] */
    protected function rateColumns(): array
    {
        return [];
    }

    protected function totals(array $rows): array
    {
        $t = parent::totals($rows);
        foreach ($this->rateColumns() as $i => $fn) {
            $t[$i] = $fn($t);
        }

        return $t;
    }

    protected function emptyText(): string
    {
        return __('Nothing in this period.');
    }

    protected static function pct(float $part, float $whole): float
    {
        return $whole > 0 ? round($part * 100 / $whole, 1) : 0.0;
    }

    /** SQL of the group key on a table alias: salesperson id, customer id or month (from the given date column). */
    protected function groupSql(string $alias, string $dateColumn): string
    {
        return match ($this->groupBy) {
            'customer' => "COALESCE({$alias}.customer_id, 0)",
            'month' => "DATE_FORMAT(" . (str_contains($dateColumn, '(') ? $dateColumn : "{$alias}.{$dateColumn}") . ", '%Y-%m')",
            default => "COALESCE({$alias}.salesperson_id, 0)",
        };
    }

    /** Readable label of each group key. */
    protected function labels(array $keys): array
    {
        $keys = array_values(array_unique(array_filter($keys, fn($k) => $k !== null && $k !== '' && $k !== 0 && $k !== '0')));
        $none = $this->groupBy === 'customer' ? __('(no customer)') : __('(no salesperson)');
        $out = ['0' => $none, '' => $none];
        if (!$keys) {
            return $out;
        }

        return $out + match ($this->groupBy) {
            'customer' => DB::table('customers')->whereIn('id', $keys)->pluck('name_en', 'id')->all(),
            'month' => array_combine($keys, array_map(fn($k) => \Carbon\Carbon::createFromFormat('Y-m', $k)->translatedFormat('M Y'), $keys)),
            default => DB::table('sales_persons')->whereIn('id', $keys)->pluck('name', 'id')->all(),
        };
    }

    /** [key => total] of a grouped count / sum query. */
    protected function grouped($query, string $keySql, string $valueSql): array
    {
        return $query->selectRaw("{$keySql} AS k, {$valueSql} AS v")->groupBy(DB::raw($keySql))->pluck('v', 'k')
            ->map(fn($v) => (float) $v)->all();
    }

    /** Charge-line total of each quotation (the quotation amount, as in the quotation drawer). */
    protected function chargesSub(): string
    {
        return '(SELECT quotation_id, SUM(local_amount) amt FROM quotation_charges GROUP BY quotation_id)';
    }

    protected function base(string $table, string $alias)
    {
        return DB::table("{$table} as {$alias}")->where("{$alias}.company_id", companyId())->whereNull("{$alias}.deleted_at");
    }

    protected function between(string $column): array
    {
        return [DB::raw("DATE({$column})"), [$this->startDate, $this->endDate]];
    }
}
