@extends('website.layout')

@section('title', 'Flikma — AI Logistics Software for Freight Forwarders | ZATCA Phase 2 Ready')
@section('meta_description', 'Flikma is the AI logistics ERP for freight forwarders and 3PLs in Saudi Arabia. Run enquiries, jobs, bills of lading, AI-scanned expenses and ZATCA Phase 2 e-invoicing from one cloud platform. No warehouse module — pure logistics.')
@section('meta_keywords', 'logistics software Saudi Arabia, freight forwarding software, 3PL ERP GCC, ZATCA Phase 2 e-invoicing, AI document scanning, AI expense capture, logistics software Bahrain, freight software Dubai')

@section('content')

    @php
        // ── Section anchors shared with the navbar, footer and Features page ──
        $modules = [
            ['id' => 'operations', 'icon' => 'bi-globe-americas',      'accent' => 'var(--emerald)', 'tint' => 'var(--emerald-soft)',  'title' => 'Freight Operations',        'desc' => 'Enquiry to delivery — air, sea and road shipments tracked end to end with live milestones.'],
            ['id' => 'bl',         'icon' => 'bi-file-earmark-text',    'accent' => 'var(--navy)',     'tint' => 'rgba(11,23,54,.07)',      'title' => 'Bills of Lading',           'desc' => 'Airway bills, sea waybills and road waybills printed to carrier specification.'],
            ['id' => 'finance',    'icon' => 'bi-receipt',              'accent' => 'var(--blue)',     'tint' => 'rgba(58,107,255,.09)',    'title' => 'Billing & Finance',         'desc' => 'Proforma, customer and supplier invoicing, payments, collections and credit notes.'],
            ['id' => 'ai',         'icon' => 'bi-stars',                'accent' => 'var(--indigo)',   'tint' => 'rgba(79,70,229,.09)',     'title' => 'AI Document Scanning',      'desc' => 'Scan any supplier invoice or bill of lading and let AI read the fields for you.'],
            ['id' => 'expenses',   'icon' => 'bi-wallet2',              'accent' => 'var(--red)',      'tint' => 'rgba(239,68,68,.09)',     'title' => 'AI Expense Capture',        'desc' => 'Photograph a receipt on your phone — AI extracts the vendor, VAT and GL code.'],
            ['id' => 'zatca',      'icon' => 'bi-shield-check',         'accent' => 'var(--emerald-dim)','tint' => 'var(--emerald-soft)',     'title' => 'ZATCA Phase 2',             'desc' => 'UBL 2.1 XML, ECDSA signing, QR stamping and real-time clearance out of the box.'],
            ['id' => 'payroll',    'icon' => 'bi-people-fill',          'accent' => 'var(--violet)',   'tint' => 'rgba(124,58,237,.09)',    'title' => 'Payroll & Attendance',      'desc' => 'Salaries, WPS-ready exports, attendance and employee loans posted to the ledger.'],
            ['id' => 'reports',    'icon' => 'bi-graph-up-arrow',       'accent' => 'var(--cyan)',     'tint' => 'rgba(6,182,212,.09)',     'title' => 'Reports & Analytics',       'desc' => 'Trial balance, aging, job profitability and tax reports — live, not month-end.'],
        ];
    @endphp

    <style>
        /* Page-scoped composition on top of the shared design system. */
        #hero { padding: 128px 0 0; position: relative; overflow: hidden; background: #fff; }
        .hero-bg-grid {
            position: absolute; inset: 0; z-index: 0;
            background-image: linear-gradient(var(--grid-line) 1px, transparent 1px), linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, black 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 50% 50%, black 40%, transparent 100%);
        }
        .hero-orb { position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
        .hero-orb-1 { width: 560px; height: 560px; background: radial-gradient(circle, rgba(0,201,123,.14) 0%, transparent 70%); top: -100px; right: -140px; }
        .hero-orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(58,107,255,.08) 0%, transparent 70%); bottom: -60px; left: -100px; }

        .hero-title { font-size: clamp(2.3rem, 4.6vw, 3.6rem); font-weight: 800; line-height: 1.08; }
        .underline-gold { position: relative; display: inline-block; }
        .underline-gold::after { content: ''; position: absolute; left: 0; right: 0; bottom: 2px; height: 4px; background: var(--gold); border-radius: 2px; opacity: .75; }

        .vision-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: var(--emerald-soft); border: 1px solid rgba(0,201,123,.3);
            color: var(--emerald-dim); font-size: .72rem; font-weight: 700;
            letter-spacing: .8px; text-transform: uppercase; padding: .4rem .9rem; border-radius: 50px;
        }

        /* Full-bleed product shot */
        .hero-shot { position: relative; z-index: 1; margin-top: 3.5rem; }
        .hero-shot-inner { position: relative; width: min(1280px, 94vw); margin-inline: auto; }
        .app-frame { background: #fff; border: 1px solid var(--line); border-radius: 20px 20px 0 0; box-shadow: 0 40px 90px rgba(10,15,30,.16); overflow: hidden; text-align: left; }
        .app-chrome { display: flex; align-items: center; gap: .45rem; padding: .7rem 1rem; background: var(--surface); border-bottom: 1px solid var(--line); }
        .app-chrome .dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .app-url { margin-left: .8rem; font-size: .72rem; color: var(--ink-ghost); background: #fff; border: 1px solid var(--line); border-radius: 50px; padding: .2rem .8rem; }
        .app-body { padding: 1.1rem; display: grid; gap: 1rem; }
        .app-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: .8rem; }
        .kpi { border: 1px solid var(--line); border-radius: 14px; padding: .85rem 1rem; background: #fff; }
        .kpi-label { font-size: .66rem; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--ink-ghost); }
        .kpi-val { font-size: 1.5rem; font-weight: 800; color: var(--ink); line-height: 1.2; margin: .2rem 0 .15rem; }
        .kpi-val small { font-size: .8rem; font-weight: 700; color: var(--ink-ghost); }
        .kpi-delta { font-size: .7rem; font-weight: 700; display: flex; align-items: center; gap: .3rem; }
        .kpi-delta.up  { color: var(--emerald-dim); }
        .kpi-delta.ok  { color: var(--ink-muted); }
        .app-main { display: grid; grid-template-columns: 1.85fr 1fr; gap: 1rem; }
        .app-table { border: 1px solid var(--line); border-radius: 14px; overflow: hidden; }
        .app-row { display: grid; grid-template-columns: 1.6fr 1fr .9fr 1.1fr; gap: .6rem; align-items: center; padding: .62rem 1rem; border-bottom: 1px solid var(--grid-line); font-size: .78rem; }
        .app-row:last-child { border-bottom: 0; }
        .app-head { font-size: .64rem; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--ink-ghost); background: var(--surface); }
        .app-row .ref { font-weight: 700; color: var(--ink); }
        .app-row .client { color: var(--ink-ghost); font-size: .72rem; }
        .app-row .lane { color: var(--ink-muted); font-size: .76rem; }
        .status-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: .45rem; }
        .tone-ok   { background: var(--emerald); }
        .tone-warn { background: var(--gold); }
        .tone-info { background: var(--blue); }
        .tone-doc  { background: var(--violet); }
        .app-rail { display: grid; gap: .8rem; align-content: start; }
        .rail-block { border: 1px solid var(--line); border-radius: 14px; padding: .9rem 1rem; background: var(--surface); }
        .rail-title { font-size: .78rem; font-weight: 700; color: var(--ink); margin-bottom: .55rem; display: flex; align-items: center; gap: .45rem; }
        .rail-title i { color: var(--emerald-dim); }
        .rail-line { font-size: .74rem; color: var(--ink-muted); display: flex; align-items: center; gap: .4rem; }
        .rail-line.muted { color: var(--ink-ghost); margin-top: .2rem; }
        .rail-item { font-size: .74rem; color: var(--ink-muted); display: flex; align-items: center; gap: .4rem; margin-bottom: .35rem; }
        .rail-tag { margin-left: auto; font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--emerald-dim); background: var(--emerald-soft); border-radius: 50px; padding: .1rem .45rem; }

        .float-dot { width: 36px; height: 36px; border-radius: 10px; background: var(--emerald-glow); display: flex; align-items: center; justify-content: center; color: var(--emerald-dim); }

        .mode-chip { display: inline-flex; align-items: center; gap: .3rem; font-size: .66rem; font-weight: 700; padding: .2rem .5rem; border-radius: 50px; }
        .mode-air  { background: rgba(58,107,255,.1);  color: var(--blue); }
        .mode-sea  { background: rgba(6,182,212,.12);  color: #0891b2; }
        .mode-road { background: rgba(124,58,237,.1);  color: var(--violet); }

        .pulse-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--emerald); display: inline-block; animation: pulse 1.5s ease-in-out infinite; }

        /* AI scan card */
        .ai-feat-card { border: 1.5px solid rgba(79,70,229,.2); border-radius: 18px; background: linear-gradient(155deg,#fbfaff 0%,#fff 55%); position: relative; overflow: hidden; }
        .ai-feat-card::before { content: ''; position: absolute; top: -60px; right: -60px; width: 240px; height: 240px; background: radial-gradient(circle, rgba(79,70,229,.18) 0%, transparent 70%); pointer-events: none; }
        .ai-pill { display: inline-flex; align-items: center; gap: .4rem; background: rgba(79,70,229,.1); border: 1px solid rgba(79,70,229,.25); color: var(--indigo); font-size: .68rem; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; padding: .3rem .8rem; border-radius: 50px; }

        /* Scan overlay animation for the AI document card */
        .scan-doc { position: relative; background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 1.1rem; overflow: hidden; }
        .scan-line { position: absolute; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, var(--indigo), transparent); animation: scanSweep 2.4s ease-in-out infinite; }
        @keyframes scanSweep { 0% { top: 4px; opacity: 0; } 15% { opacity: 1; } 85% { opacity: 1; } 100% { top: calc(100% - 4px); opacity: 0; } }
        .scan-field { display: flex; justify-content: space-between; gap: .5rem; padding: .38rem 0; border-bottom: 1px dashed var(--line); font-size: .74rem; }
        .scan-field:last-of-type { border-bottom: 0; }
        .scan-field .k { color: var(--ink-ghost); }
        .scan-field .v { font-weight: 600; color: var(--ink); text-align: right; }
        .scan-field .v.ok { color: var(--emerald-dim); }

        /* Stats */
        .stats-bar { background: var(--ink); }
        .stat-val { font-size: 2.1rem; font-weight: 800; color: #fff; line-height: 1.1; letter-spacing: -.03em; }
        .stat-val span { color: var(--emerald); }
        .stat-label { font-size: .8rem; color: rgba(255,255,255,.45); }

        /* Dark compliance section */
        .dark-check li { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.15rem; color: rgba(255,255,255,.7); font-size: .92rem; line-height: 1.6; }
        .dark-check i { color: var(--emerald); font-size: 1.05rem; margin-top: 3px; }
        .flow-step-item { display: flex; align-items: flex-start; gap: 1rem; position: relative; }
        .flow-step-item:not(:last-child)::after { content: ''; position: absolute; left: 12px; top: 30px; bottom: -16px; width: 1px; background: rgba(255,255,255,.1); }
        .flow-num { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0; background: var(--emerald); color: var(--ink); font-size: .65rem; font-weight: 800; display: flex; align-items: center; justify-content: center; }
        .ledger-card { background: var(--ink); border-radius: 14px; padding: 1.25rem; }
        .ledger-row { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: .5rem; padding: .45rem 0; border-bottom: 1px solid rgba(255,255,255,.05); font-size: .76rem; }
        .ledger-row:last-child { border-bottom: 0; }
        .ledger-row.head { color: rgba(255,255,255,.4); font-size: .66rem; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; border-bottom-color: rgba(255,255,255,.15); }

        /* Pricing teaser */
        .pricing-card { border: 1.5px solid var(--line); border-radius: 20px; background: #fff; padding: 2rem 1.75rem; height: 100%; transition: all .25s; }
        .pricing-card:hover { box-shadow: 0 20px 50px rgba(10,15,30,.07); transform: translateY(-4px); }
        .pricing-card.featured { background: var(--ink); border-color: var(--ink); transform: scale(1.03); box-shadow: 0 28px 60px rgba(10,15,30,.22); }
        .pricing-card.featured .plan-name, .pricing-card.featured .plan-price { color: #fff; }
        .pricing-card.featured .plan-desc, .pricing-card.featured .plan-feats li { color: rgba(255,255,255,.55); }
        .pricing-card.featured .plan-feats li i { color: var(--emerald); }
        .popular-chip { display: inline-block; background: var(--emerald); color: var(--ink); font-size: .65rem; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: .3rem .8rem; border-radius: 50px; margin-bottom: .9rem; }
        .plan-price { font-size: 2.4rem; font-weight: 800; letter-spacing: -.04em; line-height: 1; }
        .plan-price small { font-size: .85rem; font-weight: 500; color: var(--ink-ghost); letter-spacing: 0; }
        .pricing-card.featured .plan-price small { color: rgba(255,255,255,.4); }
        .plan-name { font-size: 1.05rem; font-weight: 700; }
        .plan-desc { font-size: .85rem; color: var(--ink-muted); }
        .plan-feats { list-style: none; padding: 0; margin: 1.25rem 0 0; }
        .plan-feats li { display: flex; align-items: flex-start; gap: .55rem; font-size: .85rem; color: var(--ink-muted); margin-bottom: .6rem; }
        .plan-feats i { color: var(--emerald-dim); font-size: .9rem; margin-top: 2px; }

        @media (max-width: 1199.98px) {
            .app-kpis { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 991.98px) {
            #hero { padding-top: 100px; }
            .hero-shot { margin-top: 2.5rem; }
            .app-main { grid-template-columns: 1fr; }
            .app-rail { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 575.98px) {
            #hero { padding-top: 88px; }
            .hero-title br { display: none; }
            .app-kpis { grid-template-columns: 1fr; }
            .app-rail { grid-template-columns: 1fr; }
            .app-row { grid-template-columns: 1.5fr .9fr; row-gap: .3rem; }
            .app-row > span:nth-child(3) { display: none; }
        }
    </style>

    <!-- ════════════════ HERO ════════════════ -->
    <header id="hero">
        <div class="hero-bg-grid"></div>
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>

        <div class="container position-relative text-center" style="z-index:1;">
            <div class="vision-badge mb-4 anim-hero d-inline-flex">
                <i class="bi bi-stars"></i> ZATCA Phase 2 Ready &middot; Saudi Vision 2030
            </div>

            <h1 class="hero-title mb-4 anim-hero delay-1">
                Run your entire forwarding business<br>
                on <span class="underline-gold">one screen</span>
            </h1>

            <p class="anim-hero delay-2 mx-auto mb-5" style="font-size:1.08rem;line-height:1.75;color:var(--ink-muted);max-width:660px;">
                Enquiries, jobs, bills of lading, invoices, collections and payroll &mdash; one cloud platform
                for freight forwarders across Saudi Arabia, Bahrain and the UAE. AI reads your documents,
                ZATCA clearance is automatic, and every number is live.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4 anim-hero delay-3">
                <a href="{{ route('register') }}" class="btn-hero-primary">Start Free Trial <i class="bi bi-arrow-right"></i></a>
                <a href="{{ url('/contact') }}" class="btn-hero-outline">Book a Live Demo <i class="bi bi-calendar-check"></i></a>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 anim-hero delay-4">
                <div class="d-flex">
                    <span class="trust-avatar">RS</span><span class="trust-avatar">AK</span>
                    <span class="trust-avatar">MA</span><span class="trust-avatar">FH</span>
                </div>
                <small class="text-ink-ghost">Trusted by freight forwarders across <strong class="text-dark">Saudi Arabia, Bahrain &amp; UAE</strong></small>
            </div>
        </div>

        <!-- Full-bleed product shot -->
        <div class="hero-shot">
            <div class="hero-shot-inner">
                <div class="app-frame anim-hero delay-3">
                    <div class="app-chrome">
                        <span class="dot" style="background:#ff5f57;"></span>
                        <span class="dot" style="background:#ffbd2e;"></span>
                        <span class="dot" style="background:#28c840;"></span>
                        <span class="app-url">app.flikma.com/operations</span>
                    </div>

                    <div class="app-body">
                        <div class="app-kpis">
                            <div class="kpi">
                                <div class="kpi-label">Live jobs</div>
                                <div class="kpi-val">148</div>
                                <div class="kpi-delta up"><i class="bi bi-arrow-up-short"></i> 12 today</div>
                            </div>
                            <div class="kpi">
                                <div class="kpi-label">Invoiced this month</div>
                                <div class="kpi-val">1.42M <small>SAR</small></div>
                                <div class="kpi-delta up"><i class="bi bi-arrow-up-short"></i> 18.4%</div>
                            </div>
                            <div class="kpi">
                                <div class="kpi-label">ZATCA cleared</div>
                                <div class="kpi-val">99.9<small>%</small></div>
                                <div class="kpi-delta ok"><span class="pulse-dot"></span> Live sync</div>
                            </div>
                            <div class="kpi">
                                <div class="kpi-label">AI documents parsed</div>
                                <div class="kpi-val">3,806</div>
                                <div class="kpi-delta up"><i class="bi bi-stars"></i> Zero retyping</div>
                            </div>
                        </div>

                        <div class="app-main">
                            <div class="app-table">
                                <div class="app-row app-head">
                                    <span>Reference</span><span>Lane</span><span>Mode</span><span>Status</span>
                                </div>
                                @foreach ([
                                    ['JOB-2026-0418', 'Al Mouil Marine', 'JED &rarr; RUH', 'sea',  'Cleared',    'ok'],
                                    ['JOB-2026-0421', 'NAC Cargo',       'DMM &rarr; BAH', 'air',  'In transit', 'warn'],
                                    ['JOB-2026-0427', 'Al Sharq Logistics', 'RUH &rarr; DXB', 'road', 'Arriving', 'info'],
                                    ['JOB-2026-0430', 'Gulf Sea Lines',  'JED &rarr; SHA', 'sea',  'Docs ready', 'doc'],
                                ] as [$ref, $client, $lane, $mode, $status, $tone])
                                    <div class="app-row">
                                        <span><span class="ref">{{ $ref }}</span><br><span class="client">{{ $client }}</span></span>
                                        <span class="lane">{{ $lane }}</span>
                                        <span><span class="mode-chip mode-{{ $mode }}"><i class="bi bi-{{ $mode === 'sea' ? 'water' : ($mode === 'air' ? 'airplane' : 'truck') }}"></i> {{ ucfirst($mode) }}</span></span>
                                        <span><span class="status-dot tone-{{ $tone }}"></span>{{ $status }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="app-rail">
                                <div class="rail-block">
                                    <div class="rail-title"><i class="bi bi-shield-lock"></i> ZATCA Phase 2</div>
                                    <div class="rail-line"><span class="pulse-dot"></span> Clearance engine online</div>
                                    <div class="rail-line muted">UBL 2.1 &middot; QR &middot; cryptographic stamp</div>
                                </div>
                                <div class="rail-block">
                                    <div class="rail-title"><i class="bi bi-stars"></i> AI queue</div>
                                    <div class="rail-item"><i class="bi bi-file-earmark-text"></i> BL-8821.pdf <span class="rail-tag">parsed</span></div>
                                    <div class="rail-item"><i class="bi bi-receipt"></i> fuel-receipt.jpg <span class="rail-tag">coded</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- ════════════════ STATS ════════════════ -->
    <section class="stats-bar">
        <div class="container py-5">
            <div class="row text-center g-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-val"><span>99.9%</span></div>
                    <div class="stat-label">ZATCA clearance success rate</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-val">6 <span>sec</span></div>
                    <div class="stat-label">Average document scan &amp; field extraction</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-val">70<span>%</span></div>
                    <div class="stat-label">Less time spent typing supplier bills</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-val">3 <span>countries</span></div>
                    <div class="stat-label">Saudi Arabia, Bahrain &amp; UAE</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ AI — THE DIFFERENTIATOR ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">AI That Reads Your Paperwork</div>
                <h2>Stop retyping documents. Flikma reads them.</h2>
                <p>Forwarders live on paperwork. Supplier bills, customs entries, delivery orders and receipts pile up every single day.
                   Flikma's AI reads them the moment they land and structures them straight into your books.</p>
            </div>

            <div class="row g-4 align-items-stretch">
                <div class="col-lg-7 reveal">
                    <div class="ai-feat-card p-4 p-md-5 h-100">
                        <div class="position-relative">
                            <span class="ai-pill"><i class="bi bi-stars"></i> AI Document Scanning</span>
                            <h3 class="mt-3 mb-3" style="font-size:1.6rem;font-weight:800;">Upload once. The form fills itself.</h3>
                            <p class="mb-4" style="color:var(--ink-muted);font-size:.98rem;line-height:1.75;">
                                Drop in a supplier invoice, airway bill, sea waybill or bill of lading. Flikma extracts the
                                invoice number, dates, vendor, line items, totals and VAT number, then maps them onto the
                                right GL accounts and the right logistics service codes.
                            </p>

                            <div class="scan-doc">
                                <div class="scan-line"></div>
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:1px solid var(--line);">
                                    <i class="bi bi-file-earmark-pdf" style="font-size:1.4rem;color:var(--red);"></i>
                                    <div style="line-height:1.2;">
                                        <div style="font-weight:700;font-size:.82rem;">AL-NOBA TRANSPORT CO. — INV-88214</div>
                                        <div style="font-size:.68rem;color:var(--ink-ghost);">scanning.pdf &middot; 412 KB</div>
                                    </div>
                                    <span class="mode-chip ms-auto" style="background:rgba(79,70,229,.1);color:var(--indigo);"><i class="bi bi-stars"></i> AI</span>
                                </div>
                                <div class="scan-field"><span class="k">Invoice No.</span><span class="v ok">INV-88214 <i class="bi bi-check-circle-fill" style="color:var(--emerald);font-size:.7rem;"></i></span></div>
                                <div class="scan-field"><span class="k">Invoice Date</span><span class="v ok">2026-07-14 <i class="bi bi-check-circle-fill" style="color:var(--emerald);font-size:.7rem;"></i></span></div>
                                <div class="scan-field"><span class="k">Supplier VAT No.</span><span class="v ok">310022393500003</span></div>
                                <div class="scan-field"><span class="k">Freight &amp; Handling</span><span class="v">8,400.00 SAR</span></div>
                                <div class="scan-field"><span class="k">VAT 15%</span><span class="v">1,260.00 SAR</span></div>
                                <div class="scan-field"><span class="k">Grand Total</span><span class="v ok">9,660.00 SAR</span></div>
                                <div class="d-flex align-items-center gap-2 mt-3 pt-2" style="border-top:1px dashed var(--line);font-size:.72rem;color:var(--ink-ghost);">
                                    <i class="bi bi-magic"></i> Matched to account <strong style="color:var(--ink);">Freight Expense &mdash; Sea Freight</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="d-flex flex-column gap-4 h-100">
                        <div class="feat-card p-4 reveal">
                            <div class="d-flex align-items-start gap-3">
                                <div class="feat-icon-box" style="background:rgba(239,68,68,.09);color:var(--red);"><i class="bi bi-receipt-cutoff"></i></div>
                                <div>
                                    <h5 class="feat-title mb-1">AI Expense Capture</h5>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.65;">
                                        Photograph a fuel receipt, a courier charge or a customs fee. AI reads the vendor,
                                        the amount and the VAT, then posts it to the correct expense head with the
                                        receipt attached. No timesheets of manual entry.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="feat-card p-4 reveal">
                            <div class="d-flex align-items-start gap-3">
                                <div class="feat-icon-box" style="background:rgba(79,70,229,.09);color:var(--indigo);"><i class="bi bi-diagram-3"></i></div>
                                <div>
                                    <h5 class="feat-title mb-1">Reads more than invoices</h5>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.65;">
                                        Airway bills, sea waybills, bills of lading, customs declarations and packing lists
                                        are all parsable — including multi-page tables and handwriting on delivery notes.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="feat-card p-4 reveal">
                            <div class="d-flex align-items-start gap-3">
                                <div class="feat-icon-box" style="background:var(--emerald-soft);color:var(--emerald-dim);"><i class="bi bi-boxes"></i></div>
                                <div>
                                    <h5 class="feat-title mb-1">Your master data, not a black box</h5>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.65;">
                                        Flikma suggests chart-of-accounts and logistics service codes from your own data,
                                        so what it learns is reusable across every quotation, job and invoice.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-4 reveal" style="background:var(--ink);">
                            <div class="d-flex align-items-center gap-3">
                                <span class="float-dot"><i class="bi bi-shield-lock"></i></span>
                                <p class="mb-0" style="font-size:.82rem;color:rgba(255,255,255,.6);line-height:1.6;">
                                    Documents are processed in an isolated queue and never used to train shared models.
                                    Your freight data stays yours.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ MODULES ════════════════ -->
    <section class="fk-section" id="modules">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">One Platform</div>
                <h2>Every module a freight forwarder actually needs</h2>
                <p>No warehouse module, no stock picking, no bloat. Flikma is built end to end for the
                   enquiry &rarr; quotation &rarr; shipment &rarr; invoice &rarr; collection lifecycle.</p>
            </div>

            <div class="row g-4">
                @foreach ($modules as $m)
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ url('/features') }}#{{ $m['id'] }}" class="feat-card p-4 d-block h-100 reveal">
                            <div class="feat-icon-box mb-3" style="background:{{ $m['tint'] }};color:{{ $m['accent'] }};">
                                <i class="bi {{ $m['icon'] }}"></i>
                            </div>
                            <h6 class="feat-title mb-2">{{ $m['title'] }}</h6>
                            <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.6;">{{ $m['desc'] }}</p>
                            <span class="d-inline-flex align-items-center gap-1 mt-3 small fw-semibold" style="color:{{ $m['accent'] }};font-size:.8rem;">
                                Learn more <i class="bi bi-arrow-right" style="font-size:.75rem;"></i>
                            </span>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-5 reveal">
                <a href="{{ url('/features') }}" class="btn-hero-outline">
                    Explore all features <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ════════════════ WORKFLOW ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 reveal">
                    <div class="section-label">The Freight Lifecycle</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                        One thread from the first enquiry to the final riyal
                    </h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        Every module writes to the same ledger, so a shipment's cost, revenue and margin are
                        visible while the job is still moving &mdash; not six weeks after it closed.
                    </p>

                    <div class="ledger-card mb-4">
                        <div class="ledger-row head"><span>Account</span><span>Debit</span><span>Credit</span></div>
                        <div class="ledger-row"><span style="color:rgba(255,255,255,.75);">Accounts Receivable</span><span style="color:#fff;">24,600.00</span><span style="color:rgba(255,255,255,.3);">&mdash;</span></div>
                        <div class="ledger-row"><span style="color:rgba(255,255,255,.75);">Freight Revenue &mdash; Air</span><span style="color:rgba(255,255,255,.3);">&mdash;</span><span style="color:#fff;">21,300.00</span></div>
                        <div class="ledger-row"><span style="color:rgba(255,255,255,.75);">Carrier Payable</span><span style="color:#fff;">17,800.00</span><span style="color:rgba(255,255,255,.3);">&mdash;</span></div>
                        <div class="ledger-row"><span style="color:rgba(255,255,255,.75);">VAT Output 15%</span><span style="color:rgba(255,255,255,.3);">&mdash;</span><span style="color:#fff;">3,300.00</span></div>
                        <div class="ledger-row"><span style="font-weight:700;color:#fff;">ZATCA clearance</span><span colspan="2" style="color:var(--emerald);text-align:right;font-weight:700;">&#10003; Reported &middot; QR stamped</span></div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="bg-ink rounded-4 p-4 p-md-5 reveal">
                        <div class="d-flex flex-column gap-4">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="mode-chip" style="background:var(--emerald);color:var(--ink);font-size:.64rem;">THE CHAIN</span>
                            </div>
                            <div class="flow-step-item">
                                <span class="flow-num">1</span>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#fff;">Enquiry &amp; Quotation</h6>
                                    <p class="mb-0" style="font-size:.85rem;color:rgba(255,255,255,.5);line-height:1.6;">
                                        Capture the request, build a multi-leg quotation with real carrier rates and win or lose it against a deadline.
                                    </p>
                                </div>
                            </div>
                            <div class="flow-step-item">
                                <span class="flow-num">2</span>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#fff;">Job &amp; Bill of Lading</h6>
                                    <p class="mb-0" style="font-size:.85rem;color:rgba(255,255,255,.5);line-height:1.6;">
                                        Convert the won quote into a job, track every container, package and batch, and print the AWB, sea waybill or road waybill.
                                    </p>
                                </div>
                            </div>
                            <div class="flow-step-item">
                                <span class="flow-num">3</span>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#fff;">Invoice &amp; ZATCA Clearance</h6>
                                    <p class="mb-0" style="font-size:.85rem;color:rgba(255,255,255,.5);line-height:1.6;">
                                        Issue a Phase 2 compliant tax or simplified invoice. Flikma signs the UBL 2.1 XML, stamps the QR and reports to ZATCA.
                                    </p>
                                </div>
                            </div>
                            <div class="flow-step-item">
                                <span class="flow-num">4</span>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#fff;">Payment &amp; Collection</h6>
                                    <p class="mb-0" style="font-size:.85rem;color:rgba(255,255,255,.5);line-height:1.6;">
                                        Record receipts against invoices, chase the aging, and see the job's true margin the moment the money lands.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ ZATCA / COMPLIANCE ════════════════ -->
    <section class="fk-section bg-ink">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="section-label">Saudi Compliance</div>
                    <h2 class="mt-2 mb-3" style="color:#fff;font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                        ZATCA Phase 2 is not an add-on. It is the backbone.
                    </h2>
                    <p class="mb-4" style="color:rgba(255,255,255,.55);line-height:1.75;">
                        Most forwarders bolt e-invoicing onto an accounting package and fight it. In Flikma, compliance
                        is wired through the customer, the invoice and the ledger &mdash; so the right document is
                        produced automatically, every time.
                    </p>

                    <ul class="dark-check list-unstyled mb-0">
                        <li><i class="bi bi-check-circle-fill"></i><span>Automatic B2B tax invoice vs. B2C simplified invoice, decided by the customer's VAT registration</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>ECDSA / CAdES cryptographic signing and XAdES signed properties built into the invoice pipeline</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>UBL 2.1 XML generation with mandatory QR code stamping for buyer-side validation</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Real-time clearance for standard invoices; deferred reporting for simplified</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Credit notes and debit notes reported through the same compliant pipeline</span></li>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <div class="bg-white rounded-4 p-4 p-md-5 reveal">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <div class="fw-bold" style="font-size:.85rem;">Tax Invoice &middot; فاتورة ضريبية</div>
                                <div class="mt-1" style="font-size:.68rem;color:var(--ink-ghost);">INV-2026-00841 &middot; Al Mouil Marine</div>
                            </div>
                            <div class="text-end">
                                <span class="badge rounded-pill fw-bold" style="background:var(--emerald);color:var(--ink);font-size:.63rem;">PHASE 2</span>
                                <div class="mt-1" style="font-size:.62rem;color:var(--ink-ghost);">UUID: 8f2c&hellip;a91b</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="stat-chip h-100" style="padding:1rem;">
                                    <div class="chip-value" style="color:var(--emerald-dim);font-size:1.35rem;"><i class="bi bi-check-circle-fill"></i></div>
                                    <div class="chip-label" style="font-size:.72rem;">Clearance Status</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-chip h-100" style="padding:1rem;">
                                    <div class="chip-value" style="color:var(--blue);font-size:1.35rem;">&lt; 2s</div>
                                    <div class="chip-label" style="font-size:.72rem;">Gateway Response</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between py-2 border-bottom border-grid"><span class="small text-ink-ghost">Freight &mdash; Sea JED/RUH</span><span class="fw-semibold small">8,400.00 SAR</span></div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-grid"><span class="small text-ink-ghost">Customs &amp; clearance</span><span class="fw-semibold small">1,850.00 SAR</span></div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-grid"><span class="small text-ink-ghost">VAT 15%</span><span class="fw-semibold small">1,537.50 SAR</span></div>
                            <div class="d-flex justify-content-between align-items-center mt-2 p-3 rounded-3 bg-surface">
                                <span class="fw-bold small">Total Due</span>
                                <span class="fw-bold fs-4" style="color:var(--emerald-dim);">11,787.50 SAR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 reveal">
                <a href="{{ url('/documentation') }}#zatca" class="btn-outline-light-fk">
                    Read the ZATCA setup guide <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ════════════════ WHY FLIKMA (condensed) ════════════════ -->
    <section class="fk-section" id="why-flikma">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Why Forwarders Switch</div>
                <h2>Built by people who ran freight, not by people who read about it</h2>
                <p>The reference site we love gets praise for its accounting. Forwarders get praise for the paperwork,
                   the jobs and the paperwork again. Flikma was built for that second thing.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feat-card p-4 h-100">
                        <div class="feat-icon-box mb-3" style="background:var(--emerald-soft);color:var(--emerald-dim);"><i class="bi bi-box-arrow-in-down-left"></i></div>
                        <h5 class="feat-title mb-2">Enquiry to invoice, one system</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            No re-keying between a quoting tool, a spreadsheet and an accounting package.
                            The job file carries every charge, milestone and attachment forward automatically.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feat-card p-4 h-100">
                        <div class="feat-icon-box mb-3" style="background:rgba(79,70,229,.09);color:var(--indigo);"><i class="bi bi-cpu"></i></div>
                        <h5 class="feat-title mb-2">AI that does the typing</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            Document scanning and AI expense capture remove the single largest source of
                            data-entry errors in a forwarding office &mdash; the human transcription of documents.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feat-card p-4 h-100">
                        <div class="feat-icon-box mb-3" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-buildings"></i></div>
                        <h5 class="feat-title mb-2">Multi-company, multi-branch</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            Run a Riyadh entity, a Bahrain entity and a Dubai entity from one login with
                            consolidated reporting, separate VAT registrations and clean intercompany handling.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feat-card p-4 h-100">
                        <div class="feat-icon-box mb-3" style="background:rgba(6,182,212,.09);color:var(--cyan);"><i class="bi bi-translate"></i></div>
                        <h5 class="feat-title mb-2">Arabic and English, on paper</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            Bilingual invoices, bills of lading and ZATCA QR payloads, because the customs
                            counter and the customer both need the Arabic.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feat-card p-4 h-100">
                        <div class="feat-icon-box mb-3" style="background:rgba(124,58,237,.09);color:var(--violet);"><i class="bi bi-clock-history"></i></div>
                        <h5 class="feat-title mb-2">Month-end in days, not weeks</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            Live trial balance, aging and tax positions. When the month closes there is nothing
                            left to reconstruct.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="feat-card p-4 h-100">
                        <div class="feat-icon-box mb-3" style="background:rgba(244,185,66,.14);color:#b4801a;"><i class="bi bi-flag"></i></div>
                        <h5 class="feat-title mb-2">Vision 2030 ready</h2>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            Digitised customs, electronic invoices and a growing logistics sector. Flikma is
                            positioned for where the Kingdom's trade paperwork is heading.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 reveal">
                <a href="{{ url('/why-flikma') }}" class="btn-hero-outline">See the full comparison <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- ════════════════ PRICING TEASER ════════════════ -->
    <section class="fk-section bg-white" id="pricing">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Pricing</div>
                <h2>Priced per forwarder, not per employee</h2>
                <p>Unlimited users on every plan. You pay for the operation you run, and the price includes
                   ZATCA Phase 2 and the AI document scanning.</p>
            </div>

            <div class="row g-4 align-items-stretch">
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="pricing-card d-flex flex-column">
                        <div class="plan-name mb-1">Starter</div>
                        <div class="plan-desc mb-3">For new forwarders getting off spreadsheets.</div>
                        <div class="plan-price">0 <small>SAR / mo</small></div>
                        <ul class="plan-feats">
                            <li><i class="bi bi-check2"></i> 1 company, 2 users</li>
                            <li><i class="bi bi-check2"></i> Enquiry, quotation &amp; jobs</li>
                            <li><i class="bi bi-check2"></i> Invoicing &amp; collections</li>
                            <li><i class="bi bi-check2"></i> 50 AI document scans / month</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-ghost-light text-center mt-auto mt-4 w-100">Start Free</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 reveal">
                    <div class="pricing-card d-flex flex-column">
                        <div class="plan-name mb-1">Growth</div>
                        <div class="plan-desc mb-3">For established offices with real volume.</div>
                        <div class="plan-price">399 <small>SAR / mo</small></div>
                        <ul class="plan-feats">
                            <li><i class="bi bi-check2"></i> 3 companies, unlimited users</li>
                            <li><i class="bi bi-check2"></i> Full B/L &amp; airway bill suite</li>
                            <li><i class="bi bi-check2"></i> 500 AI scans / month</li>
                            <li><i class="bi bi-check2"></i> Payroll &amp; attendance</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-ghost-light text-center mt-auto mt-4 w-100">Start Free</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 reveal">
                    <div class="pricing-card featured d-flex flex-column">
                        <span class="popular-chip">Most Popular</span>
                        <div class="plan-name mb-1">Professional</div>
                        <div class="plan-desc mb-3">For multi-branch and multi-entity groups.</div>
                        <div class="plan-price">799 <small>SAR / mo</small></div>
                        <ul class="plan-feats">
                            <li><i class="bi bi-check2"></i> Unlimited companies &amp; users</li>
                            <li><i class="bi bi-check2"></i> Unlimited AI document scans</li>
                            <li><i class="bi bi-check2"></i> Full ZATCA + consolidated reporting</li>
                            <li><i class="bi bi-check2"></i> API access &amp; priority support</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-emerald text-center mt-auto mt-4 w-100">Start Free</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3 reveal">
                    <div class="pricing-card d-flex flex-column">
                        <div class="plan-name mb-1">Enterprise</div>
                        <div class="plan-desc mb-3">For groups with bespoke integration needs.</div>
                        <div class="plan-price" style="font-size:1.9rem;">Custom</div>
                        <ul class="plan-feats">
                            <li><i class="bi bi-check2"></i> Private cloud or on-premise</li>
                            <li><i class="bi bi-check2"></i> Custom AI model tuning</li>
                            <li><i class="bi bi-check2"></i> SSO, audit logs, SLA</li>
                            <li><i class="bi bi-check2"></i> Named implementation lead</li>
                        </ul>
                        <a href="{{ url('/contact') }}" class="btn-ghost-light text-center mt-auto mt-4 w-100">Contact Sales</a>
                    </div>
                </div>
            </div>

            <p class="text-center mt-4 mb-0 small text-ink-ghost reveal">
                Prices in SAR, excluding VAT. Annual billing saves two months. <a href="{{ url('/pricing') }}" class="text-emerald fw-semibold">Compare every feature &rarr;</a>
            </p>
        </div>
    </section>

    <!-- ════════════════ FINAL CTA ════════════════ -->
    <section class="pb-5">
        <div class="container">
            <div class="cta-banner p-4 p-md-5 text-center reveal">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="mb-3">Stop chasing paperwork.<br>Start moving freight.</h2>
                        <p class="mb-4" style="font-size:1.02rem;">
                            Get a live walkthrough of Flikma with your own lanes, your own carriers and your own
                            ZATCA registration on the screen. Or start the free trial and explore it yourself today.
                        </p>
                        <div class="d-flex flex-wrap gap-3 justify-content-center">
                            <a href="{{ route('register') }}" class="btn-hero-primary">Start Free Trial <i class="bi bi-arrow-right"></i></a>
                            <a href="{{ url('/contact') }}" class="btn-outline-light-fk">Book a Live Demo</a>
                        </div>
                        <p class="mt-4 mb-0" style="font-size:.82rem;color:rgba(255,255,255,.35);">
                            No credit card required &middot; Full ZATCA Phase 2 included &middot; Cancel anytime
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
