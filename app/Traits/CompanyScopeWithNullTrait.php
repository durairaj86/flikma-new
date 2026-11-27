<?php

namespace App\Traits;

use App\Scopes\CompanyScopeWithNull;

trait CompanyScopeWithNullTrait
{
    use CompanyKeyedSaves;

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScopeWithNull());
    }
}
