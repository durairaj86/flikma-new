<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\ContainerType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContainerTypeController extends Controller
{
    public function fetchAllRows()
    {
        $rows = ContainerType::select('id', 'code', 'name', 'description', 'is_active');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => function ($model) {
                    return $model->id;
                    //return encryptId($model->id);
                },
                'class' => 'row-item',
                'id' => function ($model) {
                    return 'supplier-' . strtolower($model->row_no);
                }
            ])
            ->toJson();
    }
}
