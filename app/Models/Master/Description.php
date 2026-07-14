<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\CompanyOrGlobalScopeTrait;

class Description extends Model
{
    use CompanyOrGlobalScopeTrait, CompanyScopeWithNullTrait;
    public static function descriptions(): \Illuminate\Support\Collection
    {
        return DB::table('descriptions')->where(function ($query) {
            $query->where('company_id', companyId())->orWhereNull('company_id');
        })
            ->select('id', 'description')
            ->orderBy('description')
            ->get();
    }
}
