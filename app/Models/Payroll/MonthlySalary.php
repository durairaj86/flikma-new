<?php

namespace App\Models\Payroll;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MonthlySalary extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'basic_salary',
        'housing_allowance',
        'transportation_allowance',
        'food_allowance',
        'phone_allowance',
        'other_allowance',
        'overtime_hours',
        'overtime_amount',
        'bonus',
        'deductions',
        'loan_deduction',
        'total_salary',
        'payment_date',
        'payment_method',
        'status',
        'remarks',
        'user_id',
        'company_id'
    ];

    protected $dates = [
        'payment_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the employee associated with the monthly salary.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the basic salary record associated with this monthly salary.
     */
    public function basicSalary()
    {
        return $this->belongsTo(BasicSalary::class, 'employee_id', 'employee_id')
            ->whereDate('effective_date', '<=', $this->payment_date)
            ->orderBy('effective_date', 'desc');
    }

    /**
     * Format the payment date attribute.
     */
    protected function paymentDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
