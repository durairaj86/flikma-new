<?php

namespace App\Models\Sales;

use App\Models\Master\CarrierLine;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One lane rate: what a carrier charges us (buy) and what we charge customers (sell), with its validity. */
class RateSheet extends Model
{
    use CompanyScopeTrait, LogHistoryTrait, SoftDeletes;

    protected $guarded = ['id', 'company_id', 'user_id', 'row_no', 'unique_row_no'];

    protected $casts = ['valid_from' => 'date', 'valid_to' => 'date', 'is_active' => 'boolean'];

    public function carrier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CarrierLine::class, 'carrier_id');
    }

    public const BASIS = ['per_container' => 'Per container', 'per_cbm' => 'Per CBM', 'per_kg' => 'Per kg', 'per_shipment' => 'Per shipment', 'per_pallet' => 'Per pallet'];

    /** active | expiring (ends within 30 days) | expired | inactive */
    public function validity(): string
    {
        if (!$this->is_active) return 'inactive';
        if ($this->valid_to && $this->valid_to->lt(today())) return 'expired';
        if ($this->valid_from && $this->valid_from->gt(today())) return 'upcoming';
        if ($this->valid_to && $this->valid_to->lte(today()->addDays(30))) return 'expiring';

        return 'active';
    }
}
