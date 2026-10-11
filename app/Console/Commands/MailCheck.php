<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/** Finds out why email is not going out: shows the settings in use, tests the login, and can send one test message. */
class MailCheck extends Command
{
    protected $signature = 'mail:check {--to= : Also send one test email to this address}';

    protected $description = 'Check the mail settings and the connection to the mail server';

    public function handle(): int
    {
        $default = (string) config('mail.default');
        $cfg = (array) config("mail.mailers.$default");
        $transport = $cfg['transport'] ?? '?';
        $ok = true;

        $this->line('App environment : ' . app()->environment());
        $this->line('Config cached   : ' . (app()->configurationIsCached() ? 'YES (changes in .env are ignored until: php artisan config:clear)' : 'no'));
        $this->line("Mailer in use   : $default (transport: $transport)");
        $this->line('From            : ' . config('mail.from.address') . ' / Flikma');

        // The email queue: emails are stored first and sent by the worker (php artisan emails:process, run every minute by the scheduler).
        if (\Illuminate\Support\Facades\Schema::hasTable('email_queues')) {
            $q = \App\Models\EmailQueue::query();
            $waiting = (clone $q)->whereIn('status', ['pending', 'failed'])->where('attempts', '<', \App\Services\Mail\EmailQueueService::MAX_ATTEMPTS);
            $oldest = (clone $waiting)->min('created_at');
            $this->line('Email queue     : ' . (clone $q)->where('status', 'sent')->count() . ' sent, ' . (clone $waiting)->count() . ' waiting, '
                . (clone $q)->where('status', 'failed')->where('attempts', '>=', \App\Services\Mail\EmailQueueService::MAX_ATTEMPTS)->count() . ' gave up');
            if ($oldest && \Illuminate\Support\Carbon::parse($oldest)->diffInMinutes(now()) >= 5) {
                $this->warn('Emails have been waiting for more than 5 minutes: the scheduler is probably not running. Add this cron job on the server: * * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1');
            }
            if ($last = (clone $q)->where('status', 'failed')->latest('failed_at')->first()) {
                $this->line('Last failure     : ' . $last->failed_at?->diffForHumans() . ' - ' . \Illuminate\Support\Str::limit((string) $last->error_message, 120));
            }
        }

        if ($transport === 'log' || $transport === 'array') {
            $this->error("The mailer is '$default', so emails are NOT sent, only written to the log.");
            $this->line('Set MAIL_MAILER=smtp (the old MAIL_DRIVER name is ignored) and run: php artisan config:clear');

            return self::FAILURE;
        }

        if ($transport === 'smtp') {
            $port = (int) ($cfg['port'] ?? 587);
            $this->line('SMTP server     : ' . ($cfg['host'] ?? '?') . ":$port, username " . (empty($cfg['username']) ? 'NOT SET' : 'set') . ', password ' . (empty($cfg['password']) ? 'NOT SET' : 'set'));
            // 1) can this server reach the mail server at all?
            $sock = @fsockopen($cfg['host'] ?? '', $port, $errno, $errstr, 10);
            if (!$sock) {
                $this->error("Cannot connect to {$cfg['host']}:$port ($errstr). The hosting provider may block outgoing mail ports; ask them to open port $port, or use a mail API.");

                return self::FAILURE;
            }
            fclose($sock);
            $this->info('Connection       : reached the mail server');
            // 2) do the username and password work?
            try {
                $t = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport($cfg['host'], $port, $port === 465);
                $t->setUsername((string) ($cfg['username'] ?? ''));
                $t->setPassword((string) ($cfg['password'] ?? ''));
                $t->start();
                $t->stop();
                $this->info('Login            : OK (nothing was sent)');
            } catch (\Throwable $e) {
                $ok = false;
                $this->error('Login failed     : ' . $e->getMessage());
                $this->line('For Gmail use an App Password (Google account > Security > 2-Step Verification > App passwords), not the normal password.');
            }
        }

        if ($transport === 'mailersend') {
            $key = config('mailersend-driver.api_key') ?: env('MAILERSEND_API_KEY');
            $ch = curl_init('https://api.mailersend.com/v1/domains');
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key, 'Accept: application/json'], CURLOPT_TIMEOUT => 20]);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $code === 200 ? $this->info('API key          : OK') : ($ok = false) . $this->error("API key rejected (HTTP $code). Create a new API token in MailerSend.");
        }

        if ($to = $this->option('to')) {
            try {
                Mail::raw('This is a test email from Flikma. If you can read this, email sending works.', fn($m) => $m->to($to)->subject('Flikma test email'));
                $this->info("Test email sent to $to (check Spam too).");
            } catch (\Throwable $e) {
                $ok = false;
                $this->error('Sending failed   : ' . $e->getMessage());
            }
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
