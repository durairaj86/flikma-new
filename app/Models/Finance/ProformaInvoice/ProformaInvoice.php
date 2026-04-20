<?php

namespace App\Models\Finance\ProformaInvoice;

use App\Models\Customer\Customer;
use App\Models\Job\Job;
use App\Models\Master\Description;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ProformaInvoice extends Model
{
    use LogHistoryTrait;
    public function proformaInvoiceSubs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProformaInvoiceSub::class);
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function postedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
