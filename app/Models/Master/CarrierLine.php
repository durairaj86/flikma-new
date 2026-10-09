<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

/** Shipping lines and airlines (shared reference list, no company scope). */
class CarrierLine extends Model
{
    protected $table = 'carrier_lines';
    public $timestamps = false;
    protected $guarded = [];
}
