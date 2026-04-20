<?php

namespace App\Models\Finance\JournalVoucher;

use App\Models\Customer\Customer;
use App\Models\Finance\Account\Account;
use App\Models\Job\Job;
use App\Models\Supplier\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalVoucherItem extends Model
{
    protected $fillable = [
        'journal_voucher_id', 'account_id', 'entity_type', 'entity_id', 'tax_id',
        'description', 'debit_amount', 'credit_amount', 'base_debit_amount',
        'base_credit_amount', 'tax_amount', 'base_tax_amount', 'company_id'
    ];

    /**
     * Get the journal voucher that owns the journal voucher item.
     */
    public function journalVoucher(): BelongsTo
    {
        return $this->belongsTo(JournalVoucher::class);
    }

    /**
     * Get the account that owns the journal voucher item.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the customer associated with the journal voucher item.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'entity_id')
            ->where('entity_type', 'customer');
    }

    /**
     * Get the supplier associated with the journal voucher item.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'entity_id')
            ->where('entity_type', 'supplier');
    }

    /**
     * Get the job associated with the journal voucher item.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'entity_id')
            ->where('entity_type', 'job');
    }

    /**
     * Get the tax account associated with the journal voucher item.
     */
    public function taxAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tax_id');
    }
}
