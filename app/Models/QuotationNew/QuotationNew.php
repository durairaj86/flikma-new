<?php

namespace App\Models\QuotationNew;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\CompanyScopeTrait;

class QuotationNew extends Model
{
    use CompanyScopeTrait;

    protected $table = 'quotations_new';

    protected $fillable = [
        'row_no', 'unique_row_no', 'row_created_year',
        'branch', 'department', 'quotation_date', 'client_id', 'client_address',
        'origin', 'destination', 'place_of_receipt', 'place_of_delivery',
        'inco_terms', 'valid_from', 'valid_to', 'service_type', 'pp_cc',
        'transit_time', 'frequency', 'etd', 'eta', 'destination_free_days', 'remarks',
        'freight', 'por', 'pol', 'pod', 'pof', 'carrier',
        'mark_no', 'internal_notes',
        'vessel_name', 'voyage_no', 'no_of_pcs', 'gross_weight', 'weight_unit',
        'volume', 'volume_weight', 'volume_unit', 'chargeable_unit',
        'hs_code', 'description', 'consignment_remarks',
        'status', 'company_id', 'user_id',
    ];

    // Status constants
    const STATUS_PENDING  = 1;
    const STATUS_APPROVED = 2;
    const STATUS_CANCELLED = 3;

    public static function statusLabel(int $status): string
    {
        return match ($status) {
            self::STATUS_APPROVED  => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
            default                => 'Pending',
        };
    }

    public static function allStatuses(): array
    {
        return [
            self::STATUS_PENDING   => 'Pending',
            self::STATUS_APPROVED  => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(QuotationNewCharge::class, 'quotation_new_id')->orderBy('sort_order');
    }

    // Date accessors/mutators
    protected function quotationDate(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d-m-Y') : null,
            set: fn($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d') : null,
        );
    }

    protected function validFrom(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d-m-Y') : null,
            set: fn($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d') : null,
        );
    }

    protected function validTo(): Attribute
    {
        return Attribute::make(
            get: fn($v) => $v ? \Carbon\Carbon::parse($v)->format('d-m-Y') : null,
            set: fn($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d') : null,
        );
    }
}
