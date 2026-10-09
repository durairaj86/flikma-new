<?php

namespace App\Http\Controllers\Delivery;

use App\Enums\DeliveryOrderEnum;
use App\Http\Controllers\Controller;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Job\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/** Delivery orders: pending -> dispatched -> delivered (or cancelled). */
class DeliveryOrderController extends Controller
{
    private function formData(DeliveryOrder $order): array
    {
        return [
            'order' => $order,
            'jobs' => Job::select('id', 'row_no', 'customer_id', 'consignee', 'delivery_address', 'pickup_address', 'container_no', 'no_of_pieces', 'weight', 'description')
                ->with('customer:id,name_en')->orderByDesc('id')->get(),
        ];
    }

    public function modal()
    {
        $order = new DeliveryOrder();
        $order->do_date = today();

        return view('modules.delivery-order.delivery-order-form', $this->formData($order));
    }

    public function edit($id)
    {
        return view('modules.delivery-order.delivery-order-form', $this->formData(DeliveryOrder::findOrFail($id)));
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']), function ($q) use ($filter) {
                $q->whereBetween('do_date', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })
            ->when(!empty($filter['customers']), fn($q) => $q->whereIn('customer_id', decodeIds($filter['customers'])))
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('row_no', 'like', "%{$s}%")->orWhere('vehicle_no', 'like', "%{$s}%")->orWhere('driver_name', 'like', "%{$s}%")
                        ->orWhere('transporter', 'like', "%{$s}%")->orWhere('consignee', 'like', "%{$s}%")->orWhere('container_no', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn($c) => $c->where('name_en', 'like', "%{$s}%")->orWhere('name_ar', 'like', "%{$s}%"))
                        ->orWhereHas('job', fn($j) => $j->where('row_no', 'like', "%{$s}%"));
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = DeliveryOrder::with(['customer:id,name_en,name_ar,row_no', 'job:id,row_no'])
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->where('status', DeliveryOrderEnum::fromName($request->tab)))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('id');

        $counts = $this->applyFilters(DeliveryOrder::select('status', DB::raw('COUNT(*) as total')), $filter)->groupBy('status')->pluck('total', 'status')->toArray();
        $all = [];
        foreach (DeliveryOrderEnum::cases() as $s) {
            $all[$s->name] = $counts[$s->value] ?? 0;
        }
        $all['all'] = array_sum($all);

        // Planned delivery date passed and still not delivered
        $late = $this->applyFilters(DeliveryOrder::query(), $filter)->whereIn('status', [1, 2])->whereDate('delivery_date', '<', today())->count();

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Delivery Order ' . htmlspecialchars($m->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'do-' . $m->id,
            ])
            ->addColumn('do_date_f', fn($m) => $m->do_date?->format('d-m-Y'))
            ->addColumn('delivery_date_f', fn($m) => $m->delivery_date?->format('d-m-Y'))
            ->addColumn('delivered_at_f', fn($m) => $m->delivered_at?->format('d-m-Y H:i'))
            ->addColumn('is_late', fn($m) => in_array((int) $m->status, [1, 2], true) && $m->delivery_date && $m->delivery_date->lt(today()))
            ->with(['statusCounts' => $all, 'cards' => ['late' => $late]])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $v = $request->validate([
            'customer' => 'required|exists:customers,id',
            'job_id' => 'nullable|exists:jobs,id',
            'do_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'pickup_location' => 'nullable|string|max:255',
            'delivery_address' => 'required|string|max:1000',
            'consignee' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'transporter' => 'nullable|string|max:150',
            'vehicle_no' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:150',
            'driver_phone' => 'nullable|string|max:50',
            'container_no' => 'nullable|string|max:255',
            'packages' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'cargo_description' => 'nullable|string|max:2000',
            'received_by' => 'nullable|string|max:150',
            'pod_notes' => 'nullable|string|max:2000',
            'remarks' => 'nullable|string|max:2000',
        ]);

        if ($request->filled('data-id')) {
            $order = DeliveryOrder::findOrFail($request->input('data-id'));
            if (in_array((int) $order->status, [DeliveryOrderEnum::DELIVERED->value, DeliveryOrderEnum::CANCELLED->value], true)) {
                return response()->json(['status' => 'error', 'message' => __('A delivered or cancelled delivery order can no longer be edited.')], 422);
            }
        } else {
            $order = new DeliveryOrder();
            $date = Carbon::parse(formDate($v['do_date']));
            $order->unique_row_no = (DeliveryOrder::whereYear('do_date', $date->year)->max('unique_row_no') ?? 0) + 1;
            $order->row_no = 'DO-' . $date->format('y') . '-' . sprintf('%04d', $order->unique_row_no);
            $order->status = DeliveryOrderEnum::PENDING->value;
            $this->setBaseColumns($order);
        }

        $order->fill([
            'customer_id' => $v['customer'],
            'job_id' => $v['job_id'] ?? null,
            'do_date' => formDate($v['do_date']),
            'delivery_date' => !empty($v['delivery_date']) ? formDate($v['delivery_date']) : null,
            'pickup_location' => $v['pickup_location'] ?? null,
            'delivery_address' => $v['delivery_address'],
            'consignee' => $v['consignee'] ?? null,
            'contact_person' => $v['contact_person'] ?? null,
            'contact_phone' => $v['contact_phone'] ?? null,
            'transporter' => $v['transporter'] ?? null,
            'vehicle_no' => $v['vehicle_no'] ?? null,
            'driver_name' => $v['driver_name'] ?? null,
            'driver_phone' => $v['driver_phone'] ?? null,
            'container_no' => $v['container_no'] ?? null,
            'packages' => !empty($v['packages']) ? $v['packages'] : null,
            'weight' => (float) ($v['weight'] ?? 0) > 0 ? $v['weight'] : null,
            'cargo_description' => $v['cargo_description'] ?? null,
            'received_by' => $v['received_by'] ?? null,
            'pod_notes' => $v['pod_notes'] ?? null,
            'remarks' => $v['remarks'] ?? null,
        ]);
        $order->save();

        return response()->json(['status' => 'success', 'message' => __('Delivery order saved successfully'), 'id' => $order->id]);
    }

    public function actions($id)
    {
        $o = DeliveryOrder::select('id', 'status')->findOrFail($id);
        $st = (int) $o->status;
        $menu = collect();

        $moves = match ($st) {
            DeliveryOrderEnum::PENDING->value => [[DeliveryOrderEnum::DISPATCHED, 'row_dispatched', 'confirmed'], [DeliveryOrderEnum::CANCELLED, 'row_rejected', 'rejected']],
            DeliveryOrderEnum::DISPATCHED->value => [[DeliveryOrderEnum::DELIVERED, 'row_delivered', 'verified'], [DeliveryOrderEnum::PENDING, 'row_pending', 'pending'], [DeliveryOrderEnum::CANCELLED, 'row_rejected', 'rejected']],
            DeliveryOrderEnum::CANCELLED->value => [[DeliveryOrderEnum::PENDING, 'row_pending', 'pending']],
            default => [],
        };
        if ($moves) {
            $menu->push([
                'label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to',
                'items' => array_map(fn($m) => ['label' => __($m[0]->label()), 'id' => $m[1], 'data-id' => $o->id, 'data-value' => $m[0]->value, 'icon' => $m[2]], $moves),
            ]);
        }
        $menu->push(['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'view']);
        $menu->push(['label' => __('Print'), 'code' => '01CSPR', 'id' => 'row_print', 'class' => 'row_print', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'print',
            'onclick' => 'DELIVERY_ORDER.printPreview(' . $o->id . ')']);
        if (in_array($st, [DeliveryOrderEnum::PENDING->value, DeliveryOrderEnum::DISPATCHED->value], true)) {
            $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'edit']);
        }
        if (in_array($st, [DeliveryOrderEnum::PENDING->value, DeliveryOrderEnum::CANCELLED->value], true)) {
            $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $o->id, 'type' => 'item', 'icon' => 'delete']);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status)
    {
        $o = DeliveryOrder::findOrFail($id);
        $to = DeliveryOrderEnum::tryFrom((int) $status);
        if (!$to) {
            return response()->json(['status' => 'error', 'message' => __('Unknown status.')], 422);
        }
        if ($to === DeliveryOrderEnum::DISPATCHED && !$o->vehicle_no && !$o->driver_name) {
            return response()->json(['status' => 'error', 'message' => __('Add the vehicle number or driver (Edit) before dispatching.')], 422);
        }
        $o->status = $to->value;
        // Delivered: stamp the actual time; moving back clears it.
        $o->delivered_at = $to === DeliveryOrderEnum::DELIVERED ? now() : null;
        $o->save();

        return response()->json(['status' => 'success', 'message' => __('Delivery order status updated successfully!'), 'data' => ['id' => $o->id, 'status' => $o->status]]);
    }

    public function overview($id)
    {
        $order = DeliveryOrder::with(['customer', 'job'])->findOrFail($id);
        [$origin, $timeline] = $this->timeline($order);

        return view('modules.delivery-order.view-overview', compact('order', 'origin', 'timeline'));
    }

    private function timeline(DeliveryOrder $o): array
    {
        $events = [];
        $rank = 0;
        $add = function ($label, $at, $icon, $module, $by = null, $meta = null, $link = null) use (&$events, &$rank) {
            if (!$at) return;
            $events[] = ['label' => $label, 'at' => $at, 'icon' => $icon, 'module' => $module, 'by' => $by, 'meta' => $meta, 'link' => $link,
                'rank' => $rank, 'key' => Carbon::parse($at)->timestamp, 'seq' => count($events)];
        };

        $job = $o->job_id ? Job::find($o->job_id) : null;
        $quotation = $job && $job->quotation_id ? \App\Models\Quotation\Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? \App\Models\Enquiry\Enquiry::find($quotation->enquiry_id) : null;
        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', 'enquiry', null, null, ['enquiry', $enquiry->id, $enquiry->row_no]);
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', 'quotation', null, null, ['quotation', $quotation->id, $quotation->row_no]);
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', 'job', null, null, ['job', $job->id, $job->row_no]);

        $rank = 1;
        $origin = $job
            ? (($quotation ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' : '') . __('Job') . ' → ' . __('Delivery Order'))
            : __('Delivery order created directly');

        $labels = [1 => __('Moved to Pending'), 2 => __('Dispatched'), 3 => __('Delivered'), 4 => __('Cancelled')];
        $icons = [1 => 'bi-clock', 2 => 'bi-truck', 3 => 'bi-check2-circle', 4 => 'bi-x-circle'];
        $ignore = ['status', 'updated_at', 'delivered_at'];
        $seen = false;
        $logs = \App\Models\Log\LogHistory::where('loggable_type', DeliveryOrder::class)->where('loggable_id', $o->id)
            ->where('created_at', '>=', $o->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seen = true;
                $add(__('Delivery order created') . ' · ' . $o->row_no, $log->created_at, 'bi-file-earmark-arrow-up', 'delivery', $by);
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (int) $new['status'];
                    $add($labels[$st] ?? __('Status changed'), $log->created_at, $icons[$st] ?? 'bi-clock', 'delivery', $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add(__('Delivery order updated'), $log->created_at, 'bi-pencil-square', 'delivery', $by);
                }
            }
        }
        if (!$seen) $add(__('Delivery order created') . ' · ' . $o->row_no, $o->created_at, 'bi-file-earmark-arrow-up', 'delivery');

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }

    public function print($id)
    {
        $order = DeliveryOrder::with(['customer', 'job'])->findOrFail($id);

        return view('modules.delivery-order.print', ['order' => $order, 'company' => authUserCompany()]);
    }

    public function delete($id)
    {
        $o = DeliveryOrder::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('delivery_order', (int) $id);
        if ($why) {
            return $guard->refusal(__('delivery order'), $why);
        }
        $o->delete();

        return response()->json(['status' => 'success', 'message' => __('Delivery order deleted successfully')]);
    }
}
