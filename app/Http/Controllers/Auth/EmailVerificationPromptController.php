<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the page where the emailed code is entered. A first visit with no open code sends one,
     * so a person who just registered (or was asked to verify later) is never left waiting.
     */
    public function __invoke(Request $request, EmailOtpService $otp): RedirectResponse|View
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $state = $otp->state($user);
        if (!$state['active'] && $state['resend_in'] === 0) {
            try {
                // The automatic first send never shows an error: if it cannot go out now, the worker keeps trying.
                $otp->send($user, null, false);
            } catch (\Throwable $e) {
                Log::error('Could not queue the verification code: ' . $e->getMessage());
            }
            $state = $otp->state($user);
        }
        // Only mention a problem when the code email really has not gone out.
        $delayed = $state['active'] && in_array($otp->delivery($user), ['failed', 'pending'], true);

        return view('auth.verify-email', [
            'maskedEmail' => EmailOtpService::mask($user->email),
            'state' => $state,
            'sendFailed' => false,
            'delayed' => $delayed,
            'ttl' => EmailOtpService::TTL_MINUTES,
        ]);
    }
}
