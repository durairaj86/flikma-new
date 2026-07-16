@section('page-title', 'Verify Your Email')
<x-app-layout>
    <div class="d-flex align-items-center gap-2 p-3">
        <i class="bi bi-lightning-charge-fill text-finance fs-4"></i>
        <span class="fw-bold text-dark fs-5">Flikma</span>
    </div>

    <div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 130px);">
        <div class="w-100 p-4" style="max-width: 480px;">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-finance-subtle text-finance mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-envelope-paper fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-2">Verify Your Email</h3>
                    <p class="text-muted mb-4">
                        {{ __("Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.") }}
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success border-0 shadow-sm small mb-4 text-start">
                            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                        </div>
                    @elseif (session('status') == 'verification-link-failed')
                        <div class="alert alert-danger border-0 shadow-sm small mb-4 text-start">
                            {{ __('We could not send the verification email right now. Please try again in a moment.') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-finance w-100 py-3 fw-bold mb-3 shadow-sm text-uppercase tracking-wider">
                            Resend Verification Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="pt-3 mt-2 border-top">
                        @csrf
                        <button type="submit" class="btn btn-link text-muted small text-decoration-none">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* This page is reached before email verification, so the main nav has
           nothing valid to point at yet — hide it, keep only the header
           (so the page still reads as "you're logged in", per design). */
        #sidebar-container { display: none !important; }

        :root { --finance-primary: #0d9488; }
        .bg-finance-subtle { background-color: rgba(13, 148, 136, 0.1); }
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

        .tracking-wider { letter-spacing: 0.05em; }
    </style>
</x-app-layout>
