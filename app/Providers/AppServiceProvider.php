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
