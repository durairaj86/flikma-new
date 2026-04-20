<?php

namespace App\Models\BL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaybillSub extends Model
{
    use HasFactory;

    protected $table = 'waybill_subs';

    protected $fillable = [
        'waybill_id',
        'description_id',
        'comment',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'fragile',
    ];

    /**
     * Get the waybill that owns the waybill sub.
     */
    public function waybill()
    {
        return $this->belongsTo(Waybill::class, 'waybill_id');
    }

    /**
     * Get the description associated with the waybill sub.
     */
    public function description()
    {
        return $this->belongsTo(\App\Models\Master\Description::class, 'description_id');
    }
}
