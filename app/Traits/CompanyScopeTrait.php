<?php

namespace App\Traits;

use App\Scopes\CompanyScope;

trait CompanyScopeTrait
{
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
}
