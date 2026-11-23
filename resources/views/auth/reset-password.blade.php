<x-guest-layout>
    <x-auth-panel :title="__('Reset Password')" :subtitle="__('Choose a new password for your account.')"
                  icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>'>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-floating mb-3">
                <input type="email" name="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="name@example.com" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                <label for="email" class="text-muted small">{{ __('Email Address') }}</label>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('Password') }}" required autocomplete="new-password">
                <label for="password" class="text-muted small">{{ __('New Password') }}</label>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating mb-4">
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control @error('password_confirmation') is-invalid @enderror"
                       placeholder="{{ __('Confirm Password') }}" required autocomplete="new-password">
                <label for="password_confirmation" class="text-muted small">{{ __('Confirm Password') }}</label>
                @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-finance w-100 py-3 fw-bold mb-3 shadow-sm text-uppercase tracking-wider">
                {{ __('Reset Password') }}
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}" class="small text-finance text-decoration-none fw-medium">&larr; {{ __('Back to Login') }}</a>
            </div>
        </form>
    </x-auth-panel>
</x-guest-layout>
