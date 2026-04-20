<?php

namespace App\Models\Payroll;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BasicSalary extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'housing_allowance',
        'transportation_allowance',
        'food_allowance',
        'phone_allowance',
        'other_allowance',
        'effective_date',
        'status',
        'remarks',
        'user_id',
        'company_id'
    ];

    protected $dates = [
        'effective_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the employee associated with the basic salary.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Format the effective date attribute.
     */
    protected function effectiveDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
