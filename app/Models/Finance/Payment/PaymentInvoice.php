<?php

namespace App\Models\Finance\Payment;

use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInvoice extends Model
{
    protected $fillable = [
        'payment_id', 'supplier_invoice_id', 'company_id', 'amount'
    ];

    /**
     * Get the payment that owns the payment invoice.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the supplier invoice that owns the payment invoice.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }
}
