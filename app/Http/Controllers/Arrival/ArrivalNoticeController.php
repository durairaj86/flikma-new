<?php

namespace App\Http\Controllers\Arrival;

use App\Enums\ArrivalNoticeEnum;
use App\Http\Controllers\Controller;
use App\Models\Arrival\ArrivalNotice;
use App\Models\Job\Job;
use App\Models\Master\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Cargo arrival notice / notification: draft -> notified -> D.O collected (or cancelled).
 * One record prints in two layouts: the short "Notice" and the bilingual "Notification" with line detention.
 */
class ArrivalNoticeController extends Controller
{
    private function formData(ArrivalNotice $notice): array
    {
        $jobs = Job::select('id', 'row_no', 'customer_id', 'shipper', 'consignee', 'consignee_address', 'pol', 'pod', 'eta', 'carrier', 'voyage_flight_no',
            'hbl_number', 'commodity', 'description', 'no_of_pieces', 'final_destination')
            ->with(['customer:id,name_en,phone', 'containers:id,job_id,container_no,container_number,container_size'])->orderByDesc('id')->limit(500)->get()
            ->map(function ($j) {
                $nos = $j->containers->map(fn($c) => $c->container_number ?: $c->container_no)->filter()->implode(', ');
                $j->setAttribute('c_nos', $nos);
                $j->setAttribute('c20', $j->containers->filter(fn($c) => str_contains((string) $c->container_size, '20'))->count());
                $j->setAttribute('c40', $j->containers->filter(fn($c) => str_contains((string) $c->container_size, '40') || str_contains((string) $c->container_size, '45'))->count());

                return $j;
            });

        return ['notice' => $notice, 'jobs' => $jobs, 'detention' => $notice->detentionTable()];
    }

    public function modal()
    {
        $notice = new ArrivalNotice();
        $notice->notice_date = today();
        $notice->subject = 'Vessel Arriving At ' . ($notice->pod ?: '');
        $notice->free_days = 7;
        $user = auth()->user();
        $notice->contact_email = $user->email ?? null;

        return view('modules.arrival-notice.arrival-notice-form', $this->formData($notice));
    }

