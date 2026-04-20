<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finance extends Model
{
    protected $table = 'finance';

    protected $fillable = [
        'voucher_no',
        'voucher_type',
        'reference_no',
        'reference_date',
        'supplier_id',
        'customer_id',
        'narration',
        'currency',
        'exchange_rate',
        'total_debit',
        'total_credit',
        'base_currency',
        'base_total_debit',
        'base_total_credit',
        'job_id',
        'job_no',
        'is_approved',
        'posted_at',
        'linked_id',
        'linked_type',
        'company_id',
        'user_id'
    ];

    /**
     * Get the finance sub entries associated with this finance entry.
     */
    public function financeSubs(): HasMany
    {
        return $this->hasMany(FinanceSub::class);
    }

    /**
     * Get the linked model that owns this finance entry.
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
