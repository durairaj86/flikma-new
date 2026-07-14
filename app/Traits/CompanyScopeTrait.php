<?php

namespace App\Traits;

use App\Scopes\CompanyScope;

trait CompanyScopeTrait
{
    protected static function bootCompanyScopeTrait()
    {
        static::addGlobalScope(new CompanyScope);
    }
}
