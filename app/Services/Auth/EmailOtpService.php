<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Email verification by one-time code.
 *  - a 6 digit code is emailed and is valid for 5 minutes;
 *  - it works one time only (used_at is set the moment it is accepted);
 *  - only a hash is stored; 5 wrong tries cancel the code; a new code can be asked for after 60 seconds, at most 5 an hour.
 */
class EmailOtpService
{
    public const TTL_MINUTES = 5;
    public const MAX_ATTEMPTS = 5;
    public const RESEND_SECONDS = 60;
    public const MAX_PER_HOUR = 5;

    private const TABLE = 'email_verification_otps';

    private function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /** The newest code of this user that is still open (not used, not expired, not cancelled). */
    private function active(User $user)
    {
        return DB::table(self::TABLE)->where('user_id', $user->id)->whereNull('used_at')
            ->where('expires_at', '>', now())->where('attempts', '<', self::MAX_ATTEMPTS)->orderByDesc('id')->first();
    }

    /** What the page needs to show: seconds left on the code, seconds until a new one can be asked for. */
    public function state(User $user): array
    {
        $active = $this->active($user);
        $last = DB::table(self::TABLE)->where('user_id', $user->id)->orderByDesc('id')->first();

        return [
            'active' => (bool) $active,
            'expires_in' => $active ? max(0, (int) now()->diffInSeconds(Carbon::parse($active->expires_at), false)) : 0,
            'attempts_left' => $active ? self::MAX_ATTEMPTS - $active->attempts : 0,
            'resend_in' => $last ? max(0, self::RESEND_SECONDS - (int) Carbon::parse($last->created_at)->diffInSeconds(now())) : 0,
        ];
    }

    /**
     * Create and email a new code (to the user's own address, or to $deliverTo when an administrator sends it elsewhere).
     * Returns null when sent, or a message when it may not be sent yet.
     * $final = false (the automatic first send when the page opens): a failed send is NOT withdrawn; the queue worker keeps
     * trying while the code is valid, and the page shows a gentle notice instead of an error.
     * Mail failures are thrown so the caller can tell the person.
     */
    public function send(User $user, ?string $deliverTo = null, bool $final = true): ?string
    {
        $state = $this->state($user);
        if ($state['resend_in'] > 0) {
            return __('Please wait :s seconds before asking for a new code.', ['s' => $state['resend_in']]);
        }
        $lastHour = DB::table(self::TABLE)->where('user_id', $user->id)->where('created_at', '>=', now()->subHour())->count();
        if ($lastHour >= self::MAX_PER_HOUR) {
            return __('Too many codes were requested. Please try again in an hour.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $id = DB::transaction(function () use ($user, $code) {
            // Only the newest code can be used: close any earlier one.
            DB::table(self::TABLE)->where('user_id', $user->id)->whereNull('used_at')->update(['used_at' => now()]);

            return DB::table(self::TABLE)->insertGetId([
                'user_id' => $user->id, 'code_hash' => $this->hash($code), 'expires_at' => now()->addMinutes(self::TTL_MINUTES),
                'attempts' => 0, 'ip' => request()->ip(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        });

        // The email is stored in the queue table and sent at once (the person is waiting). If it cannot be sent the
        // code is withdrawn, so they can ask for a new one; the queue row keeps the reason.
        $queue = app(\App\Services\Mail\EmailQueueService::class);
        $to = $deliverTo ?: $user->email;
        $mail = $queue->enqueue($to, 'Your Flikma verification code', 'emails.auth.verification-otp',
            ['userName' => $user->name, 'userEmail' => $to, 'code' => $code, 'minutes' => self::TTL_MINUTES],
            ['type' => 'otp', 'sensitive' => true, 'expires_at' => now()->addMinutes(self::TTL_MINUTES), 'company_id' => $user->company_id]);

        if (!$queue->sendNow($mail, !$final)) {
            if (!$final) {
                return null; // retried by the worker; see delivery()
            }
            $reason = (string) $mail->fresh()->error_message;
            if (app()->environment('local')) {
                // Development: the mail provider may not be set up. Keep the code usable and write it to the log, so the page can still be tried.
                \Illuminate\Support\Facades\Log::warning("[DEV] Verification code for {$user->email}: {$code} (email not sent: {$reason})");

                return null;
            }
            DB::table(self::TABLE)->where('id', $id)->delete();
            throw new \RuntimeException($reason ?: 'The email could not be sent.');
        }

        return null;
    }

    /** How the newest code email of this person is doing: sent | pending | failed | null (none). */
    public function delivery(User $user): ?string
    {
        $row = \App\Models\EmailQueue::where('type', 'otp')->where(fn($q) => $q->where('to', $user->email)->orWhere('created_by', $user->id))
            ->orderByDesc('id')->first();

        return $row?->status === 'processing' ? 'pending' : $row?->status;
    }

    /** @return string ok | none | expired | locked | invalid */
    public function verify(User $user, string $code): string
    {
        return DB::transaction(function () use ($user, $code) {
            $row = DB::table(self::TABLE)->where('user_id', $user->id)->whereNull('used_at')->orderByDesc('id')->lockForUpdate()->first();
            if (!$row) {
                return 'none';
            }
            if (Carbon::parse($row->expires_at)->isPast()) {
                return 'expired';
            }
            if ($row->attempts >= self::MAX_ATTEMPTS) {
                return 'locked';
            }
            if (!hash_equals($row->code_hash, $this->hash($code))) {
                DB::table(self::TABLE)->where('id', $row->id)->update(['attempts' => $row->attempts + 1, 'updated_at' => now()]);

                return $row->attempts + 1 >= self::MAX_ATTEMPTS ? 'locked' : 'invalid';
            }
            // Accepted: burn the code first, then mark the email as verified.
            DB::table(self::TABLE)->where('id', $row->id)->update(['used_at' => now(), 'updated_at' => now()]);
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return 'ok';
        });
    }

    /** a***@gmail.com */
    public static function mask(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($name, 0, 1) . str_repeat('*', max(2, mb_strlen($name) - 1)) . '@' . $domain;
    }
}
