<?php

namespace App\Http\Middleware;

use App\Models\Portal\PortalUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every customer portal page: a signed-in, active portal user of an existing customer.
 * The app finds "the current company" from session('company_id') (staff sessions); for the length of this one
 * request it is set to the portal user's company so shared code (invoice print, settings) works, then put back,
 * so a staff session in the same browser is never changed. Portal queries still filter by customer themselves.
 */
class PortalCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var PortalUser|null $user */
        $user = Auth::guard('portal')->user();
        if (!$user || !$user->is_active || !$user->customer || $user->customer->deleted_at) {
            Auth::guard('portal')->logout();

            return redirect()->guest(url('/portal/login'));
        }

        $session = $request->session();
        $had = $session->has('company_id');
        $previous = $session->get('company_id');
        $session->put('company_id', (int) $user->company_id);

        try {
            $response = $next($request);
        } finally {
            $had ? $session->put('company_id', $previous) : $session->forget('company_id');
        }

        // portal pages show account data: never keep them in a shared browser cache
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
