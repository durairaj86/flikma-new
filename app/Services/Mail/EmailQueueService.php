<?php

namespace App\Services\Mail;

use App\Mail\DynamicMail;
use App\Models\EmailQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The one way the app sends email: store it in email_queues, then send it.
 *   enqueue()      store only; the scheduled worker (php artisan emails:process) sends it within a minute.
 *   sendNow()      send one stored email right away (a code the person is waiting for).
 *   sendPending()  the worker: sends what is due, retrying failures a few times with a growing delay.
 */
class EmailQueueService
{
    public const MAX_ATTEMPTS = 3;
    /** Minutes to wait before the 2nd and 3rd try. */
    public const RETRY_DELAYS = [1 => 1, 2 => 5];

    /**
     * Store an email.
     * options: type, cc, from_address, from_name, attachments [[path,name,mime]], sensitive (bool), expires_at, send_after, company_id
     */
    public function enqueue(string $to, string $subject, string $view, array $data = [], array $options = []): EmailQueue
    {
        return EmailQueue::create([
            'company_id' => $options['company_id'] ?? (function_exists('companyId') ? (companyId() ?: null) : null),
            'type' => $options['type'] ?? null,
            'to' => $to,
            'cc' => $options['cc'] ?? null,
            'from_address' => $options['from_address'] ?? null,
            'from_name' => $options['from_name'] ?? null,
            'subject' => $subject,
            'view' => $view,
            'data_json' => $data,
            'attachments_json' => $options['attachments'] ?? null,
            'sensitive' => (bool) ($options['sensitive'] ?? false),
            'status' => 'pending',
            'available_at' => $options['send_after'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    /** Take the email for sending, so two workers never send the same one. Returns false when someone else has it. */
    private function claim(EmailQueue $e): bool
    {
        return (bool) DB::table('email_queues')->where('id', $e->id)->whereIn('status', ['pending', 'failed'])
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->update(['status' => 'processing', 'attempts' => DB::raw('attempts + 1'), 'updated_at' => now()]);
    }

    /**
     * Send one stored email now. Returns true when sent.
     * $retry = false: a failure is final (used for codes: the person asks for a new one instead).
     */
    public function sendNow(EmailQueue $email, bool $retry = true): bool
    {
        if ($email->expires_at && $email->expires_at->isPast()) {
            $this->finishFailed($email, __('Not sent: it expired before it could be sent.'), true);

            return false;
        }
        if (!$this->claim($email)) {
            return false;
        }
        $email->refresh();

        try {
            Mail::to($email->to)->when($email->cc, fn($m) => $m->cc(array_map('trim', explode(',', $email->cc))))
                ->sendNow(new DynamicMail($email->subject, $email->view, $email->data_json ?? [], $email->attachments_json ?? [], $email->from_address, $email->from_name));
        } catch (\Throwable $e) {
            $final = !$retry || $email->attempts >= self::MAX_ATTEMPTS;
            $this->finishFailed($email, $e->getMessage(), $final);
            Log::error("Email #{$email->id} ({$email->type}) to {$email->to} failed (attempt {$email->attempts}): " . $e->getMessage());

            return false;
        }

        $email->update([
            'status' => 'sent', 'sent_at' => now(), 'error_message' => null, 'failed_at' => null,
            // A code must not stay in the database once it has been delivered.
            'data_json' => $email->sensitive ? null : $email->data_json,
        ]);

        return true;
    }

    private function finishFailed(EmailQueue $email, string $message, bool $final): void
    {
        $delay = self::RETRY_DELAYS[$email->attempts] ?? 15;
        $email->update([
            'status' => 'failed', 'failed_at' => now(), 'error_message' => mb_substr($message, 0, 1000),
            'attempts' => $final ? max($email->attempts, self::MAX_ATTEMPTS) : $email->attempts,
            'available_at' => $final ? null : now()->addMinutes($delay),
            'data_json' => ($final && $email->sensitive) ? null : $email->data_json,
        ]);
    }

    /** The worker: send what is due. Returns how many were sent. */
    public function sendPending(int $limit = 100): int
    {
        // An email left "processing" by a crashed worker is put back so it is not lost.
        DB::table('email_queues')->where('status', 'processing')->where('updated_at', '<', now()->subMinutes(10))
            ->update(['status' => 'failed', 'available_at' => now()]);

        $due = EmailQueue::whereIn('status', ['pending', 'failed'])->where('attempts', '<', self::MAX_ATTEMPTS)
            ->where(fn($q) => $q->whereNull('available_at')->orWhere('available_at', '<=', now()))
            ->orderBy('id')->limit($limit)->get();

        $sent = 0;
        foreach ($due as $email) {
            $sent += $this->sendNow($email) ? 1 : 0;
        }

        return $sent;
    }
}
