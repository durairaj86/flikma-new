<?php

namespace App\Http\Controllers\Master\TransportDirectory;

use App\Http\Controllers\Controller;
use App\Models\Master\TransportDirectory\Port;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SeaPortController extends Controller
{
    public function fetchAllRows()
    {
        $rows = Port::select('id', 'code', 'name', 'country_name','company_id');
        return DataTables::eloquent($rows)
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
    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $seaport = new Port();
        return view('modules.master.transport-directory.seaport.seaport-form')->with('seaport', $seaport);
    }

    public function edit($id)
    {
        $seaport = Port::find($id);
        return view('modules.master.transport-directory.seaport.seaport-form')->with('seaport', $seaport);
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'port_name' => 'required|string|max:128',
            'port_code' => [
                'required',
                'string',
                'max:5',
                Rule::unique('ports', 'code')->ignore($request['data-id']), // <-- adjust model or id reference
            ],
            'country' => 'required|string|max:32',
        ];

        // Validate request
        $validated = $request->validate($rules);


        // Save customer
        if (isset($request['data-id']) and filled($request['data-id'])) {
            $port = Port::findOrFail($request->input('data-id'));
        } else {
            $port = new Port();
            $port->company_id = companyId();
        }

        $port->name = $request->input('port_name');
        $port->code = $request->input('port_code');
        $port->country_name = $request->input('country');
        $port->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Port created successfully',
            'module_id' => $port->id,
        ]);
    }

    public function actions($id)
    {
        $port = Port::select('id')->findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $port->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $port->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);


        return response()->json($contextMenu->values());
    }
}
