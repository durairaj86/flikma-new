<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Department;
use App\Models\Master\DepartmentModulePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function fetchAllRows()
    {
        // select() must come before withCount() — select() replaces the
        // whole select list, so calling it after withCount() silently wipes
        // out the users_count subquery column it had queued.
        $rows = Department::select('id', 'name', 'code', 'is_active', 'company_id', 'created_at')->withCount('users');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'class' => 'row-item',
            ])
            ->editColumn('created_at', function ($row) {
                return showDate($row->created_at);
            })
            ->editColumn('is_active', function ($row) {
                return $row->is_active ? 'Active' : 'Inactive';
            })
            ->toJson();
    }

    public function modal()
    {
        $department = new Department();
        $rights = $this->defaultRights();

        return view('modules.master.department.department-form', compact('department', 'rights'));
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        $rights = $this->rightsFor($department);

        return view('modules.master.department.department-form', compact('department', 'rights'));
    }

    public function store(Request $request)
    {
        $id = $request->input('data-id') ?: null;

        $rules = [
            'name' => [
                'required', 'string', 'max:128',
                Rule::unique('departments', 'name')->where('company_id', companyId())->ignore($id),
            ],
            'code' => ['nullable', 'string', 'max:32'],
            'is_active' => ['required'],
        ];

        $validated = $request->validate($rules);

        if ($id) {
            $department = Department::findOrFail($id);
        } else {
            $department = new Department();
            $department->company_id = companyId();
        }

        $department->name = $request->input('name');
        $department->code = $request->input('code');
        $department->is_active = (bool) $request->input('is_active');
        $department->save();

        $this->saveRights($department, (array) $request->input('rights', []));

        return response()->json([
            'status' => 'success',
            'message' => 'Department ' . ($id ? 'updated' : 'created') . ' successfully',
            'module_id' => $department->id,
        ]);
    }

    private function saveRights(Department $department, array $rights): void
    {
        DB::transaction(function () use ($department, $rights) {
            // Always scope the saved rows to the acting company (not
            // $department->company_id, which is NULL for the shared
            // default departments) — otherwise every company editing a
            // shared department's rights would collide on the same row.
            $companyId = companyId();

            foreach (array_keys(appModules()) as $module) {
                $moduleRights = $rights[$module] ?? [];

                DepartmentModulePermission::updateOrCreate(
                    ['department_id' => $department->id, 'module' => $module, 'company_id' => $companyId],
                    [
                        'can_view' => !empty($moduleRights['view']),
                        'can_create' => !empty($moduleRights['create']),
                        'can_edit' => !empty($moduleRights['edit']),
                        'can_delete' => !empty($moduleRights['delete']),
                        'can_approve' => !empty($moduleRights['approve']),
                        'can_confirm' => !empty($moduleRights['confirm']),
                    ]
                );
            }
        });
    }

    private function rightsFor(Department $department): array
    {
        $saved = $department->permissions()->get()->keyBy('module');
        $rights = [];

        foreach (appModules() as $moduleKey => $module) {
            $permission = $saved->get($moduleKey);
            $rights[$moduleKey] = [
                'label' => $module['label'],
                'view' => $permission?->can_view ?? true,
                'create' => $permission?->can_create ?? true,
                'edit' => $permission?->can_edit ?? true,
                'delete' => $permission?->can_delete ?? true,
                'approve' => $permission?->can_approve ?? true,
                'confirm' => $permission?->can_confirm ?? true,
            ];
        }

        return $rights;
    }

    private function defaultRights(): array
    {
        $rights = [];

        foreach (appModules() as $moduleKey => $module) {
            $rights[$moduleKey] = [
                'label' => $module['label'],
                'view' => true,
                'create' => true,
                'edit' => true,
                'delete' => true,
                'approve' => true,
                'confirm' => true,
            ];
        }

        return $rights;
    }

    public function actions($id)
    {
        $department = Department::select('id', 'is_active')->findOrFail($id);

        $contextMenu = collect([]);

        if ($department->is_active) {
            $contextMenu->push([
                'label' => __('In-Active'),
                'code' => '01CSED',
                'id' => 'row_inactive',
                'class' => 'row_inactive',
                'data-id' => $department->id,
                'data-value' => '0',
                'type' => 'item',
                'icon' => 'blocked',
            ]);
        } else {
            $contextMenu->push([
                'label' => __('Active'),
                'code' => '01CSED',
                'id' => 'row_active',
                'class' => 'row_active',
                'data-id' => $department->id,
                'data-value' => '1',
                'type' => 'item',
                'icon' => 'confirmed',
            ]);
        }

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $department->id,
            'type' => 'item',
            'icon' => 'edit',
        ]);

        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status)
    {
        $department = Department::findOrFail($id);
        $department->is_active = (bool) $status;
        $department->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Department status updated successfully!',
            'data' => ['id' => $department->id, 'is_active' => $department->is_active],
        ]);
    }
}
