<?php

namespace App\Models\Item;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    use \App\Traits\Log\LogHistoryTrait;
    protected static string $cache = 'items:';

    protected static array $cacheColumns = [
        'id', 'name_en', 'name_ar', 'sku_code', 'unit_id', 'account_type', 'cost_price', 'selling_price'
    ];

    public static function items($id = null)
    {
        Cache::forget(self::$cache . cacheName());
        $items = Cache::rememberForever(static::$cache . cacheName(), function () {
            return DB::table('items')->where(['items.is_active' => 1, 'items.company_id' => companyId(), 'items.branch_id' => 1])
                ->whereNull('deleted_at')->latest('id')
                ->select(static::$cacheColumns)
                ->get();
        });
        return ($id) ? $items->where('id', $id) : $items;
    }
}
