<?php

namespace App\Traits;

use App\Scopes\CompanyOrGlobalScope;

trait CompanyOrGlobalScopeTrait
{
    protected static function bootCompanyOrGlobalScopeTrait()
    {
        static::addGlobalScope(new CompanyOrGlobalScope);
    }
}
