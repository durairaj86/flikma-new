<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LogisticActivity extends Model
{
    use CompanyScopeWithNullTrait;

    protected static string $cache = 'activities:';
    protected static array $cacheColumns = [
        'id', 'code', 'name', 'mode'
    ];

    public static function activities($id = null)
    {
        //Cache::forget(self::$cache . cacheName());
        $activities = Cache::rememberForever(static::$cache . cacheName(), function () {
            return DB::table('logistic_activities')
                ->where(function ($query) {
                    $query->where('company_id', companyId())
                        ->orWhereNull('company_id');
                })
                ->whereIn('mode', ['air', 'sea', 'land'])
                ->orderBy('name')
                ->select(static::$cacheColumns)
                ->get();
        });
        return ($id) ? $activities->where('id', $id) : $activities;
    }
}
