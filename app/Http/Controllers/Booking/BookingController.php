<?php

namespace App\Http\Controllers\Booking;

use App\Enums\BookingEnum;
use App\Http\Controllers\Controller;
use App\Models\Booking\Booking;
use App\Models\Job\Job;
use App\Models\Master\CarrierLine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/** Carrier bookings: pending -> confirmed -> shipped (or cancelled). */
class BookingController extends Controller
{
    private function formData(Booking $booking): array
    {
        return [
            'booking' => $booking,
            'jobs' => Job::select('id', 'row_no', 'customer_id', 'shipment_mode', 'pol', 'pod', 'etd', 'eta')->with('customer:id,name_en')->orderByDesc('id')->get(),
            'carriers' => CarrierLine::where('is_active', 1)->orderBy('name')->get(),
            'containerTypes' => \App\Models\Master\ContainerType::query()->pluck('name')->filter()->unique()->values(),
        ];
    }

    public function modal()
    {
        $booking = new Booking();
        $booking->booking_date = today();
        $booking->shipment_mode = 'sea';

        return view('modules.booking.booking-form', $this->formData($booking));
    }

    public function edit($id)
    {
        return view('modules.booking.booking-form', $this->formData(Booking::findOrFail($id)));
    }

    /** Same filters for rows, tab counts and cards. */
    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']), function ($q) use ($filter) {
                $q->whereBetween('booking_date', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })
            ->when(!empty($filter['customers']), fn($q) => $q->whereIn('customer_id', decodeIds($filter['customers'])))
            ->when(!empty($filter['carrier_id']), fn($q) => $q->where('carrier_id', (int) $filter['carrier_id']))
            ->when(!empty($filter['shipment_mode']), fn($q) => $q->where('shipment_mode', $filter['shipment_mode']))
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('row_no', 'like', "%{$s}%")->orWhere('booking_ref', 'like', "%{$s}%")
                        ->orWhere('vessel_flight', 'like', "%{$s}%")->orWhere('pol', 'like', "%{$s}%")->orWhere('pod', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn($c) => $c->where('name_en', 'like', "%{$s}%")->orWhere('name_ar', 'like', "%{$s}%"))
                        ->orWhereHas('job', fn($j) => $j->where('row_no', 'like', "%{$s}%"));
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = Booking::with(['customer:id,name_en,name_ar,row_no', 'carrier:id,name,code', 'job:id,row_no'])
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->where('status', BookingEnum::fromName($request->tab)))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('id');

        $counts = $this->applyFilters(Booking::select('status', DB::raw('COUNT(*) as total')), $filter)->groupBy('status')->pluck('total', 'status')->toArray();
        $all = [];
        foreach (BookingEnum::cases() as $s) {
            $all[$s->name] = $counts[$s->value] ?? 0;
        }
        $all['all'] = array_sum($all);

        // Bookings whose cargo or document cut-off is within the next 7 days and that haven't sailed.
        $soon = $this->applyFilters(Booking::query(), $filter)
            ->whereIn('status', [BookingEnum::PENDING->value, BookingEnum::CONFIRMED->value])
            ->where(function ($q) {
                $q->whereBetween('cargo_cutoff', [now(), now()->addDays(7)])->orWhereBetween('doc_cutoff', [now(), now()->addDays(7)]);
            })->count();

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Booking ' . htmlspecialchars($m->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'booking-' . $m->id,
            ])
            ->addColumn('booking_date_f', fn($m) => $m->booking_date?->format('d-m-Y'))
            ->addColumn('etd_f', fn($m) => $m->etd?->format('d-m-Y'))
            ->addColumn('eta_f', fn($m) => $m->eta?->format('d-m-Y'))
            ->addColumn('cargo_cutoff_f', fn($m) => $m->cargo_cutoff?->format('d-m-Y H:i'))
            ->addColumn('doc_cutoff_f', fn($m) => $m->doc_cutoff?->format('d-m-Y H:i'))
            ->addColumn('cutoff_soon', fn($m) => in_array((int) $m->status, [1, 2], true)
                && (($m->cargo_cutoff && $m->cargo_cutoff->between(now(), now()->addDays(3))) || ($m->doc_cutoff && $m->doc_cutoff->between(now(), now()->addDays(3)))))
            ->with(['statusCounts' => $all, 'cards' => ['cutoff_soon' => $soon]])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $v = $request->validate([
            'shipment_mode' => 'required|in:sea,air,road',
            'customer' => 'required|exists:customers,id',
            'job_id' => 'nullable|exists:jobs,id',
            'carrier_id' => 'nullable|exists:carrier_lines,id',
            'booking_ref' => 'nullable|string|max:100',
            'vessel_flight' => 'nullable|string|max:150',
            'voyage_no' => 'nullable|string|max:100',
            'pol' => 'required|string|max:150',
            'pod' => 'required|string|max:150',
            'booking_date' => 'required|date',
            'etd' => 'nullable|date',
            'eta' => 'nullable|date',
            'cargo_cutoff' => 'nullable|date',
            'doc_cutoff' => 'nullable|date',
            'container_type' => 'nullable|string|max:100',
            'container_qty' => 'nullable|integer|min:0',
            'gross_weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'cargo_description' => 'nullable|string|max:2000',
            'remarks' => 'nullable|string|max:2000',
        ]);

        if ($request->filled('data-id')) {
            $booking = Booking::findOrFail($request->input('data-id'));
            if (in_array((int) $booking->status, [BookingEnum::SHIPPED->value, BookingEnum::CANCELLED->value], true)) {
                return response()->json(['status' => 'error', 'message' => __('A shipped or cancelled booking can no longer be edited.')], 422);
            }
        } else {
            $booking = new Booking();
            $year = Carbon::parse(formDate($v['booking_date']))->format('Y');
            $booking->unique_row_no = (Booking::whereYear('booking_date', $year)->max('unique_row_no') ?? 0) + 1;
            $booking->row_no = 'BK-' . Carbon::parse(formDate($v['booking_date']))->format('y') . '-' . sprintf('%04d', $booking->unique_row_no);
            $booking->status = BookingEnum::PENDING->value;
            $this->setBaseColumns($booking);
        }

        $dt = fn($x) => $x ? Carbon::parse($x)->format('Y-m-d H:i:s') : null;
        $booking->fill([
            'shipment_mode' => $v['shipment_mode'],
            'customer_id' => $v['customer'],
            'job_id' => $v['job_id'] ?? null,
            'carrier_id' => $v['carrier_id'] ?? null,
            'booking_ref' => $v['booking_ref'] ?? null,
            'vessel_flight' => $v['vessel_flight'] ?? null,
            'voyage_no' => $v['voyage_no'] ?? null,
            'pol' => $v['pol'],
            'pod' => $v['pod'],
            'booking_date' => formDate($v['booking_date']),
            'etd' => !empty($v['etd']) ? formDate($v['etd']) : null,
            'eta' => !empty($v['eta']) ? formDate($v['eta']) : null,
            'cargo_cutoff' => $dt($v['cargo_cutoff'] ?? null),
            'doc_cutoff' => $dt($v['doc_cutoff'] ?? null),
            'container_type' => $v['container_type'] ?? null,
            'container_qty' => !empty($v['container_qty']) ? $v['container_qty'] : null,
            'gross_weight' => (float) ($v['gross_weight'] ?? 0) > 0 ? $v['gross_weight'] : null,
            'volume' => (float) ($v['volume'] ?? 0) > 0 ? $v['volume'] : null,
            'cargo_description' => $v['cargo_description'] ?? null,
            'remarks' => $v['remarks'] ?? null,
        ]);
        $booking->save();

        return response()->json(['status' => 'success', 'message' => __('Booking saved successfully'), 'booking_id' => $booking->id]);
    }

    public function actions($id)
    {
        $b = Booking::select('id', 'status')->findOrFail($id);
        $st = (int) $b->status;
        $menu = collect();

        $moves = [];
        if ($st === BookingEnum::PENDING->value) {
            $moves = [[BookingEnum::CONFIRMED, 'row_confirmed', 'confirmed'], [BookingEnum::CANCELLED, 'row_rejected', 'rejected']];
        } elseif ($st === BookingEnum::CONFIRMED->value) {
            $moves = [[BookingEnum::SHIPPED, 'row_shipped', 'verified'], [BookingEnum::PENDING, 'row_pending', 'pending'], [BookingEnum::CANCELLED, 'row_rejected', 'rejected']];
        } elseif ($st === BookingEnum::CANCELLED->value) {
            $moves = [[BookingEnum::PENDING, 'row_pending', 'pending']];
        }
        if ($moves) {
            $menu->push([
                'label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to',
                'items' => array_map(fn($m) => ['label' => __($m[0]->label()), 'id' => $m[1], 'data-id' => $b->id, 'data-value' => $m[0]->value, 'icon' => $m[2]], $moves),
            ]);
        }
        $menu->push(['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $b->id, 'type' => 'item', 'icon' => 'view']);
        if (in_array($st, [BookingEnum::PENDING->value, BookingEnum::CONFIRMED->value], true)) {
            $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $b->id, 'type' => 'item', 'icon' => 'edit']);
        }
        if (in_array($st, [BookingEnum::PENDING->value, BookingEnum::CANCELLED->value], true)) {
            $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $b->id, 'type' => 'item', 'icon' => 'delete']);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status)
    {
        $b = Booking::findOrFail($id);
        $to = BookingEnum::tryFrom((int) $status);
        if (!$to) {
            return response()->json(['status' => 'error', 'message' => __('Unknown status.')], 422);
        }
        $b->status = $to->value;
        $b->save();

        return response()->json(['status' => 'success', 'message' => __('Booking status updated successfully!'), 'data' => ['id' => $b->id, 'status' => $b->status]]);
    }

    public function overview($id)
    {
        $booking = Booking::with(['customer', 'job', 'carrier'])->findOrFail($id);
        [$origin, $timeline] = $this->timeline($booking);

        return view('modules.booking.view-overview', compact('booking', 'origin', 'timeline'));
    }

    /** Time frame: where the job came from (enquiry -> quotation -> job), then the booking's own life. */
    private function timeline(Booking $b): array
    {
        $events = [];
        $rank = 0;
        $add = function ($label, $at, $icon, $module, $by = null, $meta = null, $link = null) use (&$events, &$rank) {
            if (!$at) return;
            $events[] = ['label' => $label, 'at' => $at, 'icon' => $icon, 'module' => $module, 'by' => $by, 'meta' => $meta, 'link' => $link,
                'rank' => $rank, 'key' => Carbon::parse($at)->timestamp, 'seq' => count($events)];
        };

        $job = $b->job_id ? Job::find($b->job_id) : null;
        $quotation = $job && $job->quotation_id ? \App\Models\Quotation\Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? \App\Models\Enquiry\Enquiry::find($quotation->enquiry_id) : null;

        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', 'enquiry', null, null, ['enquiry', $enquiry->id, $enquiry->row_no]);
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', 'quotation', null, null, ['quotation', $quotation->id, $quotation->row_no]);
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', 'job', null, null, ['job', $job->id, $job->row_no]);

        $rank = 1;
        $origin = $job
            ? (($quotation ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' : '') . __('Job') . ' → ' . __('Booking'))
            : __('Booking created directly');

        $labels = [1 => __('Moved to Pending'), 2 => __('Confirmed'), 3 => __('Shipped'), 4 => __('Cancelled')];
        $icons = [1 => 'bi-clock', 2 => 'bi-check-circle', 3 => 'bi-send-check', 4 => 'bi-x-circle'];
        $ignore = ['status', 'updated_at'];
        $seen = false;
        $logs = \App\Models\Log\LogHistory::where('loggable_type', Booking::class)->where('loggable_id', $b->id)
            ->where('created_at', '>=', $b->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seen = true;
                $add(__('Booking created') . ' · ' . $b->row_no, $log->created_at, 'bi-journal-check', 'booking', $by);
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (int) $new['status'];
                    $add($labels[$st] ?? __('Status changed'), $log->created_at, $icons[$st] ?? 'bi-clock', 'booking', $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add(__('Booking updated'), $log->created_at, 'bi-pencil-square', 'booking', $by);
                }
            }
        }
        if (!$seen) $add(__('Booking created') . ' · ' . $b->row_no, $b->created_at, 'bi-journal-check', 'booking');

        usort($events, fn($a, $c) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$c['rank'], $c['rank'] ? $c['key'] : $c['seq'], $c['seq']]);

        return [$origin, $events];
    }

    public function delete($id)
    {
        $b = Booking::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('booking', (int) $id);
        if ($why) {
            return $guard->refusal(__('booking'), $why);
        }
        $b->delete();

        return response()->json(['status' => 'success', 'message' => __('Booking deleted successfully')]);
    }
}
