<?php

namespace App\Models\Master;

use App\Traits\CompanyOrGlobalScopeTrait;
use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;

class HsTariff extends Model
{
    use CompanyOrGlobalScopeTrait, CompanyScopeWithNullTrait;

    protected $casts = [
        'duty_rate' => 'float',
        'is_active' => 'boolean',
    ];
}
