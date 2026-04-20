<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceSub extends Model
{
    protected $table = 'finance_sub';

    protected $fillable = [
        'finance_id',
        'voucher_no',
        'voucher_type',
        'reference_no',
        'supplier_id',
        'account_id',
        'reference_date',
        'description',
        'debit',
        'credit',
        'currency',
        'base_debit',
        'base_credit',
        'base_currency',
        'exchange_rate',
        'job_id',
        'job_no',
        'cost_center_id',
        'is_tax_line',
        'is_auto_generated',
        'linked_id',
        'linked_type',
        'user_id',
        'company_id'
    ];

    /**
     * Get the finance entry that owns this finance sub entry.
     */
    public function finance(): BelongsTo
    {
        return $this->belongsTo(Finance::class);
    }

    /**
     * Get the linked model that owns this finance sub entry.
     */
    public function linked()
    {
        return $this->morphTo();
    }

    public function referenceDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
