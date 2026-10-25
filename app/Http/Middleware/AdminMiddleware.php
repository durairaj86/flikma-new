<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** /admin is for platform staff (users.is_admin). Company owners (role 1) are not admins. */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access to admin panel.');
        }

        return $next($request);
    }
}
