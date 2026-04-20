<?php

namespace App\Models\Finance\Payment;

use App\Models\Documents\Documents;
use App\Models\Finance\Finance;
use App\Models\Finance\Payment\PaymentAdditionalTransaction;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use App\Models\Supplier\Supplier;
use App\Models\User;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use LogHistoryTrait, SoftDeletes;

    protected $fillable = [
        'row_no', 'supplier_id', 'job_id', 'job_no', 'payment_date', 'payment_method',
        'reference_no', 'currency', 'currency_rate', 'sub_total', 'tax_total', 'bank_charges',
        'other_charges', 'grand_total', 'base_sub_total', 'base_tax_total', 'base_bank_charges',
        'base_other_charges', 'base_grand_total', 'notes', 'status',
        'disapproval_reason', 'created_by', 'updated_by', 'approved_by', 'approved_at'
    ];

    /**
     * Get the supplier associated with the payment.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the job associated with the payment.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the invoices associated with the payment.
     */
    public function paymentInvoices(): HasMany
    {
        return $this->hasMany(PaymentInvoice::class);
    }

    /**
     * Get the supplier invoices associated with the payment through payment_invoices.
     */
    public function supplierInvoices()
    {
        return $this->belongsToMany(SupplierInvoice::class, 'payment_invoices')
            ->withPivot('amount')
            ->withTimestamps();
    }

    /**
     * Get the documents associated with the payment.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    /**
     * Get the user who created the payment.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the payment.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who approved the payment.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Format payment_date attribute.
     */
    protected function paymentDate(): Attribute
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
     * Get the finance entries associated with the payment.
     */
    public function financeEntries()
    {
        return $this->morphMany(Finance::class, 'linked');
    }

    /**
     * Get the additional transactions associated with the payment.
     */
    public function additionalTransactions(): HasMany
    {
        return $this->hasMany(PaymentAdditionalTransaction::class);
    }
}
