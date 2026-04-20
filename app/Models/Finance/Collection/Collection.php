<?php

namespace App\Models\Finance\Collection;

use App\Models\Documents\Documents;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Finance;
use App\Models\Job\Job;
use App\Models\Customer\Customer;
use App\Models\User;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use LogHistoryTrait, SoftDeletes;

    protected $fillable = [
        'row_no', 'customer_id', 'job_id', 'job_no', 'collection_date', 'collection_method',
        'reference_no', 'currency', 'currency_rate', 'sub_total', 'tax_total', 'bank_charges',
        'other_charges', 'grand_total', 'base_sub_total', 'base_tax_total', 'base_bank_charges',
        'base_other_charges', 'base_grand_total', 'notes', 'status',
        'disapproval_reason', 'created_by', 'updated_by', 'approved_by', 'approved_at'
    ];

    /**
     * Get the customer associated with the collection.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the job associated with the collection.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the invoices associated with the collection.
     */
    public function collectionInvoices(): HasMany
    {
        return $this->hasMany(CollectionInvoice::class);
    }

    /**
     * Get the customer invoices associated with the collection through collection_invoices.
     */
    public function customerInvoices()
    {
        return $this->belongsToMany(CustomerInvoice::class, 'collection_invoices')
            ->withPivot('amount')
            ->withTimestamps();
    }

    /**
     * Get the documents associated with the collection.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    /**
     * Get the user who created the collection.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the collection.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who approved the collection.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Format collection_date attribute.
     */
    protected function collectionDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * Format approved_at attribute.
     */
    protected function approvedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s') : null,
        );
    }

    /**
     * Get the finance entries associated with the collection.
     */
    public function financeEntries()
    {
        return $this->morphMany(Finance::class, 'linked');
    }
}
