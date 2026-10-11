<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\SalaryStructure;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalaryStructureController extends Controller
{
    public function index(): View
    {
        $structures = SalaryStructure::with('employee:id,name,department')
            ->orderByDesc('effective_from')
            ->paginate(15);

        $employees = User::where('is_employee', true)->orderBy('name')->get(['id', 'name', 'department']);

        return view('modules.payroll.salary-structures.index', compact('structures', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            // Close out any currently-open structure for this employee so history is preserved.
            SalaryStructure::where('employee_id', $data['employee_id'])
                ->whereNull('effective_to')
                ->update(['effective_to' => Carbon::parse($data['effective_from'])->subDay()]);

            SalaryStructure::create($data);
        });

        return back()->with('success', __('Salary structure created.'));
    }

    public function update(Request $request, SalaryStructure $salary_structure): RedirectResponse
    {
        // The edit popup has no employee picker (a structure never changes hands); keep the existing one.
        $request->merge(['employee_id' => $salary_structure->employee_id]);
        $data = $this->validated($request, $salary_structure->id);
        $salary_structure->update($data);
        return back()->with('success', __('Salary structure updated.'));
    }

    public function destroy(SalaryStructure $salary_structure): RedirectResponse
    {
        if ($salary_structure->usedByPayroll()) {
            return back()->withErrors(['salary_structure' => __('This salary structure cannot be deleted because payroll has already been generated from it. Close it with an end date and add a new structure instead.')]);
        }
        $salary_structure->delete();
        return back()->with('success', __('Salary structure deleted.'));
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'housing_allowance' => ['nullable', 'numeric', 'min:0'],
            'transportation_allowance' => ['nullable', 'numeric', 'min:0'],
            'other_allowances' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
        ]);

        $data['housing_allowance'] = $data['housing_allowance'] ?? 0;
        $data['transportation_allowance'] = $data['transportation_allowance'] ?? 0;
        $data['other_allowances'] = $data['other_allowances'] ?? 0;
        $data['total_salary'] = $data['basic_salary'] + $data['housing_allowance']
            + $data['transportation_allowance'] + $data['other_allowances'];

        return $data;
    }
}
