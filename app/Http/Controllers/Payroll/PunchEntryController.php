<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\AttendanceDevice;
use App\Models\Payroll\AttendancePunch;
use App\Models\Payroll\PayrollRecord;
use App\Models\User;
use App\Services\Attendance\PunchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Add, edit and delete punches by hand (machine offline, forgot to punch, wrong time …). */
class PunchEntryController extends Controller
{
    public function __construct(private PunchService $service)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $employee = User::where('is_employee', true)->whereNull('terminated_at')->findOrFail($data['employee_id']);
        $at = Carbon::createFromFormat('Y-m-d H:i', $data['date'] . ' ' . $data['time'])->startOfMinute();

        $machineId = (string) ($employee->device_user_id ?: $employee->employee_code ?: 'EMP-' . $employee->id);
        $this->guard($employee, $at, $machineId, null);

        DB::transaction(function () use ($employee, $at, $machineId, $data) {
            $device = AttendanceDevice::forManual(companyId());
            $punch = AttendancePunch::withoutGlobalScopes()->create([
                'company_id' => companyId(), 'device_id' => $device->id, 'device_user_id' => $machineId, 'employee_id' => $employee->id,
                'punched_at' => $at, 'direction' => $data['direction'] ?: null, 'status' => 'processed',
                'raw' => ['manual' => true, 'by' => auth()->id(), 'note' => $data['note'] ?? null],
            ]);
            $this->service->apply($punch, $employee);
        });

        return redirect()->route('payroll.punch-log')->with('success', __('Punch added and applied to attendance.'));
    }

    public function update(Request $request, AttendancePunch $punch): RedirectResponse
    {
        $manual = (bool) ($punch->raw['manual'] ?? false);
        if ($punch->status === 'locked') {
            throw ValidationException::withMessages(['punch' => __('This punch belongs to a month with generated payroll and cannot be edited.')]);
        }
        $data = $this->validated($request, $manual);
        $oldDate = Carbon::parse($punch->punched_at)->toDateString();
        $oldEmployeeId = $punch->employee_id;

        $employee = $manual ? User::where('is_employee', true)->whereNull('terminated_at')->findOrFail($data['employee_id'])
            : ($punch->employee_id ? User::find($punch->employee_id) : null);
        $at = Carbon::createFromFormat('Y-m-d H:i', $data['date'] . ' ' . $data['time'])->startOfMinute();

        $machineId = $manual ? (string) ($employee->device_user_id ?: $employee->employee_code ?: 'EMP-' . $employee->id) : $punch->device_user_id;
        $this->guard($employee, $at, $machineId, $punch);

        DB::transaction(function () use ($punch, $employee, $at, $machineId, $data, $manual, $oldDate, $oldEmployeeId) {
            $raw = $punch->raw ?? [];
            $raw['note'] = $data['note'] ?? null;   // the note box in the edit window (machine punches can carry a note too)
            $raw['edited_by'] = auth()->id();
            $raw['edited_at'] = now()->toDateTimeString();
            $raw['original_time'] = $raw['original_time'] ?? Carbon::parse($punch->punched_at)->toDateTimeString();
            $punch->update([
                'punched_at' => $at, 'direction' => $data['direction'] ?: null, 'device_user_id' => $machineId,
                'employee_id' => $employee?->id, 'raw' => $raw,
            ]);
            // Punches with no employee (unmapped) only change their own record; otherwise recompute the affected days.
            if ($oldEmployeeId) {
                $this->service->rebuildDay(companyId(), $oldEmployeeId, $oldDate);
            }
            if ($employee) {
                $this->service->rebuildDay(companyId(), $employee->id, $at->toDateString());
            }
        });

        return redirect()->route('payroll.punch-log')->with('success', __('Punch updated and attendance recalculated.'));
    }

    public function destroy(AttendancePunch $punch): RedirectResponse
    {
        if (!($punch->raw['manual'] ?? false)) {
            throw ValidationException::withMessages(['punch' => __('Only manually added punches can be deleted. Machine punches are a record of what the machine sent.')]);
        }
        if ($punch->employee_id && $this->locked($punch->employee_id, Carbon::parse($punch->punched_at))) {
            throw ValidationException::withMessages(['punch' => __('Payroll for that month has been generated, so the punch cannot be deleted.')]);
        }
        $employeeId = $punch->employee_id;
        $date = Carbon::parse($punch->punched_at)->toDateString();

        DB::transaction(function () use ($punch, $employeeId, $date) {
            $punch->delete();
            if ($employeeId) {
                $this->service->rebuildDay(companyId(), $employeeId, $date);
            }
        });

        return redirect()->route('payroll.punch-log')->with('success', __('Punch deleted and attendance recalculated.'));
    }

    private function validated(Request $request, bool $withEmployee): array
    {
        $data = $request->validate([
            'employee_id' => [$withEmployee ? 'required' : 'nullable', Rule::exists('users', 'id')->where('company_id', companyId())->where('is_employee', true)],
            'date' => ['required', 'string'],
            'time' => ['required', 'date_format:H:i'],
            'direction' => ['nullable', 'in:in,out'],
            'note' => ['nullable', 'string', 'max:200'],
        ], [
            'employee_id.required' => __('Select an employee.'),
            'time.required' => __('Enter the punch time.'),
            'time.date_format' => __('Time must look like 08:30 (24-hour).'),
        ]);

        $date = formDate($data['date']);
        if (!$date) {
            throw ValidationException::withMessages(['date' => __('Enter a valid date.')]);
        }
        $data['date'] = $date;
        $at = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $data['time']);
        if ($at->gt(now()->addMinutes(5))) {
            throw ValidationException::withMessages(['date' => __('A punch cannot be in the future.')]);
        }
        if ($at->lt(now()->subYears(2))) {
            throw ValidationException::withMessages(['date' => __('A punch cannot be more than 2 years old.')]);
        }

        return $data;
    }

    /** No duplicates (same employee / machine ID in the same minute) and no changes inside a payroll-locked month. */
    private function guard(?User $employee, Carbon $at, string $machineId, ?AttendancePunch $ignore): void
    {
        if ($employee && $this->locked($employee->id, $at)) {
            throw ValidationException::withMessages(['date' => __('Payroll for :m has already been generated for this employee, so attendance for that month is locked.', ['m' => $at->format('M Y')])]);
        }

        $dup = AttendancePunch::withoutGlobalScopes()->where('company_id', companyId())
            ->when($ignore, fn ($q) => $q->where('id', '!=', $ignore->id))
            ->whereBetween('punched_at', [$at->copy()->startOfMinute(), $at->copy()->endOfMinute()])
            ->where(function ($q) use ($employee, $machineId) {
                $q->where('device_user_id', $machineId);
                if ($employee) {
                    $q->orWhere('employee_id', $employee->id);
                }
            })->first();

        if ($dup) {
            throw ValidationException::withMessages(['time' => __('A punch for this employee already exists at :t (:src). Duplicate punches are not allowed.', [
                't' => Carbon::parse($dup->punched_at)->format('d-m-Y H:i'),
                'src' => ($dup->raw['manual'] ?? false) ? __('manual') : __('machine'),
            ])]);
        }
    }

    private function locked(int $employeeId, Carbon $at): bool
    {
        return PayrollRecord::withoutGlobalScopes()->where('company_id', companyId())->where('employee_id', $employeeId)
            ->where('status', '!=', 'cancelled')->where('month', (int) $at->month)->where('year', (int) $at->year)->exists();
    }
}
