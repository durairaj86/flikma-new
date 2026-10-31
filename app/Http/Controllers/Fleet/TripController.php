<?php

namespace App\Http\Controllers\Fleet;

use App\Enums\TripEnum;
use App\Http\Controllers\Controller;
use App\Models\Fleet\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/** Trips: truck trips from planning to completion, with freight and cost. */
class TripController extends Controller
{
    private function formData(Trip $item): array
    {
        return [
            'item' => $item,
            'vehicles' => \App\Models\Fleet\Vehicle::where('status', 1)->orderBy('plate_no')->get(['id', 'plate_no', 'driver_id']),
            'drivers' => \App\Models\Fleet\Driver::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'jobs' => \App\Models\Job\Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->orderByDesc('id')->limit(300)->get(),
            'deliveryOrders' => \App\Models\Delivery\DeliveryOrder::select('id', 'row_no')->orderByDesc('id')->limit(300)->get(),
        ];
    }

    public function modal()
    {
        $item = new Trip();
        $item->trip_date = today();

        return view('modules.fleet-trip.trip-form', $this->formData($item));
    }

    public function edit($id)
    {
        return view('modules.fleet-trip.trip-form', $this->formData(Trip::findOrFail($id)));
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']), function ($q) use ($filter) {
                $q->whereBetween('trip_date', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('row_no', 'like', "%{$s}%");
                    $q->orWhere('origin', 'like', "%{$s}%");
                    $q->orWhere('destination', 'like', "%{$s}%");
                    $q->orWhereHas('vehicle', fn($r) => $r->where('plate_no', 'like', "%{$s}%"));
                    $q->orWhereHas('driver', fn($r) => $r->where('name', 'like', "%{$s}%"));
                    $q->orWhereHas('job', fn($r) => $r->where('row_no', 'like', "%{$s}%"));
                    $q->orWhereHas('deliveryOrder', fn($r) => $r->where('row_no', 'like', "%{$s}%"));
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = Trip::with(['vehicle:id,plate_no', 'driver:id,name', 'job:id,row_no', 'deliveryOrder:id,row_no'])
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->where('status', TripEnum::fromName($request->tab)))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('id');

        $counts = $this->applyFilters(Trip::select('status', DB::raw('COUNT(*) as total')), $filter)->groupBy('status')->pluck('total', 'status')->toArray();
        $all = [];
        foreach (TripEnum::cases() as $s) {
            $all[$s->name] = $counts[$s->value] ?? 0;
        }
        $all['all'] = array_sum($all);
        $done = $this->applyFilters(Trip::query(), $filter)->where('status', 3);
        $cards = ['margin' => number_format((float) (clone $done)->sum('freight_amount') - (float) (clone $done)->sum('driver_allowance') - (float) (clone $done)->sum('fuel_cost') - (float) (clone $done)->sum('other_cost'), 2)];

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Trip ' . htmlspecialchars((string) $m->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'trip-' . $m->id,
            ])
            ->addColumn('trip_date_f', fn($m) => $m->trip_date?->format('d-m-Y'))
            ->addColumn('started_f', fn($m) => $m->started_at ? Carbon::parse($m->started_at)->format('d-m H:i') : null)
            ->addColumn('completed_f', fn($m) => $m->completed_at ? Carbon::parse($m->completed_at)->format('d-m H:i') : null)
            ->addColumn('freight_f', fn($m) => number_format((float) $m->freight_amount, 2))
            ->addColumn('cost', fn($m) => (float) $m->driver_allowance + (float) $m->fuel_cost + (float) $m->other_cost)
            ->addColumn('cost_f', fn($m) => number_format((float) $m->driver_allowance + (float) $m->fuel_cost + (float) $m->other_cost, 2))
            ->addColumn('margin', fn($m) => (float) $m->freight_amount - ((float) $m->driver_allowance + (float) $m->fuel_cost + (float) $m->other_cost))
            ->addColumn('margin_f', fn($m) => number_format((float) $m->freight_amount - ((float) $m->driver_allowance + (float) $m->fuel_cost + (float) $m->other_cost), 2))
            ->with(['statusCounts' => $all, 'cards' => $cards])
            ->toJson();
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'trip_date' => 'required|date',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'job_id' => 'nullable|exists:jobs,id',
            'delivery_order_id' => 'nullable|exists:delivery_orders,id',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'freight_amount' => 'nullable|numeric|min:0',
            'driver_allowance' => 'nullable|numeric|min:0',
            'fuel_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:2000',
        ]);

        if ($request->filled('data-id')) {
            $item = Trip::findOrFail($request->input('data-id'));
            if (!in_array((int) $item->status, [TripEnum::PLANNED->value, TripEnum::STARTED->value], true)) {
                return response()->json(['status' => 'error', 'message' => __('This Trip can no longer be edited.')], 422);
            }
        } else {
            $item = new Trip();
            $date = Carbon::parse(formDate($v['trip_date']));
            $item->unique_row_no = (Trip::withTrashed()->whereYear('trip_date', $date->year)->max('unique_row_no') ?? 0) + 1;
            $item->row_no = 'TR-' . $date->format('y') . '-' . sprintf('%04d', $item->unique_row_no);
            $item->status = TripEnum::PLANNED->value;
            $this->setBaseColumns($item);
        }

        $item->fill([
            'trip_date' => !empty($v['trip_date']) ? formDate($v['trip_date']) : null,
            'vehicle_id' => $v['vehicle_id'] ?? null,
            'driver_id' => $v['driver_id'] ?? null,
            'job_id' => $v['job_id'] ?? null,
            'delivery_order_id' => $v['delivery_order_id'] ?? null,
            'origin' => $v['origin'] ?? null,
            'destination' => $v['destination'] ?? null,
            'distance_km' => ($v['distance_km'] ?? '') !== '' ? (float) $v['distance_km'] : null,
            'freight_amount' => ($v['freight_amount'] ?? '') !== '' ? (float) $v['freight_amount'] : null,
            'driver_allowance' => ($v['driver_allowance'] ?? '') !== '' ? (float) $v['driver_allowance'] : null,
            'fuel_cost' => ($v['fuel_cost'] ?? '') !== '' ? (float) $v['fuel_cost'] : null,
            'other_cost' => ($v['other_cost'] ?? '') !== '' ? (float) $v['other_cost'] : null,
            'remarks' => $v['remarks'] ?? null,
        ]);
        $item->save();

        return response()->json(['status' => 'success', 'message' => __('Trip saved successfully'), 'id' => $item->id]);
    }

    public function actions($id)
    {
        $o = Trip::select('id', 'status')->findOrFail($id);
        $st = (int) $o->status;
        $menu = collect();

        $moves = match ($st) {
            TripEnum::PLANNED->value => [[TripEnum::STARTED, 'row_started', 'confirmed'], [TripEnum::CANCELLED, 'row_rejected', 'rejected']],
            TripEnum::STARTED->value => [[TripEnum::COMPLETED, 'row_completed', 'verified'], [TripEnum::PLANNED, 'row_planned', 'pending'], [TripEnum::CANCELLED, 'row_rejected', 'rejected']],
            TripEnum::CANCELLED->value => [[TripEnum::PLANNED, 'row_planned', 'pending']],
            default => [],
        };
        if ($moves) {
            $menu->push([
                'label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to',
                'items' => array_map(fn($m) => ['label' => __($m[0]->label()), 'id' => $m[1], 'data-id' => $o->id, 'data-value' => $m[0]->value, 'icon' => $m[2]], $moves),
            ]);
        }
        $menu->push(['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'view']);
        if (in_array($st, [TripEnum::PLANNED->value, TripEnum::STARTED->value], true)) {
            $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'edit']);
        }
        if (in_array($st, [1, 4], true)) {
            $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'delete']);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status)
    {
        $o = Trip::findOrFail($id);
        $to = TripEnum::tryFrom((int) $status);
        if (!$to) {
            return response()->json(['status' => 'error', 'message' => __('Unknown status.')], 422);
        }
        if ($to === TripEnum::STARTED && !$o->vehicle_id) {
            return response()->json(['status' => 'error', 'message' => __('Choose the vehicle (Edit) before starting the trip.')], 422);
        }
        // Stamp the real times: started / completed; moving back to planned clears them.
        if ($to === TripEnum::STARTED) {
            $o->started_at = $o->started_at ?: now();
            $o->completed_at = null;
        } elseif ($to === TripEnum::COMPLETED) {
            $o->started_at = $o->started_at ?: now();
            $o->completed_at = now();
        } elseif ($to === TripEnum::PLANNED) {
            $o->started_at = null;
            $o->completed_at = null;
        }

        $o->status = $to->value;
        $o->save();

        return response()->json(['status' => 'success', 'message' => __('Trip status updated successfully!'), 'data' => ['id' => $o->id, 'status' => $o->status]]);
    }

    public function overview($id)
    {
        $item = Trip::with(['vehicle:id,plate_no', 'driver:id,name', 'job:id,row_no', 'deliveryOrder:id,row_no'])->findOrFail($id);
        $activity = \App\Models\Log\LogHistory::where('loggable_type', Trip::class)->where('loggable_id', $item->id)->where('created_at', '>=', $item->created_at->copy()->subSeconds(5))->orderBy('id')->get();

        return view('modules.fleet-trip.view-overview', compact('item', 'activity'));
    }

    public function delete($id)
    {
        $o = Trip::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('trip', (int) $id);
        if ($why) {
            return $guard->refusal(__('Trip'), $why);
        }
        $o->delete();

        return response()->json(['status' => 'success', 'message' => __('Trip deleted successfully')]);
    }
}
