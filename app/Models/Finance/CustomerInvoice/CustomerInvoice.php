<?php

namespace App\Models\Finance\CustomerInvoice;

use App\Models\Customer\Customer;
use App\Models\Documents\Documents;
use App\Models\Job\Job;
use App\Models\Job\JobContainer;
use App\Models\Job\JobPackage;
use App\Models\Zatca\ZatcaHistory;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvoice extends Model
{
    use LogHistoryTrait, SoftDeletes;

    public function customerInvoiceSubs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerInvoiceSub::class);
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    public function jobContainers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JobContainer::class, 'job_id', 'job_id');
    }

    public function jobPackages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JobPackage::class, 'job_id', 'job_id');
    }

    public function zatcaHistory()
    {
        return $this->morphOne(ZatcaHistory::class, 'invoice');
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
