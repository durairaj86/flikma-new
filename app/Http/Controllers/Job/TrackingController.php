<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\Job\Job;
use App\Models\Job\JobMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Shipment tracking: a milestone line per job, updated by staff and shareable with the customer. */
class TrackingController extends Controller
{
    /** Milestones for each kind of shipment, in order. */
    public static function template(?string $mode): array
    {
        $mode = strtolower((string)$mode);
        if (in_array($mode, ['sea', 'ocean'])) {
            return ['booked' => 'Booking confirmed', 'docs_received' => 'Documents received', 'cargo_received' => 'Cargo received', 'loaded' => 'Loaded on vessel',
                'departed' => 'Vessel departed', 'arrived' => 'Vessel arrived', 'customs' => 'Customs cleared', 'do_released' => 'D/O released', 'delivered' => 'Delivered'];
        }
        if ($mode === 'air') {
            return ['booked' => 'Booking confirmed', 'docs_received' => 'Documents received', 'cargo_received' => 'Cargo received', 'departed' => 'Flight departed',
                'arrived' => 'Flight arrived', 'customs' => 'Customs cleared', 'do_released' => 'D/O released', 'delivered' => 'Delivered'];
        }

        return ['booked' => 'Booking confirmed', 'docs_received' => 'Documents received', 'picked_up' => 'Picked up', 'in_transit' => 'In transit',
            'customs' => 'Customs cleared', 'delivered' => 'Delivered'];
    }

    /** Job fields that already hold the date of a step (filled in the job form or the customs module). */
    private static function fromJob(Job $job, string $code)
    {
        $v = match ($code) {
            'docs_received' => $job->doc_received,
            'departed', 'in_transit' => $job->atd,
            'arrived' => $job->ata,
            'do_released' => $job->do_date,
            'customs' => $job->clearance?->clearance_status === 'cleared' ? ($job->clearance->clearance_date ?: $job->clearance->updated_at) : null,
            default => null,
        };

        return \App\Services\Job\JobInsights::date($v);
    }

    /** Write a step's date back to the job so every screen agrees. */
    private static function syncJob(Job $job, string $code, $date): void
    {
        $col = ['docs_received' => 'doc_received', 'departed' => 'atd', 'in_transit' => 'atd', 'arrived' => 'ata', 'do_released' => 'do_date'][$code] ?? null;
        if ($col) {
            $job->$col = $date ? \Illuminate\Support\Carbon::parse($date)->toDateString() : null;
        }
    }

    /** Template merged with what has been saved for the job (dates already on the job count as done). */
    public static function steps(Job $job, $saved = null): array
    {
        $saved = ($saved ?? JobMilestone::where('job_id', $job->id)->get())->keyBy('code');
        $steps = [];
        foreach (self::template($job->shipment_mode) as $code => $label) {
            $m = $saved->get($code);
            $fromJob = self::fromJob($job, $code);
            // Dates that also live on the job (docs received, ATD, ATA, D/O) always follow the job, so both screens show the same day.
            $synced = in_array($code, ['docs_received', 'departed', 'in_transit', 'arrived', 'do_released']);
            $actual = $synced ? ($fromJob ?: $m?->actual_at) : ($m?->actual_at ?: $fromJob);
            $skipped = !$actual && (bool) $m?->skipped;
            $steps[] = ['code' => $code, 'label' => $label, 'actual' => $actual, 'skipped' => $skipped, 'auto' => $actual && !$m?->actual_at, 'location' => $m?->location, 'remarks' => $m?->remarks];
        }

        return $steps;
    }

    public function index(Request $request)
    {
        $jobs = Job::with('customer:id,name_en', 'clearance:id,job_id,clearance_status,clearance_date,updated_at')
            ->when($request->show !== 'all', fn($q) => $q->where('status', 1))
            ->when($request->q, function ($q, $s) {
                $q->where(function ($q) use ($s) {
                    $q->where('row_no', 'like', "%{$s}%")->orWhere('hbl_number', 'like', "%{$s}%")
                        ->orWhere('awb_number', 'like', "%{$s}%")->orWhere('container_no', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn($c) => $c->where('name_en', 'like', "%{$s}%"));
                });
            })
            ->orderByDesc('id')->limit(100)->get();

        $saved = JobMilestone::whereIn('job_id', $jobs->pluck('id'))->get()->groupBy('job_id');
        $rows = $jobs->map(function ($job) use ($saved) {
            $steps = self::steps($job, $saved->get($job->id, collect()));
            $done = collect($steps)->whereNotNull('actual')->count();
            $current = collect($steps)->first(fn($s) => !$s['actual'] && !$s['skipped']);

            return ['job' => $job, 'steps' => $steps, 'done' => $done, 'skipped' => collect($steps)->where('skipped', true)->count(), 'total' => count($steps), 'next' => $current['label'] ?? null];
        });

        return view('modules.tracking.index', ['rows' => $rows, 'q' => $request->q, 'show' => $request->show]);
    }

