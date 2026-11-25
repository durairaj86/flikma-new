<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Crm\CrmAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

/** Master list of CRM actions ("Call Done", "RFQ Received", "Quote Due" ...) in their categories, picked when an activity is logged. */
class CrmActionController extends Controller
{
    public function fetchAllRows()
    {
        CrmAction::seedDefaults();
        $rows = CrmAction::select('id', 'code', 'name', 'category', 'description', 'sort_order', 'is_active', 'company_id');
        $used = DB::table('crm_activities')->where('company_id', companyId())->whereNull('deleted_at')
            ->selectRaw('action_id, COUNT(*) AS n')->groupBy('action_id')->pluck('n', 'action_id');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'class' => 'row-item',
            ])
            ->editColumn('name', fn($row) => $row->label())
            ->addColumn('category_label', fn($row) => CrmAction::categoryLabel($row->category))
            ->addColumn('category_style', fn($row) => CrmAction::badgeStyle($row->category))
            ->editColumn('description', fn($row) => $row->description ? __($row->description) : '')
            ->addColumn('auto', fn($row) => in_array($row->code, \App\Services\Crm\CrmAutoLog::CODES, true))
            ->addColumn('used', fn($row) => (int) ($used[$row->id] ?? 0))
            ->editColumn('is_active', fn($row) => $row->is_active ? __('Active') : __('Inactive'))
            ->filterColumn('category', fn($q, $k) => $q->whereIn('category', collect(CrmAction::CATEGORIES)
                ->filter(fn($c) => str_contains(mb_strtolower(__($c['label'])), mb_strtolower($k)))->keys()->all()))
            ->toJson();
    }

    public function modal()
    {
        $action = new CrmAction();
        $action->is_active = true;
        $action->category = 'outreach';
        $action->sort_order = (CrmAction::max('sort_order') ?? 0) + 1;

        return view('modules.master.crm-action.crm-action-form', compact('action'));
    }

    public function edit($id)
    {
        $action = CrmAction::findOrFail($id);

        return view('modules.master.crm-action.crm-action-form', compact('action'));
    }

    public function store(Request $request)
    {
        $id = $request->input('data-id') ?: null;
        $action = $id ? CrmAction::findOrFail($id) : null;

        $request->validate([
            'name' => ['required', 'string', 'max:100',
                Rule::unique('crm_actions', 'name')->where('company_id', companyId())->ignore($id)],
            // a default action keeps its category: the system and the summaries count on it (e.g. "Quote Due" is a to-do)
            'category' => [$action?->code ? 'nullable' : 'required', Rule::in(array_keys(CrmAction::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ], [], ['name' => __('Action'), 'category' => __('Category'), 'sort_order' => __('Sort order')]);

        if (!$action) {
            $action = new CrmAction();
            $this->setBaseColumns($action);
        }
        $action->name = trim($request->input('name'));
        if (!$action->code) {
            $action->category = $request->input('category');
        }
        $action->description = trim((string) $request->input('description')) ?: null;
        $action->sort_order = (int) $request->input('sort_order', 0);
        $action->is_active = $request->boolean('is_active');
        $action->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Action saved successfully'),
            'module_id' => $action->id,
        ]);
    }

    public function actions($id)
    {
        $action = CrmAction::select('id', 'code', 'is_active')->findOrFail($id);

        $menu = collect([[
            'label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit',
            'data-id' => $action->id, 'type' => 'item', 'icon' => 'edit',
        ], $action->is_active ? [
            'label' => __('In-Active'), 'code' => '01CSED', 'id' => 'row_inactive', 'class' => 'row_inactive',
            'data-id' => $action->id, 'data-value' => '0', 'type' => 'item', 'icon' => 'blocked',
        ] : [
            'label' => __('Active'), 'code' => '01CSED', 'id' => 'row_active', 'class' => 'row_active',
            'data-id' => $action->id, 'data-value' => '1', 'type' => 'item', 'icon' => 'confirmed',
        ]]);
        // a default action can be renamed or switched off, never deleted
        if (!$action->code) {
            $menu->push([
                'label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete',
                'data-id' => $action->id, 'type' => 'item', 'icon' => 'delete',
            ]);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status)
    {
        $action = CrmAction::findOrFail($id);
        $action->is_active = (bool) $status;
        $action->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Action status updated successfully!'),
        ]);
    }

    public function destroy($id)
    {
        $action = CrmAction::findOrFail($id);
        if ($action->code) {
            return response()->json(['status' => 'error', 'message' => __('A standard action cannot be deleted; switch it off instead.')], 422);
        }
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('crm_action', (int) $id);
        if ($why) {
            return $guard->refusal(__('action'), $why);
        }
        $action->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Action deleted successfully'),
        ]);
    }
}
