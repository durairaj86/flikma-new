<?php

namespace App\Models\BL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirwayBillSub extends Model
{
    use HasFactory;

    protected $table = 'airway_bill_subs';

    protected $fillable = [
        'airway_bill_id',
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
     * Get the airway bill that owns the airway bill sub.
     */
    public function airwayBill()
    {
        return $this->belongsTo(AirwayBill::class, 'airway_bill_id');
    }

    /**
     * Get the description associated with the airway bill sub.
     */
    public function description()
    {
        return $this->belongsTo(\App\Models\Master\Description::class, 'description_id');
    }
}
