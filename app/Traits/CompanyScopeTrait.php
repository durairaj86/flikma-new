<?php

namespace App\Traits;

use App\Scopes\CompanyScope;

trait CompanyScopeTrait
{
    use CompanyKeyedSaves;

    protected static function bootCompanyScopeTrait()
    {
        static::addGlobalScope(new CompanyScope);
    }
}
