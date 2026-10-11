<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Only the account owner (the person who registered the company) may pass. */
class OwnerOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(isSuperUser($request->user()), 403, 'Only the account owner can open this section.');

        return $next($request);
    }
}
