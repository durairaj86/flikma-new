<?php

namespace App\Models\Log;

use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

class LogHistory extends Model
{
    protected $fillable = [
        'company_id',
        'loggable_type',
        'loggable_id',
        'loggable_number',
        'loggable_name',
        'user_id',
        'action',
        'changes'
    ];

    protected $casts = [
        'changes' => 'array',
        'user_id' => 'array',
    ];

    public function loggable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
