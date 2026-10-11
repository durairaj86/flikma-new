<?php

namespace App\Services\Payroll;

use App\Models\Payroll\Attendance;
use App\Models\Master\Company;
use App\Models\Payroll\EmployeeLoan;
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

        $absentDays = $attendance->where('status', 'absent')->count();
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
            'half_days' => $halfDays,
            'overtime_hours' => $overtimeHours,
            'lop_deduction' => $lopDeduction,
            'gosi_deduction' => $gosiDeduction,
        ];
    }
}
