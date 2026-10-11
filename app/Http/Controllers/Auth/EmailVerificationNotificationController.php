<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            // Returns a message when a new code may not be sent yet (too soon, or too many today).
            $why = app(\App\Services\Auth\EmailOtpService::class)->send($request->user());
        } catch (\Throwable $e) {
            Log::error('Failed to send the verification code: '.$e->getMessage());

            return back()->with('status', 'verification-link-failed');
        }
        if ($why) {
            return back()->withErrors(['otp' => $why]);
        }

        return back()->with('status', 'otp-sent');
    }
}
