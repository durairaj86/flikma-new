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

            // every module and every sub menu gets its own row
            $offered = rightActions();
            foreach (appRights() as $module => $right) {
                if ($right['owner_only']) continue;   // the Super User's alone: never given to a department
                $moduleRights = $rights[$module] ?? [];

                DepartmentModulePermission::updateOrCreate(
                    ['department_id' => $department->id, 'module' => $module, 'company_id' => $companyId],
                    [
                        // an action the section does not have is saved off (the screen shows no box for it)
                        'can_view' => $this->ticked($moduleRights, 'view', $offered[$module]),
                        'can_create' => $this->ticked($moduleRights, 'create', $offered[$module]),
                        'can_edit' => $this->ticked($moduleRights, 'edit', $offered[$module]),
                        'can_delete' => $this->ticked($moduleRights, 'delete', $offered[$module]),
                        'can_approve' => $this->ticked($moduleRights, 'approve', $offered[$module]),
                        'can_confirm' => $this->ticked($moduleRights, 'confirm', $offered[$module]),
                    ]
                );
            }
        });
        DepartmentModulePermission::forgetEffective();
    }

    private function ticked(array $moduleRights, string $action, array $offered): bool
    {
        return in_array($action, $offered, true) && !empty($moduleRights[$action]);
    }

    private function rightsFor(Department $department): array
    {
        // what is really in force: the company's own rows, else the shared default rows (a module with no row at all is denied)
        return rightsMatrix(DepartmentModulePermission::effectiveFor((int) $department->id));
    }

    private function defaultRights(): array
    {
        return rightsMatrix(null);
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
            'message' => __('Department status updated successfully!'),
            'data' => ['id' => $department->id, 'is_active' => $department->is_active],
        ]);
    }
}
