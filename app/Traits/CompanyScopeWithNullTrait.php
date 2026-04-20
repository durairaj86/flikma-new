<?php

namespace App\Traits;

use App\Scopes\CompanyScopeWithNull;

trait CompanyScopeWithNullTrait
{
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScopeWithNull());
    }
}
