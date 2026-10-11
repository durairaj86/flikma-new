<?php

namespace App\Models\Payroll;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'employee_id',
        'payroll_number',
        'month',
        'year',
        'basic_salary',
        'housing_allowance',
        'transportation_allowance',
        'other_allowances',
        'total_earnings',
        'loan_deduction',
        'other_deductions',
        'total_deductions',
        'net_payable',
        'status',
        'payment_date',
        'bank_account_id',
        'expense_id',
        'journal_voucher_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transportation_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_payable' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\Expense\Expense::class);
    }

    public function journalVoucher(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\JournalVoucher\JournalVoucher::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\Account\Account::class, 'bank_account_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
