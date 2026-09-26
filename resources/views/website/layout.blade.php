<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Flikma — AI Logistics &amp; Freight Forwarding Software for Saudi Arabia')</title>
    <meta name="description" content="@yield('meta_description', 'Flikma is AI-powered logistics software for freight forwarders and 3PLs in Saudi Arabia, Bahrain and Dubai. Manage enquiries, jobs, bills of lading, expenses, ZATCA Phase 2 e-invoicing and scanned supplier documents in one cloud ERP.')">
    <meta name="keywords" content="@yield('meta_keywords', 'logistics software Saudi Arabia, freight forwarding software Bahrain, logistics ERP Dubai, 3PL software GCC, ZATCA Phase 2 e-invoicing software, AI document scanning, OCR supplier invoices, freight management software')">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Flikma — AI Logistics &amp; Freight Forwarding Software for Saudi Arabia')">
    <meta property="og:description" content="@yield('meta_description', 'AI-powered logistics software for freight forwarders and 3PLs in Saudi Arabia, Bahrain and Dubai.')">
    <meta property="og:image" content="{{ url('/img/logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('img/logo1.png') }}">

    @if (request()->routeIs('website.home'))
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "name": "Flikma",
            "url": "{{ url('/') }}",
            "logo": "{{ url('/img/logo.png') }}",
            "description": "AI-powered cloud ERP for freight forwarding, third-party logistics and ZATCA Phase 2 e-invoicing across the GCC.",
            "address": {
                "@@type": "PostalAddress",
                "addressLocality": "Riyadh",
                "addressCountry": "SA"
            },
            "areaServed": ["SA", "BH", "AE"],
            "contactPoint": {
                "@@type": "ContactPoint",
                "telephone": "+966595555343",
                "contactType": "sales"
            }
        }
        </script>
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "SoftwareApplication",
            "name": "Flikma",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "description": "AI logistics and freight forwarding ERP with ZATCA Phase 2 e-invoicing, AI expense capture and document scanning.",
            "offers": {
                "@@type": "Offer",
                "price": "0",
                "priceCurrency": "SAR"
            }
        }
        </script>
    @endif

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════════════
           FLIKMA DESIGN SYSTEM
           Single source of truth — every page inherits these tokens.
           ═══════════════════════════════════════════════════════════ */
        :root {
            --bs-body-font-family: 'DM Sans', sans-serif;

            /* Core palette */
            --ink:          #0a0f1e;
            --ink-soft:     #1e2740;
            /* Dark panel surfaces (footer, CTA bands, featured cards, table heads).
               Kept separate from --ink so heading text stays near-black. */
            --ink-panel:    #0b1736;
            --ink-muted:    #4a5578;
            --ink-ghost:    #8896b0;
            --surface:      #f5f7fc;
            --line:         rgba(60, 80, 140, .08);
            --line-strong:  rgba(60, 80, 140, .14);

            /* Brand */
            --emerald:      #00c97b;
            --emerald-dim:  #00a863;
            --emerald-soft: rgba(0, 201, 123, .10);
            --emerald-glow: rgba(0, 201, 123, .18);

            /* Semantic accents (one per module) */
            --blue:   #3a6bff;
            --cyan:   #06b6d4;
            --violet: #7c3aed;
            --indigo: #4f46e5;
            --gold:   #f4b942;
            --red:    #ef4444;
            --navy:   #0b1736;

            --grid-line: rgba(60, 80, 140, .07);
        }

        html { scroll-behavior: smooth; }
        /* Alpine bindings: hide x-cloak elements until Alpine hydrates. */
        [x-cloak] { display: none !important; }
        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background: var(--surface);
            overflow-x: hidden;
            /* The navbar is fixed at 68px — offset the whole document once here
               instead of repeating margin-top on every page hero. */
            padding-top: 68px;
        }

        h1, h2, h3, h4, h5, h6, .font-display {
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            letter-spacing: -.02em;
        }
        a { text-decoration: none; }
        ::selection { background: var(--emerald-glow); }

        /* ── Utilities ── */
        .bg-ink          { background-color: var(--ink-panel) !important; }
        .bg-surface      { background-color: var(--surface) !important; }
        .bg-emerald-soft { background-color: var(--emerald-soft) !important; }
        .text-ink-muted  { color: var(--ink-muted) !important; }
        .text-ink-ghost  { color: var(--ink-ghost) !important; }
        .text-emerald    { color: var(--emerald) !important; }
        .text-emerald-dim{ color: var(--emerald-dim) !important; }
        .border-grid     { border-color: var(--grid-line) !important; }
        .font-display    { font-family: 'DM Sans', sans-serif; }

        .section-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--emerald);
        }

        /* ── Navbar ── */
        .navbar-brand {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            font-weight: 800;
            font-size: 1.3rem;
            letter-spacing: -.4px;
            color: var(--ink);
        }
        .navbar-brand .flk-word { color: var(--ink); }
        .navbar-brand .flk-word em { font-style: normal; color: var(--emerald); }
        .navbar .nav-link {
            font-size: .875rem;
            font-weight: 500;
            border-radius: 8px;
            transition: color .2s, background .2s;
        }
        .navbar .nav-link:hover { color: var(--ink) !important; background: var(--surface); }
        .navbar .dropdown-menu {
            border: 1px solid var(--line);
            box-shadow: 0 16px 40px rgba(10, 15, 30, .1);
            border-radius: 14px;
            padding: .5rem;
        }
        .navbar .dropdown-item {
            font-size: .875rem;
            font-weight: 500;
            color: var(--ink-muted);
            border-radius: 9px;
            padding: .5rem .75rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }
        .navbar .dropdown-item i { font-size: 1rem; width: 1.15rem; text-align: center; flex-shrink: 0; }
        .navbar .dropdown-item:hover { background: var(--surface); color: var(--ink); }
        .navbar .dropdown-divider { border-color: var(--line); }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: #fff !important;
                border-top: 1px solid #eef2f7;
                box-shadow: 0 14px 30px rgba(10, 15, 30, .10);
                padding: .75rem 1rem 1.25rem;
                margin: 0 -12px;
                border-radius: 0 0 14px 14px;
            }
            .navbar-collapse .nav-link { padding: .65rem 1rem !important; }
            .navbar-collapse .dropdown-menu { border: 0; box-shadow: none; padding-left: .75rem; }
        }

        /* ── Buttons ── */
        .btn-emerald {
            background: var(--emerald); color: #fff; border: none;
            border-radius: 10px; padding: .55rem 1.15rem;
            font-size: .875rem; font-weight: 600;
            transition: all .2s; display: inline-block;
        }
        .btn-emerald:hover { background: var(--emerald-dim); color: #fff; transform: translateY(-1px); }

        .btn-ghost-light {
            background: #fff; border: 1px solid var(--line-strong); color: var(--ink);
            border-radius: 10px; padding: .53rem 1.1rem;
            font-size: .875rem; font-weight: 600;
            transition: all .2s; display: inline-block;
        }
        .btn-ghost-light:hover { border-color: var(--emerald); color: var(--emerald-dim); }

        .btn-hero-primary {
            background: var(--emerald); color: #fff; border-radius: 12px; padding: .85rem 2rem;
            font-weight: 600; box-shadow: 0 6px 20px var(--emerald-glow); transition: all .2s;
            text-decoration: none; display: inline-flex; align-items: center; gap: .5rem;
        }
        .btn-hero-primary:hover { background: var(--emerald-dim); color: #fff; transform: translateY(-2px); }

        .btn-hero-outline {
            background: transparent; border: 1.5px solid rgba(10, 15, 30, .15); color: var(--ink);
            border-radius: 12px; padding: .85rem 1.75rem; font-weight: 500; text-decoration: none;
            display: inline-flex; align-items: center; gap: .5rem; transition: all .2s;
        }
        .btn-hero-outline:hover { border-color: var(--emerald); color: var(--emerald-dim); }

        .btn-cta-main {
            background: var(--ink-panel); color: #fff; border: none;
            border-radius: 14px; padding: 1rem 2.25rem; font-weight: 600;
            transition: all .2s; display: inline-flex; align-items: center; gap: .5rem;
        }
        .btn-cta-main:hover { background: #000; color: var(--emerald); transform: translateY(-2px); }

        .btn-outline-light-fk {
            background: transparent; border: 1.5px solid rgba(255, 255, 255, .25); color: #fff;
            border-radius: 14px; padding: 1rem 2.25rem; font-weight: 500;
            display: inline-flex; align-items: center; gap: .5rem; transition: all .2s;
        }
        .btn-outline-light-fk:hover { border-color: var(--emerald); color: var(--emerald); }

        /* ── Cards ── */
        .feat-card {
            border: 1.5px solid var(--line); border-radius: 18px;
            background: #fff; height: 100%; transition: all .25s;
        }
        .feat-card:hover { box-shadow: 0 20px 50px rgba(10, 15, 30, .07); transform: translateY(-4px); }
        .feat-icon-box {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex-shrink: 0;
        }
        .feat-title { font-weight: 700; letter-spacing: -.01em; }
        .hover-lift { transition: transform .25s, box-shadow .25s; }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(10, 15, 30, .1) !important; }

        .stat-chip {
            border: 1.5px solid var(--line); border-radius: 14px; background: #fff;
            padding: 1.25rem 1rem; text-align: center;
        }
        .stat-chip .chip-value { font-size: 1.8rem; font-weight: 800; line-height: 1.1; letter-spacing: -.03em; }
        .stat-chip .chip-label { font-size: .78rem; color: var(--ink-ghost); }

        .cross-link-card {
            border: 1.5px solid var(--line); border-radius: 16px; background: #fff;
            padding: 1.75rem; height: 100%; transition: all .25s;
        }
        .cross-link-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(10, 15, 30, .07); }

        /* ── Page hero (shared by every subpage) ── */
        .page-hero { background: #fff; border-bottom: 1px solid var(--line); padding: 76px 0 68px; }
        .page-hero h1 {
            font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; line-height: 1.08;
        }
        .page-hero p { font-size: 1.05rem; line-height: 1.7; color: var(--ink-muted); }
        .hero-pill {
            display: inline-flex; align-items: center; gap: .5rem;
            background: var(--emerald-soft); border: 1px solid rgba(0, 201, 123, .3);
            color: var(--emerald-dim); font-size: .72rem; font-weight: 700;
            letter-spacing: .8px; text-transform: uppercase; padding: .4rem .9rem; border-radius: 50px;
        }

        /* ── Section rhythm ── */
        .fk-section { padding: 76px 0; }
        .section-head { max-width: 660px; }
        .section-head h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; line-height: 1.15; margin: .6rem 0 .75rem;
        }
        .section-head p { color: var(--ink-muted); font-size: 1.02rem; line-height: 1.7; }
        .section-head.text-center { margin-left: auto; margin-right: auto; }

        /* ── Dark CTA band (shared) ── */
        .cta-banner { background: var(--ink-panel); border-radius: 20px; }
        .cta-banner h2 { color: #fff; font-weight: 800; }
        .cta-banner p { color: rgba(255, 255, 255, .55); }

        /* ── FAQ accordion ── */
        .faq-item { border: 1px solid rgba(60, 80, 140, .1); border-radius: 14px; background: #fff; overflow: hidden; }
        .faq-item + .faq-item { margin-top: .75rem; }
        .faq-q {
            width: 100%; background: none; border: 0; text-align: left;
            padding: 1.15rem 1.35rem; font-weight: 600; font-size: .95rem; color: var(--ink);
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        }
        .faq-answer { max-height: 0; overflow: hidden; transition: max-height .35s ease; }
        .faq-answer.open { max-height: 400px; }
        .faq-answer p { padding: 0 1.35rem 1.25rem; margin: 0; color: var(--ink-muted); font-size: .9rem; line-height: 1.7; }

        /* ── Forms ── */
        .form-ctrl {
            width: 100%; background: var(--surface); border: 1.5px solid transparent;
            border-radius: 10px; padding: .7rem .9rem; font-size: .9rem; color: var(--ink);
            transition: all .2s;
        }
        .form-ctrl:focus {
            outline: none; border-color: var(--emerald); background: #fff;
            box-shadow: 0 0 0 4px var(--emerald-glow);
        }
        .form-label-fk { font-size: .8rem; font-weight: 600; color: var(--ink-soft); margin-bottom: .35rem; }

        /* ── Trust avatars ── */
        .trust-avatar {
            width: 32px; height: 32px; border-radius: 50%; border: 2px solid #fff;
            background: var(--surface); display: inline-flex; align-items: center; justify-content: center;
            font-size: .65rem; font-weight: 700; color: var(--ink-soft); margin-left: -8px;
        }
        .trust-avatar:first-child { margin-left: 0; }

        /* ── Sticky in-page nav (features / docs) ── */
        .feature-nav { position: sticky; top: 90px; }
        .feature-nav .nav-link {
            font-size: .875rem; font-weight: 500; color: #64748b;
            border-left: 2px solid #e2e8f0; padding: 10px 18px;
            border-radius: 0; transition: all .2s;
            display: flex; align-items: center; gap: .6rem;
        }
        .feature-nav .nav-link:hover { color: var(--emerald); background: rgba(0, 201, 123, .06); }
        .feature-nav .nav-link.active {
            color: var(--emerald); border-left-color: var(--emerald);
            background: rgba(0, 201, 123, .07); font-weight: 600;
        }

        /* ── Meters / meters used on pricing + about ── */
        .bar-track { background: #e2e8f0; border-radius: 50px; height: 6px; overflow: hidden; }
        .bar-fill  { background: var(--emerald); border-radius: 50px; height: 100%; }

        /* ── Animations ── */
        @keyframes floatY { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes pulse  { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .4; transform: scale(.8); } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .anim-hero { animation: fadeUp .65s ease both; }
        .delay-1 { animation-delay: .1s; } .delay-2 { animation-delay: .2s; }
        .delay-3 { animation-delay: .3s; } .delay-4 { animation-delay: .4s; }

        .reveal { opacity: 0; transform: translateY(22px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .reveal { opacity: 1; transform: none; transition: none; }
            .anim-hero, .float-badge, .pulse-dot { animation: none !important; }
        }
    </style>

    {{-- Reveal animations must never hide content when JavaScript is unavailable. --}}
    <noscript>
        <style>
            .reveal { opacity: 1 !important; transform: none !important; }
            [x-cloak] { display: revert !important; }
            .no-js-hide { display: revert !important; }
        </style>
    </noscript>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @yield('extra_head')
</head>
<body>

<!-- ════════ NAVBAR ════════ -->
<nav class="navbar navbar-expand-lg bg-white border-bottom fixed-top py-0" style="height:68px;">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('img/logos/Flikma_logo.svg') }}" alt="Flikma" width="130" class="d-block">
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#flkNav" aria-controls="flkNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="flkNav">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 {{ request()->routeIs('website.home') ? 'fw-semibold text-emerald' : '' }}" href="{{ url('/') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 {{ request()->routeIs('website.features') ? 'fw-semibold text-emerald' : '' }}" href="{{ url('/features') }}">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 {{ request()->routeIs('website.services') ? 'fw-semibold text-emerald' : '' }}" href="{{ url('/services') }}">Services</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle px-3 py-2 {{ request()->routeIs('website.why-flikma') ? 'fw-semibold text-emerald' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Why Flikma</a>
                    <ul class="dropdown-menu shadow border-0">
                        <li><a class="dropdown-item" href="{{ url('/why-flikma') }}#comparison"><i class="bi bi-columns-gap" style="color:var(--ink-ghost);"></i>Switching to Flikma</a></li>
                        <li><a class="dropdown-item" href="{{ url('/why-flikma') }}#security"><i class="bi bi-shield-lock" style="color:var(--emerald);"></i>Security &amp; Data</a></li>
                        <li><a class="dropdown-item" href="{{ url('/why-flikma') }}#results"><i class="bi bi-graph-up-arrow" style="color:var(--blue);"></i>Customer Results</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 {{ request()->routeIs('website.pricing') ? 'fw-semibold text-emerald' : '' }}" href="{{ url('/pricing') }}">Pricing</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle px-3 py-2 {{ request()->routeIs('website.about', 'website.contact', 'website.documentation', 'website.products') ? 'fw-semibold text-emerald' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Company</a>
                    <ul class="dropdown-menu shadow border-0">
                        <li><a class="dropdown-item" href="{{ url('/about') }}"><i class="bi bi-buildings" style="color:var(--ink-ghost);"></i>About Flikma</a></li>
                        <li><a class="dropdown-item" href="{{ url('/documentation') }}"><i class="bi bi-journal-text" style="color:var(--blue);"></i>Documentation</a></li>
                        <li><a class="dropdown-item" href="{{ url('/products') }}"><i class="bi bi-box-seam" style="color:var(--violet);"></i>Platform Modules</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ url('/contact') }}"><i class="bi bi-chat-square-text" style="color:var(--emerald);"></i>Contact &amp; Demo</a></li>
                    </ul>
                </li>
            </ul>

            <div class="d-flex gap-2 mt-3 mt-lg-0">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-emerald">Go to My Account</a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost-light">Login</a>
                    <a href="{{ route('register') }}" class="btn-emerald">Get Started Free</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

@yield('content')

<!-- ════════ FOOTER ════════ -->
<footer class="py-5 mt-5" style="background:var(--ink-panel);color:#fff;">
    <div class="container pt-3">
        <div class="row g-4 pb-4" style="border-bottom:1px solid rgba(255,255,255,.07);">
            <div class="col-lg-4">
                <a class="d-inline-flex align-items-center gap-2 text-decoration-none mb-3" href="{{ url('/') }}">
                    <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect width="32" height="32" rx="9" fill="#00c97b"/>
                        <path d="M10 16h13M16.5 9.5 23 16l-6.5 6.5" stroke="#0a0f1e" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span style="font-weight:800;font-size:1.4rem;letter-spacing:-.4px;color:#fff;">Flik<em style="font-style:normal;color:var(--emerald);">ma</em></span>
                </a>
                <p style="font-size:.85rem;color:rgba(255,255,255,.35);max-width:280px;line-height:1.65;">
                    AI-powered cloud ERP for freight forwarding and 3PL — with ZATCA Phase 2 e-invoicing, AI expense capture and document scanning built in. Aligned with Saudi Vision 2030.
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" aria-label="LinkedIn" style="color:rgba(255,255,255,.45);font-size:1.05rem;"><i class="bi bi-linkedin"></i></a>
                    <a href="#" aria-label="X" style="color:rgba(255,255,255,.45);font-size:1.05rem;"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="YouTube" style="color:rgba(255,255,255,.45);font-size:1.05rem;"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div style="font-size:.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3);">Platform</div>
                <ul class="list-unstyled mt-3 mb-0">
                    <li class="mb-2"><a href="{{ url('/features') }}#operations" style="font-size:.85rem;color:rgba(255,255,255,.45);">Freight Operations</a></li>
                    <li class="mb-2"><a href="{{ url('/features') }}#bl" style="font-size:.85rem;color:rgba(255,255,255,.45);">Bills of Lading</a></li>
                    <li class="mb-2"><a href="{{ url('/features') }}#finance" style="font-size:.85rem;color:rgba(255,255,255,.45);">Billing &amp; Finance</a></li>
                    <li class="mb-2"><a href="{{ url('/features') }}#ai" style="font-size:.85rem;color:rgba(255,255,255,.45);">AI Document Scanning</a></li>
                    <li class="mb-2"><a href="{{ url('/features') }}#expenses" style="font-size:.85rem;color:rgba(255,255,255,.45);">AI Expense Capture</a></li>
                    <li class="mb-2"><a href="{{ url('/features') }}#zatca" style="font-size:.85rem;color:rgba(255,255,255,.45);">ZATCA Phase 2</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <div style="font-size:.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3);">Company</div>
                <ul class="list-unstyled mt-3 mb-0">
                    <li class="mb-2"><a href="{{ url('/about') }}" style="font-size:.85rem;color:rgba(255,255,255,.45);">About Us</a></li>
                    <li class="mb-2"><a href="{{ url('/why-flikma') }}" style="font-size:.85rem;color:rgba(255,255,255,.45);">Why Flikma</a></li>
                    <li class="mb-2"><a href="{{ url('/services') }}" style="font-size:.85rem;color:rgba(255,255,255,.45);">Services</a></li>
                    <li class="mb-2"><a href="{{ url('/pricing') }}" style="font-size:.85rem;color:rgba(255,255,255,.45);">Pricing</a></li>
                    <li class="mb-2"><a href="{{ url('/contact') }}" style="font-size:.85rem;color:rgba(255,255,255,.45);">Contact</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <div style="font-size:.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3);">Resources</div>
                <ul class="list-unstyled mt-3 mb-0">
                    <li class="mb-2"><a href="{{ url('/documentation') }}" style="font-size:.85rem;color:rgba(255,255,255,.45);">Documentation</a></li>
                    <li class="mb-2"><a href="{{ url('/documentation') }}#operations" style="font-size:.85rem;color:rgba(255,255,255,.45);">Getting Started</a></li>
                    <li class="mb-2"><a href="{{ url('/documentation') }}#zatca" style="font-size:.85rem;color:rgba(255,255,255,.45);">ZATCA Guide</a></li>
                    <li class="mb-2"><a href="{{ url('/documentation') }}#ai" style="font-size:.85rem;color:rgba(255,255,255,.45);">AI Scanning Guide</a></li>
                    <li class="mb-2"><a href="https://zatca.gov.sa" target="_blank" rel="noopener" style="font-size:.85rem;color:rgba(255,255,255,.45);">ZATCA Portal <i class="bi bi-box-arrow-up-right" style="font-size:.7rem;"></i></a></li>
                </ul>
            </div>

            <div class="col-lg-2">
                <div style="font-size:.7rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.3);">Contact</div>
                <ul class="list-unstyled mt-3 mb-0">
                    <li class="mb-2 d-flex align-items-start gap-2"><i class="bi bi-telephone" style="color:var(--emerald);font-size:.85rem;margin-top:3px;"></i><a href="tel:+966595555343" style="font-size:.85rem;color:rgba(255,255,255,.45);" dir="ltr">+966 59 555 5343</a></li>
                    <li class="mb-2 d-flex align-items-start gap-2"><i class="bi bi-envelope" style="color:var(--emerald);font-size:.85rem;margin-top:3px;"></i><a href="mailto:support@flikma.com" style="font-size:.85rem;color:rgba(255,255,255,.45);">support@flikma.com</a></li>
                    <li class="mb-2 d-flex align-items-start gap-2"><i class="bi bi-geo-alt" style="color:var(--emerald);font-size:.85rem;margin-top:3px;"></i><span style="font-size:.85rem;color:rgba(255,255,255,.45);">Riyadh, Saudi Arabia</span></li>
                </ul>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-4 gap-2">
            <span style="font-size:.8rem;color:rgba(255,255,255,.25);">&copy; {{ date('Y') }} Flikma. All rights reserved. Built for Saudi Vision 2030.</span>
            <div class="d-flex gap-3">
                <a href="{{ url('/about') }}" style="font-size:.8rem;color:rgba(255,255,255,.3);">About</a>
                <a href="{{ url('/contact') }}" style="font-size:.8rem;color:rgba(255,255,255,.3);">Contact</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    /* Scroll reveal — any .reveal element fades up once it enters the viewport. */
    (function () {
        var els = document.querySelectorAll('.reveal');
        if (!els.length) return;
        if (!('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('visible'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var i = Array.prototype.indexOf.call(entry.target.parentNode.children, entry.target);
                setTimeout(function () { entry.target.classList.add('visible'); }, Math.max(0, i) * 80);
                io.unobserve(entry.target);
            });
        }, { threshold: 0.08 });
        els.forEach(function (el) { io.observe(el); });
    })();

    /* Sticky sidebar scroll-spy for pages that declare data-spy-nav. */
    (function () {
        var nav = document.querySelector('[data-spy-nav]');
        if (!nav) return;
        var links = nav.querySelectorAll('.nav-link');
        var sections = [].slice.call(document.querySelectorAll('section[id]'));
        if (!links.length || !sections.length) return;
        window.addEventListener('scroll', function () {
            var y = window.scrollY;
            var current = null;
            sections.forEach(function (s) {
                if (s.offsetTop - 140 <= y) current = s.id;
            });
            links.forEach(function (l) {
                l.classList.toggle('active', l.getAttribute('href') === '#' + current);
            });
        }, { passive: true });
    })();
</script>

@yield('extra_scripts')
</body>
</html>
