<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CurrencyController extends Controller
{
    public function fetchAllRows()
    {
        $rows = DB::/*connection('masters')->*/table('currencies')->select('id', 'code', 'name', 'country');
        return DataTables::query($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => function ($model) {
                    return $model->id;
                    //return encryptId($model->id);
                },
                'class' => 'row-item',
            ])
            ->toJson();
    }
}
