<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScopeWithNull implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // eager loads (with(): a raw IN list of keys) get the same filter; they used to skip it
        $companyId = companyId(); // the current user's company
        if (!$companyId) {
            return;
        }
        // a company that was given its own copy of this table at onboarding sees only its own rows
        if (\App\Support\CompanyDefaults::hidesShared($model->getTable(), (int) $companyId)) {
            $builder->where($model->getTable() . '.company_id', $companyId);

            return;
        }
        // records of this company OR shared ones (company_id NULL)
        $builder->where(function ($q) use ($model, $companyId) {
            $q->where($model->getTable() . '.company_id', $companyId)
                ->orWhereNull($model->getTable() . '.company_id');
        });
    }
}
