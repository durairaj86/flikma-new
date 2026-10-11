<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Shift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeProfileController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $employees = User::where('is_employee', true)
            ->with('shift:id,name')
            ->withCount(['salaryStructures as has_current_salary' => function ($q) use ($today) {
                $q->whereDate('effective_from', '<=', $today)
                    ->where(function ($q2) use ($today) {
                        $q2->whereNull('effective_to')->orWhereDate('effective_to', '>=', $today);
                    });
            }])
            ->orderBy('name')
            ->paginate(15);

        return view('modules.payroll.employees.index', compact('employees'));
    }

    public function show(User $user): View
    {
        abort_unless($user->is_employee, 404);

        $user->load('shift:id,name', 'reportingManager:id,name');
        $current = $user->currentSalaryStructure();
        $structures = $user->salaryStructures()->orderByDesc('effective_from')->limit(5)->get();
        $records = $user->payrollRecords()->orderByDesc('year')->orderByDesc('month')->limit(6)->get();
        $loans = $user->employeeLoans()->orderByDesc('start_date')->limit(5)->get();

        return view('modules.payroll.employees.show', compact('user', 'current', 'structures', 'records', 'loans'));
    }

    public function edit(User $user): View
    {
        $shifts = Shift::where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);
        $managers = User::where('is_employee', true)->where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('modules.payroll.employees.edit', compact('user', 'shifts', 'managers'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'employment_type' => ['required', 'in:full_time,part_time,contract,hourly'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'reporting_manager_id' => ['nullable', 'exists:users,id', 'different:id'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'iban' => ['nullable', 'string', 'max:50'],
            'gosi_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        if (($data['reporting_manager_id'] ?? null) == $user->id) {
            return back()->withErrors(['reporting_manager_id' => __('An employee cannot report to themself.')]);
        }

        $user->update($data);

        return redirect()->route('payroll.employees.index')->with('success', __('Payroll profile updated.'));
    }
}
