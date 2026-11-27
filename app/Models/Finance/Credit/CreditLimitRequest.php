<?php

namespace App\Models\Finance\Credit;

use App\Models\Customer\Customer;
use App\Models\User;
use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A request to change a customer's credit limit / days (Finances › Credit Approval). */
class CreditLimitRequest extends Model
{
    use CompanyScopeTrait;

    public const STATUSES = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];

    protected $fillable = ['company_id', 'customer_id', 'current_limit', 'current_days', 'requested_limit', 'requested_days', 'reason',
        'status', 'requested_by', 'decided_by', 'decided_at', 'decision_note'];

    protected $casts = ['decided_at' => 'datetime'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
