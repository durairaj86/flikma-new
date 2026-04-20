<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\BasicSalary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BasicSalaryController extends Controller
{
    /**
     * Display the basic salary list view.
     */
    public function index()
    {
        return view('modules.payroll.basic-salary.list');
    }

    /**
     * Show the form for creating a new basic salary.
     */
    public function create()
    {
        $employees = User::all()->pluck('name', 'id');
        return view('modules.payroll.basic-salary.form', compact('employees'));
    }

    /**
     * Show the form for editing the specified basic salary.
     */
    public function edit($id)
    {
        $basicSalary = BasicSalary::findOrFail($id);
        $employees = User::all()->pluck('name', 'id');
        return view('modules.payroll.basic-salary.form', compact('basicSalary', 'employees'));
    }

    /**
     * Store a newly created or update an existing basic salary.
     */
    public function store(Request $request)
    {

        $numericFields = [
            'basic_salary',
            'housing_allowance',
            'transportation_allowance',
            'food_allowance',
            'phone_allowance',
            'other_allowance',
        ];

        foreach ($numericFields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);

                if (is_array($value)) {
                    // Handle table/array inputs (like quantity[], weight[])
                    $cleaned = array_map(fn($v) => is_string($v) ? str_replace(',', '', $v) : $v, $value);
                } else {
                    // Handle direct fields (like loan_amount, basic_salary)
                    $cleaned = is_string($value) ? str_replace(',', '', $value) : $value;
                }

                $request->merge([$field => $cleaned]);
            }
        }

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'basic_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',
            'food_allowance' => 'nullable|numeric|min:0',
            'phone_allowance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            // If it's an update
            if ($request->has('data-id') && $request->input('data-id')) {
                $basicSalary = BasicSalary::findOrFail($request->input('data-id'));
                $basicSalary->update($data);
                $message = 'Basic salary updated successfully';
            } else {
                // Generate row_no
                $lastRecord = BasicSalary::latest('id')->first();
                $newId = $lastRecord ? $lastRecord->id + 1 : 1;
                $data['row_no'] = 'BS-' . str_pad($newId, 5, '0', STR_PAD_LEFT);

                $data['user_id'] = Auth::id();
                $data['company_id'] = companyId();

                $basicSalary = BasicSalary::create($data);
                $message = 'Basic salary created successfully';
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error saving waybill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch all basic salaries for DataTables.
     */
    public function fetchAllRows(Request $request)
    {
        $query = BasicSalary::with('employee')->select('basic_salaries.*');

        return DataTables::of($query)
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->row_no,
                'data-title' => fn($model) => $model->row_no,
                'class' => 'row-item',
            ])
            ->addColumn('employee_name', function ($row) {
                return $row->employee ? $row->employee->name : 'N/A';
            })
            ->addColumn('total_allowances', function ($row) {
                return $row->housing_allowance + $row->transportation_allowance +
                    $row->food_allowance + $row->phone_allowance + $row->other_allowance;
            })
            ->addColumn('total_salary', function ($row) {
                return $row->basic_salary + $row->housing_allowance + $row->transportation_allowance +
                    $row->food_allowance + $row->phone_allowance + $row->other_allowance;
            })
            ->addColumn('actions', function ($row) {
                return $this->actions($row->id);
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Generate action buttons for DataTables.
     */
    public function actions($id)
    {
        $basicSalary = BasicSalary::select(
            'id',
            'status'
        )->withTrashed()->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];

        if ($basicSalary->status === 'draft') {
            $contextMenu->push([
                'label' => __('Mark as Confirmed'),
                'code' => '01CSBK',
                'id' => 'row_in_transit',
                'data-id' => $basicSalary->id,
                'data-value' => 'confirmed',
                'type' => 'item',
                'icon' => 'confirmed',
                'separator' => 'after',
            ]);
        } elseif ($basicSalary->status === 'confirmed') {
            $contextMenu->push([
                'label' => __('Mark as Draft'),
                'code' => '01CSBK',
                'id' => 'row_pending',
                'data-id' => $basicSalary->id,
                'data-value' => 'draft',
                'type' => 'item',
                'icon' => 'pending',
            ]);
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $basicSalary->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'BASIC_SALARY.printPreview(' . $basicSalary->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $basicSalary->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($basicSalary->status === 'draft') {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $basicSalary->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $basicSalary->id,
                'type' => 'item',
                'icon' => 'delete'
            ];
            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit, $delete]
            ]);
        }
        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $basicSalary = BasicSalary::findOrFail($id);
        $basicSalary->status = $status;
        $basicSalary->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Basic salary status updated successfully!',
            'data' => [
                'id' => $basicSalary->id,
                'status' => $basicSalary->status,
            ],
        ]);
    }

    /**
     * Display the waybill overview
     */
    public function overview($id)
    {
        // In a real implementation, you would fetch the waybill by ID
        // For now, we'll just return a simple view
        $salary = BasicSalary::withTrashed()->findOrFail($id);
        return view('modules.payroll.basic-salary.view-overview')->with('salary', $salary);
    }

    /**
     * Print the waybill
     */
    public function print($id)
    {
        // In a real implementation, you would fetch the waybill by ID
        // For now, we'll just return a simple view
        $salary = BasicSalary::withTrashed()->findOrFail($id);
        return view('modules.payroll.basic-salary.view-overview')->with('salary', $salary);
    }

    /**
     * Remove the specified basic salary from storage.
     */
    public function destroy($id)
    {
        try {
            $basicSalary = BasicSalary::findOrFail($id);
            $basicSalary->delete();

            return response()->json(['success' => true, 'message' => 'Basic salary deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
