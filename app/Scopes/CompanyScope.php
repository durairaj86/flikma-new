<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $query = $builder->getQuery();

        // Skip if query already has raw "IN" filters
        $hasInRaw = collect($query->wheres)->contains(function ($where) {
            return isset($where['type']) && $where['type'] === 'InRaw';
        });

        if (!$hasInRaw) {
            $companyId = companyId(); // Your helper to get current user's company_id

            if ($companyId) {
                // ✅ Include records where company_id matches
                $builder->where($model->getTable() . '.company_id', $companyId);
            }
        }
    }
}
