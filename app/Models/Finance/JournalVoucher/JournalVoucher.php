<?php

namespace App\Models\Finance\JournalVoucher;

use App\Models\Documents\Documents;
use App\Models\Job\Job;
use App\Models\User;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalVoucher extends Model
{
    use LogHistoryTrait, SoftDeletes;

    protected $fillable = [
        'row_no', 'voucher_type', 'job_id', 'job_no', 'voucher_date',
        'reference_no', 'currency', 'currency_rate', 'debit_total', 'credit_total',
        'base_debit_total', 'base_credit_total', 'notes', 'status',
        'disapproval_reason', 'created_by', 'updated_by', 'approved_by', 'approved_at'
    ];

    /**
     * Get the job associated with the journal voucher.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the items associated with the journal voucher.
     */
    public function journalVoucherItems(): HasMany
    {
        return $this->hasMany(JournalVoucherItem::class);
    }

    /**
     * Get the documents associated with the journal voucher.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    /**
     * Get the user who created the journal voucher.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the journal voucher.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who approved the journal voucher.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Format voucher_date attribute.
     */
    protected function voucherDate(): Attribute
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
}
