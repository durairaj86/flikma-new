<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScopeWithNull implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $query = $builder->getQuery();

        // Skip if query already has raw "IN" filters
        $hasInRaw = collect($query->wheres)->contains(function ($where) {
            return isset($where['type']) && $where['type'] === 'InRaw';
        });

        if (! $hasInRaw) {
            $companyId = companyId(); // Your helper to get current user's company_id

            if ($companyId) {
                // a company that was given its own copy of this table at onboarding sees only its own rows
                if (\App\Support\CompanyDefaults::hidesShared($model->getTable(), (int) $companyId)) {
                    $builder->where($model->getTable() . '.company_id', $companyId);

                    return;
                }
                // ✅ Include records where company_id matches OR is NULL
                $builder->where(function ($q) use ($model, $companyId) {
                    $q->where($model->getTable() . '.company_id', $companyId)
                        ->orWhereNull($model->getTable() . '.company_id');
                });
            }
        }
    }
}
