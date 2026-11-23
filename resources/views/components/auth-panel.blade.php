{{-- Two-panel shell of the sign-in pages (same look as auth/login): brand panel left, the form card right. --}}
@props(['title', 'subtitle' => null, 'icon' => null])
<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <div class="col-lg-7 d-none d-lg-flex flex-column justify-content-center align-items-center bg-finance text-white p-5">
            <div class="max-w-md text-center">
                <h1 class="display-5 fw-bold mb-3">{{ __('Master Your Ledger.') }}</h1>
                <p class="lead opacity-75">Streamlined ZATCA integration and real-time financial reporting at your fingertips.</p>
            </div>
        </div>

        <div class="col-lg-5 d-flex align-items-center justify-content-center bg-light">
            <div class="w-100 p-4 p-md-5" style="max-width: 450px;">
                @if ($icon)
                    <div class="auth-icon mb-4">{!! $icon !!}</div>
                @endif
                <div class="mb-4">
                    <h3 class="fw-bold text-dark mb-1">{{ $title }}</h3>
                    @if ($subtitle)
                        <p class="text-muted mb-0">{{ $subtitle }}</p>
                    @endif
                </div>

                {{ $slot }}

                <div class="text-center pt-3 mt-4 border-top">
                    <p class="small text-muted mb-0">
                        Powered by <span class="fw-bold text-dark">FinancialSystems v2.0</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root { --finance-primary: #0d9488; --finance-dark: #0f172a; }
    .text-finance { color: var(--finance-primary); }
    .btn-finance { background-color: var(--finance-primary); border: none; color: #fff; transition: all .3s ease; }
    .btn-finance:hover, .btn-finance:focus { background-color: #0f766e; color: #fff; transform: translateY(-1px); }
    .btn-finance:focus-visible { outline: 3px solid rgba(13, 148, 136, .35); outline-offset: 2px; }
    .form-control { border: 1px solid #e2e8f0; border-radius: 8px; }
    .form-control:focus { border-color: var(--finance-primary); box-shadow: 0 0 0 .25rem rgba(13, 148, 136, .1); }
    .form-floating > label { padding-left: 1rem; }
    .tracking-wider { letter-spacing: .05em; }
    .auth-icon { width: 56px; height: 56px; border-radius: 14px; background: rgba(13, 148, 136, .1); color: var(--finance-primary); display: flex; align-items: center; justify-content: center; }
    .auth-icon svg { width: 28px; height: 28px; }
    .bg-finance { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); position: relative; overflow: hidden; }
    .bg-finance::before {
        content: ""; position: absolute; width: 200%; height: 200%;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
</style>
