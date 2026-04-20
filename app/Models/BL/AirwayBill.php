<?php

namespace App\Models\BL;

use App\Models\Documents\Documents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirwayBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'airway_bills';

    protected $fillable = [
        'row_no',
        'job_id',
        'customer_id',
        'airway_bill_date',
        'delivery_date',
        'delivery_address',
        'contact_person',
        'contact_phone',
        'origin_airport',
        'destination_airport',
        'carrier',
        'flight_number',
        'departure_time',
        'arrival_time',
        'shipment_type',
        'service_type',
        'payment_method',
        'special_instructions',
        'status',
    ];

    protected $dates = [
        'airway_bill_date',
        'delivery_date',
        'departure_time',
        'arrival_time',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the job associated with the airway bill.
     */
    public function job()
    {
        return $this->belongsTo(\App\Models\Job\Job::class, 'job_id');
    }

    /**
     * Get the customer associated with the airway bill.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class, 'customer_id');
    }

    /**
     * Get the airway bill sub items for the airway bill.
     */
    public function airwayBillSubs()
    {
        return $this->hasMany(AirwayBillSub::class, 'airway_bill_id');
    }

    /**
     * Get the documents for the airway bill.
     */
    public function documents()
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    protected function airwaybillDate(): Attribute
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
