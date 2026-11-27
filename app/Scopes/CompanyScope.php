<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $companyId = companyId(); // the current user's company
        if (!$companyId) {
            return;
        }
        $column = $model->getTable() . '.company_id';

        // An eager load (with()) asks for the related rows by key with a raw IN list. It used to skip the company filter
        // altogether; now it takes the rows of this company plus shared rows (company_id NULL, e.g. default departments),
        // which is what those loads have always shown, and never another company's rows.
        $eagerLoad = collect($builder->getQuery()->wheres)->contains(fn ($where) => ($where['type'] ?? null) === 'InRaw');
        if ($eagerLoad) {
            $builder->where(fn ($q) => $q->where($column, $companyId)->orWhereNull($column));

            return;
        }

        $builder->where($column, $companyId);
    }
}
