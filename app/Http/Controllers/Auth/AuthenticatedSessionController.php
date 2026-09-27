<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // ✅ Get company_id from the logged-in user
        $companyId = Auth::user()->company_id ?? null;

        // ✅ Store in session
        if ($companyId) {
            session(['company_id' => $companyId]);
        }

        if (! Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $intended = session()->pull('url.intended');

        if ($intended && $this->isRoutableUrl($intended)) {
            return redirect()->to($intended);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Determine whether a stored intended URL still resolves to a real route.
     *
     * A stale url.intended (page removed, renamed, or never matched) would
     * otherwise send the user straight to a 404 page right after login.
     */
    protected function isRoutableUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host && ! hash_equals((string) request()->getHost(), $host)) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        if (parse_url($url, PHP_URL_QUERY) !== null && str_contains($url, '?')) {
            $path .= '?'.parse_url($url, PHP_URL_QUERY);
        }

        if (trim($path, '/') === trim(route('login', absolute: false), '/')) {
            return false;
        }

        try {
            app('router')->getRoutes()->match(
                \Illuminate\Http\Request::create($path, 'GET')
            );

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
