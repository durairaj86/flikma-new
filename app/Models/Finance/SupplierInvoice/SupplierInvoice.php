<?php

namespace App\Models\Finance\SupplierInvoice;

use App\Models\Documents\Documents;
use App\Models\Job\Job;
use App\Models\Supplier\Supplier;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SupplierInvoice extends Model
{
    use LogHistoryTrait;
    public function supplierInvoiceSubs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupplierInvoiceSub::class);
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    protected function postedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : \Carbon\Carbon::today()->format('d-m-Y'),
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function invoiceDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function dueAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
