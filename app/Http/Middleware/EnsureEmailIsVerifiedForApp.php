<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;

/**
 * Same as Laravel's "verified", with one difference: a request made by the page's own scripts (a modal, a drawer,
 * a list loading its data) gets a small JSON answer instead of the verification page, so the page can show
 * "please verify your email" instead of pasting that page into a modal.
 */
class EnsureEmailIsVerifiedForApp extends EnsureEmailIsVerified
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        $user = $request->user();
        $unverified = !$user || ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail());

        if ($unverified && ($request->ajax() || $request->expectsJson())) {
            return response()->json([
                'code' => 'email_unverified',
                'message' => __('Please verify your email address to use this feature.'),
            ], 403);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
