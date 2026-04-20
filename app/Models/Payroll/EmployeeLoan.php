<?php

namespace App\Models\Payroll;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EmployeeLoan extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'row_no',
        'loan_amount',
        'interest_rate',
        'number_of_installments',
        'installment_amount',
        'loan_date',
        'first_payment_date',
        'payment_method',
        'status',
        'remaining_amount',
        'remaining_installments',
        'purpose',
        'remarks',
        'user_id',
        'company_id'
    ];

    protected $dates = [
        'loan_date',
        'first_payment_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the employee associated with the loan.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Format the loan date attribute.
     */
    protected function loanDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * Format the first payment date attribute.
     */
    protected function firstPaymentDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
