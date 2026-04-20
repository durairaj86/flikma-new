<?php

namespace App\Models\Finance\SupplierInvoice;

use Illuminate\Database\Eloquent\Model;

class SupplierInvoiceSub extends Model
{
    public function supplierInvoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }
}
