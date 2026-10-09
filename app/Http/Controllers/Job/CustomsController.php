<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Job\Job;
use App\Models\Job\JobClearance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Customs clearance progress per job. The clearance record is created with the job (Job form, "Customs & Clearance" tab);
 * this page lists them all and lets the clearance team update the status, documents and duty in one place.
 */
class CustomsController extends Controller
{
    /** status key => label (kept as stored by the Job form). */
    private const STATUSES = ['pending' => 'Pending', 'under-process' => 'Under Process', 'cleared' => 'Cleared', 'on-hold' => 'On Hold'];
    private const TAB_KEYS = ['pending', 'under-process', 'cleared', 'on-hold'];

    /** Clearances of this company's live jobs, with the status normalised (blank = pending). */
    private function baseQuery()
    {
        return JobClearance::query()
            ->join('jobs', 'jobs.id', '=', 'job_clearances.job_id')
            ->whereNull('jobs.deleted_at')
            ->where('jobs.company_id', companyId());
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(!empty($filter['customers']), fn($q) => $q->whereIn('jobs.customer_id', decodeIds($filter['customers'])))
            ->when(!empty($filter['type_of_clearance']), fn($q) => $q->where('job_clearances.type_of_clearance', $filter['type_of_clearance']))
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(function ($q) use ($s) {
                    $q->where('jobs.row_no', 'like', "%{$s}%")->orWhere('job_clearances.declaration_no', 'like', "%{$s}%")
                        ->orWhere('job_clearances.bayan_no', 'like', "%{$s}%")->orWhere('job_clearances.customs_broker', 'like', "%{$s}%")
                        ->orWhere('job_clearances.do_no', 'like', "%{$s}%")->orWhere('job_clearances.port_clearance', 'like', "%{$s}%")
                        ->orWhereIn('jobs.customer_id', Customer::where('name_en', 'like', "%{$s}%")->orWhere('name_ar', 'like', "%{$s}%")->pluck('id'));
                });
            });
    }

    private const STATUS_SQL = "COALESCE(NULLIF(job_clearances.clearance_status, ''), 'pending')";

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = $this->baseQuery()
            ->select('job_clearances.*', 'jobs.row_no as job_no', 'jobs.customer_id', 'jobs.shipment_mode')
            ->selectRaw(self::STATUS_SQL . ' as status_key')
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->whereRaw(self::STATUS_SQL . ' = ?', [$request->tab]))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('job_clearances.id');

        $counts = $this->applyFilters($this->baseQuery(), $filter)
            ->selectRaw(self::STATUS_SQL . ' as k, COUNT(*) as total')->groupBy('k')->pluck('total', 'k')->toArray();
        $all = ['all' => 0];
        foreach (self::TAB_KEYS as $k) {
            $all[$k] = (int) ($counts[$k] ?? 0);
            $all['all'] += $all[$k];
        }

        $duty = $this->applyFilters($this->baseQuery(), $filter)
            ->selectRaw('COALESCE(SUM(job_clearances.duty_amount),0) as ours, COALESCE(SUM(job_clearances.duty_amount_client),0) as client')->first();

        $customers = Customer::whereIn('id', $this->baseQuery()->pluck('jobs.customer_id')->filter()->unique())->get(['id', 'name_en', 'row_no'])->keyBy('id');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Clearance ' . htmlspecialchars((string) $m->job_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'customs-' . $m->id,
            ])
            ->addColumn('customer', fn($m) => $customers[$m->customer_id] ?? null)
            ->addColumn('duty_f', fn($m) => number_format((float) $m->duty_amount, decimals()))
            ->addColumn('duty_client_f', fn($m) => number_format((float) $m->duty_amount_client, decimals()))
            ->addColumn('clearance_date_f', fn($m) => $m->clearance_date)
            ->addColumn('do_date_f', fn($m) => $m->do_date)
            ->addColumn('bayan_date_f', fn($m) => $m->bayan_date)
            ->with([
                'statusCounts' => $all,
                'cards' => ['duty' => number_format((float) $duty->ours, decimals()), 'duty_client' => number_format((float) $duty->client, decimals())],
            ])
            ->toJson();
    }

    private function find($id): JobClearance
    {
        $c = $this->baseQuery()->select('job_clearances.*')->where('job_clearances.id', $id)->first();
        abort_unless($c, 404);

        return $c;
    }

    public function edit($id)
    {
        $clearance = $this->find($id);
        $job = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->find($clearance->job_id);

        return view('modules.customs.customs-form', compact('clearance', 'job'));
    }

    public function store(Request $request, $id)
    {
        $clearance = $this->find($id);

        $v = $request->validate([
            'clearance_status' => 'nullable|in:' . implode(',', array_keys(self::STATUSES)),
            'type_of_clearance' => 'nullable|string|max:50',
            'customs_broker' => 'nullable|string|max:255',
            'port_clearance' => 'nullable|string|max:255',
            'hs_code' => 'nullable|string|max:50',
            'declaration_no' => 'nullable|string|max:100',
            'bayan_no' => 'nullable|string|max:100',
            'do_no' => 'nullable|string|max:100',
            'doc_received' => 'nullable|date',
            'bl_receive_date' => 'nullable|date',
            'original_doc_received' => 'nullable|date',
            'saber_certificate_date' => 'nullable|date',
            'bayan_date' => 'nullable|date',
            'do_date' => 'nullable|date',
            'clearance_date' => 'nullable|date',
            'demurrage_date' => 'nullable|date',
            'duty_amount' => 'nullable|numeric|min:0',
            'duty_amount_client' => 'nullable|numeric|min:0',
            'clearance_remarks' => 'nullable|string|max:1000',
            'do_remarks' => 'nullable|string|max:1000',
        ]);

        foreach (['clearance_status', 'type_of_clearance', 'customs_broker', 'port_clearance', 'hs_code', 'declaration_no', 'bayan_no', 'do_no', 'clearance_remarks', 'do_remarks'] as $f) {
            $clearance->{$f} = $v[$f] ?? null;
        }
        foreach (['doc_received', 'bl_receive_date', 'original_doc_received', 'saber_certificate_date', 'bayan_date', 'do_date', 'clearance_date', 'demurrage_date'] as $f) {
            $clearance->{$f} = $v[$f] ?? null;
        }
        $clearance->duty_amount = $v['duty_amount'] ?? 0;
        $clearance->duty_amount_client = $v['duty_amount_client'] ?? 0;
        $clearance->lab_clearance = $request->boolean('lab_clearance');
        $clearance->inspection = $request->boolean('inspection');
        // Marking it cleared without a date: stamp today.
        if (($v['clearance_status'] ?? null) === 'cleared' && empty($v['clearance_date'])) {
            $clearance->clearance_date = now()->format('Y-m-d');
        }
        $clearance->save();

        return response()->json(['status' => 'success', 'message' => __('Clearance saved successfully')]);
    }

    public function actions($id)
    {
        $c = $this->find($id);
        $current = $c->clearance_status ?: 'pending';
        $icons = ['pending' => 'pending', 'under-process' => 'confirmed', 'cleared' => 'verified', 'on-hold' => 'blocked'];
        $items = [];
        foreach (self::STATUSES as $key => $label) {
            if ($key !== $current) {
                $items[] = ['label' => __($label), 'id' => 'row_status_' . $key, 'class' => 'row_status', 'data-id' => $c->id, 'data-value' => $key, 'icon' => $icons[$key]];
            }
        }

        return response()->json([
            ['label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to', 'items' => $items],
            ['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $c->id, 'type' => 'item', 'icon' => 'view'],
            ['label' => __('Update'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $c->id, 'type' => 'item', 'icon' => 'edit'],
        ]);
    }

    public function updateStatus($id, $status)
    {
        abort_unless(array_key_exists($status, self::STATUSES), 422);
        $c = $this->find($id);
        $c->clearance_status = $status;
        if ($status === 'cleared' && !$c->clearance_date) {
            $c->clearance_date = now()->format('Y-m-d');
        }
        $c->save();

        return response()->json(['status' => 'success', 'message' => __('Clearance status updated successfully!')]);
    }

    public function overview($id)
    {
        $clearance = $this->find($id);
        $job = Job::with('customer:id,name_en,row_no')->find($clearance->job_id);
        [$origin, $timeline] = $this->timeline($clearance, $job);

        return view('modules.customs.view-overview', compact('clearance', 'job', 'origin', 'timeline') + ['statuses' => self::STATUSES]);
    }

    /** Time frame: the job's origin, then every customs milestone that has a date, oldest first. */
    private function timeline(JobClearance $c, ?Job $job): array
    {
        $events = [];
        $rank = 0;
        $add = function ($label, $at, $icon, $module, $meta = null, $link = null) use (&$events, &$rank) {
            if (!$at) return;
            $ts = Carbon::parse($at);
            $events[] = ['label' => $label, 'at' => $ts->format('Y-m-d'), 'icon' => $icon, 'module' => $module, 'by' => null, 'meta' => $meta, 'link' => $link,
                'rank' => $rank, 'key' => $ts->timestamp, 'seq' => count($events), 'date_only' => true];
        };

        $quotation = $job && $job->quotation_id ? \App\Models\Quotation\Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? \App\Models\Enquiry\Enquiry::find($quotation->enquiry_id) : null;
        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', 'enquiry', null, ['enquiry', $enquiry->id, $enquiry->row_no]);
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', 'quotation', null, ['quotation', $quotation->id, $quotation->row_no]);
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', 'job', null, ['job', $job->id, $job->row_no]);

        $rank = 1;
        $origin = $job
            ? (($quotation ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' : '') . __('Job') . ' → ' . __('Customs Clearance'))
            : __('Customs Clearance');

        $add(__('Documents copy received'), $c->doc_received, 'bi-file-earmark-arrow-down', 'customs');
        $add(__('BL received'), $c->bl_receive_date, 'bi-file-earmark-text', 'customs');
        $add(__('Original documents received'), $c->original_doc_received, 'bi-file-earmark-check', 'customs');
        $add(__('Saber certificate'), $c->saber_certificate_date, 'bi-patch-check', 'customs');
        $add(__('Bayan filed'), $c->bayan_date, 'bi-clipboard-check', 'customs', $c->bayan_no ?: null);
        $add(__('Delivery order issued'), $c->do_date, 'bi-truck', 'customs', $c->do_no ?: null);
        $add(__('Cleared'), $c->clearance_date, 'bi-check-circle', 'customs');
        $add(__('Demurrage starts'), $c->demurrage_date, 'bi-exclamation-triangle', 'customs');

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }
}
