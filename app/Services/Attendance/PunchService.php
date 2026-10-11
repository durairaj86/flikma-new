<?php

namespace App\Services\Attendance;

use App\Models\Payroll\Attendance;
use App\Models\Payroll\AttendanceDevice;
use App\Models\Payroll\AttendancePunch;
use App\Models\Payroll\PayrollRecord;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Turns raw punches from any biometric / card / face machine into Attendance rows.
 *
 * Every punch is kept in attendance_punches (audit trail + re-processing), then folded into the employee's
 * daily Attendance record: earliest punch = check-in, latest punch = check-out. Manual edits are never
 * overwritten — a punch can only move check-in earlier or check-out later.
 */
class PunchService
{
    /** Two punches closer than this are treated as the same tap (double-scan). */
    private const DEBOUNCE_SECONDS = 60;

    /**
     * @param  array<int, array{device_user_id?: string, employee_id?: int, punched_at: Carbon, direction?: ?string, raw?: array}>  $punches
     * @return array{received:int, processed:int, unmapped:int, duplicate:int, locked:int}
     */
    public function ingest(AttendanceDevice $device, array $punches): array
    {
        $result = ['received' => 0, 'processed' => 0, 'unmapped' => 0, 'duplicate' => 0, 'locked' => 0];

        foreach ($punches as $p) {
            $result['received']++;

            $employee = null;
            if (!empty($p['employee_id'])) {
                $employee = User::withoutGlobalScopes()->where('company_id', $device->company_id)
                    ->where('is_employee', true)->find($p['employee_id']);
            }
            $deviceUserId = (string) ($p['device_user_id'] ?? ($employee?->device_user_id ?? $employee?->employee_code ?? $employee?->id ?? ''));
            if ($deviceUserId === '') {
                $result['unmapped']++;
                continue;
            }
            $employee ??= $this->resolveEmployee($device->company_id, $deviceUserId);

            $punch = AttendancePunch::withoutGlobalScopes()->firstOrNew([
                'company_id' => $device->company_id,
                'device_user_id' => $deviceUserId,
                'punched_at' => $p['punched_at'],
            ]);
            if ($punch->exists && $punch->status !== 'unmapped') {
                $result['duplicate']++;
                continue;
            }

            $punch->fill([
                'device_id' => $device->id,
                'employee_id' => $employee?->id,
                'direction' => $p['direction'] ?? null,
                'raw' => $p['raw'] ?? null,
            ]);
            $punch->status = $employee ? $this->apply($punch, $employee) : 'unmapped';
            $punch->save();

            $result[$punch->status === 'processed' ? 'processed' : $punch->status]++;
        }

        if ($result['received'] > 0) {
            $device->forceFill(['last_punch_at' => now()])->save();
        }

        return $result;
    }

    /** Employee lookup by machine PIN: explicit device id → employee code → numeric part of the code ("EMP-0007" ≙ 7). */
    public function resolveEmployee(int $companyId, string $deviceUserId): ?User
    {
        $base = User::withoutGlobalScopes()->where('company_id', $companyId)->where('is_employee', true)->whereNull('terminated_at');

        $hit = (clone $base)->where('device_user_id', $deviceUserId)->first()
            ?? (clone $base)->where('employee_code', $deviceUserId)->first();
        if ($hit) {
            return $hit;
        }

        if (ctype_digit($deviceUserId)) {
            $n = (int) $deviceUserId;
            return (clone $base)->whereNull('device_user_id')->whereNotNull('employee_code')->get(['id', 'employee_code', 'company_id'])
                ->first(fn ($u) => preg_match('/(\d+)$/', $u->employee_code, $m) && (int) $m[1] === $n);
        }

        return null;
    }

    /** Folds one punch into the day's Attendance. Returns the punch status. */
    public function apply(AttendancePunch $punch, User $employee): string
    {
        $at = Carbon::parse($punch->punched_at);

        $locked = PayrollRecord::withoutGlobalScopes()->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)->where('status', '!=', 'cancelled')
            ->where('month', (int) $at->month)->where('year', (int) $at->year)->exists();
        if ($locked) {
            return 'locked';
        }

