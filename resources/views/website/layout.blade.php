<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Flikma is smart logistics software for freight forwarders, 3PLs and logistics companies in Saudi Arabia, Bahrain and Dubai — manage every shipment, document and customer with speed, accuracy and complete visibility.')">
    <meta name="keywords" content="@yield('meta_keywords', 'logistics software Saudi Arabia, freight forwarding software Bahrain, logistics ERP Dubai, ZATCA e-invoicing software, freight management software GCC')">
    <title>@yield('title', 'Flikma — Smart Logistics Software for Saudi Arabia, Bahrain & Dubai')</title>

    <link rel="icon" href="{{ asset('img/logo1.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|plus-jakarta-sans:500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        :root {
            --fk-primary: #0e3b2e;
            --fk-primary-dark: #092a20;
            --fk-accent: #17a34a;
            --fk-accent-dark: #128a3d;
            --fk-mint: #eaf6ee;
            --fk-mint-border: #cfe9d7;
            --fk-ink: #10231c;
            --fk-muted: #5b6b66;
            --fk-border: #e6ece9;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--fk-ink);
            background: #fff;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, .fk-display {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--fk-ink);
        }

        a { text-decoration: none; }

        /* Navbar */
        .fk-navbar {
            background: #fff;
            border-bottom: 1px solid var(--fk-border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .fk-logo-mark {
            width: 34px; height: 34px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .fk-logo-word {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--fk-primary);
            letter-spacing: -0.01em;
        }
        .fk-navbar .nav-link {
            font-weight: 600;
            color: var(--fk-ink);
            font-size: 0.92rem;
            padding: 0.5rem 0.9rem;
        }
        .fk-navbar .nav-link:hover,
        .fk-navbar .nav-link.active { color: var(--fk-accent); }
        .fk-navbar .dropdown-menu { border: 1px solid var(--fk-border); box-shadow: 0 12px 30px rgba(14,59,46,0.1); border-radius: 0.75rem; }
        .fk-navbar .dropdown-item { font-weight: 500; font-size: 0.9rem; padding: 0.5rem 1rem; }
        .fk-navbar .dropdown-item:hover { background: var(--fk-mint); color: var(--fk-primary); }

        .fk-btn-primary {
            background: var(--fk-primary);
            border: 1.5px solid var(--fk-primary);
            color: #fff !important;
            font-weight: 600;
            padding: 0.6rem 1.4rem;
            border-radius: 0.5rem;
            transition: all 0.15s ease;
            display: inline-block;
        }
        .fk-btn-primary:hover { background: var(--fk-primary-dark); border-color: var(--fk-primary-dark); color: #fff; }

        .fk-btn-outline {
            background: #fff;
            border: 1.5px solid var(--fk-border);
            color: var(--fk-ink) !important;
            font-weight: 600;
            padding: 0.58rem 1.35rem;
            border-radius: 0.5rem;
            transition: all 0.15s ease;
            display: inline-block;
        }
        .fk-btn-outline:hover { border-color: var(--fk-primary); color: var(--fk-primary) !important; }

        .fk-btn-lang {
            background: #fff;
            border: 1.5px solid var(--fk-border);
            color: var(--fk-ink) !important;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
        }

        .fk-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--fk-accent);
        }

        .fk-section { padding: 4.5rem 0; }
        @media (max-width: 767px) { .fk-section { padding: 3rem 0; } }

        /* Scaled down from Bootstrap's oversized defaults for a tighter,
           more professional B2B look (display-4 is 3.5rem out of the box). */
        .display-4 { font-size: 2.5rem; }
        .display-5 { font-size: 2.15rem; }
        .display-6 { font-size: 1.65rem; }
        h1 { font-size: 2rem; }
        h2 { font-size: 1.6rem; }
        h3 { font-size: 1.35rem; }
        h4, h5 { font-size: 1.1rem; }
        p, li { font-size: 0.95rem; }
        .fs-4 { font-size: 1.15rem !important; }
        .fs-5 { font-size: 1.05rem !important; }
        @media (max-width: 767px) {
            .display-4 { font-size: 1.85rem; }
            .display-5 { font-size: 1.65rem; }
            .display-6 { font-size: 1.4rem; }
        }

        .fk-card {
            border: 1px solid var(--fk-border);
            border-radius: 1rem;
            background: #fff;
            transition: all 0.2s ease;
        }
        .fk-card:hover { box-shadow: 0 14px 34px rgba(14,59,46,0.08); transform: translateY(-3px); border-color: #d3e8d9; }

        .fk-icon-box {
            width: 48px; height: 48px;
            border-radius: 0.75rem;
            background: var(--fk-mint);
            color: var(--fk-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .text-fk-muted { color: var(--fk-muted) !important; }
        .text-fk-accent { color: var(--fk-accent) !important; }
        .fk-shadow-lg { box-shadow: 0 24px 60px rgba(14,59,46,0.14); }

        .fk-stats-bar { background: var(--fk-primary); color: #fff; }
        .fk-stats-bar .fk-stat-num { font-weight: 800; font-size: 1.5rem; }
        .fk-stats-bar .fk-stat-label { font-size: 0.8rem; color: rgba(255,255,255,0.7); }

        .fk-cta-band {
            background: linear-gradient(120deg, var(--fk-primary) 0%, var(--fk-primary-dark) 100%);
            border-radius: 1.25rem;
        }

        .fk-footer { background: #081c15; color: #9db3ab; }
        .fk-footer a { color: #c6d6cf; }
        .fk-footer a:hover { color: #fff; }
        .fk-footer h6 { color: #fff; font-weight: 700; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.06em; }
        .fk-footer .fk-logo-word { color: #fff; }

        @media print { .fk-navbar, .fk-footer { display: none; } }
    </style>

    @yield('extra_head')
</head>
<body>

    {{-- Navbar --}}
    <nav class="fk-navbar navbar navbar-expand-lg py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('website.home') }}">
                <span class="fk-logo-mark">
                    <svg width="30" height="30" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 26L14 16L4 6" stroke="#0e3b2e" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 26L25 16L15 6" stroke="#17a34a" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="fk-logo-word">FLIKMA</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#fkNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="fkNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('website.home') ? 'active' : '' }}" href="{{ route('website.home') }}">Why Flikma</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Solutions</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('website.products') }}#operations">Operations</a></li>
                            <li><a class="dropdown-item" href="{{ route('website.products') }}#finance">Finance</a></li>
                            <li><a class="dropdown-item" href="{{ route('website.products') }}#compliance">Compliance</a></li>
                            <li><a class="dropdown-item" href="{{ route('website.products') }}#payroll">Payroll</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('website.features') ? 'active' : '' }}" href="{{ route('website.features') }}">Features</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('website.pricing') ? 'active' : '' }}" href="{{ route('website.pricing') }}">Pricing</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Resources</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Blog</a></li>
                            <li><a class="dropdown-item" href="#">Help Center</a></li>
                            <li><a class="dropdown-item" href="#">Case Studies</a></li>
                            <li><a class="dropdown-item" href="#">Documentation</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Company</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('website.services') }}">Services</a></li>
                            <li><a class="dropdown-item" href="{{ route('website.home') }}#about">About Us</a></li>
                            <li><a class="dropdown-item" href="{{ route('website.contact') }}">Contact</a></li>
                        </ul>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <a href="#" class="fk-btn-lang text-center">العربية</a>
                    <a href="{{ route('website.contact') }}" class="fk-btn-primary text-center">Request Demo</a>
                </div>
            </div>
        </div>
    </nav>

    @yield('content')

    {{-- Footer --}}
    <footer class="fk-footer pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 26L14 16L4 6" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 26L25 16L15 6" stroke="#17a34a" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="fk-logo-word">FLIKMA</span>
                    </div>
                    <p class="small" style="max-width: 300px;">Smart logistics software to manage your shipments, customers, and operations with complete efficiency and visibility.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#"><i class="bi bi-linkedin fs-5"></i></a>
                        <a href="#"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#"><i class="bi bi-youtube fs-5"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>Solutions</h6>
                    <ul class="list-unstyled small mt-3">
                        <li class="mb-2"><a href="{{ route('website.products') }}">Freight Management</a></li>
                        <li class="mb-2"><a href="{{ route('website.products') }}">Operations</a></li>
                        <li class="mb-2"><a href="{{ route('website.products') }}">Billing &amp; Invoicing</a></li>
                        <li class="mb-2"><a href="{{ route('website.products') }}">CRM</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>Platform</h6>
                    <ul class="list-unstyled small mt-3">
                        <li class="mb-2"><a href="{{ route('website.features') }}">Features</a></li>
                        <li class="mb-2"><a href="#">Integrations</a></li>
                        <li class="mb-2"><a href="#">Security</a></li>
                        <li class="mb-2"><a href="#">Mobile App</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>Resources</h6>
                    <ul class="list-unstyled small mt-3">
                        <li class="mb-2"><a href="#">Blog</a></li>
                        <li class="mb-2"><a href="#">Help Center</a></li>
                        <li class="mb-2"><a href="#">Case Studies</a></li>
                        <li class="mb-2"><a href="#">Documents</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6>Company</h6>
                    <ul class="list-unstyled small mt-3">
                        <li class="mb-2"><a href="{{ route('website.home') }}#about">About Us</a></li>
                        <li class="mb-2"><a href="#">Careers</a></li>
                        <li class="mb-2"><a href="{{ route('website.services') }}">Partners</a></li>
                        <li class="mb-2"><a href="{{ route('website.contact') }}">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary opacity-25 my-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="mb-2">Contact Us</h6>
                    <div class="small mb-1"><i class="bi bi-telephone me-2"></i>+966 595555343</div>
                    <div class="small mb-1"><i class="bi bi-envelope me-2"></i>support@flikma.com</div>
                    <div class="small"><i class="bi bi-geo-alt me-2"></i>Riyadh, Saudi Arabia</div>
                </div>
            </div>
            <hr class="border-secondary opacity-25 my-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small">
                <div>&copy; {{ date('Y') }} Flikma. All rights reserved.</div>
                <div class="d-flex gap-3 mt-2 mt-md-0">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">SLA</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('extra_scripts')
</body>
</html>
