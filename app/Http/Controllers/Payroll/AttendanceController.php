<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    /**
     * Display the attendance list view with calendar.
     */
    public function index()
    {
        $month = request('month', date('m'));
        $year = request('year', date('Y'));
        $employees = User::all()->pluck('name', 'id');
        $months = $this->getMonthsList();
        $years = $this->getYearsList();

        return view('modules.payroll.attendance.list', compact('month', 'year', 'employees', 'months', 'years'));
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create()
    {
        $employees = User::all()->pluck('name', 'id');
        $statusOptions = Attendance::getStatusOptions();

        return view('modules.payroll.attendance.form', compact('employees', 'statusOptions'));
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        $employees = User::all()->pluck('name', 'id');
        $statusOptions = Attendance::getStatusOptions();

        return view('modules.payroll.attendance.form', compact('attendance', 'employees', 'statusOptions'));
    }

    /**
     * Store a newly created or update an existing attendance record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,half-day,leave',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            // If it's an update
            if ($request->has('data-id') && $request->input('data-id')) {
                $attendance = Attendance::findOrFail($request->input('data-id'));
                $attendance->update($data);
                $message = 'Attendance record updated successfully';
            } else {
                // Check if a record already exists for this employee on this date
                $existingRecord = Attendance::where('employee_id', $request->input('employee_id'))
                                          ->where('date', Carbon::parse($request->input('date'))->format('Y-m-d'))
                                          ->first();

                if ($existingRecord) {
                    return response()->json([
                        'success' => false,
                        'message' => 'An attendance record already exists for this employee on the selected date'
                    ], 422);
                }

                $data['user_id'] = auth()->id();
                $data['company_id'] = companyId();

                $attendance = Attendance::create($data);
                $message = 'Attendance record created successfully';
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => $message,
                //'redirect' => '/bl/waybill'
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
     * Fetch all attendance records for DataTables.
     */
    public function fetchAllRows(Request $request)
    {
        $query = Attendance::with('employee')->select('attendances.*');

        // Apply filters if provided
        if ($request->has('month') && $request->input('month')) {
            $query->whereMonth('date', $request->input('month'));
        }

        if ($request->has('year') && $request->input('year')) {
            $query->whereYear('date', $request->input('year'));
        }

        if ($request->has('employee_id') && $request->input('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        return DataTables::of($query)
            ->addColumn('employee_name', function ($row) {
                return $row->employee ? $row->employee->name : 'N/A';
            })
            ->addColumn('formatted_date', function ($row) {
                return Carbon::parse($row->date)->format('d M Y');
            })
            ->addColumn('day_of_week', function ($row) {
                return Carbon::parse($row->date)->format('l');
            })
            ->addColumn('actions', function ($row) {
                return $this->actions($row->id);
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Get monthly calendar data with attendance records.
     */
    public function getMonthlyCalendar(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $employeeId = $request->input('employee_id');

        // Get all days in the month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $calendarData = [];
        $currentDate = $startDate->copy();

        // Get all attendance records for the month and employee
        $attendanceRecords = Attendance::whereMonth('date', $month)
                                      ->whereYear('date', $year);

        if ($employeeId) {
            $attendanceRecords = $attendanceRecords->where('employee_id', $employeeId);
        }

        $attendanceRecords = $attendanceRecords->get()->keyBy(function($item) {
            return $item->employee_id . '-' . $item->date;
        });

        // If no specific employee is selected, get all active employees
        $employees = $employeeId
            ? User::where('id', $employeeId)->where('status', 'active')->get()
            : User::where('status', 'active')->get();

        // Build calendar data for each employee
        foreach ($employees as $employee) {
            $employeeData = [
                'id' => $employee->id,
                'name' => $employee->name,
                'days' => []
            ];

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $date = $currentDate->format('Y-m-d');
                $key = $employee->id . '-' . $date;

                $attendance = $attendanceRecords->get($key);

                $employeeData['days'][] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day' => $currentDate->format('d'),
                    'day_of_week' => $currentDate->format('D'),
                    'is_weekend' => $currentDate->isWeekend(),
                    'attendance_id' => $attendance ? $attendance->id : null,
                    'status' => $attendance ? $attendance->status : null,
                    'check_in' => $attendance ? $attendance->check_in : null,
                    'check_out' => $attendance ? $attendance->check_out : null,
                ];

                $currentDate->addDay();
            }

            $calendarData[] = $employeeData;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'calendar' => $calendarData,
                'month_name' => Carbon::createFromDate($year, $month, 1)->format('F'),
                'year' => $year,
                'start_day_of_week' => $startDate->dayOfWeek,
                'days_in_month' => $endDate->day,
            ]
        ]);
    }

    /**
     * Remove the specified attendance record from storage.
     */
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return response()->json(['success' => true, 'message' => 'Attendance record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate action buttons for DataTables.
     */
    private function actions($id)
    {
        $editBtn = '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit-record" data-id="' . $id . '">
                        <i class="bi bi-pencil-square"></i>
                    </a>';

        $deleteBtn = '<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-record" data-id="' . $id . '">
                        <i class="bi bi-trash"></i>
                      </a>';

        return '<div class="d-flex gap-2">' . $editBtn . $deleteBtn . '</div>';
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
}
