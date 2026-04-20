<?php

namespace App\Models\Finance\ProformaInvoice;

use Illuminate\Database\Eloquent\Model;

class ProformaInvoiceSub extends Model
{
    public function proformaInvoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProformaInvoice::class);
    }

}
