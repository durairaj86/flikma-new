<?php

namespace App\Models\Payroll;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'start_time',
        'end_time',
        'grace_minutes',
        'week_off_days',
        'is_active',
    ];

    protected $casts = [
        'week_off_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'shift_id');
    }
}
