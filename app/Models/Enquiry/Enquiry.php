<?php

namespace App\Models\Enquiry;

use App\Models\Customer\Customer;
use App\Models\Master\LogisticActivity;
use App\Models\Prospect\Prospect;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enquiry extends Model
{
    use LogHistoryTrait;

    //public $fillable = ['customer_id', 'company_id', 'user_id', 'shipment_type', 'shipment_category'];

    protected $guarded = []; // allow all fields

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function enquirySubs(): HasMany
    {
        return $this->hasMany(EnquirySub::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(LogisticActivity::class);
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

    protected function expiryDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
