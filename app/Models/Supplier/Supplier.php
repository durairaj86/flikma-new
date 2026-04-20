<?php

namespace App\Models\Supplier;

use App\Enums\SupplierStatusEnum;
use App\Models\BaseModel;
use App\Models\Master\Company;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Supplier extends BaseModel
{
    use LogHistoryTrait;
    // Allow mass assignment for these fields
    protected $fillable = [
        'row_no', 'unique_row_no', 'name_en', 'name_ar', 'currency', 'business_type', 'cr_number', 'vat_number',
        'credit_limit', 'credit_days', 'address1_en', 'address1_ar', 'address2_en', 'address2_ar',
        'city_en', 'city_ar', 'region', 'postal_code', 'country_en', 'country_ar',
        'email', 'phone', 'alt_phone', 'preferred_shipping', 'preferred_carrier', 'default_port',
        'payment_method', 'iban', 'payment_terms','user_id','company_id','status','building_number', 'plot_no'
    ];

    protected static string $cache = 'suppliers:';
    protected static array $cacheColumns = [
        'id', 'name_en', 'name_ar', 'row_no', 'email'
    ];

    public static function suppliers($id = null)
    {
        Cache::forget(self::$cache . cacheName());
        $suppliers = Cache::rememberForever(static::$cache . cacheName(), function () {
            return DB::table('suppliers')->where(['suppliers.status' => SupplierStatusEnum::CONFIRMED->value, 'suppliers.company_id' => companyId()])
                ->whereNull('deleted_at')->latest('id')
                ->select(static::$cacheColumns)
                ->get();
        });
        return ($id) ? $suppliers->where('id', $id) : $suppliers;
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
