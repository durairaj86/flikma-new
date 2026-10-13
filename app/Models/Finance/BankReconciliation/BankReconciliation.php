<?php

namespace App\Models\Finance\BankReconciliation;

use App\Models\Finance\Account\Account;
use App\Models\Finance\FinanceSub;
use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** One bank (or cash) statement being matched with the books. See BankReconciliationService. */
class BankReconciliation extends Model
{
    use CompanyScopeTrait;

    protected $table = 'bank_reconciliations';

    protected $guarded = [];

    protected $casts = [
        'statement_date' => 'date',
        'completed_at' => 'datetime',
        'statement_balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'cleared_balance' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** The journal lines ticked on this statement. */
    public function lines(): BelongsToMany
    {
        return $this->belongsToMany(FinanceSub::class, 'bank_reconciliation_lines', 'bank_reconciliation_id', 'finance_sub_id')->withTimestamps();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