    public function edit($id)
    {
        return view('modules.arrival-notice.arrival-notice-form', $this->formData(ArrivalNotice::findOrFail($id)));
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']), function ($q) use ($filter) {
                $q->whereBetween('notice_date', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })
            ->when(!empty($filter['customers']), fn($q) => $q->whereIn('customer_id', decodeIds($filter['customers'])))
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('row_no', 'like', "%{$s}%")->orWhere('bl_no', 'like', "%{$s}%")->orWhere('vessel_name', 'like', "%{$s}%")
                        ->orWhere('consignee', 'like', "%{$s}%")->orWhere('container_nos', 'like', "%{$s}%")->orWhere('pod', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn($c) => $c->where('name_en', 'like', "%{$s}%")->orWhere('name_ar', 'like', "%{$s}%"))
                        ->orWhereHas('job', fn($j) => $j->where('row_no', 'like', "%{$s}%"));
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = ArrivalNotice::with(['customer:id,name_en,name_ar,row_no', 'job:id,row_no'])
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->where('status', ArrivalNoticeEnum::fromName($request->tab)))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('id');

        $counts = $this->applyFilters(ArrivalNotice::select('status', DB::raw('COUNT(*) as total')), $filter)->groupBy('status')->pluck('total', 'status')->toArray();
        $all = [];
        foreach (ArrivalNoticeEnum::cases() as $s) {
            $all[$s->name] = $counts[$s->value] ?? 0;
        }
        $all['all'] = array_sum($all);

        // Vessel arrives within 3 days (or already arrived) and the consignee has not been told yet
        $urgent = $this->applyFilters(ArrivalNotice::query(), $filter)->where('status', ArrivalNoticeEnum::DRAFT->value)
            ->whereNotNull('eta')->whereDate('eta', '<=', today()->addDays(3))->count();

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Arrival Notice ' . htmlspecialchars($m->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'an-' . $m->id,
            ])
            ->addColumn('notice_date_f', fn($m) => $m->notice_date?->format('d-m-Y'))
            ->addColumn('eta_f', fn($m) => $m->eta?->format('d-m-Y'))
            ->addColumn('notified_at_f', fn($m) => $m->notified_at?->format('d-m-Y H:i'))
            ->addColumn('is_urgent', fn($m) => (int) $m->status === ArrivalNoticeEnum::DRAFT->value && $m->eta && $m->eta->lte(today()->addDays(3)))
            ->addColumn('container_count', fn($m) => (int) $m->containers_20 + (int) $m->containers_40)
            ->with(['statusCounts' => $all, 'cards' => ['urgent' => $urgent]])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $v = $request->validate([
            'customer' => 'required|exists:customers,id',
            'job_id' => 'nullable|exists:jobs,id',
            'notice_date' => 'required|date',
            'to_mobile' => 'nullable|string|max:50',
            'to_fax' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'shipper' => 'nullable|string|max:255',
            'consignee' => 'nullable|string|max:255',
            'consignee_address' => 'nullable|string|max:1000',
            'notify_party' => 'nullable|string|max:1000',
            'bl_no' => 'required|string|max:100',
            'vessel_name' => 'required|string|max:150',
            'voyage_no' => 'nullable|string|max:100',
            'eta' => 'nullable|date',
            'pol' => 'required|string|max:150',
            'pod' => 'required|string|max:150',
            'final_destination' => 'nullable|string|max:150',
            'container_nos' => 'nullable|string|max:2000',
            'containers_20' => 'nullable|integer|min:0',
            'containers_40' => 'nullable|integer|min:0',
            'packages' => 'nullable|integer|min:0',
            'commodity' => 'nullable|string|max:2000',
            'carrier_name' => 'nullable|string|max:150',
            'return_depot' => 'nullable|string|max:255',
            'free_days' => 'nullable|integer|min:0|max:365',
            'detention' => 'nullable|array',
            'contact_person' => 'nullable|string|max:150',
            'contact_tel' => 'nullable|string|max:50',
            'contact_fax' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:150',
            'remarks' => 'nullable|string|max:2000',
        ]);

        if ($request->filled('data-id')) {
            $notice = ArrivalNotice::findOrFail($request->input('data-id'));
            if (in_array((int) $notice->status, [ArrivalNoticeEnum::COLLECTED->value, ArrivalNoticeEnum::CANCELLED->value], true)) {
                return response()->json(['status' => 'error', 'message' => __('A collected or cancelled arrival notice can no longer be edited.')], 422);
            }
        } else {
            $notice = new ArrivalNotice();
            $date = Carbon::parse(formDate($v['notice_date']));
            $notice->unique_row_no = (ArrivalNotice::withTrashed()->whereYear('notice_date', $date->year)->max('unique_row_no') ?? 0) + 1;
            $notice->row_no = 'AN-' . $date->format('y') . '-' . sprintf('%04d', $notice->unique_row_no);
            $notice->status = ArrivalNoticeEnum::DRAFT->value;
            $this->setBaseColumns($notice);
        }

        $notice->fill([
            'customer_id' => $v['customer'],
            'job_id' => $v['job_id'] ?? null,
            'notice_date' => formDate($v['notice_date']),
            'to_mobile' => $v['to_mobile'] ?? null,
            'to_fax' => $v['to_fax'] ?? null,
            'subject' => $v['subject'] ?? null,
            'shipper' => $v['shipper'] ?? null,
            'consignee' => $v['consignee'] ?? null,
            'consignee_address' => $v['consignee_address'] ?? null,
            'notify_party' => $v['notify_party'] ?? null,
            'bl_no' => $v['bl_no'],
            'vessel_name' => $v['vessel_name'],
            'voyage_no' => $v['voyage_no'] ?? null,
            'eta' => !empty($v['eta']) ? formDate($v['eta']) : null,
            'pol' => $v['pol'],
            'pod' => $v['pod'],
            'final_destination' => $v['final_destination'] ?? null,
            'container_nos' => $v['container_nos'] ?? null,
            'containers_20' => (int) ($v['containers_20'] ?? 0),
            'containers_40' => (int) ($v['containers_40'] ?? 0),
            'packages' => !empty($v['packages']) ? $v['packages'] : null,
            'commodity' => $v['commodity'] ?? null,
            'carrier_name' => $v['carrier_name'] ?? null,
            'return_depot' => $v['return_depot'] ?? null,
            'free_days' => (int) ($v['free_days'] ?? 7),
            'detention' => $this->cleanDetention($v['detention'] ?? null),
            'contact_person' => $v['contact_person'] ?? null,
            'contact_tel' => $v['contact_tel'] ?? null,
            'contact_fax' => $v['contact_fax'] ?? null,
            'contact_email' => $v['contact_email'] ?? null,
            'remarks' => $v['remarks'] ?? null,
        ]);
        $notice->save();

        return response()->json(['status' => 'success', 'message' => __('Arrival notice saved successfully'), 'id' => $notice->id]);
    }

    /** Keep only the expected shape; null means "use the standard table". */
    private function cleanDetention(?array $in): ?array
    {
        if (!$in) {
            return null;
        }
        $out = [];
        foreach (['standard', 'special', 'reefer'] as $k) {
            $g = $in[$k] ?? null;
            if (!is_array($g)) {
                return null;
            }
            $tiers = [];
            foreach (array_values($g['tiers'] ?? []) as $t) {
                $tiers[] = ['days' => ($t['days'] ?? '') === '' ? null : (int) $t['days'], 'r20' => (float) ($t['r20'] ?? 0), 'r40' => (float) ($t['r40'] ?? 0)];
            }
            $out[$k] = ['free' => (int) ($g['free'] ?? 0), 'tiers' => $tiers];
        }

        return $out == ArrivalNotice::DEFAULT_DETENTION ? null : $out;
    }

    public function actions($id)
    {
        $n = ArrivalNotice::select('id', 'status')->findOrFail($id);
        $st = (int) $n->status;
        $menu = collect();

        $moves = match ($st) {
            ArrivalNoticeEnum::DRAFT->value => [[ArrivalNoticeEnum::NOTIFIED, 'row_notified', 'confirmed'], [ArrivalNoticeEnum::CANCELLED, 'row_rejected', 'rejected']],
            ArrivalNoticeEnum::NOTIFIED->value => [[ArrivalNoticeEnum::COLLECTED, 'row_collected', 'verified'], [ArrivalNoticeEnum::DRAFT, 'row_draft', 'pending'], [ArrivalNoticeEnum::CANCELLED, 'row_rejected', 'rejected']],
            ArrivalNoticeEnum::CANCELLED->value => [[ArrivalNoticeEnum::DRAFT, 'row_draft', 'pending']],
            default => [],
        };
        if ($moves) {
            $menu->push([
                'label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to',
                'items' => array_map(fn($m) => ['label' => __($m[0]->label()), 'id' => $m[1], 'data-id' => $n->id, 'data-value' => $m[0]->value, 'icon' => $m[2]], $moves),
            ]);
        }
        $menu->push(['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $n->id, 'type' => 'item', 'icon' => 'view']);
        $menu->push(['label' => __('Print Notice'), 'code' => '01CSPN', 'id' => 'row_print_notice', 'class' => 'row_print', 'data-id' => $n->id, 'type' => 'item', 'icon' => 'print',
            'onclick' => "ARRIVAL_NOTICE.printPreview({$n->id}, 'notice')"]);
        $menu->push(['label' => __('Print Notification'), 'code' => '01CSPF', 'id' => 'row_print_notification', 'class' => 'row_print', 'data-id' => $n->id, 'type' => 'item', 'icon' => 'print',
            'onclick' => "ARRIVAL_NOTICE.printPreview({$n->id}, 'notification')"]);
        if (in_array($st, [ArrivalNoticeEnum::DRAFT->value, ArrivalNoticeEnum::NOTIFIED->value], true)) {
            $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $n->id, 'type' => 'item', 'icon' => 'edit']);
        }
        if (in_array($st, [ArrivalNoticeEnum::DRAFT->value, ArrivalNoticeEnum::CANCELLED->value], true)) {
            $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $n->id, 'type' => 'item', 'icon' => 'delete']);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status)
    {
        $n = ArrivalNotice::findOrFail($id);
        $to = ArrivalNoticeEnum::tryFrom((int) $status);
        if (!$to) {
            return response()->json(['status' => 'error', 'message' => __('Unknown status.')], 422);
        }
        $n->status = $to->value;
        // Notified: stamp when the consignee was told; moving back to draft clears it.
        if ($to === ArrivalNoticeEnum::NOTIFIED) {
            $n->notified_at = now();
        } elseif ($to === ArrivalNoticeEnum::DRAFT) {
            $n->notified_at = null;
            $n->notified_via = null;
        }
        $n->save();

        return response()->json(['status' => 'success', 'message' => __('Arrival notice status updated successfully!'), 'data' => ['id' => $n->id, 'status' => $n->status]]);
    }

    public function overview($id)
    {
        $notice = ArrivalNotice::with(['customer', 'job'])->findOrFail($id);
        [$origin, $timeline] = $this->timeline($notice);

        return view('modules.arrival-notice.view-overview', compact('notice', 'origin', 'timeline'));
    }

    private function timeline(ArrivalNotice $o): array
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
            ? (($quotation ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' : '') . __('Job') . ' → ' . __('Arrival Notice'))
            : __('Arrival notice created directly');

        $labels = [1 => __('Moved to Draft'), 2 => __('Consignee notified'), 3 => __('Delivery order collected'), 4 => __('Cancelled')];
        $icons = [1 => 'bi-clock', 2 => 'bi-megaphone', 3 => 'bi-check2-circle', 4 => 'bi-x-circle'];
        $ignore = ['status', 'updated_at', 'notified_at', 'notified_via'];
        $seen = false;
        $logs = \App\Models\Log\LogHistory::where('loggable_type', ArrivalNotice::class)->where('loggable_id', $o->id)
            ->where('created_at', '>=', $o->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seen = true;
                $add(__('Arrival notice created') . ' · ' . $o->row_no, $log->created_at, 'bi-file-earmark-arrow-up', 'arrival', $by);
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (int) $new['status'];
                    $add($labels[$st] ?? __('Status changed'), $log->created_at, $icons[$st] ?? 'bi-clock', 'arrival', $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add(__('Arrival notice updated'), $log->created_at, 'bi-pencil-square', 'arrival', $by);
                }
            }
        }
        if (!$seen) $add(__('Arrival notice created') . ' · ' . $o->row_no, $o->created_at, 'bi-file-earmark-arrow-up', 'arrival');

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }

    /** Printable page: ?format=notice (short, with bank details) or ?format=notification (bilingual, line detention). */
    public function print(Request $request, $id)
    {
        $notice = ArrivalNotice::with(['customer', 'job'])->findOrFail($id);
        $format = $request->query('format') === 'notification' ? 'notification' : 'notice';
        $bank = Bank::where('status', 1)->whereNotNull('iban_code')->where('iban_code', '!=', '')->orderBy('sort')->orderBy('id')->first();

        return view('modules.arrival-notice.print-' . $format, ['notice' => $notice, 'company' => authUserCompany(), 'bank' => $bank]);
    }

    public function delete($id)
    {
        $n = ArrivalNotice::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('arrival_notice', (int) $id);
        if ($why) {
            return $guard->refusal(__('arrival notice'), $why);
        }
        $n->delete();

        return response()->json(['status' => 'success', 'message' => __('Arrival notice deleted successfully')]);
    }
}
