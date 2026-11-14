<?php

namespace App\Services\Payroll;

use App\Models\Payroll\Attendance;
use App\Models\Master\Company;
use App\Models\Payroll\EmployeeLoan;
use App\Models\Payroll\Shift;
use App\Models\Payroll\SalaryStructure;
use App\Models\User;
use Illuminate\Support\Carbon;

class PayrollCalculationService
{
    /**
     * Computes one employee's payroll for a given month, in the exact shape
     * payroll_records expects (basic_salary, housing_allowance, ..., net_payable).
     * Returns null if the employee has no salary structure covering that month —
     * the caller treats that as "skipped", not an error.
     */
    public function calculate(User $employee, int $month, int $year): ?array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;

        $structure = SalaryStructure::where('employee_id', $employee->id)
            ->whereDate('effective_from', '<=', $monthEnd)
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $monthStart);
            })
            ->latest('effective_from')
            ->first();

        if (!$structure) {
            return null;
        }

        $basic = (float) $structure->basic_salary;
        $housing = (float) $structure->housing_allowance;
        $transport = (float) $structure->transportation_allowance;
        $grossSalary = $basic + $housing + $transport + (float) $structure->other_allowances;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        // A working day with no attendance record at all is an absence, not a free day.
        $unrecorded = $this->unrecordedWorkingDays($employee, $monthStart, $monthEnd, $attendance);
        $unemployedDays = $this->daysOutsideEmployment($employee, $monthStart, $monthEnd);
        $absentDays = $attendance->where('status', 'absent')->count() + $unrecorded + $unemployedDays;
        $halfDays = $attendance->where('status', 'half_day')->count();
        $overtimeHours = (float) $attendance->sum('overtime_hours');

        $perDayRate = $daysInMonth > 0 ? $grossSalary / $daysInMonth : 0;
        $lopDays = $absentDays + ($halfDays * 0.5);
        $lopDeduction = round($perDayRate * $lopDays, 2);

        $hourlyRate = $daysInMonth > 0 ? $basic / ($daysInMonth * 8) : 0;
        $overtimeAmount = round($hourlyRate * 1.5 * $overtimeHours, 2);

        $activeLoan = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->first();
        $loanDeduction = $activeLoan
            ? round(min((float) $activeLoan->installment_amount, (float) $activeLoan->remaining_amount), 2)
            : 0.0;

        $company = Company::withoutGlobalScopes()->find($employee->company_id);
        $gosiDeduction = ($company && $company->gosi_enabled)
            ? round($grossSalary * ((float) $company->gosi_employee_rate / 100), 2)
            : 0.0;

        $otherAllowances = round((float) $structure->other_allowances + $overtimeAmount, 2);
        $otherDeductions = round($lopDeduction + $gosiDeduction, 2);

        $totalEarnings = round($basic + $housing + $transport + $otherAllowances, 2);
        $totalDeductions = round($loanDeduction + $otherDeductions, 2);
        $netPayable = round($totalEarnings - $totalDeductions, 2);

        return [
            'basic_salary' => $basic,
            'housing_allowance' => $housing,
            'transportation_allowance' => $transport,
            'other_allowances' => $otherAllowances,
            'total_earnings' => $totalEarnings,
            'loan_deduction' => $loanDeduction,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_payable' => $netPayable,
            // Extra context for the preview screen, not part of payroll_records:
            'absent_days' => $absentDays,
            'unrecorded_days' => $unrecorded,
            'half_days' => $halfDays,
            'overtime_hours' => $overtimeHours,
            'lop_deduction' => $lopDeduction,
            'gosi_deduction' => $gosiDeduction,
        ];
    }

    /** Week-off weekdays (0 = Sunday) of the employee's shift; Friday when the employee has no shift. */
    private function weekOffDays(User $employee): array
    {
        $shift = $employee->shift_id ? Shift::withoutGlobalScopes()->find($employee->shift_id) : null;

        return $shift ? array_map('intval', $shift->week_off_days ?? []) : [5];
    }

    /** Last day of the month that can already be judged: today's punches may still arrive, so the current month stops at yesterday. */
    private function lastJudgedDay(Carbon $monthEnd): Carbon
    {
        return $monthEnd->copy()->min(now()->subDay()->startOfDay());
    }

    /** Employed, non-week-off days up to the last judged day that have no attendance row (not even holiday / leave). */
    private function unrecordedWorkingDays(User $employee, Carbon $monthStart, Carbon $monthEnd, $attendance): int
    {
        $from = $employee->joining_date ? $monthStart->copy()->max(Carbon::parse($employee->joining_date)->startOfDay()) : $monthStart->copy();
        $to = $this->lastJudgedDay($monthEnd);
        if ($employee->terminated_at) {
            $to = $to->min(Carbon::parse($employee->terminated_at)->startOfDay());
        }

        $recorded = $attendance->map(fn ($a) => Carbon::parse($a->date)->toDateString())->flip();
        $off = $this->weekOffDays($employee);
        $count = 0;
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            if (!in_array($d->dayOfWeek, $off, true) && !$recorded->has($d->toDateString())) {
                $count++;
            }
        }

        return $count;
    }

    /** Calendar days of the month before the joining date or after the termination date (unpaid). */
    private function daysOutsideEmployment(User $employee, Carbon $monthStart, Carbon $monthEnd): int
    {
        $days = 0;
        if ($employee->joining_date) {
            $join = Carbon::parse($employee->joining_date)->startOfDay();
            if ($join->gt($monthStart)) {
                $days += (int) $monthStart->diffInDays($join->min($monthEnd->copy()->addDay()));
            }
        }
        if ($employee->terminated_at) {
            $end = Carbon::parse($employee->terminated_at)->startOfDay();
            if ($end->lt($monthEnd)) {
                $days += (int) $end->max($monthStart->copy()->subDay())->diffInDays($monthEnd);
            }
        }

        return $days;
    }
}
