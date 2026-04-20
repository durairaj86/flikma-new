<?php

namespace App\Models\Prospect;

use App\Enums\CustomerStatusEnum;
use App\Models\Master\Salesperson\Salesperson;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Prospect extends Model
{
    use LogHistoryTrait;
    protected static string $cache = 'prospectCustomers:';
    protected static array $cacheColumns = [
        'id', 'name_en', 'email', 'phone', 'row_no'
    ];

    public static function prospectCustomers($id = null)
    {
        Cache::forget(self::$cache . cacheName());
        $prospects = Cache::rememberForever(static::$cache . cacheName(), function () {
            return DB::table('prospects')->where('prospects.company_id', companyId())
                ->whereNull('deleted_at')->latest('id')
                ->select(static::$cacheColumns)
                ->get();
        });
        return ($id) ? $prospects->where('id', $id) : $prospects;
    }

    public function salesperson(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Salesperson::class, 'id', 'salesperson_id');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => app()->getLocale() === 'ar'
                ? $this->name_ar
                : $this->name_en
        );
    }
}
