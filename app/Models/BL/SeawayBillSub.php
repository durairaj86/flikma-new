<?php

namespace App\Models\BL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeawayBillSub extends Model
{
    use HasFactory;

    protected $table = 'seaway_bill_subs';

    protected $fillable = [
        'seaway_bill_id',
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
     * Get the seaway bill that owns the seaway bill sub.
     */
    public function seawayBill()
    {
        return $this->belongsTo(SeawayBill::class, 'seaway_bill_id');
    }

    /**
     * Get the description associated with the seaway bill sub.
     */
    public function description()
    {
        return $this->belongsTo(\App\Models\Master\Description::class, 'description_id');
    }
}
