<x-guest-layout>
    <x-auth-panel :title="__('Forgot Password?')"
                  :subtitle="__('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.')"
                  icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/></svg>'>

        <x-auth-session-status class="mb-4 alert alert-success border-0 shadow-sm small" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-floating mb-4">
                <input type="email" name="email" id="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="name@example.com" value="{{ old('email') }}" required autofocus autocomplete="username">
                <label for="email" class="text-muted small">{{ __('Email Address') }}</label>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-finance w-100 py-3 fw-bold mb-3 shadow-sm text-uppercase tracking-wider">
                {{ __('Email Password Reset Link') }}
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}" class="small text-finance text-decoration-none fw-medium">&larr; {{ __('Back to Login') }}</a>
            </div>
        </form>
    </x-auth-panel>
</x-guest-layout>
