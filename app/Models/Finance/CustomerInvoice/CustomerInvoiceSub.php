<?php

namespace App\Models\Finance\CustomerInvoice;

use App\Models\Master\Description;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvoiceSub extends Model
{
    use SoftDeletes;
    public function customerInvoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    public function description()
    {
        return $this->belongsTo(Description::class);
    }
}
