<?php

namespace App\Services\Job;

use App\Http\Controllers\Job\TrackingController;
use App\Models\Job\Job;
use Illuminate\Support\Carbon;

/** Rule-based reading of the open jobs: what is late, what is arriving, what is not billed. */
class JobInsights
{
    const SOON_DAYS = 3;
    const WEEK_DAYS = 7;

    public static function date($value): ?Carbon
    {
        if (!filled($value)) {
            return null;
        }
        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /** Where one pending job stands against its ETA. */
    public static function health(Job $j): array
    {
        $eta = self::date($j->eta);
        // Same rule as the tracking steps, so the badge and the tracking line never disagree.
        $step = collect(TrackingController::steps($j, $j->milestones))->firstWhere('code', 'arrived');
        $arrived = $step ? (bool) $step['actual'] : filled($j->ata);
        if ($arrived) {
            return ['state' => 'arrived', 'label' => 'Arrived', 'days' => null];
        }
        if (!$eta) {
            return ['state' => 'noeta', 'label' => 'No ETA', 'days' => null];
        }
        $d = today()->diffInDays($eta, false);
        if ($d < 0) {
            return ['state' => 'delayed', 'label' => __(':nd late', ['n' => abs($d)]), 'days' => $d];
        }
        if ($d <= self::SOON_DAYS) {
            return ['state' => 'soon', 'label' => $d === 0 ? 'Today' : "in {$d}d", 'days' => $d];
        }

        return ['state' => 'ontime', 'label' => "in {$d}d", 'days' => $d];
    }

    public static function billing(Job $j): string
    {
        if ($j->invoices->where('status', 3)->isNotEmpty()) {
            return 'approved';
        }

        return $j->invoices->isNotEmpty() ? 'draft' : 'none';
    }

    public static function progress(Job $j): array
    {
        $steps = TrackingController::steps($j, $j->milestones);
        $done = collect($steps)->whereNotNull('actual')->count();
        $skipped = collect($steps)->where('skipped', true)->count();
        $next = collect($steps)->first(fn($s) => !$s['actual'] && !$s['skipped']);
        $last = collect($steps)->filter(fn($s) => $s['actual'])->last();
        $total = count($steps);

        return [
            'done' => $done, 'skipped' => $skipped, 'total' => $total,
            // A skipped step counts as passed, so the job can still reach 100%.
            'pct' => $total ? round(($done + $skipped) / $total * 100) : 0,
            'next' => $next['label'] ?? null,
            'current' => $last['label'] ?? null,
            'segments' => array_map(fn($s) => $s['actual'] ? 'done' : ($s['skipped'] ? 'skip' : 'todo'), $steps),
        ];
    }

    public static function inClearance(Job $j): bool
    {
        return in_array(strtolower((string)$j->clearance?->clearance_status), ['pending', 'under-process', 'under process', 'in-progress']);
    }

    public static function pending()
    {
        return Job::with('customer:id,name_en', 'invoices:id,job_id,status', 'clearance:id,job_id,clearance_status,clearance_date,updated_at', 'milestones')
            ->where('status', 1)->orderByDesc('id')->limit(500)->get();
    }

    /** Job ids for each quick filter. */
    public static function buckets($jobs = null): array
    {
        $jobs ??= self::pending();
        $b = ['delayed' => [], 'arriving' => [], 'noeta' => [], 'unbilled' => [], 'clearance' => []];
        foreach ($jobs as $j) {
            $h = self::health($j)['state'];
            if ($h === 'delayed') $b['delayed'][] = $j->id;
            if ($h === 'soon' || ($h === 'ontime' && self::health($j)['days'] <= self::WEEK_DAYS)) $b['arriving'][] = $j->id;
            if ($h === 'noeta') $b['noeta'][] = $j->id;
            if (self::billing($j) === 'none') $b['unbilled'][] = $j->id;
            if (self::inClearance($j)) $b['clearance'][] = $j->id;
        }

        return $b;
    }

    public static function snapshot(): array
    {
        $jobs = self::pending();
        $b = self::buckets($jobs);
        $attention = $jobs->filter(fn($j) => in_array($j->id, $b['delayed']))->take(4)->map(fn($j) => [
            'id' => $j->id, 'no' => $j->row_no, 'customer' => $j->customer?->name_en, 'label' => self::health($j)['label'],
        ])->values();

        return ['active' => $jobs->count(), 'counts' => array_map('count', $b), 'attention' => $attention];
    }

    /** Compact text the AI assistant reads. */
    public static function context(int $limit = 80): string
    {
        $lines = [];
        foreach (self::pending()->take($limit) as $j) {
            $h = self::health($j);
            $p = self::progress($j);
            $lines[] = implode(' | ', array_filter([
                $j->row_no ?: 'Job #' . $j->id, $j->customer?->name_en, ucfirst((string)$j->shipment_mode),
                trim(($j->pol_name ?: $j->origin) . ' > ' . ($j->pod_name ?: $j->destination), ' >'),
                $j->carrier, 'ETD ' . ($j->etd ?: '-'), 'ETA ' . ($j->eta ?: '-'),
                'health: ' . $h['state'] . ($h['days'] !== null ? " ({$h['label']})" : ''),
                "progress {$p['done']}/{$p['total']}" . ($p['next'] ? ", next: {$p['next']}" : ''),
                'billing: ' . self::billing($j),
                self::inClearance($j) ? 'in customs clearance' : null,
            ]));
        }

        return implode("\n", $lines);
    }
}
