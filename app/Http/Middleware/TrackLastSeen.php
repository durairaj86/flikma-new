<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stamps users.last_seen_at (at most every 5 minutes) so the admin "Logged Users Today" page also lists people
 * kept signed in with "Remember me". An admin using "Login as" is not counted as the company user.
 */
class TrackLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if ($user && !session('impersonating') && $request->hasSession()) {
            $last = (int) session('last_seen_stamp', 0);
            if (time() - $last >= 300) {
                DB::table('users')->where('id', $user->getAuthIdentifier())->update(['last_seen_at' => now()]);
                session(['last_seen_stamp' => time()]);
            }
        }

        return $response;
    }
}
