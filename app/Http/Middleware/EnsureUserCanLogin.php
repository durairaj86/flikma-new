<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out a person whose access was withdrawn while they were already in (terminated, or login switched off): the login form
 * only stops new sign-ins, but a session or a "Remember me" cookie would otherwise keep working. An admin using "Login as" is
 * let through: they are acting for the company, and the admin panel has its own access rules.
 */
class EnsureUserCanLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !session('impersonating') && method_exists($user, 'canLogin') && !$user->canLogin()) {
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            $message = __('Your access has been withdrawn. Please contact your administrator.');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $message], 401);
            }

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
