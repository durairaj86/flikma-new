<x-guest-layout>
    <x-auth-panel :title="__('Confirm Password')"
                  :subtitle="__('This is a secure area of the application. Please confirm your password before continuing.')"
                  icon='<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>'>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="form-floating mb-4">
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('Password') }}" required autofocus autocomplete="current-password">
                <label for="password" class="text-muted small">{{ __('Password') }}</label>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-finance w-100 py-3 fw-bold shadow-sm text-uppercase tracking-wider">
                {{ __('Confirm') }}
            </button>
        </form>
    </x-auth-panel>
</x-guest-layout>
