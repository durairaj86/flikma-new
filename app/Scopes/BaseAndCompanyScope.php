<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseAndCompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $companyId = companyId(); // your helper to get current company_id

        // Only apply filter if companyId exists
        if ($companyId) {
            $table = $model->getTable();

            $builder->where(function ($query) use ($table, $companyId) {
                $query->where($table . '.company_id', $companyId)
                    ->orWhereNull($table . '.company_id'); // include nulls
            });
        }
    }
}
