<x-guest-layout>
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <div class="col-lg-7 d-none d-lg-flex flex-column justify-content-center align-items-center bg-finance text-white p-5">
                <div class="max-w-md text-center">
                    <div class="mb-4">
                        <i class="bi bi-bank2 display-1 opacity-50"></i>
                    </div>
                    <h1 class="display-5 fw-bold mb-3">Master Your Ledger.</h1>
                    <p class="lead opacity-75">Streamlined ZATCA integration and real-time financial reporting at your fingertips.</p>
                </div>
            </div>

            <div class="col-lg-5 d-flex align-items-center justify-content-center bg-light">
                <div class="w-100 p-4 p-md-5 text-center" style="max-width: 450px;">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-finance" style="font-size: 3.5rem;"></i>
                    </div>

                    <h3 class="fw-bold text-dark mb-2">You're All Set!</h3>
                    <p class="text-muted mb-4">Your email address has been verified. Welcome aboard &mdash; your account is ready to use.</p>

                    <a href="{{ route('dashboard') }}" class="btn btn-finance w-100 py-3 fw-bold shadow-sm text-uppercase tracking-wider">
                        Continue to Dashboard
                    </a>

                    <div class="text-center pt-4 mt-4 border-top">
                        <p class="small text-muted mb-0">
                            Powered by <span class="fw-bold text-dark">FinancialSystems v2.0</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --finance-primary: #0d9488;
            --finance-dark: #0f172a;
        }

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
</x-guest-layout>
