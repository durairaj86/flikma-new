<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\EmployeeLoan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeeLoanController extends Controller
{
    /**
     * Display the employee loan list view.
     */
    public function index()
    {
        return view('modules.payroll.employee-loan.list');
    }

    /**
     * Show the form for creating a new employee loan.
     */
    public function create()
    {
        $employees = User::where('status', 'active')->get();
        $paymentMethods = ['bank_transfer', 'cash', 'check'];
        $statuses = ['pending', 'approved', 'rejected', 'paid', 'partially_paid'];

        return view('modules.payroll.employee-loan.form', compact('employees', 'paymentMethods', 'statuses'));
    }

    /**
     * Show the form for editing the specified employee loan.
     */
    public function edit($id)
    {
        $employeeLoan = EmployeeLoan::findOrFail($id);
        $employees = User::where('status', 'active')->get();
        $paymentMethods = ['bank_transfer', 'cash', 'check'];
        $statuses = ['pending', 'approved', 'rejected', 'paid', 'partially_paid'];

        return view('modules.payroll.employee-loan.form', compact('employeeLoan', 'employees', 'paymentMethods', 'statuses'));
    }

    /**
     * Store a newly created or update an existing employee loan.
     */
    public function store(Request $request)
    {
        $numericFields = [
            'loan_amount',
            'interest_rate',
            'installment_amount',
            'remaining_amount',
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
            'loan_amount' => 'required|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0',
            'number_of_installments' => 'required|integer|min:1',
            'installment_amount' => 'required|numeric|min:0',
            'loan_date' => 'required|date',
            'first_payment_date' => 'required|date|after_or_equal:loan_date',
            'payment_method' => 'required|in:bank_transfer,cash,check',
            'remaining_amount' => 'required|numeric|min:0',
            'remaining_installments' => 'required|integer|min:0',
            'purpose' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            // If it's an update
            if ($request->has('data-id') && $request->input('data-id')) {
                $employeeLoan = EmployeeLoan::findOrFail($request->input('data-id'));
                $employeeLoan->update($data);
                $message = 'Employee loan updated successfully';
            } else {
                // Generate row_no
                $lastRecord = EmployeeLoan::latest('id')->first();
                $newId = $lastRecord ? $lastRecord->id + 1 : 1;
                $data['row_no'] = 'EL-' . str_pad($newId, 5, '0', STR_PAD_LEFT);

                // Set initial remaining values
                $data['remaining_amount'] = $request->input('loan_amount');
                $data['remaining_installments'] = $request->input('number_of_installments');
                $data['status'] = 'pending';
                $data['user_id'] = Auth::id();
                $data['company_id'] = companyId();

                $employeeLoan = EmployeeLoan::create($data);
                $message = 'Employee loan created successfully';
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
     * Fetch all employee loans for DataTables.
     */
    public function fetchAllRows(Request $request)
    {
        $query = EmployeeLoan::with('employee')->select('employee_loans.*');

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
        $employeeLoan = EmployeeLoan::select(
            'id',
            'status'
        )->withTrashed()->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];

        if ($employeeLoan->status === 'pending') {
            $contextMenu->push([
                'label' => __('Mark as Approved'),
                'code' => '01CSBK',
                'id' => 'row_approved',
                'data-id' => $employeeLoan->id,
                'data-value' => 'approved',
                'type' => 'item',
                'icon' => 'confirmed',
            ], [
                'label' => __('Mark as Rejected'),
                'code' => '01CSBK',
                'id' => 'row_rejected',
                'data-id' => $employeeLoan->id,
                'data-value' => 'rejected',
                'type' => 'item',
                'icon' => 'rejected',
                'separator' => 'after',
            ]);
        } elseif ($employeeLoan->status === 'approved') {
            $contextMenu->push([
                'label' => __('Mark as Paid'),
                'code' => '01CSBK',
                'id' => 'row_paid',
                'data-id' => $employeeLoan->id,
                'data-value' => 'paid',
                'type' => 'item',
                'icon' => 'paid',
            ], [
                'label' => __('Mark as Partially Paid'),
                'code' => '01CSBK',
                'id' => 'row_partially_paid',
                'data-id' => $employeeLoan->id,
                'data-value' => 'partially_paid',
                'type' => 'item',
                'icon' => 'partial',
                'separator' => 'after',
            ]);
        } elseif ($employeeLoan->status === 'partially_paid') {
            $contextMenu->push([
                'label' => __('Mark as Paid'),
                'code' => '01CSBK',
                'id' => 'row_paid',
                'data-id' => $employeeLoan->id,
                'data-value' => 'paid',
                'type' => 'item',
                'icon' => 'paid',
                'separator' => 'after',
            ]);
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $employeeLoan->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'EMPLOYEE_LOAN.printPreview(' . $employeeLoan->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $employeeLoan->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($employeeLoan->status === 'pending') {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $employeeLoan->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit]
            ]);
        }
        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $employeeLoan = EmployeeLoan::findOrFail($id);
        $employeeLoan->status = $status;
        $employeeLoan->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Loan status updated successfully!',
            'data' => [
                'id' => $employeeLoan->id,
                'status' => $employeeLoan->status,
            ],
        ]);
    }

    /**
     * Remove the specified employee loan from storage.
     */
    public function destroy($id)
    {
        try {
            $employeeLoan = EmployeeLoan::findOrFail($id);
            $employeeLoan->delete();

            return response()->json(['success' => true, 'message' => 'Employee loan deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified employee loan.
     */
    public function overview($id)
    {
        $employeeLoan = EmployeeLoan::with('employee')->findOrFail($id);
        return view('modules.payroll.employee-loan.view-overview', compact('employeeLoan'));
    }

    /**
     * Print
     */
    public function print($id)
    {
        // In a real implementation, you would fetch the waybill by ID
        // For now, we'll just return a simple view
        $employeeLoan = EmployeeLoan::withTrashed()->findOrFail($id);
        return view('modules.payroll.employee-loan.view-overview', compact('employeeLoan'));
    }
}
