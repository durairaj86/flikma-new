<?php

namespace App\Console\Commands;

use App\Models\EmailQueue;
use App\Services\Mail\EmailQueueService;
use Illuminate\Console\Command;

class ProcessEmailQueue extends Command
{
    protected $signature = 'emails:process {--limit=100 : Most emails to send in one run} {--retry-failed : Give emails that gave up a fresh set of tries}';

    protected $description = 'Send the emails waiting in the email queue';

    public function handle(EmailQueueService $queue): int
    {
        if ($this->option('retry-failed')) {
            $n = EmailQueue::where('status', 'failed')->where('attempts', '>=', EmailQueueService::MAX_ATTEMPTS)->where('sensitive', false)
                ->update(['attempts' => 0, 'available_at' => null]);
            $this->line("$n failed emails will be tried again.");
        }
        $sent = $queue->sendPending((int) $this->option('limit'));
        $left = EmailQueue::whereIn('status', ['pending', 'failed'])->where('attempts', '<', EmailQueueService::MAX_ATTEMPTS)->count();
        $this->info("Sent $sent email(s). Still waiting: $left. Gave up: " . EmailQueue::where('status', 'failed')->where('attempts', '>=', EmailQueueService::MAX_ATTEMPTS)->count() . '.');

        return self::SUCCESS;
    }
}
