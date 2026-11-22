@extends('portal.layout')
@section('title', __('Forgot password?'))
@section('content')
    <div class="mx-auto" style="max-width:420px">
        <div class="p-card p-4">
            <h4 class="fw-bold mb-1">{{ __('Forgot password?') }}</h4>
            <p class="text-muted small mb-4">{{ __('Enter your email; we will send a link to choose a new password.') }}</p>
            <form method="POST" action="{{ url('/portal/forgot') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary w-100">{{ __('Send link') }}</button>
            </form>
            <div class="text-center mt-3"><a class="small" href="{{ url('/portal/login') }}">{{ __('Back to sign in') }}</a></div>
        </div>
    </div>
@endsection
