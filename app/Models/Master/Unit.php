<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Unit extends Model
{
    public static function units(): \Illuminate\Support\Collection
    {
        return DB::table('units')->where(function ($query) {
            $query->where('company_id', companyId())->orWhereNull('company_id');
        })
            ->select('id', 'unit_name', 'unit_symbol', 'status')
            ->where('status', 1)
            ->orderBy('unit_name')
            ->get();
    }
}
