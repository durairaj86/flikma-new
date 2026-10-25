<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // users.last_login: shown in the users list and counted on the admin dashboard
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            \Illuminate\Support\Facades\DB::table('users')->where('id', $event->user->getAuthIdentifier())->update(['last_login' => now()]);
        });

        // /log-viewer (System Logs in the admin panel) is for platform admins only
        \Illuminate\Support\Facades\Gate::define('viewLogViewer', fn ($user = null) => (bool) $user?->is_admin);

        // Every document that belongs to a job is mirrored into job_links (one common table), and the job's
        // summary (jobs.invoice_json) is refreshed from it.
        foreach (array_keys(\App\Services\Job\JobLinks::TYPES) as $model) {
            $model::saved(function ($m) {
                foreach (\App\Services\Job\JobLinks::sync($m) as $jobId) {
                    \App\Services\Job\JobInvoiceSummary::refresh($jobId);
                }
            });
            $model::deleted(function ($m) {
                foreach (\App\Services\Job\JobLinks::remove($m) as $jobId) {
                    \App\Services\Job\JobInvoiceSummary::refresh($jobId);
                }
            });
            if (method_exists($model, 'restored')) {
                $model::restored(function ($m) {
                    foreach (\App\Services\Job\JobLinks::sync($m) as $jobId) {
                        \App\Services\Job\JobInvoiceSummary::refresh($jobId);
                    }
                });
            }
        }

        // Once a person's email is verified (by code or by the old link), they get the welcome email, one time.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Verified::class, function ($event) {
            try {
                app(\App\Services\Mail\WelcomeEmail::class)->queue($event->user);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Could not queue the welcome email: ' . $e->getMessage());
            }
        });

        View::share('user', $this->getUser());
    }

    private function getUser()
    {
        $user = Auth::user();
        if ($user) {
            return $user;
        }
        
        return new class {
            public $name = 'Guest';
            public $email = '';
            public $profile_photo_path = null;
        };
    }
}
