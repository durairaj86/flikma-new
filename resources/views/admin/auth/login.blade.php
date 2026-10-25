<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - Flikma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        /* simple light sign-in: logo on top, one white card, dark action button */
        body { margin: 0; min-height: 100vh; background: #f3f4f6; font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif; color: #111827;
               display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px 16px; }
        .al-logo { display: block; height: 52px; width: auto; margin: 0 auto 6px; }
        .al-tag { text-align: center; font-size: .78rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #6b7280; margin-bottom: 22px; }
        .al-card { width: 100%; max-width: 448px; background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04); }
        .al-label { display: block; font-size: .875rem; font-weight: 500; color: #374151; margin-bottom: 6px; }
        .al-input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 1rem; color: #111827; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.05); transition: border-color .15s, box-shadow .15s; }
        .al-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .al-input.is-invalid { border-color: #dc2626; }
        .al-error { color: #dc2626; font-size: .82rem; margin-top: 6px; }
        .al-check { display: inline-flex; align-items: center; gap: 8px; font-size: .875rem; color: #4b5563; cursor: pointer; }
        .al-check input { width: 16px; height: 16px; accent-color: #1f2937; }
        .al-forgot { font-size: .875rem; color: #4b5563; text-decoration: underline; text-underline-offset: 2px; }
        .al-forgot:hover { color: #111827; }
        .al-btn { background: #1f2937; color: #fff; border: 0; border-radius: 6px; padding: 10px 18px; font-size: .75rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .al-btn:hover { background: #374151; }
        .al-btn:focus-visible { outline: 2px solid #6366f1; outline-offset: 2px; }
    </style>
</head>
<body>
    <img src="{{ asset('img/logo.png') }}" alt="Flikma" class="al-logo">
    <div class="al-tag">Admin Panel</div>

    <div class="al-card">
        <form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label for="email" class="al-label">Email</label>
                <input type="email" id="email" name="email" class="al-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')<div class="al-error">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="al-label">Password</label>
                <input type="password" id="password" name="password" class="al-input @error('password') is-invalid @enderror" required autocomplete="current-password">
                @error('password')<div class="al-error">{{ $message }}</div>@enderror
            </div>
            <label class="al-check mb-3">
                <input type="checkbox" name="remember" id="remember" @checked(old('remember'))> Remember me
            </label>
            <div class="d-flex align-items-center justify-content-end gap-3 mt-2">
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="al-forgot">Forgot your password?</a>
                @endif
                <button type="submit" class="al-btn">Log in</button>
            </div>
        </form>
    </div>
</body>
</html>
