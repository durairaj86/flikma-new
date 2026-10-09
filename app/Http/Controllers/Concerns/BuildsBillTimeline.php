<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Enquiry\Enquiry;
use App\Models\Job\Job;
use App\Models\Log\LogHistory;
use App\Models\Quotation\Quotation;
use Illuminate\Support\Carbon;

/**
 * Time frame for the bill drawers (waybill / airway bill / seaway bill): how the job came about
 * (enquiry -> quotation -> job), then the bill's own life (created / updated / status changes).
 */
trait BuildsBillTimeline
{
    protected function billTimeline($bill, string $billLabel): array
    {
        $events = [];
        $rank = 0;
        $add = function ($label, $at, $icon, $module, $by = null, $meta = null, $link = null) use (&$events, &$rank) {
            if (!$at) return;
            $events[] = ['label' => $label, 'at' => $at, 'icon' => $icon, 'module' => $module, 'by' => $by, 'meta' => $meta, 'link' => $link,
                'rank' => $rank, 'key' => Carbon::parse($at)->timestamp, 'seq' => count($events)];
        };

        $job = $bill->job_id ? Job::find($bill->job_id) : null;
        $quotation = $job && $job->quotation_id ? Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? Enquiry::find($quotation->enquiry_id) : null;

        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', 'enquiry', null, null, ['enquiry', $enquiry->id, $enquiry->row_no]);
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', 'quotation', null, null, ['quotation', $quotation->id, $quotation->row_no]);
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', 'job', null, null, ['job', $job->id, $job->row_no]);

        $rank = 1;
        $origin = $quotation
            ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' . __('Job') . ' → ' . $billLabel
            : ($job ? __('Job') . ' → ' . $billLabel : $billLabel . ' ' . __('created directly'));

        $icons = ['delivered' => 'bi-check-circle', 'in_transit' => 'bi-truck', 'cancelled' => 'bi-x-circle', 'pending' => 'bi-clock'];
        $ignore = ['status', 'updated_at'];
        $seenCreate = false;
        $logs = LogHistory::where('loggable_type', get_class($bill))
            ->where('loggable_id', $bill->id)
            ->where('created_at', '>=', $bill->created_at->copy()->subSeconds(5))
            ->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seenCreate = true;
                $add($billLabel . ' ' . __('created') . ' · ' . $bill->row_no, $log->created_at, 'bi-file-earmark-richtext', 'bill', $by);
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (string) $new['status'];
                    $add(__(ucfirst(str_replace('_', ' ', $st))), $log->created_at, $icons[$st] ?? 'bi-clock', 'bill', $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add($billLabel . ' ' . __('updated'), $log->created_at, 'bi-pencil-square', 'bill', $by);
                }
            }
        }
        if (!$seenCreate) $add($billLabel . ' ' . __('created') . ' · ' . $bill->row_no, $bill->created_at, 'bi-file-earmark-richtext', 'bill');

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }
}
