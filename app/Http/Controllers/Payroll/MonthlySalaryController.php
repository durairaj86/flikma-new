<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\BasicSalary;
use App\Models\Payroll\MonthlySalary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MonthlySalaryController extends Controller
{
    /**
     * Display the monthly salary list view.
     */
    public function index()
    {
        return view('modules.payroll.monthly-salary.list');
    }

    /**
     * Show the form for creating a new monthly salary.
     */
    public function create()
    {
        $employees = User::all();
        $months = $this->getMonthsList();
        $years = $this->getYearsList();
        $paymentMethods = ['bank_transfer', 'cash', 'check'];

        $date = Carbon::today()->subMonth(); // Or previousMonth()
        $salaryMonth = $date->month; // Result: 12
        $salaryYear = $date->year;   // Result: 2025

        return view('modules.payroll.monthly-salary.form', compact('employees', 'months', 'years', 'paymentMethods', 'salaryMonth', 'salaryYear'));
    }

    /**
     * Show the form for editing the specified monthly salary.
     */
    public function edit($id)
    {
        $monthlySalary = MonthlySalary::findOrFail($id);
        $employees = User::all();
        $months = $this->getMonthsList();
        $years = $this->getYearsList();
        $paymentMethods = ['bank_transfer', 'cash', 'check'];

        $salaryMonth = $monthlySalary->month;
        $salaryYear = $monthlySalary->year;

        return view('modules.payroll.monthly-salary.form', compact('monthlySalary', 'employees', 'months', 'years', 'paymentMethods', 'salaryMonth', 'salaryYear'));
    }

    /**
     * Store a newly created or update an existing monthly salary.
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
            'overtime_amount',
            'bonus',
            'deductions',
            'loan_deduction'
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
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000',
            'basic_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transportation_allowance' => 'nullable|numeric|min:0',
            'food_allowance' => 'nullable|numeric|min:0',
            'phone_allowance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_amount' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'total_salary' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,check',
            'status' => 'required|in:pending,paid,cancelled',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            // Calculate total salary if not provided
            if (!$request->has('total_salary') || !$request->input('total_salary')) {
                $data['total_salary'] = $request->input('basic_salary') +
                    ($request->input('housing_allowance') ?? 0) +
                    ($request->input('transportation_allowance') ?? 0) +
                    ($request->input('food_allowance') ?? 0) +
                    ($request->input('phone_allowance') ?? 0) +
                    ($request->input('other_allowance') ?? 0) +
                    ($request->input('overtime_amount') ?? 0) +
                    ($request->input('bonus') ?? 0) -
                    ($request->input('deductions') ?? 0) -
                    ($request->input('loan_deduction') ?? 0);
            }

            // If it's an update
            if ($request->has('data-id') && $request->input('data-id')) {
                $monthlySalary = MonthlySalary::findOrFail($request->input('data-id'));
                $monthlySalary->update($data);
                $message = 'Monthly salary updated successfully';
            } else {
                // Check if a record already exists for this employee, month, and year
                $existingRecord = MonthlySalary::where('employee_id', $request->input('employee_id'))
                    ->where('month', $request->input('month'))
                    ->where('year', $request->input('year'))
                    ->first();

                if ($existingRecord) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A salary record already exists for this employee in the selected month and year'
                    ], 422);
                }

                // Generate row_no
                $lastRecord = MonthlySalary::latest('id')->first();
                $newId = $lastRecord ? $lastRecord->id + 1 : 1;
                $data['row_no'] = 'MS-' . str_pad($newId, 5, '0', STR_PAD_LEFT);

                $data['user_id'] = Auth::id();
                $data['company_id'] = companyId();

                $monthlySalary = MonthlySalary::create($data);
                $message = 'Monthly salary created successfully';
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
     * Fetch all monthly salaries for DataTables.
     */
    public function fetchAllRows(Request $request)
    {
        $query = MonthlySalary::with('employee')->select('monthly_salaries.*');

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
            ->addColumn('month_year', function ($row) {
                $monthName = date('F', mktime(0, 0, 0, $row->month, 10));
                return $monthName . ' ' . $row->year;
            })
            ->addColumn('actions', function ($row) {
                return $this->actions($row->id);
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Remove the specified monthly salary from storage.
     */
    public function destroy($id)
    {
        try {
            $monthlySalary = MonthlySalary::findOrFail($id);
            $monthlySalary->delete();

            return response()->json(['success' => true, 'message' => 'Monthly salary deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get employee's basic salary details.
     */
    public function getEmployeeBasicSalary(Request $request)
    {
        $employeeId = $request->input('employee_id');

        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Employee ID is required'], 422);
        }

        $basicSalary = BasicSalary::where('employee_id', $employeeId)
            ->where('status', 'confirmed')
            ->orderBy('effective_date', 'desc')
            ->first();

        if (!$basicSalary) {
            return response()->json(['success' => false, 'message' => 'No basic salary record found for this employee'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Employee basic salary fetched successfully',
            'data' => $basicSalary
        ]);
    }

    /**
     * Get list of months for dropdown.
     */
    private function getMonthsList()
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
    }

    /**
     * Get list of years for dropdown.
     */
    private function getYearsList()
    {
        $currentYear = date('Y');
        $years = [];

        for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
            $years[$i] = $i;
        }

        return $years;
    }

    public function actions($id)
    {
        $monthlySalary = MonthlySalary::select(
            'id',
            'status'
        )->withTrashed()->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];

        if ($monthlySalary->status === 'pending') {
            $contextMenu->push([
                'label' => __('Mark as Paid'),
                'code' => '01CSBK',
                'id' => 'row_paid',
                'data-id' => $monthlySalary->id,
                'data-value' => 'paid',
                'type' => 'item',
                'icon' => 'confirmed',
            ], [
                'label' => __('Mark as Cancelled'),
                'code' => '01CSBK',
                'id' => 'row_cancelled',
                'data-id' => $monthlySalary->id,
                'data-value' => 'cancelled',
                'type' => 'item',
                'icon' => 'cancelled',
                'separator' => 'after',
            ]);
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $monthlySalary->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'MONTHLY_SALARY.printPreview(' . $monthlySalary->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $monthlySalary->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($monthlySalary->status === 'pending') {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $monthlySalary->id,
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
        $monthlySalary = MonthlySalary::findOrFail($id);
        $monthlySalary->status = $status;
        $monthlySalary->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Monthly salary status updated successfully!',
            'data' => [
                'id' => $monthlySalary->id,
                'status' => $monthlySalary->status,
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
        $monthlySalary = MonthlySalary::withTrashed()->findOrFail($id);
        return view('modules.payroll.monthly-salary.view-overview')->with('monthlySalary', $monthlySalary);
    }

    /**
     * Print the waybill
     */
    public function print($id)
    {
        // In a real implementation, you would fetch the waybill by ID
        // For now, we'll just return a simple view
        $monthlySalary = MonthlySalary::withTrashed()->findOrFail($id);
        return view('modules.payroll.monthly-salary.view-overview')->with('monthlySalary', $monthlySalary);
    }
}
