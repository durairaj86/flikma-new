<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\PackageCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PackageCodeController extends Controller
{
    public function fetchAllRows()
    {
        $rows = PackageCode::select('id', 'name', 'description', 'status','company_id');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => function ($model) {
                    return $model->id;
                    //return encryptId($model->id);170719911113
                },
                'class' => 'row-item',
                'id' => function ($model) {
                    return 'package-code-' . strtolower($model->row_no);
                }
            ])
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $packageCode = new PackageCode();
        return view('modules.master.package-code.package-code-form')->with('packageCode', $packageCode);
    }

    public function edit($id)
    {
        $packageCode = PackageCode::find($id);
        return view('modules.master.package-code.package-code-form')->with('packageCode', $packageCode);
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'package_name' => [
                'required',
                'string',
                'max:255',
                // Unique rule, ignore current record if editing
                Rule::unique('package_codes', 'name')->ignore($request->input('data-id')),
            ],
            'description' => 'nullable|string|max:255',
        ];

// Validate request
        $validated = $request->validate($rules);

// Save customer
        if (filled($request->input('data-id'))) {
            $packageCode = PackageCode::findOrFail($request->input('data-id'));
        } else {
            $packageCode = new PackageCode();
            $this->setBaseColumns($packageCode);
        }

        $packageCode->name = $request->input('package_name');
        $packageCode->description = $request->input('description');
        $packageCode->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Package created successfully',
            'module_id' => $packageCode->id,
        ]);
    }

    public function actions($id)
    {
        $packageCode = PackageCode::select('id', 'status')->findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $packageCode->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $packageCode->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);


        return response()->json($contextMenu->values());
    }
}
