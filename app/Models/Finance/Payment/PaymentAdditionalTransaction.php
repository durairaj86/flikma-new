<?php

namespace App\Models\Finance\Payment;

use App\Models\Finance\Account\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAdditionalTransaction extends Model
{
    protected $fillable = [
        'payment_id', 'account_id', 'description', 'amount', 'is_debit', 'company_id'
    ];

    /**
     * Get the payment that owns the additional transaction.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the account associated with the additional transaction.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
