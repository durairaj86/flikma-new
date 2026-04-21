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

    public static function companies($companyId = null)
    {
        $companyId = $companyId ?? session('company_id');
        
        if (!$companyId && auth()->check()) {
            $companyId = auth()->user()->company_id ?? null;
            if ($companyId) {
                session(['company_id' => $companyId]);
            }
        }
        
        if (!$companyId) {
            return null;
        }
        
        Cache::forget(self::$cache . cacheName());
        return Cache::rememberForever(static::$cache . $companyId, function () use ($companyId) {
            return static::find($companyId);
        });
    }
}