        $att = Attendance::withoutGlobalScopes()->firstOrNew([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'date' => $at->toDateString(),
        ]);

        $t = $at->format('H:i:s');
        $in = $att->check_in ? $this->hms($att->check_in) : null;
        $out = $att->check_out ? $this->hms($att->check_out) : null;

        if ($punch->direction === 'in') {
            $in = $in === null ? $t : min($in, $t);
        } elseif ($punch->direction === 'out') {
            $out = $out === null ? $t : max($out, $t);
        } elseif ($in === null) {
            $in = $t;
        } elseif ($t < $in) {
            $out = $out === null ? $in : max($out, $in);
            $in = $t;
        } elseif ($this->secondsBetween($in, $t) >= self::DEBOUNCE_SECONDS) {
            $out = $out === null ? $t : max($out, $t);
        }

        $att->check_in = $in;
        $att->check_out = $out;
        if (!$att->status || $att->status === 'absent') {
            $att->status = 'present';
        }
        if (!$att->exists) {
            $att->overtime_hours = 0;
        }
        $att->remarks = $att->remarks ?: __('Biometric punch');
        $att->save();

        return 'processed';
    }

    /** Re-run punches that could not be matched (call after mapping an employee). */
    public function reprocessUnmapped(int $companyId): int
    {
        $done = 0;
        AttendancePunch::withoutGlobalScopes()->where('company_id', $companyId)->where('status', 'unmapped')
            ->orderBy('punched_at')->get()->each(function (AttendancePunch $punch) use ($companyId, &$done) {
                $employee = $this->resolveEmployee($companyId, $punch->device_user_id);
                if (!$employee) {
                    return;
                }
                $punch->employee_id = $employee->id;
                $punch->status = $this->apply($punch, $employee);
                $punch->save();
                $done++;
            });

        return $done;
    }

    /**
     * Recalculate one employee's check-in / check-out for a day from the punches that remain
     * (used after a punch is edited or deleted, where the "can only extend" rule of apply() would keep stale times).
     */
    public function rebuildDay(int $companyId, int $employeeId, string $date): void
    {
        $att = Attendance::withoutGlobalScopes()->where('company_id', $companyId)->where('employee_id', $employeeId)->where('date', $date)->first();
        $punches = AttendancePunch::withoutGlobalScopes()->where('company_id', $companyId)->where('employee_id', $employeeId)
            ->where('status', 'processed')->whereDate('punched_at', $date)->orderBy('punched_at')->get();

        if ($punches->isEmpty()) {
            if ($att) {
                $att->update(['check_in' => null, 'check_out' => null]);
            }
            return;
        }

        $in = $out = null;
        foreach ($punches as $p) {
            $t = Carbon::parse($p->punched_at)->format('H:i:s');
            if ($p->direction === 'in') {
                $in = $in === null ? $t : min($in, $t);
            } elseif ($p->direction === 'out') {
                $out = $out === null ? $t : max($out, $t);
            }
        }
        $plain = $punches->whereNull('direction')->map(fn ($p) => Carbon::parse($p->punched_at)->format('H:i:s'))->values();
        if ($plain->isNotEmpty()) {
            $first = $plain->first();
            $last = $plain->last();
            $in = $in === null ? $first : min($in, $first);
            if ($this->secondsBetween($first, $last) >= self::DEBOUNCE_SECONDS) {
                $out = $out === null ? $last : max($out, $last);
            }
        }

        $att ??= new Attendance(['company_id' => $companyId, 'employee_id' => $employeeId, 'date' => $date, 'status' => 'present', 'overtime_hours' => 0]);
        $att->company_id = $companyId;
        $att->check_in = $in;
        $att->check_out = $out;
        if (!$att->status || $att->status === 'absent') {
            $att->status = 'present';
        }
        $att->save();
    }

    private function hms(string $time): string
    {
        return strlen($time) === 5 ? $time . ':00' : substr($time, 0, 8);
    }

    private function secondsBetween(string $a, string $b): int
    {
        return abs(strtotime("1970-01-01 $b") - strtotime("1970-01-01 $a"));
    }
}
