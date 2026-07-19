<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\HsTariff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class HsTariffController extends Controller
{
    public function fetchAllRows()
    {
        $rows = HsTariff::select('id', 'hs_code', 'description', 'duty_rate', 'unit', 'is_active', 'company_id');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'class' => 'row-item',
                'id' => fn($model) => 'hs-tariff-' . $model->id,
            ])
            ->editColumn('duty_rate', function ($row) {
                return number_format($row->duty_rate, 2) . '%';
            })
            ->toJson();
    }

    public function modal()
    {
        $hsTariff = new HsTariff();
        return view('modules.master.hs-tariff.hs-tariff-form', compact('hsTariff'));
    }

    public function edit($id)
    {
        $hsTariff = HsTariff::findOrFail($id);
        return view('modules.master.hs-tariff.hs-tariff-form', compact('hsTariff'));
    }

    public function store(Request $request)
    {
        $id = filled($request->input('data-id')) ? $request->input('data-id') : null;
        $companyId = companyId();

        $rules = [
            'hs_code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('hs_tariffs', 'hs_code')->where('company_id', $companyId)->ignore($id),
            ],
            'description' => ['required', 'string', 'max:255'],
            'duty_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'unit' => ['nullable', 'string', 'max:64'],
        ];

        $validated = $request->validate($rules);

        if ($id) {
            $hsTariff = HsTariff::findOrFail($id);
        } else {
            $hsTariff = new HsTariff();
            $this->setBaseColumns($hsTariff);
        }

        $hsTariff->hs_code = $validated['hs_code'];
        $hsTariff->description = $validated['description'];
        $hsTariff->duty_rate = $validated['duty_rate'];
        $hsTariff->unit = $validated['unit'] ?? null;
        $hsTariff->is_active = $request->boolean('is_active', true);
        $hsTariff->save();

        return response()->json([
            'status' => 'success',
            'message' => 'HS Tariff saved successfully',
            'module_id' => $hsTariff->id,
        ]);
    }

    public function actions($id)
    {
        $hsTariff = HsTariff::select('id')->findOrFail($id);

        $contextMenu = collect([[
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $hsTariff->id,
            'type' => 'item',
            'icon' => 'edit',
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $hsTariff->id,
            'type' => 'item',
            'icon' => 'delete',
        ]]);

        return response()->json($contextMenu->values());
    }

    public function destroy($id)
    {
        $hsTariff = HsTariff::findOrFail($id);
        $hsTariff->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'HS Tariff deleted successfully',
        ]);
    }
}
