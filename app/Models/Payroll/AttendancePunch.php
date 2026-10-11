<?php

namespace App\Models\Payroll;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePunch extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'device_id', 'device_user_id', 'employee_id', 'punched_at', 'direction', 'status', 'raw'];

    protected $casts = ['punched_at' => 'datetime', 'raw' => 'array'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'device_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'employee_id');
    }
}
