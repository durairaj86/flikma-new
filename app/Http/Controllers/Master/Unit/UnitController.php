<?php

namespace App\Http\Controllers\Master\Unit;

use App\Http\Controllers\Controller;
use App\Models\Master\Unit;
use Illuminate\Http\Request;
use App\Support\CompanyDefaults;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Mockery\Exception;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    private static $cache = 'units:';

    public function fetchAllRows()
    {
        $rows = Unit::select('id', 'unit_name', 'unit_symbol', 'status', 'company_id', 'created_at');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->name,
                'class' => 'row-item',
                'id' => fn($model) => 'unit-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('created_at', function ($row) {
                return showDate($row->created_at);
            })
            // on / off as this company sees it: a shared row it has switched off for itself counts as off
            ->editColumn('status', function ($row) {
                return $this->isOn($row) ? __('Active') : __('In-Active');
            })
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $unit = new Unit();

        return view('modules.master.unit.unit-form', [
            'unit' => $unit,
        ]);
    }

    public function edit($id)
    {
        $unit = Unit::find($id);
        return view('modules.master.unit.unit-form')->with('unit', $unit);
    }

    public function store(Request $request)
    {
        // 1. Determine the ID being updated (for the 'ignore' rule)
        $unitId = $request->input('data-id');
        $unitSymbol = $request->input('unit_symbol');

        // 2. Get the company ID context
        // NOTE: Replace this with your actual logic to get the company ID
        // If the unit should be global, this should be null.
        $currentCompanyId = companyId();

        // 3. Define the conditional unique rules
        $rules = [
            'unit_name' => [
                'required',
                'string',
                'max:128',
                // Check for uniqueness across global units (company_id IS NULL)
                // AND check for uniqueness across the current company's units
                Rule::unique('units')->where(function ($query) use ($currentCompanyId, $unitId) {
                    // The unit name must be unique where the company_id is EITHER NULL OR the current company ID.
                    $query->whereNull('company_id')
                        ->orWhere('company_id', $currentCompanyId);

                    // When updating, ignore the current unit's ID
                    if ($unitId) {
                        $query->where('id', '!=', $unitId);
                    }
                })
            ],
            'unit_symbol' => [
                'nullable', // Assuming symbol is optional
                'string',
                'max:50',
                // Apply the same conditional unique check for the symbol
                Rule::unique('units')->where(function ($query) use ($currentCompanyId, $unitId, $unitSymbol) {
                    $query->whereNull('company_id')
                        ->orWhere('company_id', $currentCompanyId);

                    // When updating, ignore the current unit's ID
                    if ($unitId) {
                        $query->where('id', '!=', $unitId);
                    }

                    // Important: Don't check for uniqueness if the symbol is empty/null in the request
                    if (empty($unitSymbol)) {
                        $query->whereRaw('1 = 0'); // Force an impossible condition to bypass the check
                    }
                })
            ],
        ];

        // Validate request
        $validated = $request->validate($rules, [
            'unit_name.unique' => 'The unit name is already in use by a global unit or a unit in your company.',
            'unit_symbol.unique' => 'The unit symbol is already in use by a global unit or a unit in your company.',
        ]);

        // Save unit logic
        if ($unitId && Unit::where('id', $unitId)->exists()) {
            $unit = Unit::findOrFail($unitId);
        } else {
            $unit = new Unit();
            $unit->status = 1;
            $this->setBaseColumns($unit);
            $unit->company_id = $currentCompanyId; // Set the company ID on creation
        }

        $unit->unit_name = $request->input('unit_name');
        $unit->unit_symbol = $request->input('unit_symbol');
        $unit->save();

        Cache::forget(self::$cache . cacheName());
        return response()->json([
            'status' => 'success',
            'message' => 'Unit ' . ($unitId ? 'updated' : 'created') . ' successfully',
            'module_id' => $unit->id,
        ]);
    }

    /** Is this unit on for the current company? Off when the row is off, or it is a shared row this company switched off. */
    private function isOn(Unit $row): bool
    {
        if ($row->status != 1) {
            return false;
        }

        return !($row->company_id === null && in_array((int) $row->id, CompanyDefaults::switchedOff('units', (int) companyId()), true));
    }

    /**
     * Edit, and Active / In-Active. There is no Delete: a unit may be on invoices and quotations already, so it is switched off (no
     * longer offered in new dropdowns) instead. A shared unit (company_id empty) belongs to every company, so switching it off only
     * records that for this company; the row itself, and every other company, are not touched.
     */
    public function actions($id)
    {
        $unit = Unit::select('id', 'status', 'company_id')->findOrFail($id);

        $contextMenu = collect([]);

        $contextMenu->push($this->isOn($unit) ? [
            'label' => __('In-Active'),
            'code' => '01CSED',
            'id' => 'row_inactive',
            'class' => 'row_inactive',
            'data-id' => $unit->id,
            'data-value' => '0',
            'type' => 'item',
            'icon' => 'blocked'
        ] : [
            'label' => __('Active'),
            'code' => '01CSED',
            'id' => 'row_active',
            'class' => 'row_active',
            'data-id' => $unit->id,
            'data-value' => '1',
            'type' => 'item',
            'icon' => 'confirmed'
        ]);

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $unit->id,
            'type' => 'item',
            'icon' => 'edit'
        ]);

        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        abort_unless(in_array((string) $status, ['0', '1'], true), 404);
        $unit = Unit::findOrFail($id);
        $message = __('Unit status updated successfully!');
        if ($unit->company_id === null) {
            // shared: only this company's own switch changes, the row and every other company are not touched
            CompanyDefaults::setSharedActive('units', (int) companyId(), (int) $unit->id, (string) $status === '1');

            return response()->json(['status' => 'success', 'message' => $message, 'data' => ['id' => $unit->id, 'status' => (int) $status]]);
        }
        if (in_array($status, [0, 1])) {
            DB::beginTransaction();
            try {
                $unit->status = $status;
                $unit->save();
                Cache::forget(self::$cache . cacheName());
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return errorResponse($e->getMessage());
            }
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'id' => $unit->id,
                    'status' => $unit->status,
                ],
            ]);
        }
    }
}
