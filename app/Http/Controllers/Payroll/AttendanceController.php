<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        // Accepts either ISO (calendar links) or d-m-Y (flatpickr-submitted filter form) input.
        $date = Carbon::parse($request->query('date', now()->toDateString()))->toDateString();
        $viewMode = $request->query('view') === 'monthly' ? 'monthly' : 'daily';
        $employeeId = $request->query('employee_id');

        $query = Attendance::query()
            ->with('employee:id,name,department,designation')
            ->whereDate('date', $date)
            ->latest('id');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $records = $query->paginate(15);

        $employees = User::where('is_employee', true)->orderBy('name')->get(['id', 'name', 'department']);

        $month = Carbon::parse($date)->startOfMonth();
        $monthSummary = Attendance::query()
            ->whereBetween('date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->selectRaw('date, status, count(*) as total')
            ->groupBy('date', 'status')
            ->get()
            ->groupBy(fn ($row) => $row->date->format('Y-m-d'))
            ->map(fn ($rows) => $rows->pluck('total', 'status'));

        // "Records for :date" as a full roster — every employee, marked present/absent/
        // not-marked for the selected day, rather than only the employees who happen to
        // already have a row for it.
        $dailyRecordsByEmployee = Attendance::where('date', $date)
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->get()
            ->keyBy('employee_id');

        $dailyRows = $employees
            ->when($employeeId, fn ($col) => $col->where('id', $employeeId))
            ->map(fn ($emp) => [
                'employee' => $emp,
                'record' => $dailyRecordsByEmployee->get($emp->id),
            ])
            ->values();

        // Monthly roll-up — per-employee present/absent/leave/holiday day counts for the
        // selected month, used to drive the "how many days present/absent" summary and
        // the per-employee day-by-day drill-down (see employeeMonth()).
        $employeeMonthRows = collect();
        if ($viewMode === 'monthly') {
            $monthCounts = Attendance::query()
                ->whereBetween('date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
                ->selectRaw('employee_id, status, count(*) as total')
                ->groupBy('employee_id', 'status')
                ->get()
                ->groupBy('employee_id')
                ->map(fn ($rows) => $rows->pluck('total', 'status'));

            $employeeMonthRows = $employees
                ->when($employeeId, fn ($col) => $col->where('id', $employeeId))
                ->map(function ($emp) use ($monthCounts) {
                    $counts = $monthCounts->get($emp->id, collect());

                    return [
                        'employee' => $emp,
                        'present' => $counts->get('present', 0),
                        'absent' => $counts->get('absent', 0),
                        'leave' => $counts->get('leave', 0) + $counts->get('half_day', 0),
                        'holiday' => $counts->get('holiday', 0),
                    ];
                })
                ->values();
        }

        if ($request->ajax()) {
            return response()->json([
                'data' => collect($records->items())->map(function (Attendance $a) {
                    return [
                        'id' => $a->id,
                        'date' => $a->date->format('Y-m-d'),
                        'status' => $a->status,
                        'check_in' => $a->check_in,
                        'check_out' => $a->check_out,
                        'overtime_hours' => (float) $a->overtime_hours,
                        'remarks' => $a->remarks,
                        'employee' => $a->employee ? [
                            'id' => $a->employee->id,
                            'name' => $a->employee->name,
                            'department' => $a->employee->department,
                        ] : null,
                    ];
                }),
                'pagination' => $records->links('pagination::bootstrap-5')->render(),
                'counter' => "Showing {$records->firstItem()} to {$records->lastItem()} of {$records->total()} results",
            ])->header('Vary', 'X-Requested-With');
        }

        $dateDisplay = Carbon::parse($date)->format('d-m-Y');

        return view('modules.payroll.attendance.index', compact(
            'records', 'employees', 'date', 'dateDisplay', 'month', 'monthSummary',
            'viewMode', 'dailyRows', 'employeeMonthRows'
        ));
    }

    /**
     * Every day of the given month for one employee, with their attendance record
     * merged in where one exists — powers the "click an employee to see day-by-day"
     * drill-down in the Monthly view, so it can render one editable row per day
     * regardless of whether that day already has a record.
     */
    public function employeeMonth(Request $request, User $employee): JsonResponse
    {
        $month = Carbon::parse($request->query('month', now()->toDateString()))->startOfMonth();

        $records = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $a) => $a->date->toDateString());

        $days = [];
        $cursor = $month->copy();
        while ($cursor->month === $month->month) {
            $dStr = $cursor->toDateString();
            $record = $records->get($dStr);
            $days[] = [
                'date' => $dStr,
                'label' => $cursor->format('D, d M'),
                'status' => $record->status ?? null,
                'check_in' => $record && $record->check_in ? substr($record->check_in, 0, 5) : null,
                'check_out' => $record && $record->check_out ? substr($record->check_out, 0, 5) : null,
                'overtime_hours' => $record ? (float) $record->overtime_hours : 0,
                'remarks' => $record->remarks ?? null,
            ];
            $cursor->addDay();
        }

        return response()->json([
            'employee' => ['id' => $employee->id, 'name' => $employee->name],
            'days' => $days,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,leave,half_day,holiday'],
            'check_in' => ['nullable', 'date_format:H:i,H:i:s'],
            'check_out' => ['nullable', 'date_format:H:i,H:i:s'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'employee_id.required' => __('Please select an employee.'),
            'date.required' => __('Please select a date.'),
            'status.required' => __('Please select a status.'),
        ]);

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => Carbon::parse($data['date'])->toDateString()],
            [
                'status' => $data['status'],
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'overtime_hours' => $data['overtime_hours'] ?? 0,
                'remarks' => $data['remarks'] ?? null,
            ]
        );

        // The per-day inline editor in the Monthly view's employee drill-down saves via
        // fetch() and needs a JSON result back rather than a redirect + reloaded page.
        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Attendance saved.'),
                'attendance' => [
                    'id' => $attendance->id,
                    'date' => $attendance->date->toDateString(),
                    'status' => $attendance->status,
                    'check_in' => $attendance->check_in ? substr($attendance->check_in, 0, 5) : null,
                    'check_out' => $attendance->check_out ? substr($attendance->check_out, 0, 5) : null,
                    'overtime_hours' => (float) $attendance->overtime_hours,
                ],
            ]);
        }

        return redirect($this->previousUrlWithoutOpenParam())->with('success', __('Attendance saved.'));
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:present,absent,leave,half_day,holiday'],
            'check_in' => ['nullable', 'date_format:H:i,H:i:s'],
            'check_out' => ['nullable', 'date_format:H:i,H:i:s'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => __('Please select a status.'),
        ]);

        $attendance->update([
            'status' => $data['status'],
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            'overtime_hours' => $data['overtime_hours'] ?? 0,
            'remarks' => $data['remarks'] ?? null,
        ]);

        return redirect($this->previousUrlWithoutOpenParam())->with('success', __('Attendance updated.'));
    }

    /**
     * The "New Attendance Record" modal auto-opens whenever ?open=create is present
     * (see payroll/attendance/index.blade.php), which is also how the calendar day
     * links launch it. A plain back() after a successful save would redirect right
     * back to that same ?open=create URL and pop the modal open again, so strip the
     * param here instead of leaving it to the referrer.
     */
    private function previousUrlWithoutOpenParam(): string
    {
        $previous = url()->previous();
        $parts = parse_url($previous);
        parse_str($parts['query'] ?? '', $query);
        unset($query['open']);

        $url = $parts['path'] ?? '/';
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    public function checkIn(Request $request): JsonResponse
    {
        $employeeId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::firstOrNew(['employee_id' => $employeeId, 'date' => $today]);
        if ($attendance->exists && $attendance->check_in) {
            return response()->json(['status' => 'error', 'message' => __('You have already checked in today.')], 422);
        }

        $attendance->status = 'present';
        $attendance->check_in = now()->format('H:i:s');
        $attendance->save();

        return response()->json(['status' => 'success', 'message' => __('Checked in at :time', ['time' => now()->format('h:i A')])]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $employeeId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employeeId)->where('date', $today)->first();
        if (!$attendance || !$attendance->check_in) {
            return response()->json(['status' => 'error', 'message' => __('You must check in before checking out.')], 422);
        }
        if ($attendance->check_out) {
            return response()->json(['status' => 'error', 'message' => __('You have already checked out today.')], 422);
        }

        $attendance->check_out = now()->format('H:i:s');
        $attendance->save();

        return response()->json(['status' => 'success', 'message' => __('Checked out at :time', ['time' => now()->format('h:i A')])]);
    }

    /**
     * Biometric-device-ready punch endpoint. Authenticated via a shared secret
     * header rather than session auth, since a physical device cannot log in.
     * No physical device is available to test against in this environment —
     * this endpoint is interface-complete and ready to wire to a real device.
     */
    public function punch(Request $request): JsonResponse
    {
        $token = (string) config('services.biometric.token');
        if ($token === '' || !hash_equals($token, (string) $request->header('X-Device-Token'))) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:in,out'],
            'timestamp' => ['nullable', 'date'],
        ]);

        $timestamp = isset($data['timestamp']) ? \Illuminate\Support\Carbon::parse($data['timestamp']) : now();
        $date = $timestamp->toDateString();

        // No session here (a machine called us): take the company from the employee.
        $employee = User::withoutGlobalScopes()->where('is_employee', true)->findOrFail($data['employee_id']);
        $attendance = Attendance::withoutGlobalScopes()->firstOrNew(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'date' => $date]);
        $attendance->status = $attendance->status ?: 'present';

        if ($data['type'] === 'in') {
            $attendance->check_in = $attendance->check_in ?: $timestamp->format('H:i:s');
        } else {
            $attendance->check_out = $timestamp->format('H:i:s');
        }

        $attendance->save();

        return response()->json(['status' => 'success', 'attendance_id' => $attendance->id]);
    }
}
