<?php

namespace App\Http\Controllers\Fleet;

use App\Enums\VehicleEnum;
use App\Http\Controllers\Controller;
use App\Models\Fleet\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/** Vehicles: trucks, trailers and vans with their registration and insurance dates. */
class VehicleController extends Controller
{
    private function formData(Vehicle $item): array
    {
        return [
            'item' => $item,
            'drivers' => \App\Models\Fleet\Driver::where('status', 1)->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function modal()
    {
        $item = new Vehicle();

        return view('modules.fleet-vehicle.vehicle-form', $this->formData($item));
    }

    public function edit($id)
    {
        return view('modules.fleet-vehicle.vehicle-form', $this->formData(Vehicle::findOrFail($id)));
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('plate_no', 'like', "%{$s}%");
                    $q->orWhere('make_model', 'like', "%{$s}%");
                    $q->orWhere('row_no', 'like', "%{$s}%");
                    $q->orWhereHas('driver', fn($r) => $r->where('name', 'like', "%{$s}%"));
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = Vehicle::with(['driver:id,name'])
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->where('status', VehicleEnum::fromName($request->tab)))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('id');

        $counts = $this->applyFilters(Vehicle::select('status', DB::raw('COUNT(*) as total')), $filter)->groupBy('status')->pluck('total', 'status')->toArray();
        $all = [];
        foreach (VehicleEnum::cases() as $s) {
            $all[$s->name] = $counts[$s->value] ?? 0;
        }
        $all['all'] = array_sum($all);
        $cards = ['expiring' => $this->applyFilters(Vehicle::query(), $filter)->whereIn('status', [1, 2])->where(fn($q) => $q->where(fn($w) => $w->whereNotNull('registration_expiry')->whereDate('registration_expiry', '<=', today()->addDays(30)))->orWhere(fn($w) => $w->whereNotNull('insurance_expiry')->whereDate('insurance_expiry', '<=', today()->addDays(30))))->count()];

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Vehicle ' . htmlspecialchars((string) $m->plate_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'vehicle-' . $m->id,
            ])
            ->addColumn('registration_expiry_f', fn($m) => $m->registration_expiry?->format('d-m-Y'))
            ->addColumn('insurance_expiry_f', fn($m) => $m->insurance_expiry?->format('d-m-Y'))
            ->addColumn('reg_state', fn($m) => $m->registration_expiry ? ($m->registration_expiry->lt(today()) ? 'expired' : ($m->registration_expiry->lte(today()->addDays(30)) ? 'soon' : 'ok')) : null)
            ->addColumn('ins_state', fn($m) => $m->insurance_expiry ? ($m->insurance_expiry->lt(today()) ? 'expired' : ($m->insurance_expiry->lte(today()->addDays(30)) ? 'soon' : 'ok')) : null)
            ->with(['statusCounts' => $all, 'cards' => $cards])
            ->toJson();
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'plate_no' => 'required|string|max:50',
            'type' => 'required|in:truck,trailer,container_truck,van,pickup,other',
            'make_model' => 'nullable|string|max:100',
            'capacity_kg' => 'nullable|numeric|min:0',
            'driver_id' => 'nullable|exists:drivers,id',
            'registration_expiry' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
        ]);

        if ($request->filled('data-id')) {
            $item = Vehicle::findOrFail($request->input('data-id'));
            if (!in_array((int) $item->status, [VehicleEnum::ACTIVE->value, VehicleEnum::MAINTENANCE->value, VehicleEnum::INACTIVE->value], true)) {
                return response()->json(['status' => 'error', 'message' => __('This Vehicle can no longer be edited.')], 422);
            }
        } else {
            $item = new Vehicle();
            $item->unique_row_no = (Vehicle::withTrashed()->max('unique_row_no') ?? 0) + 1;
            $item->row_no = 'VH-' . sprintf('%04d', $item->unique_row_no);
            $item->status = VehicleEnum::ACTIVE->value;
            $this->setBaseColumns($item);
        }

        $item->fill([
            'plate_no' => $v['plate_no'] ?? null,
            'type' => $v['type'] ?? null,
            'make_model' => $v['make_model'] ?? null,
            'capacity_kg' => ($v['capacity_kg'] ?? '') !== '' ? (float) $v['capacity_kg'] : null,
            'driver_id' => $v['driver_id'] ?? null,
            'registration_expiry' => !empty($v['registration_expiry']) ? formDate($v['registration_expiry']) : null,
            'insurance_expiry' => !empty($v['insurance_expiry']) ? formDate($v['insurance_expiry']) : null,
            'remarks' => $v['remarks'] ?? null,
        ]);
        $item->save();

        return response()->json(['status' => 'success', 'message' => __('Vehicle saved successfully'), 'id' => $item->id]);
    }

    public function actions($id)
    {
        $o = Vehicle::select('id', 'status')->findOrFail($id);
        $st = (int) $o->status;
        $menu = collect();

        $moves = match ($st) {
            VehicleEnum::ACTIVE->value => [[VehicleEnum::MAINTENANCE, 'row_maintenance', 'pending'], [VehicleEnum::INACTIVE, 'row_inactive', 'rejected']],
            VehicleEnum::MAINTENANCE->value => [[VehicleEnum::ACTIVE, 'row_active', 'confirmed'], [VehicleEnum::INACTIVE, 'row_inactive', 'rejected']],
            VehicleEnum::INACTIVE->value => [[VehicleEnum::ACTIVE, 'row_active', 'confirmed']],
            default => [],
        };
        if ($moves) {
            $menu->push([
                'label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to',
                'items' => array_map(fn($m) => ['label' => __($m[0]->label()), 'id' => $m[1], 'data-id' => $o->id, 'data-value' => $m[0]->value, 'icon' => $m[2]], $moves),
            ]);
        }
        $menu->push(['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'view']);
        if (in_array($st, [VehicleEnum::ACTIVE->value, VehicleEnum::MAINTENANCE->value, VehicleEnum::INACTIVE->value], true)) {
            $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'edit']);
        }
        if (in_array($st, [1, 2, 3], true)) {
            $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'delete']);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status)
    {
        $o = Vehicle::findOrFail($id);
        $to = VehicleEnum::tryFrom((int) $status);
        if (!$to) {
            return response()->json(['status' => 'error', 'message' => __('Unknown status.')], 422);
        }

        $o->status = $to->value;
        $o->save();

        return response()->json(['status' => 'success', 'message' => __('Vehicle status updated successfully!'), 'data' => ['id' => $o->id, 'status' => $o->status]]);
    }

    public function overview($id)
    {
        $item = Vehicle::with(['driver:id,name'])->findOrFail($id);
        $activity = \App\Models\Log\LogHistory::where('loggable_type', Vehicle::class)->where('loggable_id', $item->id)->where('created_at', '>=', $item->created_at->copy()->subSeconds(5))->orderBy('id')->get();

        return view('modules.fleet-vehicle.view-overview', compact('item', 'activity'));
    }

    public function delete($id)
    {
        $o = Vehicle::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('vehicle', (int) $id);
        if ($why) {
            return $guard->refusal(__('Vehicle'), $why);
        }
        $o->delete();

        return response()->json(['status' => 'success', 'message' => __('Vehicle deleted successfully')]);
    }
}
