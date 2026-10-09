<?php

namespace App\Http\Controllers\BL;

use App\Enums\MasterBlEnum;
use App\Http\Controllers\Controller;
use App\Models\BL\AirwayBill;
use App\Models\BL\MasterBl;
use App\Models\BL\SeawayBill;
use App\Models\Master\CarrierLine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Master B/L (sea) / master AWB (air) with the house bills consolidated under it.
 * House bills are the existing Seaway Bills (sea) and Airway Bills (air); linking sets their master_bl_id.
 */
class MasterBlController extends Controller
{
    private function houseModel(string $mode): string
    {
        return $mode === 'air' ? AirwayBill::class : SeawayBill::class;
    }

    private function formData(MasterBl $master): array
    {
        return ['master' => $master, 'carriers' => CarrierLine::where('is_active', 1)->orderBy('name')->get()];
    }

    public function modal()
    {
        $master = new MasterBl();
        $master->shipment_mode = 'sea';
        $master->freight_terms = 'prepaid';
        $master->issue_date = today();

        return view('modules.master-bl.master-bl-form', $this->formData($master));
    }

    public function edit($id)
    {
        return view('modules.master-bl.master-bl-form', $this->formData(MasterBl::findOrFail($id)));
    }

    /** House bills of the right kind that can be put under this master: not linked anywhere, or already linked here. */
    public function houseBills($mode, $masterId = null)
    {
        $model = $this->houseModel($mode);
        $rows = $model::with(['customer:id,name_en', 'job:id,row_no'])
            ->where(function ($q) use ($masterId) {
                $q->whereNull('master_bl_id');
                if ($masterId) {
                    $q->orWhere('master_bl_id', (int) $masterId);
                }
            })
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('id')->get();

        return response()->json($rows->map(fn($b) => [
            'id' => $b->id,
            'no' => $b->row_no,
            'customer' => $b->customer->name_en ?? '-',
            'job' => $b->job->row_no ?? null,
            'linked' => $masterId && (int) $b->master_bl_id === (int) $masterId,
        ])->values());
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']), function ($q) use ($filter) {
                $q->whereBetween('issue_date', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })
            ->when(!empty($filter['carrier_id']), fn($q) => $q->where('carrier_id', (int) $filter['carrier_id']))
            ->when(!empty($filter['shipment_mode']), fn($q) => $q->where('shipment_mode', $filter['shipment_mode']))
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('row_no', 'like', "%{$s}%")->orWhere('mbl_no', 'like', "%{$s}%")->orWhere('vessel_flight', 'like', "%{$s}%")
                        ->orWhere('pol', 'like', "%{$s}%")->orWhere('pod', 'like', "%{$s}%")->orWhere('shipper', 'like', "%{$s}%")->orWhere('consignee', 'like', "%{$s}%")
                        ->orWhereHas('carrier', fn($c) => $c->where('name', 'like', "%{$s}%"));
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = MasterBl::with(['carrier:id,name,code'])->withCount(['seawayBills', 'airwayBills'])
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->where('status', MasterBlEnum::fromName($request->tab)))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('id');

        $counts = $this->applyFilters(MasterBl::select('status', DB::raw('COUNT(*) as total')), $filter)->groupBy('status')->pluck('total', 'status')->toArray();
        $all = [];
        foreach (MasterBlEnum::cases() as $s) {
            $all[$s->name] = $counts[$s->value] ?? 0;
        }
        $all['all'] = array_sum($all);

        $ids = $this->applyFilters(MasterBl::query(), $filter)->pluck('id');
        $houses = SeawayBill::whereIn('master_bl_id', $ids)->count() + AirwayBill::whereIn('master_bl_id', $ids)->count();

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Master B/L ' . htmlspecialchars($m->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'mbl-' . $m->id,
            ])
            ->addColumn('issue_date_f', fn($m) => $m->issue_date?->format('d-m-Y'))
            ->addColumn('etd_f', fn($m) => $m->etd?->format('d-m-Y'))
            ->addColumn('eta_f', fn($m) => $m->eta?->format('d-m-Y'))
            ->addColumn('house_count', fn($m) => (int) $m->seaway_bills_count + (int) $m->airway_bills_count)
            ->with(['statusCounts' => $all, 'cards' => ['houses' => $houses]])
            ->toJson();
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'shipment_mode' => 'required|in:sea,air',
            'mbl_no' => 'required|string|max:100',
            'carrier_id' => 'nullable|exists:carrier_lines,id',
            'vessel_flight' => 'nullable|string|max:150',
            'voyage_no' => 'nullable|string|max:100',
            'pol' => 'required|string|max:150',
            'pod' => 'required|string|max:150',
            'issue_date' => 'required|date',
            'etd' => 'nullable|date',
            'eta' => 'nullable|date',
            'shipper' => 'nullable|string|max:255',
            'consignee' => 'nullable|string|max:255',
            'freight_terms' => 'required|in:prepaid,collect',
            'remarks' => 'nullable|string|max:2000',
            'house_ids' => 'nullable|array',
            'house_ids.*' => 'integer',
        ]);

        if ($request->filled('data-id')) {
            $master = MasterBl::findOrFail($request->input('data-id'));
            if (in_array((int) $master->status, [MasterBlEnum::CLOSED->value, MasterBlEnum::CANCELLED->value], true)) {
                return response()->json(['status' => 'error', 'message' => __('A closed or cancelled master B/L can no longer be edited.')], 422);
            }
        } else {
            $master = new MasterBl();
            $date = Carbon::parse(formDate($v['issue_date']));
            $master->unique_row_no = (MasterBl::withTrashed()->whereYear('issue_date', $date->year)->max('unique_row_no') ?? 0) + 1;
            $master->row_no = 'MBL-' . $date->format('y') . '-' . sprintf('%04d', $master->unique_row_no);
            $master->status = MasterBlEnum::DRAFT->value;
            $this->setBaseColumns($master);
        }

        DB::transaction(function () use ($master, $v, $request) {
            $oldMode = $master->exists ? $master->shipment_mode : null;
            $master->fill([
                'shipment_mode' => $v['shipment_mode'],
                'mbl_no' => $v['mbl_no'],
                'carrier_id' => $v['carrier_id'] ?? null,
                'vessel_flight' => $v['vessel_flight'] ?? null,
                'voyage_no' => $v['voyage_no'] ?? null,
                'pol' => $v['pol'],
                'pod' => $v['pod'],
                'issue_date' => formDate($v['issue_date']),
                'etd' => !empty($v['etd']) ? formDate($v['etd']) : null,
                'eta' => !empty($v['eta']) ? formDate($v['eta']) : null,
                'shipper' => $v['shipper'] ?? null,
                'consignee' => $v['consignee'] ?? null,
                'freight_terms' => $v['freight_terms'],
                'remarks' => $v['remarks'] ?? null,
            ])->save();

            // Release every house bill that was here (either kind), then attach the ticked ones of the right kind.
            SeawayBill::where('master_bl_id', $master->id)->update(['master_bl_id' => null]);
            AirwayBill::where('master_bl_id', $master->id)->update(['master_bl_id' => null]);
            $ids = array_map('intval', (array) $request->input('house_ids', []));
            if ($ids) {
                $model = $this->houseModel($v['shipment_mode']);
                $model::whereIn('id', $ids)->whereNull('master_bl_id')->update(['master_bl_id' => $master->id]);
            }
        });

        return response()->json(['status' => 'success', 'message' => __('Master B/L saved successfully'), 'id' => $master->id]);
    }

    public function actions($id)
    {
        $m = MasterBl::select('id', 'status')->findOrFail($id);
        $st = (int) $m->status;
        $menu = collect();

        $moves = match ($st) {
            MasterBlEnum::DRAFT->value => [[MasterBlEnum::ISSUED, 'row_issued', 'confirmed'], [MasterBlEnum::CANCELLED, 'row_rejected', 'rejected']],
            MasterBlEnum::ISSUED->value => [[MasterBlEnum::CLOSED, 'row_closed', 'verified'], [MasterBlEnum::DRAFT, 'row_draft', 'pending'], [MasterBlEnum::CANCELLED, 'row_rejected', 'rejected']],
            MasterBlEnum::CLOSED->value => [[MasterBlEnum::ISSUED, 'row_issued', 'confirmed']],
            MasterBlEnum::CANCELLED->value => [[MasterBlEnum::DRAFT, 'row_draft', 'pending']],
            default => [],
        };
        $menu->push([
            'label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to',
            'items' => array_map(fn($x) => ['label' => __($x[0]->label()), 'id' => $x[1], 'data-id' => $m->id, 'data-value' => $x[0]->value, 'icon' => $x[2]], $moves),
        ]);
        $menu->push(['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $m->id, 'type' => 'item', 'icon' => 'view']);
        if (in_array($st, [MasterBlEnum::DRAFT->value, MasterBlEnum::ISSUED->value], true)) {
            $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $m->id, 'type' => 'item', 'icon' => 'edit']);
        }
        if (in_array($st, [MasterBlEnum::DRAFT->value, MasterBlEnum::CANCELLED->value], true)) {
            $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $m->id, 'type' => 'item', 'icon' => 'delete']);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status)
    {
        $m = MasterBl::findOrFail($id);
        $to = MasterBlEnum::tryFrom((int) $status);
        if (!$to) {
            return response()->json(['status' => 'error', 'message' => __('Unknown status.')], 422);
        }
        if ($to === MasterBlEnum::ISSUED && $m->houseBills()->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => __('Add at least one house bill (Edit) before issuing the master.')], 422);
        }
        $m->status = $to->value;
        $m->save();
        // A cancelled master lets go of its house bills so they can be put under another master.
        if ($to === MasterBlEnum::CANCELLED) {
            SeawayBill::where('master_bl_id', $m->id)->update(['master_bl_id' => null]);
            AirwayBill::where('master_bl_id', $m->id)->update(['master_bl_id' => null]);
        }

        return response()->json(['status' => 'success', 'message' => __('Master B/L status updated successfully!'), 'data' => ['id' => $m->id, 'status' => $m->status]]);
    }

    public function overview($id)
    {
        $master = MasterBl::with(['carrier', 'seawayBills.customer:id,name_en', 'airwayBills.customer:id,name_en', 'seawayBills.job:id,row_no', 'airwayBills.job:id,row_no'])->findOrFail($id);
        $timeline = $this->timeline($master);

        return view('modules.master-bl.view-overview', compact('master', 'timeline'));
    }

    private function timeline(MasterBl $m): array
    {
        $labels = [1 => __('Moved to Draft'), 2 => __('Issued'), 3 => __('Closed'), 4 => __('Cancelled')];
        $icons = [1 => 'bi-clock', 2 => 'bi-file-earmark-check', 3 => 'bi-check2-all', 4 => 'bi-x-circle'];
        $events = [];
        $seen = false;
        $logs = \App\Models\Log\LogHistory::where('loggable_type', MasterBl::class)->where('loggable_id', $m->id)
            ->where('created_at', '>=', $m->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seen = true;
                $events[] = ['label' => __('Master B/L created') . ' · ' . $m->row_no, 'at' => $log->created_at, 'icon' => 'bi-diagram-2', 'by' => $by];
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $events[] = ['label' => $labels[(int) $new['status']] ?? __('Status changed'), 'at' => $log->created_at, 'icon' => $icons[(int) $new['status']] ?? 'bi-clock', 'by' => $by];
                } elseif (array_diff(array_keys($new), ['updated_at'])) {
                    $events[] = ['label' => __('Master B/L updated'), 'at' => $log->created_at, 'icon' => 'bi-pencil-square', 'by' => $by];
                }
            }
        }
        if (!$seen) {
            array_unshift($events, ['label' => __('Master B/L created') . ' · ' . $m->row_no, 'at' => $m->created_at, 'icon' => 'bi-diagram-2', 'by' => null]);
        }

        return $events;
    }

    public function delete($id)
    {
        $m = MasterBl::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('master_bl', (int) $id);
        if ($why) {
            return $guard->refusal(__('master B/L'), $why);
        }
        $m->delete();

        return response()->json(['status' => 'success', 'message' => __('Master B/L deleted successfully')]);
    }
}
