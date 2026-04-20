<x-guest-layout>
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <div class="col-lg-7 d-none d-lg-flex flex-column justify-content-center align-items-center bg-finance text-white p-5">
                <div class="max-w-md text-center">
                    <div class="mb-4">
                        <i class="bi bi-bank2 display-1 opacity-50"></i>
                    </div>
                    <h1 class="display-5 fw-bold mb-3">Join Our Platform.</h1>
                    <p class="lead opacity-75">Create your account to access ZATCA-integrated financial reporting and real-time analytics.</p>

                    <div class="mt-5 d-flex gap-4 justify-content-center">
                        <div class="text-center">
                            <h4 class="mb-0 fw-bold">Secure</h4>
                            <small class="opacity-50">Data Storage</small>
                        </div>
                        <div class="vr"></div>
                        <div class="text-center">
                            <h4 class="mb-0 fw-bold">Real-time</h4>
                            <small class="opacity-50">Reports</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 d-flex align-items-center justify-content-center bg-light">
                <div class="w-100 p-4 p-md-5" style="max-width: 450px;">

                    <div class="mb-5">
                        <h3 class="fw-bold text-dark mb-1">Create Account</h3>
                        <p class="text-muted">Enter your details to get started.</p>
                    </div>

                    <x-auth-session-status class="mb-4 alert alert-success border-0 shadow-sm small" :status="session('status')" />

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="form-floating mb-3">
                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="floatingName"
                                   placeholder="John Doe"
                                   value="{{ old('name') }}"
                                   required autofocus>
                            <label for="floatingName" class="text-muted small">Full Name</label>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="floatingEmail"
                                   placeholder="name@example.com"
                                   value="{{ old('email') }}"
                                   required>
                            <label for="floatingEmail" class="text-muted small">Email Address</label>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="floatingPassword"
                                   placeholder="Password"
                                   required autocomplete="new-password">
                            <label for="floatingPassword" class="text-muted small">Password</label>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control"
                                   id="floatingConfirmPassword"
                                   placeholder="Confirm Password"
                                   required autocomplete="new-password">
                            <label for="floatingConfirmPassword" class="text-muted small">Confirm Password</label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check m-0">
                                <input class="form-check-input custom-check" type="checkbox" name="terms" id="terms" required>
                                <label class="form-check-label small text-muted" for="terms">
                                    I agree to the <a href="#" class="text-finance">Terms</a>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-finance w-100 py-3 fw-bold mb-4 shadow-sm text-uppercase tracking-wider">
                            Create Account
                        </button>

                        <div class="text-center pt-3 border-top">
                            <p class="small text-muted mb-0">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-finance text-decoration-none fw-medium">Sign In</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --finance-primary: #0d9488;
            --finance-dark: #0f172a;
        }

        .bg-finance { background-color: var(--finance-dark); }
        .text-finance { color: var(--finance-primary); }

        .btn-finance {
            background-color: var(--finance-primary);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-finance:hover {
            background-color: #0f766e;
            color: white;
            transform: translateY(-1px);
        }

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: var(--finance-primary);
            box-shadow: 0 0 0 0.25rem rgba(13, 148, 136, 0.1);
        }

        .form-floating > label { padding-left: 1rem; }

        .custom-check:checked {
            background-color: var(--finance-primary);
            border-color: var(--finance-primary);
        }

        .tracking-wider { letter-spacing: 0.05em; }

        .bg-finance {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            position: relative;
            overflow: hidden;
        }

        .bg-finance::before {
            content: "";
            position: absolute;
            width: 200%;
            height: 200%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</x-guest-layout>