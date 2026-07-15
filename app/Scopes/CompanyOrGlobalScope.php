<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Scope for master data (descriptions, units, activities, accounts, ...)
 * that can be either global (company_id NULL, seeded defaults) or owned by
 * one company. Shows the current company's rows plus the global ones.
 */
class CompanyOrGlobalScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $companyId = companyId();

        if ($companyId) {
            $table = $model->getTable();
            $builder->where(function ($query) use ($table, $companyId) {
                $query->where($table . '.company_id', $companyId)
                    ->orWhereNull($table . '.company_id');
            });
        }
    }
}
