<?php

namespace App\Models\BL;

use App\Models\Master\CarrierLine;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Master bill of lading / master airway bill issued by the carrier; house bills (seaway / airway bills) are consolidated under it. */
class MasterBl extends Model
{
    use CompanyScopeTrait, LogHistoryTrait, SoftDeletes;

    protected $guarded = ['id', 'company_id', 'user_id', 'row_no', 'unique_row_no', 'status'];

    protected $casts = ['issue_date' => 'date', 'etd' => 'date', 'eta' => 'date'];

    public function carrier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CarrierLine::class, 'carrier_id');
    }

    public function seawayBills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SeawayBill::class, 'master_bl_id');
    }

    public function airwayBills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AirwayBill::class, 'master_bl_id');
    }

    /** House bills of the right kind for this master's mode. */
    public function houseBills()
    {
        return $this->shipment_mode === 'air' ? $this->airwayBills : $this->seawayBills;
    }
}
