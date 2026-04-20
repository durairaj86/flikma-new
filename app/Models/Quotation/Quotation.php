<?php

namespace App\Models\Quotation;

use App\Models\Customer\Customer;
use App\Models\Master\LogisticActivity;
use App\Models\Prospect\Prospect;
use App\Models\Master\Salesperson\Salesperson;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    use LogHistoryTrait;

    protected $fillable = [
        'customer_id', 'posted_at', 'valid_until', 'services', 'salesman',
        'prepared_by', 'quotation_type', 'shipment_mode', 'shipment_category',
        'incoterm', 'pol', 'pod', 'place_of_receipt', 'place_of_delivery',
        'final_destination', 'carrier', 'terms', 'shipper', 'volume', 'pickup_date',
        'prospect_id'
    ];

    protected $casts = [
        'services' => 'array',
    ];

    public function containers()
    {
        return $this->hasMany(QuotationContainer::class);
    }

    public function packages()
    {
        return $this->hasMany(QuotationPackage::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(LogisticActivity::class);
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    protected function postedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * Mutator for valid_until.
     */
    protected function validUntil(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * Mutator for pickup_date.
     */
    protected function pickupDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
