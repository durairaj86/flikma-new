<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Master\CarrierLine;
use App\Models\Sales\RateSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/** Rate sheets: buy / sell rate per lane with validity. Tabs: active, expiring (30 days), expired, inactive. */
class RateSheetController extends Controller
{
    private function formData(RateSheet $rate): array
    {
        return [
            'rate' => $rate,
            'carriers' => CarrierLine::where('is_active', 1)->orderBy('name')->get(),
            'currencies' => DB::table('currencies')->orderBy('code')->pluck('code')->unique()->values(),
            'containerTypes' => \App\Models\Master\ContainerType::query()->pluck('name')->filter()->unique()->values(),
            'basisList' => RateSheet::BASIS,
        ];
    }

    public function modal()
    {
        $rate = new RateSheet();
        $rate->shipment_mode = 'sea';
        $rate->currency = 'USD';
        $rate->basis = 'per_container';
        $rate->valid_from = today();
        $rate->valid_to = today()->addMonths(3);
        $rate->is_active = true;

        return view('modules.rate-sheet.rate-sheet-form', $this->formData($rate));
    }

    public function edit($id)
    {
        return view('modules.rate-sheet.rate-sheet-form', $this->formData(RateSheet::findOrFail($id)));
    }

    /** SQL conditions for each validity tab (kept in one place so rows, counts and cards agree). */
    private function validity($query, string $tab)
    {
        $today = today()->toDateString();
        $soon = today()->addDays(30)->toDateString();
        $live = fn($q) => $q->where('is_active', 1)
            ->where(fn($w) => $w->whereNull('valid_to')->orWhere('valid_to', '>=', $today))
            ->where(fn($w) => $w->whereNull('valid_from')->orWhere('valid_from', '<=', $today));

        return match ($tab) {
            'active' => $live($query),
            'expiring' => $live($query)->whereNotNull('valid_to')->where('valid_to', '<=', $soon),
            'expired' => $query->where('is_active', 1)->whereNotNull('valid_to')->where('valid_to', '<', $today),
            'inactive' => $query->where('is_active', 0),
            default => $query,
        };
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(!empty($filter['shipment_mode']), fn($q) => $q->where('shipment_mode', $filter['shipment_mode']))
            ->when(!empty($filter['carrier_id']), fn($q) => $q->where('carrier_id', (int) $filter['carrier_id']))
            ->when(!empty($filter['origin']), fn($q) => $q->where('origin', 'like', '%' . $filter['origin'] . '%'))
            ->when(!empty($filter['destination']), fn($q) => $q->where('destination', 'like', '%' . $filter['destination'] . '%'))
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('row_no', 'like', "%{$s}%")->orWhere('origin', 'like', "%{$s}%")->orWhere('destination', 'like', "%{$s}%")
                        ->orWhere('container_type', 'like', "%{$s}%")
                        ->orWhereHas('carrier', fn($c) => $c->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];
        $tab = $request->tab ?: 'all';

        $rows = $this->validity($this->applyFilters(RateSheet::with('carrier:id,name,code'), $filter), $tab)->orderByDesc('id');

        $counts = [];
        foreach (['all', 'active', 'expiring', 'expired', 'inactive'] as $t) {
            $counts[$t] = $this->validity($this->applyFilters(RateSheet::query(), $filter), $t)->count();
        }
        $avg = $this->validity($this->applyFilters(RateSheet::query(), $filter), 'active')->where('sell_rate', '>', 0)
            ->selectRaw('AVG((sell_rate - buy_rate) / sell_rate * 100) as m')->value('m');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Rate ' . htmlspecialchars($m->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'rate-' . $m->id,
            ])
            ->addColumn('validity', fn($m) => $m->validity())
            ->addColumn('buy_f', fn($m) => number_format((float) $m->buy_rate, 2))
            ->addColumn('sell_f', fn($m) => number_format((float) $m->sell_rate, 2))
            ->addColumn('margin_f', fn($m) => number_format((float) $m->sell_rate - (float) $m->buy_rate, 2))
            ->addColumn('margin_pct', fn($m) => (float) $m->sell_rate > 0 ? round(((float) $m->sell_rate - (float) $m->buy_rate) / (float) $m->sell_rate * 100, 1) : 0)
            ->addColumn('valid_from_f', fn($m) => $m->valid_from?->format('d-m-Y'))
            ->addColumn('valid_to_f', fn($m) => $m->valid_to?->format('d-m-Y'))
            ->addColumn('days_left', fn($m) => $m->valid_to ? today()->diffInDays($m->valid_to, false) : null)
            ->addColumn('basis_label', fn($m) => RateSheet::BASIS[$m->basis] ?? $m->basis)
            ->with(['statusCounts' => $counts, 'cards' => ['avg_margin' => $avg !== null ? round((float) $avg, 1) : 0]])
            ->toJson();
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'shipment_mode' => 'required|in:sea,air,road',
            'origin' => 'required|string|max:150',
            'destination' => 'required|string|max:150',
            'carrier_id' => 'nullable|exists:carrier_lines,id',
            'container_type' => 'nullable|string|max:100',
            'basis' => 'required|in:' . implode(',', array_keys(RateSheet::BASIS)),
            'currency' => 'required|string|max:10',
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
            'min_charge' => 'nullable|numeric|min:0',
            'transit_days' => 'nullable|integer|min:0|max:999',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $from = !empty($v['valid_from']) ? formDate($v['valid_from']) : null;
        $to = !empty($v['valid_to']) ? formDate($v['valid_to']) : null;
        if ($from && $to && $to < $from) {
            return response()->json(['status' => 'error', 'message' => __('Valid-to date can\'t be before the valid-from date.')], 422);
        }

        if ($request->filled('data-id')) {
            $rate = RateSheet::findOrFail($request->input('data-id'));
        } else {
            $rate = new RateSheet();
            $year = now()->year;
            $rate->unique_row_no = (RateSheet::whereYear('created_at', $year)->withTrashed()->max('unique_row_no') ?? 0) + 1;
            $rate->row_no = 'RT-' . now()->format('y') . '-' . sprintf('%04d', $rate->unique_row_no);
            $rate->is_active = true;
            $this->setBaseColumns($rate);
        }

        $rate->fill([
            'shipment_mode' => $v['shipment_mode'],
            'origin' => $v['origin'],
            'destination' => $v['destination'],
            'carrier_id' => $v['carrier_id'] ?? null,
            'container_type' => $v['container_type'] ?? null,
            'basis' => $v['basis'],
            'currency' => strtoupper($v['currency']),
            'buy_rate' => $v['buy_rate'],
            'sell_rate' => $v['sell_rate'],
            'min_charge' => (float) ($v['min_charge'] ?? 0) > 0 ? $v['min_charge'] : null,
            'transit_days' => $v['transit_days'] ?? null,
            'valid_from' => $from,
            'valid_to' => $to,
            'remarks' => $v['remarks'] ?? null,
        ])->save();

        return response()->json(['status' => 'success', 'message' => __('Rate saved successfully'), 'id' => $rate->id]);
    }

    public function actions($id)
    {
        $r = RateSheet::select('id', 'is_active')->findOrFail($id);

        return response()->json([
            ['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $r->id, 'type' => 'item', 'icon' => 'view'],
            ['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $r->id, 'type' => 'item', 'icon' => 'edit'],
            ['label' => __('Duplicate'), 'code' => '01CSDP', 'id' => 'row_duplicate', 'class' => 'row_duplicate', 'data-id' => $r->id, 'type' => 'item', 'icon' => 'add'],
            $r->is_active
                ? ['label' => __('Deactivate'), 'code' => '01CSIA', 'id' => 'row_inactive', 'class' => 'row_inactive', 'data-id' => $r->id, 'data-value' => 'inactive', 'type' => 'item', 'icon' => 'blocked']
                : ['label' => __('Activate'), 'code' => '01CSAC', 'id' => 'row_active', 'class' => 'row_active', 'data-id' => $r->id, 'data-value' => 'active', 'type' => 'item', 'icon' => 'confirmed'],
            ['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $r->id, 'type' => 'item', 'icon' => 'delete', 'separator' => 'before'],
        ]);
    }

    public function updateStatus($id, $status)
    {
        abort_unless(in_array($status, ['active', 'inactive'], true), 422);
        $r = RateSheet::findOrFail($id);
        $r->is_active = $status === 'active';
        $r->save();

        return response()->json(['status' => 'success', 'message' => __('Rate status updated successfully!')]);
    }

    /** A copy with a new number and the validity starting today (the quick way to roll a rate into the next quarter). */
    public function duplicate($id)
    {
        $src = RateSheet::findOrFail($id);
        $copy = $src->replicate();
        $copy->unique_row_no = (RateSheet::withTrashed()->whereYear('created_at', now()->year)->max('unique_row_no') ?? 0) + 1;
        $copy->row_no = 'RT-' . now()->format('y') . '-' . sprintf('%04d', $copy->unique_row_no);
        if ($src->valid_from && $src->valid_to) {
            $span = $src->valid_from->diffInDays($src->valid_to);
            $copy->valid_from = today();
            $copy->valid_to = today()->addDays($span);
        }
        $copy->is_active = true;
        $copy->user_id = auth()->id();
        $copy->save();

        return response()->json(['status' => 'success', 'message' => __('Rate duplicated as :no', ['no' => $copy->row_no]), 'id' => $copy->id]);
    }

    public function overview($id)
    {
        $rate = RateSheet::with('carrier')->findOrFail($id);
        $timeline = $this->timeline($rate);

        return view('modules.rate-sheet.view-overview', compact('rate', 'timeline'));
    }

    private function timeline(RateSheet $r): array
    {
        $events = [];
        $seen = false;
        $logs = \App\Models\Log\LogHistory::where('loggable_type', RateSheet::class)->where('loggable_id', $r->id)
            ->where('created_at', '>=', $r->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seen = true;
                $events[] = ['label' => __('Rate created') . ' · ' . $r->row_no, 'at' => $log->created_at, 'icon' => 'bi-tags', 'by' => $by];
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                $old = $log->changes['old'] ?? [];
                if (isset($new['is_active'])) {
                    $events[] = ['label' => $new['is_active'] ? __('Activated') : __('Deactivated'), 'at' => $log->created_at, 'icon' => $new['is_active'] ? 'bi-check-circle' : 'bi-pause-circle', 'by' => $by];
                } elseif (array_diff(array_keys($new), ['updated_at'])) {
                    $meta = null;
                    if (isset($new['sell_rate'], $old['sell_rate'])) {
                        $meta = __('Sell') . ' ' . number_format((float) $old['sell_rate'], 2) . ' → ' . number_format((float) $new['sell_rate'], 2);
                    } elseif (isset($new['buy_rate'], $old['buy_rate'])) {
                        $meta = __('Buy') . ' ' . number_format((float) $old['buy_rate'], 2) . ' → ' . number_format((float) $new['buy_rate'], 2);
                    }
                    $events[] = ['label' => __('Rate updated'), 'at' => $log->created_at, 'icon' => 'bi-pencil-square', 'by' => $by, 'meta' => $meta];
                }
            }
        }
        if (!$seen) {
            array_unshift($events, ['label' => __('Rate created') . ' · ' . $r->row_no, 'at' => $r->created_at, 'icon' => 'bi-tags', 'by' => null]);
        }

        return $events;
    }

    public function delete($id)
    {
        RateSheet::findOrFail($id)->delete();

        return response()->json(['status' => 'success', 'message' => __('Rate deleted successfully')]);
    }
}
