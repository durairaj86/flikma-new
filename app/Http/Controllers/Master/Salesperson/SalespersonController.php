<?php

namespace App\Http\Controllers\Master\Salesperson;

use App\Http\Controllers\Controller;
use App\Models\Master\Salesperson\Salesperson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Yajra\DataTables\Facades\DataTables;

class SalespersonController extends Controller
{
    private static $cache = 'activeSalesmen:';

    public function fetchAllRows()
    {
        $rows = Salesperson::select('id', 'name', 'status', 'user_id', 'company_id', 'created_at', 'commission_type', 'commission_rate', 'late_rate', 'late_days', 'monthly_target', 'commission_slabs')->with('user:id,name');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->name,
                'class' => 'row-item',
                'id' => fn($model) => 'salesperson-' . strtolower($model->row_no ?? $model->id),
            ])
            // the commission scheme used by the Sales Commission report
            ->addColumn('commission', function ($row) {
                $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.') . '%';
                if ($row->commission_type === 'slab') {
                    $slabs = $row->commission_slabs ?: [];
                    if (!$slabs) return '<span class="text-muted">' . e(__('Slab wise')) . ' · ' . e(__('no slabs')) . '</span>';
                    $rates = collect($slabs)->pluck('rate');
                    return '<span class="badge bg-primary-subtle text-primary-emphasis me-1">' . e(__('Slab wise')) . '</span>'
                        . count($slabs) . ' ' . e(__('slabs')) . ' · ' . $pct($rates->min()) . ($rates->min() != $rates->max() ? ' – ' . $pct($rates->max()) : '');
                }
                if ($row->commission_rate === null && $row->late_rate === null) return '<span class="text-muted">' . e(__('Not set')) . '</span>';
                return '<span class="badge bg-success-subtle text-success-emphasis me-1">' . e(__('Flat')) . '</span>'
                    . $pct($row->commission_rate) . ' <span class="text-muted small">' . e(__('on time')) . '</span> · '
                    . $pct($row->late_rate) . ' <span class="text-muted small">' . e(__('late')) . '</span>';
            })
            ->addColumn('late_limit', fn ($row) => ($row->late_days ?: 120) . ' ' . __('days'))
            ->addColumn('target', fn ($row) => (float) $row->monthly_target > 0 ? number_format((float) $row->monthly_target, 2) : '<span class="text-muted">—</span>')
            ->rawColumns(['commission', 'target'])
            ->editColumn('user_id', function ($row) {
                return $row->user->name;
            })
            ->editColumn('created_at', function ($row) {
                return showDate($row->created_at);
            })
            ->editColumn('status', function ($row) {
                return $row->status == 1 ? 'Active' : 'Inactive';
            })
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $salesperson = new Salesperson();

        return view('modules.master.salesperson.salesperson-form', [
            'salesperson' => $salesperson,
        ]);
    }

    public function edit($id)
    {
        $salesperson = Salesperson::find($id);
        return view('modules.master.salesperson.salesperson-form')->with('salesperson', $salesperson);
    }

    public function store(Request $request)
    {
        // amounts may be typed with thousands separators
        $plain = fn ($v) => is_array($v) ? array_map(fn ($x) => is_string($x) ? str_replace(',', '', $x) : $x, $v) : (is_string($v) ? str_replace(',', '', $v) : $v);
        foreach (['commission_rate', 'late_rate', 'late_days', 'monthly_target', 'slab_from', 'slab_to', 'slab_rate', 'slab_late_rate'] as $k) {
            if ($request->has($k)) $request->merge([$k => $plain($request->input($k))]);
        }

        // Common validation rules
        $rules = [
            'name' => 'required|string|max:128',
            'commission_type' => 'nullable|in:flat,slab',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'late_rate' => 'nullable|numeric|min:0|max:100',
            'late_days' => 'nullable|integer|min:0|max:3650',
            'monthly_target' => 'nullable|numeric|min:0',
            'slab_from.*' => 'nullable|numeric|min:0',
            'slab_to.*' => 'nullable|numeric|min:0',
            'slab_rate.*' => 'nullable|numeric|min:0|max:100',
            'slab_late_rate.*' => 'nullable|numeric|min:0|max:100',
        ];

        // Validate request
        $validated = $request->validate($rules);


        // Save customer
        if (isset($request['data-id']) and filled($request['data-id'])) {
            $salesperson = Salesperson::findOrFail($request->input('data-id'));
        } else {
            $salesperson = new Salesperson();
            $salesperson->status = 1;//default active
            $salesperson->unique_row_no = sprintf("%03d", (Salesperson::max('unique_row_no') ?? 0) + 1);
            $salesperson->row_no = 'SLE' . $salesperson->unique_row_no;
            $this->setBaseColumns($salesperson);
        }

        $salesperson->name = $request->input('name');

        // commission scheme (Sales Commission report): flat rates, or slabs picked by the period's gross profit
        $num = fn ($v) => ($v === null || $v === '') ? null : (float) str_replace(',', '', $v);
        $salesperson->commission_type = $request->input('commission_type') === 'slab' ? 'slab' : 'flat';
        $salesperson->commission_rate = $num($request->input('commission_rate'));
        $salesperson->late_rate = $num($request->input('late_rate'));
        $salesperson->late_days = (int) ($request->input('late_days') ?: 120);
        $salesperson->monthly_target = $num($request->input('monthly_target'));
        $slabs = [];
        foreach ((array) $request->input('slab_from', []) as $i => $from) {
            $rate = $num($request->input('slab_rate')[$i] ?? null);
            if ($rate === null || $salesperson->commission_type !== 'slab') continue;   // an empty row, or not a slab scheme
            $slabs[] = ['from' => $num($from) ?? 0, 'to' => $num($request->input('slab_to')[$i] ?? null),
                'rate' => $rate ?? 0, 'late_rate' => $num($request->input('slab_late_rate')[$i] ?? null) ?? 0];
        }
        usort($slabs, fn ($a, $b) => $a['from'] <=> $b['from']);
        $salesperson->commission_slabs = $slabs ?: null;
        $salesperson->save();
        Cache::forget(self::$cache . cacheName());
        return response()->json([
            'status' => 'success',
            'message' => __('Salesperson created successfully'),
            'module_id' => $salesperson->id,
        ]);
    }

    public function actions($id)
    {
        $salesperson = Salesperson::select('id', 'status')->findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items

        if ($salesperson->status == 1) {
            $contextMenu->push([
                'label' => __('In-Active'),
                'code' => '01CSED',
                'id' => 'row_inactive',
                'class' => 'row_inactive',
                'data-id' => $salesperson->id,
                'data-value' => '0',
                'type' => 'item',
                'icon' => 'blocked'
            ]);
        } else {
            $contextMenu->push([
                'label' => __('Active'),
                'code' => '01CSED',
                'id' => 'row_active',
                'class' => 'row_active',
                'data-id' => $salesperson->id,
                'data-value' => '1',
                'type' => 'item',
                'icon' => 'confirmed'
            ]);
        }

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $salesperson->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $salesperson->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);


        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $salesperson = Salesperson::findOrFail($id);
        $message = 'Salesperson status updated successfully!';
        if (in_array($status, [0, 1])) {
            DB::beginTransaction();
            try {
                $salesperson->update(['status' => $status]);
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
                    'id' => $salesperson->id,
                    'status' => $salesperson->status,
                ],
            ]);
        }
    }

    /** Delete — refused (HTTP 422 + the reasons) while anything still uses this record. */
    public function delete($id)
    {
        $model = Salesperson::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('salesperson', (int) $id);
        if ($why) {
            return $guard->refusal(__('salesperson'), $why);
        }

        $model->delete();
        Cache::forget(self::$cache . cacheName());

        return response()->json(['status' => 'success', 'message' => __('Salesperson deleted successfully')]);
    }
}
