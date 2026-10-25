<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use App\Models\EmailQueue;
use App\Services\Mail\EmailQueueService;
use Illuminate\Http\Request;

/**
 * Admin view of email_queues: what was queued, whether it went out, and why it failed.
 * emails:process (every minute) sends pending rows and retries failed ones up to EmailQueueService::MAX_ATTEMPTS.
 * A failed email the admin skips becomes 'skipped': never retried and left out of the failed counts.
 */
class EmailQueueController extends Controller
{
    /** email_queues.type => readable name */
    public const TYPES = [
        'otp' => 'Verification code',
        'contact' => 'Contact form (to support)',
        'enquiry_reply' => 'Enquiry reply',
        'newsletter' => 'Newsletter',
        'quotation' => 'Quotation',
        'invoice' => 'Invoice',
        'welcome' => 'Welcome',
        'test' => 'Test email',
    ];

    public function index(Request $request)
    {
        $max = EmailQueueService::MAX_ATTEMPTS;
        $filters = $request->only(['q', 'status', 'type', 'from', 'to']);

        $stats = [
            'total' => EmailQueue::count(),
            'sent' => EmailQueue::where('status', 'sent')->count(),
            'pending' => EmailQueue::whereIn('status', ['pending', 'processing'])->count(),
            'retrying' => EmailQueue::where('status', 'failed')->where('attempts', '<', $max)->count(),
            'gave_up' => EmailQueue::where('status', 'failed')->where('attempts', '>=', $max)->count(),
            'skipped' => EmailQueue::where('status', 'skipped')->count(),
            'today' => EmailQueue::where('status', 'sent')->whereDate('sent_at', today())->count(),
        ];

        $emails = EmailQueue::query()
            ->when(trim($filters['q'] ?? '') !== '', function ($q) use ($filters) {
                $term = '%' . trim($filters['q']) . '%';
                $q->where(fn ($w) => $w->where('to', 'like', $term)->orWhere('subject', 'like', $term));
            })
            ->when($filters['status'] ?? null, fn ($q, $v) => match ($v) {
                'retrying' => $q->where('status', 'failed')->where('attempts', '<', $max),
                'gave_up' => $q->where('status', 'failed')->where('attempts', '>=', $max),
                'pending' => $q->whereIn('status', ['pending', 'processing']),
                default => $q->where('status', $v),
            })
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $types = EmailQueue::whereNotNull('type')->distinct()->orderBy('type')->pluck('type')
            ->mapWithKeys(fn ($t) => [$t => self::TYPES[$t] ?? ucfirst(str_replace('_', ' ', $t))]);

        return view('admin.email-queue.index', [
            'emails' => $emails,
            'stats' => $stats,
            'filters' => $filters,
            'types' => $types,
            'typeLabels' => self::TYPES,
            'maxAttempts' => $max,
        ]);
    }

    /** Details for the side panel (JSON). Sensitive emails (verification codes) never show their data. */
    public function show(EmailQueue $emailQueue)
    {
        return response()->json([
            'id' => $emailQueue->id,
            'to' => $emailQueue->to,
            'cc' => $emailQueue->cc,
            'from' => trim(($emailQueue->from_name ?? '') . ' ' . ($emailQueue->from_address ? '<' . $emailQueue->from_address . '>' : '')),
            'subject' => $emailQueue->subject,
            'type' => self::TYPES[$emailQueue->type] ?? ($emailQueue->type ?: $emailQueue->view),
            'status' => $emailQueue->status,
            'attempts' => $emailQueue->attempts,
            'created_at' => $emailQueue->created_at?->format('d-m-Y H:i:s'),
            'sent_at' => $emailQueue->sent_at?->format('d-m-Y H:i:s'),
            'failed_at' => $emailQueue->failed_at?->format('d-m-Y H:i:s'),
            'skipped_at' => $emailQueue->skipped_at?->format('d-m-Y H:i:s'),
            'error' => $emailQueue->error_message,
            'data' => $emailQueue->sensitive ? '(hidden: contains a verification code)' : $emailQueue->data_json,
        ]);
    }

    /** Put a failed email back in the queue; the every-minute job sends it again. */
    public function retry(EmailQueue $emailQueue)
    {
        abort_unless($emailQueue->status === 'failed', 422, 'Only failed emails can be retried.');
        if ($emailQueue->sensitive && empty($emailQueue->data_json)) {
            return back()->with('error', "Email #{$emailQueue->id} was a verification code and its content is gone — ask the user for a new code instead.");
        }

        $emailQueue->update(['status' => 'pending', 'attempts' => 0, 'available_at' => now(), 'failed_at' => null, 'skipped_at' => null, 'error_message' => null]);
        ActivityLog::log('updated', 'email_queue', $emailQueue->id, "Retried email #{$emailQueue->id} to {$emailQueue->to}");

        return back()->with('success', "Email #{$emailQueue->id} queued again — it will be sent within a minute.");
    }

    /** Dismiss a failed email for good: it stops retrying and no longer counts as failed. */
    public function skip(EmailQueue $emailQueue)
    {
        abort_unless($emailQueue->status === 'failed', 422, 'Only failed emails can be skipped.');

        // the error message stays so it is still clear why it was skipped
        $emailQueue->update(['status' => 'skipped', 'skipped_at' => now()]);
        ActivityLog::log('updated', 'email_queue', $emailQueue->id, "Skipped failed email #{$emailQueue->id} to {$emailQueue->to}");

        return back()->with('success', "Email #{$emailQueue->id} skipped — it won't be retried or counted as failed.");
    }
}
