<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\CompanyOrGlobalScopeTrait;

class Description extends Model
{
    use CompanyOrGlobalScopeTrait, CompanyScopeWithNullTrait;
    /** Does this database have descriptions.is_active yet? (older databases do not: then every description counts as active) */
    private static function hasActiveColumn(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('descriptions', 'is_active');
    }

    /**
     * What a dropdown offers: the active descriptions, plus any the screen already holds ($keepIds / $keepNames), so an old document
     * that uses a description that has since been made inactive still shows it, and saving that document does not lose it.
     */
    public static function pickable(array $keepIds = [], array $keepNames = []): \Illuminate\Support\Collection
    {
        if (!self::hasActiveColumn()) {
            return static::descriptions();
        }
        $keepIds = array_values(array_filter(array_map('intval', $keepIds)));
        $keepNames = array_values(array_filter(array_map('strval', $keepNames)));

        return DB::table('descriptions')->where(function ($query) {
            $query->where('company_id', companyId())->orWhereNull('company_id');
        })->where(function ($query) use ($keepIds, $keepNames) {
            // active, and not a shared row this company has switched off for itself
            $off = \App\Support\CompanyDefaults::switchedOff('descriptions', (int) companyId());
            $query->where(function ($on) use ($off) {
                $on->where('is_active', 1);
                if ($off) {
                    $on->whereNotIn('id', $off);
                }
            });
            if ($keepIds) {
                $query->orWhereIn('id', $keepIds);
            }
            if ($keepNames) {
                $query->orWhereIn('description', $keepNames);
            }
        })->select('id', 'description')->orderBy('description')->get();
    }

    /** Every description of the company (shared ones included), active or not: for looking a name up. Dropdowns use pickable(). */
    public static function descriptions(): \Illuminate\Support\Collection
    {
        return DB::table('descriptions')->where(function ($query) {
            $query->where('company_id', companyId())->orWhereNull('company_id');
        })
            ->select('id', 'description')
            ->orderBy('description')
            ->get();
    }
}
