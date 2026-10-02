<?php

namespace App\Models\Billing;

use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use CompanyScopeTrait;

    protected $fillable = [
        'company_id', 'package_id', 'invoice_number', 'amount', 'billing_cycle',
        'payment_status', 'payment_method', 'start_date', 'expiry_date',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'package_id' => 'integer',
            'start_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Master\Company::class, 'company_id');
    }

    /**
     * The cloned view reads $payment->package?->name. Plans live in config, not
     * a table, so the controller assigns a Plan value object onto this
     * attribute instead of relying on an Eloquent relation.
     */
    public function plan(): ?Plan
    {
        return Plan::find($this->package_id);
    }
}
