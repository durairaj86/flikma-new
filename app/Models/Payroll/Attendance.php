<?php

namespace App\Models\Payroll;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToCompany;

    protected $table = 'attendance';

    protected $fillable = [
        'company_id',
        'employee_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'overtime_hours',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }
}
