<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalUser;
use App\Services\Mail\EmailQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/** Customer portal sign-in, sign-out, set password (from the invite email) and forgotten password. */
class PortalAuthController extends Controller
{
    public function showLogin()
    {
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'portal-login:' . strtolower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => __('Too many attempts. Try again in :s seconds.', ['s' => RateLimiter::availableIn($key)])]);
        }

        $ok = Auth::guard('portal')->attempt(
            ['email' => strtolower(trim($request->input('email'))), 'password' => $request->input('password'), 'is_active' => 1],
            $request->boolean('remember')
        );
        if (!$ok) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => __('The email or password is not correct.')]);
        }
        RateLimiter::clear($key);

        // new session id against session fixation; a staff login in the same browser is kept
        $request->session()->migrate(true);
        $user = Auth::guard('portal')->user();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(url('/portal'));
    }

    public function logout(Request $request)
    {
        Auth::guard('portal')->logout();
        $request->session()->regenerateToken();

        return redirect(url('/portal/login'));
    }

    /** The link from the invite (or reset) email: choose a password. */
    public function showSetPassword(string $token)
    {
        $user = PortalUser::findByToken($token);

        return view('portal.set-password', ['token' => $token, 'user' => $user]);
    }

    public function setPassword(Request $request, string $token)
    {
        $user = PortalUser::findByToken($token);
        if (!$user || !$user->is_active) {
            return redirect(url('/portal/login'))->withErrors(['email' => __('This link has expired. Ask for a new one.')]);
        }
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->password = $request->input('password');
        $user->password_set_at = now();
        $user->invite_token = null;
        $user->invite_expires_at = null;
        $user->save();

        Auth::guard('portal')->login($user);
        $request->session()->migrate(true);
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect(url('/portal'))->with('notice', __('Your password is set. Welcome!'));
    }

    public function showForgot()
    {
        return view('portal.forgot');
    }

    /** Always the same answer, so the page does not tell which emails have a login. */
    public function sendReset(Request $request, EmailQueueService $mail)
    {
        $request->validate(['email' => ['required', 'email']]);
        $key = 'portal-reset:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => __('Too many attempts. Try again in :s seconds.', ['s' => RateLimiter::availableIn($key)])]);
        }
        RateLimiter::hit($key, 300);

        $user = PortalUser::where('email', strtolower(trim($request->input('email'))))->where('is_active', 1)->first();
        if ($user && $user->password_set_at) {
            $token = $user->issueToken(PortalUser::RESET_HOURS);
            $mail->enqueue($user->email, 'Reset your customer portal password', 'emails.generic', [
                'body' => "Hello {$user->name},\n\nUse the button below to choose a new password for the customer portal. The link works for one hour.\n\nIf you did not ask for this, you can ignore this email.",
                'action_url' => url('/portal/password/' . $token),
                'action_label' => 'Choose a new password',
            ], ['type' => 'portal-reset', 'company_id' => $user->company_id, 'sensitive' => true, 'expires_at' => now()->addHour()]);
        }

        return back()->with('notice', __('If this email has a portal login, a link to reset the password has been sent.'));
    }
}
