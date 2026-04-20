<?php

namespace App\Models\BL;

use App\Models\Documents\Documents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waybill extends Model
{
    use HasFactory, SoftDeletes;

    //protected $table = 'waybills';

    protected $fillable = [
        'row_no',
        'job_id',
        'customer_id',
        'waybill_date',
        'delivery_date',
        'delivery_address',
        'contact_person',
        'contact_phone',
        'shipment_type',
        'service_type',
        'payment_method',
        'special_instructions',
    ];

    protected $dates = [
        'waybill_date',
        'delivery_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the job associated with the waybill.
     */
    public function job()
    {
        return $this->belongsTo(\App\Models\Job\Job::class, 'job_id');
    }

    /**
     * Get the customer associated with the waybill.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class, 'customer_id');
    }

    /**
     * Get the waybill sub items for the waybill.
     */
    public function waybillSubs()
    {
        return $this->hasMany(WaybillSub::class, 'waybill_id');
    }

    /**
     * Get the documents for the waybill.
     */
    public function documents()
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    protected function waybillDate(): Attribute
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
}
