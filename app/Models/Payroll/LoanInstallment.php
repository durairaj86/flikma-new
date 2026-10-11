<?php

namespace App\Models\Payroll;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallment extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'loan_id',
        'amount',
        'paid_date',
        'bank_account_id',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\Account\Account::class, 'bank_account_id');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'loan_id');
    }
}
