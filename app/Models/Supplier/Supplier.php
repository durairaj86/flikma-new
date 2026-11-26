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
use App\Traits\CompanyScopeTrait;

class Supplier extends BaseModel
{
    use CompanyScopeTrait, LogHistoryTrait;
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
        // currency / credit_days feed the dropdown's data-currency / data-credit-days (payment & bill forms lock the currency to it)
        'id', 'name_en', 'name_ar', 'row_no', 'email', 'currency', 'credit_days'
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

    /**
     * The currency is locked once the supplier has any finance document (draft included) or ledger entry:
     * those are all in the supplier's currency, and payments must match it.
     */
    public function hasFinanceTransactions(): bool
    {
        return \App\Models\Finance\FinanceSub::where('supplier_id', $this->id)->exists()
            || \App\Models\Finance\Finance::where('supplier_id', $this->id)->exists()
            || \App\Models\Finance\SupplierInvoice\SupplierInvoice::where('supplier_id', $this->id)->exists()
            || \App\Models\Finance\Payment\Payment::where('supplier_id', $this->id)->exists()
            || \App\Models\Finance\Adjustment\DebitNote::where('supplier_id', $this->id)->exists();
    }
}
