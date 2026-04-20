<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Incoterm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IncotermsController extends Controller
{
    public function fetchAllRows()
    {
        $rows = $rows = DB::/*connection('masters')->*/table('incoterms')->select('id', 'code', 'name', 'description', 'transport_mode');
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
