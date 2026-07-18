<?php

namespace App\Models\Quotation;

use Illuminate\Database\Eloquent\Model;

class QuotationCharge extends Model
{
    protected $table = 'quotation_charges';

    protected $fillable = [
        'quotation_id', 'line_no', 'charge_description',
        'unit', 'qty', 'currency', 'ex_rate',
        'amount_per_qty', 'fcy_amount', 'local_amount',
        'tax_group_code', 'remarks', 'sort_order',
    ];
}
