<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Crm\LostReason;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

/** Master list of the reasons a deal is lost (asked when a prospect, enquiry or quotation is marked lost / cancelled). */
class LostReasonController extends Controller
{
    public function fetchAllRows()
    {
        LostReason::seedDefaults();
        $rows = LostReason::select('id', 'name', 'sort_order', 'is_active', 'company_id');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'class' => 'row-item',
            ])
            ->addColumn('used', fn($row) => $this->usage($row->id))
            ->editColumn('is_active', fn($row) => $row->is_active ? __('Active') : __('Inactive'))
            ->toJson();
    }

    public function modal()
    {
        $lostReason = new LostReason();
        $lostReason->is_active = true;
        $lostReason->sort_order = (LostReason::max('sort_order') ?? 0) + 1;

        return view('modules.master.lost-reason.lost-reason-form', compact('lostReason'));
    }

    public function edit($id)
    {
        $lostReason = LostReason::findOrFail($id);

        return view('modules.master.lost-reason.lost-reason-form', compact('lostReason'));
    }

    public function store(Request $request)
    {
        $id = $request->input('data-id') ?: null;

        $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique('lost_reasons', 'name')->where('company_id', companyId())->ignore($id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ], [], ['name' => __('Reason'), 'sort_order' => __('Sort order')]);

        if ($id) {
            $lostReason = LostReason::findOrFail($id);
        } else {
            $lostReason = new LostReason();
            $this->setBaseColumns($lostReason);
        }
        $lostReason->name = trim($request->input('name'));
        $lostReason->sort_order = (int) $request->input('sort_order', 0);
        $lostReason->is_active = $request->boolean('is_active');
        $lostReason->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Lost reason saved successfully'),
            'module_id' => $lostReason->id,
        ]);
    }

    public function actions($id)
    {
        $lostReason = LostReason::select('id', 'is_active')->findOrFail($id);

        $contextMenu = collect([[
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $lostReason->id,
            'type' => 'item',
            'icon' => 'edit',
        ], $lostReason->is_active ? [
            'label' => __('In-Active'),
            'code' => '01CSED',
            'id' => 'row_inactive',
            'class' => 'row_inactive',
            'data-id' => $lostReason->id,
            'data-value' => '0',
            'type' => 'item',
            'icon' => 'blocked',
        ] : [
            'label' => __('Active'),
            'code' => '01CSED',
            'id' => 'row_active',
            'class' => 'row_active',
            'data-id' => $lostReason->id,
            'data-value' => '1',
            'type' => 'item',
            'icon' => 'confirmed',
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $lostReason->id,
            'type' => 'item',
            'icon' => 'delete',
        ]]);

        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status)
    {
        $lostReason = LostReason::findOrFail($id);
        $lostReason->is_active = (bool) $status;
        $lostReason->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Lost reason status updated successfully!'),
        ]);
    }

    public function destroy($id)
    {
        $lostReason = LostReason::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('lost_reason', (int) $id);
        if ($why) {
            return $guard->refusal(__('lost reason'), $why);
        }
        $lostReason->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Lost reason deleted successfully'),
        ]);
    }

    /** How many lost prospects / enquiries / quotations carry this reason. */
    private function usage(int $id): int
    {
        return \DB::table('prospects')->where('lost_reason_id', $id)->whereNull('deleted_at')->count()
            + \DB::table('enquiries')->where('lost_reason_id', $id)->whereNull('deleted_at')->count()
            + \DB::table('quotations')->where('lost_reason_id', $id)->whereNull('deleted_at')->count();
    }
}
