<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Applies the locale saved by the header's EN/AR toggle (routes/web.php's
     * locale/{locale} route) to every request, so app()->getLocale() and
     * __() resolve consistently across views without each controller having
     * to set it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if (in_array($locale, ['en', 'ar'], true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
