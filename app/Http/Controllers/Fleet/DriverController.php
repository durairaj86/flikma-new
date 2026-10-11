<?php

namespace App\Http\Controllers\Fleet;

use App\Enums\DriverEnum;
use App\Http\Controllers\Controller;
use App\Models\Fleet\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/** Drivers: the people who drive your trucks. */
class DriverController extends Controller
{
    private function formData(Driver $item): array
    {
        return [
            'item' => $item,
        ];
    }

    public function modal()
    {
        $item = new Driver();

        return view('modules.fleet-driver.driver-form', $this->formData($item));
    }

    public function edit($id)
    {
        return view('modules.fleet-driver.driver-form', $this->formData(Driver::findOrFail($id)));
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%");
                    $q->orWhere('phone', 'like', "%{$s}%");
                    $q->orWhere('iqama_no', 'like', "%{$s}%");
                    $q->orWhere('license_no', 'like', "%{$s}%");
                    $q->orWhere('row_no', 'like', "%{$s}%");
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = Driver::with([])
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->where('status', DriverEnum::fromName($request->tab)))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('id');

        $counts = $this->applyFilters(Driver::select('status', DB::raw('COUNT(*) as total')), $filter)->groupBy('status')->pluck('total', 'status')->toArray();
        $all = [];
        foreach (DriverEnum::cases() as $s) {
            $all[$s->name] = $counts[$s->value] ?? 0;
        }
        $all['all'] = array_sum($all);
        $cards = ['expiring' => $this->applyFilters(Driver::query(), $filter)->where('status', 1)->whereNotNull('license_expiry')->whereDate('license_expiry', '<=', today()->addDays(30))->count()];

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Driver ' . htmlspecialchars((string) $m->name, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'driver-' . $m->id,
            ])
            ->addColumn('license_expiry_f', fn($m) => $m->license_expiry?->format('d-m-Y'))
            ->addColumn('license_state', fn($m) => $m->license_expiry ? ($m->license_expiry->lt(today()) ? 'expired' : ($m->license_expiry->lte(today()->addDays(30)) ? 'soon' : 'ok')) : null)
            ->with(['statusCounts' => $all, 'cards' => $cards])
            ->toJson();
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'iqama_no' => 'nullable|string|max:50',
            'license_no' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
        ]);

        if ($request->filled('data-id')) {
            $item = Driver::findOrFail($request->input('data-id'));
            if (!in_array((int) $item->status, [DriverEnum::ACTIVE->value, DriverEnum::INACTIVE->value], true)) {
                return response()->json(['status' => 'error', 'message' => __('This Driver can no longer be edited.')], 422);
            }
        } else {
            $item = new Driver();
            $item->unique_row_no = (Driver::withTrashed()->max('unique_row_no') ?? 0) + 1;
            $item->row_no = 'DR-' . sprintf('%04d', $item->unique_row_no);
            $item->status = DriverEnum::ACTIVE->value;
            $this->setBaseColumns($item);
        }

        $item->fill([
            'name' => $v['name'] ?? null,
            'phone' => $v['phone'] ?? null,
            'iqama_no' => $v['iqama_no'] ?? null,
            'license_no' => $v['license_no'] ?? null,
            'license_expiry' => !empty($v['license_expiry']) ? formDate($v['license_expiry']) : null,
            'remarks' => $v['remarks'] ?? null,
        ]);
        $item->save();

        return response()->json(['status' => 'success', 'message' => __('Driver saved successfully'), 'id' => $item->id]);
    }

    public function actions($id)
    {
        $o = Driver::select('id', 'status')->findOrFail($id);
        $st = (int) $o->status;
        $menu = collect();

        $moves = match ($st) {
            DriverEnum::ACTIVE->value => [[DriverEnum::INACTIVE, 'row_inactive', 'pending']],
            DriverEnum::INACTIVE->value => [[DriverEnum::ACTIVE, 'row_active', 'confirmed']],
            default => [],
        };
        if ($moves) {
            $menu->push([
                'label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to',
                'items' => array_map(fn($m) => ['label' => __($m[0]->label()), 'id' => $m[1], 'data-id' => $o->id, 'data-value' => $m[0]->value, 'icon' => $m[2]], $moves),
            ]);
        }
        $menu->push(['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'view']);
        if (in_array($st, [DriverEnum::ACTIVE->value, DriverEnum::INACTIVE->value], true)) {
            $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'edit']);
        }
        if (in_array($st, [1, 2], true)) {
            $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'delete']);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status)
    {
        $o = Driver::findOrFail($id);
        $to = DriverEnum::tryFrom((int) $status);
        if (!$to) {
            return response()->json(['status' => 'error', 'message' => __('Unknown status.')], 422);
        }

        $o->status = $to->value;
        $o->save();

        return response()->json(['status' => 'success', 'message' => __('Driver status updated successfully!'), 'data' => ['id' => $o->id, 'status' => $o->status]]);
    }

    public function overview($id)
    {
        $item = Driver::with([])->findOrFail($id);
        $activity = \App\Models\Log\LogHistory::where('loggable_type', Driver::class)->where('loggable_id', $item->id)->where('created_at', '>=', $item->created_at->copy()->subSeconds(5))->orderBy('id')->get();

        return view('modules.fleet-driver.view-overview', compact('item', 'activity'));
    }

    public function delete($id)
    {
        $o = Driver::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('driver', (int) $id);
        if ($why) {
            return $guard->refusal(__('Driver'), $why);
        }
        $o->delete();

        return response()->json(['status' => 'success', 'message' => __('Driver deleted successfully')]);
    }
}
