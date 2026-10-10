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
        // Keep jobs.invoice_json (counts of supplier invoices, customer invoices and credit notes) in step.
        foreach ([
            \App\Models\Finance\SupplierInvoice\SupplierInvoice::class,
            \App\Models\Finance\CustomerInvoice\CustomerInvoice::class,
            \App\Models\Finance\Adjustment\CreditNote::class,
        ] as $model) {
            $refresh = function ($m) {
                \App\Services\Job\JobInvoiceSummary::refresh($m->job_id ? (int) $m->job_id : null);
                $old = $m->getOriginal('job_id');
                if ($old && (int) $old !== (int) $m->job_id) {
                    \App\Services\Job\JobInvoiceSummary::refresh((int) $old);
                }
            };
            $model::saved($refresh);
            $model::deleted($refresh);
            if (method_exists($model, 'restored')) {
                $model::restored($refresh);
            }
        }

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
