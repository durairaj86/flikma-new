<?php

namespace App\Models\QuotationNew;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationNewCharge extends Model
{
    protected $table = 'quotation_new_charges';

    protected $fillable = [
        'quotation_new_id',
        'charge_description', 'ofd_type', 'unit', 'qty',
        'freight', 'dr_cr', 'qty_amount', 'fcy_amount', 'amount_inr', 'tax_amount_inr',
        'is_standard',
        'bill_to_id', 'currency', 'ex_rate', 'tax_group_code',
        'taxable_amount', 'tax_amount_sale', 'sale_remarks',
        'vendor_id', 'reference_no', 'cost_date', 'cost_amount',
        'sort_order',
    ];

    protected $casts = [
        'is_standard' => 'boolean',
    ];

    public function quotationNew(): BelongsTo
    {
        return $this->belongsTo(QuotationNew::class, 'quotation_new_id');
    }

    public function billTo(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'bill_to_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'vendor_id');
    }
}
