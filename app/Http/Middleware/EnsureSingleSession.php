<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * One person, one sign-in. Every interactive sign-in stores a new token on the user and in that device's session (see the Login
 * listener in AppServiceProvider). A request whose session holds an older token comes from a device that was signed in before the
 * latest one, so it is signed out here.
 *
 *  - session token differs from the user's token  -> signed in somewhere else since: signed out.
 *  - session has no token (a "Remember me" cookie reviving the session, or a session from before this existed): allowed only when
 *    the device's own token cookie is the current one, and then it takes over the token; otherwise signed out. A user nobody has
 *    signed in as since the column was added (token NULL) is not limited yet.
 *  - admin "Login as" is never limited: the session belongs to the admin.
 *
 * The sign-out leaves the remember token alone (unlike logout()), so it cannot break the remember-me of the device that is current.
 */
class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || session('impersonating') || !$request->hasSession()) {
            return $next($request);
        }

        $current = $user->login_token ?? null;
        if ($current === null) {
            return $next($request);
        }

        $held = session('login_token');
        if ($held === null && hash_equals((string) $current, (string) $request->cookie('login_token'))) {
            session(['login_token' => $current]);   // this device is the current one, restored by its remember-me cookie
            $held = $current;
        }
        if ($held !== null && hash_equals((string) $current, (string) $held)) {
            return $next($request);
        }

        Auth::guard('web')->forgetUser();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cookie::queue(Cookie::forget(Auth::guard('web')->getRecallerName()));
        Cookie::queue(Cookie::forget('login_token'));

        $message = __('You were signed out because your account was signed in on another device.');
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 'error', 'message' => $message], 401);
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
