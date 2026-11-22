@extends('portal.layout')
@section('title', __('Choose a password'))
@section('content')
    <div class="mx-auto" style="max-width:420px">
        <div class="p-card p-4">
            @if(!$user || !$user->is_active)
                <h5 class="fw-bold">{{ __('This link has expired') }}</h5>
                <p class="text-muted small">{{ __('Ask us to send a new invitation, or use "Forgot password?" on the sign-in page.') }}</p>
                <a href="{{ url('/portal/login') }}" class="btn btn-primary">{{ __('Sign in') }}</a>
            @else
                <h4 class="fw-bold mb-1">{{ __('Choose a password') }}</h4>
                <p class="text-muted small mb-4">{{ $user->name }} · {{ $user->email }}</p>
                <form method="POST" action="{{ url('/portal/password/' . $token) }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('New password') }}</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autofocus autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">{{ __('At least 8 characters, with letters and numbers.') }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">{{ __('Repeat the password') }}</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary w-100">{{ __('Save password and sign in') }}</button>
                </form>
            @endif
        </div>
    </div>
@endsection