    public function edit($id)
    {
        $job = Job::with('clearance')->findOrFail($id);

        return view('modules.tracking.form', ['job' => $job, 'steps' => self::steps($job)]);
    }

    public function save(Request $request, $id)
    {
        $job = Job::findOrFail($id);
        $template = self::template($job->shipment_mode);
        $sort = 0;
        foreach ($template as $code => $label) {
            $sort++;
            $date = $request->input("date.$code");
            $loc = $request->input("location.$code");
            $skip = !$date && $request->boolean("skipped.$code");
            $row = JobMilestone::firstOrNew(['job_id' => $job->id, 'code' => $code]);
            if (!$date && !$loc && !$skip && !$row->exists) {
                continue;
            }
            $row->fill(['label' => $label, 'sort' => $sort, 'location' => $loc, 'user_id' => auth()->id(), 'skipped' => $skip,
                'actual_at' => $date ? formDate($date) : null])->save();
        }
        // Dates also go back to the job (documents received, ATD, ATA, D/O) so the job form shows the same.
        $job->timestamps = false;
        foreach ($template as $code => $label) {
            self::syncJob($job, $code, $request->input("date.$code") ? formDate($request->input("date.$code")) : null);
        }
        $job->save();

        return response()->json(['status' => 'success', 'message' => __('Tracking updated')]);
    }

    /** One click: mark the next open step as done today. */
    public function advance($id)
    {
        $job = Job::with('milestones', 'clearance')->findOrFail($id);
        $next = collect(self::steps($job, $job->milestones))->first(fn($s) => !$s['actual'] && !$s['skipped']);
        if (!$next) {
            return response()->json(['status' => 'error', 'message' => __('All steps are already done.')], 422);
        }
        $sort = array_search($next['code'], array_keys(self::template($job->shipment_mode))) + 1;
        JobMilestone::updateOrCreate(['job_id' => $job->id, 'code' => $next['code']],
            ['label' => $next['label'], 'sort' => $sort, 'actual_at' => today(), 'user_id' => auth()->id()]);
        $job->timestamps = false;
        self::syncJob($job, $next['code'], today());
        $job->save();

        return response()->json(['status' => 'success', 'message' => __($next['label']) . ' ✓']);
    }

    /** Skip the next open step (it is not needed for this job). */
    public function skipNext($id)
    {
        $job = Job::with('milestones', 'clearance')->findOrFail($id);
        $next = collect(self::steps($job, $job->milestones))->first(fn($s) => !$s['actual'] && !$s['skipped']);
        if (!$next) {
            return response()->json(['status' => 'error', 'message' => __('Nothing left to skip.')], 422);
        }
        $sort = array_search($next['code'], array_keys(self::template($job->shipment_mode))) + 1;
        JobMilestone::updateOrCreate(['job_id' => $job->id, 'code' => $next['code']],
            ['label' => $next['label'], 'sort' => $sort, 'skipped' => true, 'actual_at' => null, 'user_id' => auth()->id()]);

        return response()->json(['status' => 'success', 'message' => __($next['label']) . ' - ' . __('skipped')]);
    }

    /** Create (or return) the customer-facing link. */
    public function share($id)
    {
        $job = Job::findOrFail($id);
        if (!$job->tracking_token) {
            $job->timestamps = false;
            $job->tracking_token = Str::random(32);
            $job->save();
        }

        return response()->json(['status' => true, 'url' => url('/track/' . $job->tracking_token)]);
    }

    /** Public page: no login, only this one shipment, no money. */
    public function publicShow($token)
    {
        $job = Job::withoutGlobalScopes()->with('clearance')->where('tracking_token', $token)->firstOrFail();
        $saved = JobMilestone::withoutGlobalScopes()->where('job_id', $job->id)->get();

        return view('modules.tracking.public', ['job' => $job, 'steps' => self::steps($job, $saved)]);
    }
}
