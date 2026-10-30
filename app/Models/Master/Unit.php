<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\CompanyOrGlobalScopeTrait;

class Unit extends Model
{
    use CompanyOrGlobalScopeTrait;

    /**
     * What a dropdown offers: the active units, plus any the screen already holds ($keepIds / $keepNames), so an old document that uses
     * a unit that has since been made inactive still shows it, and saving that document does not lose it.
     */
    public static function units(array $keepIds = [], array $keepNames = []): \Illuminate\Support\Collection
    {
        $keepIds = array_values(array_filter(array_map('intval', $keepIds)));
        $keepNames = array_values(array_filter(array_map('strval', $keepNames)));

        return DB::table('units')->where(function ($query) {
            $query->where('company_id', companyId())->orWhereNull('company_id');
        })
            ->select('id', 'unit_name', 'unit_symbol', 'status')
            ->where(function ($query) use ($keepIds, $keepNames) {
                // active, and not a shared row this company has switched off for itself
                $off = \App\Support\CompanyDefaults::switchedOff('units', (int) companyId());
                $query->where(function ($on) use ($off) {
                    $on->where('status', 1);
                    if ($off) {
                        $on->whereNotIn('id', $off);
                    }
                });
                if ($keepIds) {
                    $query->orWhereIn('id', $keepIds);
                }
                if ($keepNames) {
                    $query->orWhereIn('unit_name', $keepNames);
                }
            })
            ->orderBy('unit_name')
            ->get();
    }
}
