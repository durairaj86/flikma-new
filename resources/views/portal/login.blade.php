@extends('portal.layout')
@section('title', __('Sign in'))
@section('content')
    <div class="mx-auto" style="max-width:420px">
        <div class="text-end small mb-2"><a href="{{ url('/portal/locale/' . (app()->getLocale() === 'ar' ? 'en' : 'ar')) }}">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a></div>
        <div class="p-card p-4">
            <h4 class="fw-bold mb-1">{{ __('Customer Portal') }}</h4>
            <p class="text-muted small mb-4">{{ __('Track your shipments, see your invoices and statement.') }}</p>
            <form method="POST" action="{{ url('/portal/login') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label small" for="remember">{{ __('Remember me') }}</label>
                    </div>
                    <a class="small" href="{{ url('/portal/forgot') }}">{{ __('Forgot password?') }}</a>
                </div>
                <button class="btn btn-primary w-100">{{ __('Sign in') }}</button>
            </form>
        </div>
    </div>
@endsection
