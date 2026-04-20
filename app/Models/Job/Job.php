<?php

namespace App\Models\Job;

use App\Models\Customer\Customer;
use App\Models\Documents\Documents;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Master\LogisticActivity;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use LogHistoryTrait, SoftDeletes;

    public $fillable = ['status'];
    protected $casts = [
        'services' => 'array',
        'cargo_requirements' => 'array',
    ];
    protected $appends = ['pol_code', 'pol_name','pod_code', 'pod_name'];
    public static $mapFromQuotation = [
        'customer_id',
        'prospect_id',
        'posted_at',
        'services',
        'activity_id',
        'shipment_mode',
        'shipment_category',
        'incoterm',
        'pol',
        'pod',
        'place_of_receipt',
        'place_of_delivery',
        'final_destination',
        'carrier',
        'prepared_by',
        'salesperson_id',
        'terms',
        'shipper',
        'commodity',
        'pickup_date',
        'pickup_address',
    ];


    public function documents(): MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function clearance()
    {
        return $this->hasOne(JobClearance::class);
    }


    public function containers()
    {
        return $this->hasMany(JobContainer::class);
    }

    public function packages()
    {
        return $this->hasMany(JobPackage::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(LogisticActivity::class);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    protected function polCode(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) =>
            isset($attributes['pol']) ? explode('-', $attributes['pol'])[0] : '',
        );
    }

    /**
     * Get the POL Name (The part after the dash)
     */
    protected function polName(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (empty($attributes['pol']) || !str_contains($attributes['pol'], '-')) {
                    return null;
                }
                return explode('-', $attributes['pol'])[1];
            },
        );
    }

    protected function podCode(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) =>
            isset($attributes['pod']) ? explode('-', $attributes['pod'])[0] : '',
        );
    }

    protected function podName(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (empty($attributes['pod']) || !str_contains($attributes['pod'], '-')) {
                    return null;
                }
                return explode('-', $attributes['pod'])[1];
            },
        );
    }

    protected function postedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function eta(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function etd(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function ata(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function atd(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function pickupDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function deliveryDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function dutyPaymentDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
