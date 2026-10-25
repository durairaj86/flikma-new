<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Punch machines / vendor APIs set up in "pull" mode are polled every minute (each respects its own interval).
\Illuminate\Support\Facades\Schedule::command('attendance:pull')->everyMinute()->withoutOverlapping();

// Every email is stored in email_queues first; this sends what is waiting (and retries the ones that failed).
\Illuminate\Support\Facades\Schedule::command('emails:process')->everyMinute()->withoutOverlapping();
// Newsletters scheduled in /admin: queue them once their time comes (emails:process then sends them).
\Illuminate\Support\Facades\Schedule::call(function () {
    \App\Models\Admin\Newsletter::where('status', 'scheduled')->where('scheduled_at', '<=', now())->get()
        ->each(fn ($n) => \App\Http\Controllers\Admin\NewsletterController::queue($n, app(\App\Services\Mail\EmailQueueService::class)));
})->everyMinute()->name('newsletters:send-due')->withoutOverlapping();
// Finance redesign phase 1: keep the new ledger reporting columns filled until PostingService writes them (docs/finance-ledger-design.md)
\Illuminate\Support\Facades\Schedule::command('finance:sync-columns')->hourly()->withoutOverlapping();
// Finance redesign phase 2: keep finance_allocations current until PostingService writes them
\Illuminate\Support\Facades\Schedule::command('finance:sync-allocations')->hourly()->withoutOverlapping();
