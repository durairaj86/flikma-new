<?php

namespace App\Models\Finance\Collection;

use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionInvoice extends Model
{
    protected $fillable = [
        'collection_id', 'customer_invoice_id', 'company_id', 'amount'
    ];

    /**
     * Get the collection that owns the collection invoice.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the customer invoice that owns the collection invoice.
     */
    public function customerInvoice(): BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class);
    }
}
