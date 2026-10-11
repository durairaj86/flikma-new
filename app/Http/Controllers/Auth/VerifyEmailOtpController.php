<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailOtpController extends Controller
{
    /** Check the code the person typed. */
    public function __invoke(Request $request, EmailOtpService $otp): RedirectResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }
        $data = $request->validate(['otp' => ['required', 'digits:6']], [
            'otp.required' => __('Enter the 6 digit code from your email.'),
            'otp.digits' => __('The code is 6 digits.'),
        ]);

        $result = $otp->verify($user, $data['otp']);

        if ($result === 'ok') {
            return redirect()->route('dashboard')->with('status', 'email-verified');
        }

        $left = $otp->state($user)['attempts_left'];
        $message = match ($result) {
            'expired' => __('This code has expired. Request a new one.'),
            'locked' => __('Too many wrong attempts. Request a new code.'),
            'none' => __('This code is no longer valid. Request a new one.'),
            default => __('That code is not correct.') . ($left > 0 ? ' ' . trans_choice('{1} 1 attempt left.|[2,*] :n attempts left.', $left, ['n' => $left]) : ''),
        };

        return back()->withErrors(['otp' => $message]);
    }
}
