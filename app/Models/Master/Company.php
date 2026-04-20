<?php

namespace App\Models\Master;

use App\Enums\CustomerStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    protected $table = 'companies';
    protected $casts = [
        'business_type' => 'array',
    ];
    protected static string $cache = 'company:';

    public static function companies()
    {
        Cache::forget(self::$cache . cacheName());
        return Cache::rememberForever(static::$cache . cacheName(), function () {
            return DB::table('companies')->where('id', session('company_id'))->first();
        });
    }
}
