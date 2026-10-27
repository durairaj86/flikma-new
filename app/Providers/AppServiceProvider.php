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
            if (session('impersonating')) return;   // an admin using "Login as" is not the user signing in
            \Illuminate\Support\Facades\DB::table('users')->where('id', $event->user->getAuthIdentifier())->update(['last_login' => now()]);
            // One sign-in at a time: this sign-in becomes the only valid one. Its token is saved on the user and in this device's
            // session (and a cookie, so "Remember me" on this same device is recognised later); every other device holds an older
            // token and is signed out on its next request (App\Http\Middleware\EnsureSingleSession). Only an interactive sign-in
            // counts: a "Remember me" cookie reviving an old session must not take over from the device the person is using now.
            if (request()->isMethod('post') && request()->is('login', 'admin/login', 'register')) {
                try {
                    $token = \Illuminate\Support\Str::random(40);
                    \Illuminate\Support\Facades\DB::table('users')->where('id', $event->user->getAuthIdentifier())->update(['login_token' => $token]);
                    session(['login_token' => $token]);
                    \Illuminate\Support\Facades\Cookie::queue('login_token', $token, 60 * 24 * 30);
                } catch (\Throwable $e) {
                    // an older database without the column: sign-in still works, it is just not limited to one device yet
                    \Illuminate\Support\Facades\Log::warning('Single sign-in token not saved: ' . $e->getMessage());
                }
            }
            \Illuminate\Support\Facades\DB::table('login_histories')->insert([
                'user_id' => $event->user->getAuthIdentifier(), 'company_id' => $event->user->company_id ?? null,
                'ip_address' => request()->ip(), 'user_agent' => (string) request()->userAgent(),
                'logged_in_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
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
