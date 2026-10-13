<?php

namespace App\Models\Finance;

use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model on the `finance_ledgers` view: every posted journal line with its entry, account and tax details.
 * Reports read this instead of joining finance / finance_subs / accounts themselves. Never written to (write through PostingService).
 */
class FinanceLedger extends Model
{
    use CompanyScopeTrait;

    protected $table = 'finance_ledgers';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'transaction_date' => 'date',
        'posting_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'base_debit' => 'decimal:2',
        'base_credit' => 'decimal:2',
        'is_tax_line' => 'boolean',
        'is_reconcilable' => 'boolean',
        'is_provisional' => 'boolean',
    ];

    /** Statutory reports leave provisional entries out unless asked. */
    public function scopeActual($query)
    {
        return $query->where('is_provisional', 0);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    public function save(array $options = [])
    {
        throw new \LogicException('finance_ledgers is a read-only view; post through the finance journal.');
    }

    public function delete()
    {
        throw new \LogicException('finance_ledgers is a read-only view; reverse through the finance journal.');
    }
}
