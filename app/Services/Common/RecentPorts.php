<?php

namespace App\Services\Common;

use App\Models\Master\TransportDirectory\Airport;
use App\Models\Master\TransportDirectory\Port;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The ports (sea) or airports (air) this company used most recently as POL / POD, newest first.
 * Shown by default in every POL / POD dropdown before the user types; filled up from the master list
 * (by code) when fewer than $limit have been used so the list is never short.
 */
class RecentPorts
{
    /** Documents that carry a POL / POD, as [table, pol column, pod column]. */
    private const SOURCES = [
        ['enquiries', 'pol', 'pod'],
        ['quotations', 'pol', 'pod'],
        ['jobs', 'pol', 'pod'],
        ['bookings', 'pol', 'pod'],
        ['arrival_notices', 'pol', 'pod'],
        ['master_bls', 'pol', 'pod'],
        ['airway_bills', 'origin_airport', 'destination_airport'],
    ];

    public static function get(string $mode = 'sea', int $limit = 20): Collection
    {
        $model = $mode === 'air' ? Airport::class : Port::class;

        // values in newest-first order; used newest across all documents
        $used = [];
        foreach (self::SOURCES as [$table, $pol, $pod]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $pol) || !Schema::hasColumn($table, $pod)) {
                continue;
            }
            if ($table === 'airway_bills' && $mode !== 'air') {
                continue;
            }
            $q = DB::table($table)->select([$pol . ' as a', $pod . ' as b', 'created_at']);
            if (Schema::hasColumn($table, 'company_id') && function_exists('companyId')) {
                $q->where('company_id', companyId());
            }
            if (Schema::hasColumn($table, 'deleted_at')) {
                $q->whereNull('deleted_at');
            }
            if (in_array($table, ['enquiries', 'quotations', 'jobs', 'bookings', 'master_bls'], true)
                && Schema::hasColumn($table, 'shipment_mode')) {
                $q->where('shipment_mode', $mode === 'air' ? 'air' : 'sea');
            }
            foreach ($q->orderByDesc('created_at')->limit(200)->get() as $row) {
                foreach ([$row->a, $row->b] as $v) {
                    $v = trim((string) $v);
                    if ($v !== '') {
                        $used[] = [$v, (string) $row->created_at];
                    }
                }
            }
        }
        usort($used, fn ($x, $y) => strcmp($y[1], $x[1]));

        $found = collect();
        $seen = [];
        $tried = [];
        foreach ($used as [$value]) {
            if ($found->count() >= $limit) {
                break;
            }
            if (isset($tried[$value])) {
                continue;
            }
            $tried[$value] = true;
            // saved as "CODE - Name", as a code, or as a name
            $code = str_contains($value, ' - ') ? trim(explode(' - ', $value, 2)[0]) : $value;
            $find = fn () => $model::query()->select('id', 'name', 'code');
            $row = $find()->where(fn ($q) => $q->where('code', $code)->orWhere('name', $value))->first();
            if (!$row && strlen($code) === 3) {
                $row = $find()->where('code', 'like', '__' . $code)->first();   // JED -> SAJED
            }
            if (!$row) {
                $row = $find()->where('name', 'like', $value . '%')->first();    // "Jebel Ali" -> Jebel Ali Port
            }
            if ($row && !isset($seen[$row->id])) {
                $seen[$row->id] = true;
                $found->push($row);
            }
        }

        if ($found->count() < $limit) {
            $fill = $model::query()->select('id', 'name', 'code')->whereNotIn('id', array_keys($seen))
                ->orderBy('code')->limit($limit - $found->count())->get();
            $found = $found->concat($fill);
        }

        return $found->values();
    }
}
