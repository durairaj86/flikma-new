<?php

namespace App\Models\Finance\Adjustment;

use App\Models\Documents\Documents;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use App\Models\Supplier\Supplier;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Debit note raised to a supplier against one of their invoices: it reduces what we owe them. */
class DebitNote extends Model
{
    use CompanyScopeTrait, LogHistoryTrait, SoftDeletes;

    public function debitNoteSubs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DebitNoteSub::class);
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'invoice_id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }
}
