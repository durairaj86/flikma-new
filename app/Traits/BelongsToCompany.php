<?php

namespace App\Traits;

use App\Scopes\CompanyScope;

/** Company-scoped model that also stamps company_id on create (used by the payroll module). */
trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (empty($model->company_id) && companyId()) {
                $model->company_id = companyId();
            }
        });
    }
}
