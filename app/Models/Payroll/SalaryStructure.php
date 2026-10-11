<?php

namespace App\Models\Payroll;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStructure extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'employee_id',
        'basic_salary',
        'housing_allowance',
        'transportation_allowance',
        'other_allowances',
        'total_salary',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'basic_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transportation_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'total_salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }

    /** True when a (non-cancelled) payroll record exists for this employee in the months this structure covers. */
    public function usedByPayroll(): bool
    {
        $from = \Illuminate\Support\Carbon::parse($this->effective_from)->startOfMonth();
        $to = $this->effective_to ? \Illuminate\Support\Carbon::parse($this->effective_to)->endOfMonth() : null;

        return \App\Models\Payroll\PayrollRecord::where('employee_id', $this->employee_id)
            ->where('status', '!=', 'cancelled')
            ->get(['month', 'year'])
            ->contains(function ($r) use ($from, $to) {
                $d = \Illuminate\Support\Carbon::create((int) $r->year, (int) $r->month, 1);
                return $d->gte($from) && (!$to || $d->lte($to));
            });
    }
}
