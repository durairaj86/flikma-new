<?php

namespace App\Models\Payroll;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Attendance extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'remarks',
        'user_id',
        'company_id',
    ];

    protected $dates = [
        'date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the employee associated with the attendance record.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Format the date attribute.
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * Get attendance records for a specific month and year.
     */
    public static function getMonthlyAttendance($month, $year, $employeeId = null)
    {
        $query = self::whereMonth('date', $month)
                     ->whereYear('date', $year);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->orderBy('date')->get();
    }

    /**
     * Get attendance status options.
     */
    public static function getStatusOptions()
    {
        return [
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'half-day' => 'Half Day',
            'leave' => 'Leave',
        ];
    }
}
