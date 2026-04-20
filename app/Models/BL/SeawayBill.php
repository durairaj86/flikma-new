<?php

namespace App\Models\BL;

use App\Models\Documents\Documents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeawayBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'seaway_bills';

    protected $fillable = [
        'row_no',
        'job_id',
        'customer_id',
        'seaway_bill_date',
        'delivery_date',
        'delivery_address',
        'contact_person',
        'contact_phone',
        'origin_port',
        'destination_port',
        'vessel_name',
        'voyage_number',
        'departure_time',
        'arrival_time',
        'shipment_type',
        'service_type',
        'payment_method',
        'special_instructions',
        'status',
    ];

    protected $dates = [
        'seaway_bill_date',
        'delivery_date',
        'departure_time',
        'arrival_time',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the job associated with the seaway bill.
     */
    public function job()
    {
        return $this->belongsTo(\App\Models\Job\Job::class, 'job_id');
    }

    /**
     * Get the customer associated with the seaway bill.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class, 'customer_id');
    }

    /**
     * Get the seaway bill sub items for the seaway bill.
     */
    public function seawayBillSubs()
    {
        return $this->hasMany(SeawayBillSub::class, 'seaway_bill_id');
    }

    /**
     * Get the documents for the seaway bill.
     */
    public function documents()
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    protected function seawaybillDate(): Attribute
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

    protected function departureTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y H:i') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d H:i') : null,
        );
    }

    protected function arrivalTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y H:i') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d H:i') : null,
        );
    }
}
