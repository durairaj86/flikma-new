<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Attendance;
use App\Models\Payroll\Shift;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $employeeCount = User::where('is_employee', true)->count();

        $today = now()->toDateString();
        $attendanceToday = Attendance::where('date', $today)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $shiftCount = Shift::where('is_active', true)->count();

        $noShiftCount = User::where('is_employee', true)->whereNull('shift_id')->count();
        $noSalaryStructureCount = User::where('is_employee', true)
            ->whereDoesntHave('salaryStructures', function ($q) use ($today) {
                $q->whereDate('effective_from', '<=', $today)
                    ->where(function ($q2) use ($today) {
                        $q2->whereNull('effective_to')->orWhereDate('effective_to', '>=', $today);
                    });
            })
            ->count();

        return view('modules.payroll.dashboard', compact(
            'employeeCount',
            'attendanceToday',
            'shiftCount',
            'noShiftCount',
            'noSalaryStructureCount'
        ));
    }
}
