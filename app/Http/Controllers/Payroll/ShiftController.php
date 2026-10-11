<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $shifts = Shift::withCount('employees')->orderBy('name')->paginate(15);
        return view('modules.payroll.shifts.index', compact('shifts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Shift::create($data);
        return back()->with('success', __('Shift created.'));
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $data = $this->validated($request);
        $shift->update($data);
        return back()->with('success', __('Shift updated.'));
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        if ($shift->employees()->exists()) {
            return back()->withErrors(['shift' => __('Cannot delete a shift assigned to employees. Reassign them first.')]);
        }
        $shift->delete();
        return back()->with('success', __('Shift deleted.'));
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:morning,night,rotational,custom'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'grace_minutes' => ['nullable', 'integer', 'min:0', 'max:180'],
            'week_off_days' => ['nullable', 'array'],
            'week_off_days.*' => ['integer', 'min:0', 'max:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['grace_minutes'] = $data['grace_minutes'] ?? 15;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['week_off_days'] = $data['week_off_days'] ?? [];

        return $data;
    }
}
