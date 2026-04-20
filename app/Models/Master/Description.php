<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Description extends Model
{
    use CompanyScopeWithNullTrait;
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
