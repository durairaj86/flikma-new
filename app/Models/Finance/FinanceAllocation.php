<?php

namespace App\Models\Finance;

use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One settlement of a receivable / payable line by a receipt, payment, credit note or debit note (see docs/finance-ledger-design.md). */
class FinanceAllocation extends Model
{
    use CompanyScopeTrait;

    protected $table = 'finance_allocations';

    protected $guarded = [];

    protected $casts = [
        'allocation_date' => 'date',
        'reversed_at' => 'date',
        'amount' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'fx_gain_loss' => 'decimal:2',
    ];

    public function settledLine(): BelongsTo
    {
        return $this->belongsTo(FinanceSub::class, 'settled_line_id');
    }

    public function settlingLine(): BelongsTo
    {
        return $this->belongsTo(FinanceSub::class, 'settling_line_id');
    }

    /** Allocations in force on a date: made on or before it and not reversed on or before it. */
    public function scopeAsOf($query, $date)
    {
        return $query->where('allocation_date', '<=', $date)
            ->where(fn ($q) => $q->where('status', 'active')->orWhere('reversed_at', '>', $date));
    }
}
