<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Master\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Countries offered at registration, each mapped to the currency the new
     * company's books should be kept in.
     *
     * @var array<string, string>
     */
    private const COUNTRY_CURRENCIES = [
        'Saudi Arabia' => 'SAR',
        'United Arab Emirates' => 'AED',
        'Bahrain' => 'BHD',
        'Jordan' => 'JOD',
        'Qatar' => 'QAR',
    ];

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'company_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', Rule::in(array_keys(self::COUNTRY_CURRENCIES))],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $company = new Company();
            $company->name = $request->company_name;
            $company->country = $request->country;
            $company->currency = self::COUNTRY_CURRENCIES[$request->country];
            $company->is_active = true;
            $company->save();

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->company_id = $company->id;
            $user->save();

            return $user;
        });

        // A misconfigured/unavailable mail provider must not block account
        // creation or lock the user out of the app — they can still retry
        // sending the verification email from the notice page once mail works.
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Failed to send verification email during registration: '.$e->getMessage());
        }

        Auth::login($user);

        session(['company_id' => $user->company_id]);

        return redirect(route('verification.notice', absolute: false));
    }
}
