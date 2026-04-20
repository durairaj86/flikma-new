<?php

namespace App\Models\Customer;

use App\Enums\CustomerStatusEnum;
use App\Models\Master\Salesperson\Salesperson;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use LogHistoryTrait;

    /*use LogHistoryTrait;*/
    // Allow mass assignment for these fields
    protected $fillable = [
        'row_no', 'unique_row_no', 'name_en', 'name_ar', 'currency', 'business_type', 'cr_number', 'vat_number',
        'credit_limit', 'credit_days', 'address1_en', 'address1_ar', 'address2_en', 'address2_ar',
        'city_en', 'city_ar', 'region', 'postal_code', 'country_en', 'country_ar', 'country',
        'email', 'phone', 'alt_phone', 'preferred_shipping', 'preferred_carrier', 'default_port',
        'payment_method', 'iban', 'payment_terms', 'user_id', 'company_id', 'status', 'building_number', 'plot_no',
        'salesperson_id'
    ];

    protected static string $cache = 'confirmCustomers:';
    protected static array $cacheColumns = [
        'id', 'name_en', 'name_ar', 'salesperson_id', 'row_no', 'email', 'credit_days', 'currency'
    ];

    /*protected $casts = [
        'status' => CustomerStatusEnum::class,
    ];*/

    public static function allCustomers($id = null)
    {
        Cache::forget(self::$cache . cacheName());
        $customers = Cache::rememberForever(static::$cache . cacheName(), function () {
            return DB::table('customers')->where('customers.company_id', companyId())
                ->whereNull('deleted_at')->latest('id')
                ->select(static::$cacheColumns)
                ->get();
        });
        return ($id) ? $customers->where('id', $id) : $customers;
    }

    public static function confirmedCustomers($id = null)
    {
        Cache::forget(self::$cache . cacheName());
        $customers = Cache::rememberForever(static::$cache . cacheName(), function () {
            return DB::table('customers')->where(['customers.status' => CustomerStatusEnum::CONFIRMED->value, 'customers.company_id' => companyId()])
                ->whereNull('deleted_at')->latest('id')
                ->select(static::$cacheColumns)
                ->get();
        });
        return ($id) ? $customers->where('id', $id) : $customers;
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
