@extends('website.layout')

@section('title', 'Features — AI Logistics ERP Modules for Freight Forwarders | Flikma')
@section('meta_description', 'Explore every Flikma module: freight operations, bills of lading, billing and finance, AI document scanning, AI expense capture, ZATCA Phase 2 e-invoicing, payroll and live reporting. Built for freight forwarders in Saudi Arabia and the GCC.')
@section('meta_keywords', 'freight forwarding software features, logistics ERP modules, AI document scanning, AI expense capture, ZATCA Phase 2 integration, bill of lading software, logistics reporting Saudi Arabia')

@section('content')

    @php
        $nav = [
            ['id' => 'crm',       'icon' => 'bi-people',           'label' => 'CRM & Parties'],
            ['id' => 'operations','icon' => 'bi-globe-americas',   'label' => 'Freight Operations'],
            ['id' => 'bl',        'icon' => 'bi-file-earmark-text', 'label' => 'Bills of Lading'],
            ['id' => 'finance',   'icon' => 'bi-receipt',         'label' => 'Billing & Finance'],
            ['id' => 'expenses',  'icon' => 'bi-wallet2',         'label' => 'AI Expenses'],
            ['id' => 'ai',        'icon' => 'bi-stars',           'label' => 'AI Document Scan'],
            ['id' => 'zatca',     'icon' => 'bi-shield-check',    'label' => 'ZATCA Phase 2'],
            ['id' => 'payroll',   'icon' => 'bi-people-fill',     'label' => 'Payroll'],
            ['id' => 'reports',   'icon' => 'bi-graph-up-arrow',  'label' => 'Reports'],
        ];
    @endphp

    <style>
        .feature-section { border-bottom: 1px solid var(--line); }
        .feature-section:last-of-type { border-bottom: none; }
        .feature-section .sec-icon {
            width: 60px; height: 60px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
        }
        .bullet-list { list-style: none; padding: 0; margin: 0; }
        .bullet-list li {
            display: flex; align-items: flex-start; gap: .7rem;
            font-size: .9rem; color: var(--ink-muted); line-height: 1.65; margin-bottom: .7rem;
        }
        .bullet-list i { color: var(--emerald-dim); font-size: .95rem; margin-top: 4px; flex-shrink: 0; }
        .bullet-list strong { color: var(--ink); font-weight: 600; }
        /* Right-hand visual panel */
        .panel { border: 1.5px solid var(--line); border-radius: 18px; background: #fff; padding: 1.5rem; }
        .panel-dark { border: none; border-radius: 18px; background: var(--ink-panel); padding: 1.5rem; }
        .row-item {
            display: flex; align-items: center; justify-content: space-between; gap: .75rem;
            padding: .6rem 0; border-bottom: 1px solid var(--grid-line); font-size: .8rem;
        }
        .row-item:last-child { border-bottom: 0; }
        .mini-stat { border: 1.5px solid var(--line); border-radius: 12px; padding: .9rem; text-align: center; }
        .mini-stat .v { font-size: 1.3rem; font-weight: 800; line-height: 1.1; letter-spacing: -.03em; }
        .mini-stat .l { font-size: .7rem; color: var(--ink-ghost); }
        /* Code-ish accounting block, borrowed from the shared language */
        .entry-box { font-family: 'Courier New', monospace; background: var(--ink-panel); color: #94a3b8; padding: 1.1rem; border-radius: 12px; font-size: .78rem; line-height: 1.75; }

        /* Sticky bottom module bar */
        .module-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 900; background: rgba(255, 255, 255, .92); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border-top: 1px solid var(--line); }
        .module-bar .nav { flex-wrap: nowrap; overflow-x: auto; gap: .25rem; scrollbar-width: none; }
        .module-bar .nav::-webkit-scrollbar { display: none; }
        .module-bar .nav-link { font-size: .8rem; font-weight: 600; color: var(--ink-muted); white-space: nowrap; border-radius: 8px; }
        .module-bar .nav-link:hover { background: var(--surface); color: var(--ink); }
        .module-bar .nav-link.active { color: var(--emerald-dim); background: var(--emerald-soft); }
        /* Keep the last section clear of the fixed bar */
        body { padding-bottom: 0; }
        @media (min-width: 992px) { body { padding-bottom: 58px; } }
    </style>

    <!-- ════════════════ PAGE HERO ════════════════ -->
    <header class="page-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-pill mb-3"><i class="bi bi-grid-1x2-fill"></i> Platform Features</div>
                    <h1 class="mb-3">Nine modules. One thread through the shipment.</h1>
                    <p class="mb-4" style="max-width:560px;">
                        Flikma is deliberately not a warehouse system and does not pretend to be one.
                        It covers the entire freight forwarding operation &mdash; from the first customer enquiry
                        to the ZATCA-cleared invoice and the collected riyal &mdash; with AI handling the paperwork
                        in between.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="btn-hero-primary">Start Free Trial <i class="bi bi-arrow-right"></i></a>
                        <a href="{{ url('/contact') }}" class="btn-hero-outline">Book a Demo</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--emerald-dim);">9</div><div class="chip-label">Core Modules</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--blue);">100%</div><div class="chip-label">ZATCA Phase 2</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--indigo);">AI</div><div class="chip-label">Docs &amp; Expenses</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--violet);">3</div><div class="chip-label">GCC Countries</div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ════════════════ CRM & PARTIES ════════════════ -->
    <section id="crm" class="fk-section bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <div class="sec-icon mb-3" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-people"></i></div>
                    <div class="section-label">Module 01</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">CRM &amp; Parties</h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        Customers, prospects and suppliers, each with a full trading history &mdash; every quotation
                        sent, every job run, every invoice and every riyal they still owe you.
                    </p>
                    <ul class="bullet-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Customer &amp; prospect records</strong> with credit limits, currency, VAT number and billing branch</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Supplier and carrier directory</strong> with per-lane rates and payment terms</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Balance and statement views</strong> per customer and supplier, aged in buckets</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>CSV import</strong> for existing customer and supplier lists</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Business type detection</strong> &mdash; registered vs. unregistered drives the ZATCA invoice type automatically</span></li>
                    </ul>
                </div>
                <div class="col-lg-7">
                    <div class="panel reveal">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;">Al Noor Trading Co.</div>
                                <div style="font-size:.7rem;color:var(--ink-ghost);">Customer &middot; Riyadh &middot; SAR</div>
                            </div>
                            <span class="mode-chip" style="background:var(--emerald-soft);color:var(--emerald-dim);font-size:.65rem;">VAT Registered</span>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4"><div class="mini-stat"><div class="v">18</div><div class="l">Open Jobs</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="v" style="color:var(--red);">142k</div><div class="l">Outstanding SAR</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="v" style="color:var(--emerald-dim);">96%</div><div class="l">On-time Pay</div></div></div>
                        </div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Last shipment</span><strong>JED &rarr; RUH &middot; Sea</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Credit limit</span><strong>500,000.00 SAR</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Payment terms</span><strong>Net 30</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Statements sent</span><strong>11 of 12 this year</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ FREIGHT OPERATIONS ════════════════ -->
    <section id="operations" class="fk-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5 order-lg-2">
                    <div class="sec-icon mb-3" style="background:var(--emerald-soft);color:var(--emerald-dim);"><i class="bi bi-globe-americas"></i></div>
                    <div class="section-label">Module 02</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">Freight Operations</h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        Air, sea and road in one job file. Milestones, containers, packages and charges all hang
                        off the job, so an operations coordinator never has to ask which spreadsheet is current.
                    </p>
                    <ul class="bullet-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Multi-leg shipments</strong> &mdash; POL, POD, place of receipt, final destination and every transhipment leg</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Container, package and batch tracking</strong> with dimensions, weight and seal numbers</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Full Incoterms support</strong> (2020) with duty and tax responsibility flags</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Milestone dates</strong> &mdash; ETD, ETA, ATD, ATA, pickup, delivery &mdash; with overdue highlighting</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Parties on the job</strong> &mdash; shipper, consignee, notify party and agent, carried straight to the B/L</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Job profitability</strong> &mdash; buy rates, sell rates and margin visible per leg while the job is live</span></li>
                    </ul>
                </div>
                <div class="col-lg-7 order-lg-1">
                    <div class="panel reveal">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-bold" style="font-size:.9rem;">JOB-2026-0421 &middot; NAC Cargo</div>
                            <span class="mode-chip" style="background:rgba(58,107,255,.1);color:var(--blue);font-size:.65rem;"><i class="bi bi-airplane"></i> Air</span>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><div class="mini-stat"><div class="l">Route</div><div class="v" style="font-size:.95rem;">DMM &rarr; BAH</div></div></div>
                            <div class="col-6"><div class="mini-stat"><div class="l">Shipment Mode</div><div class="v" style="font-size:.95rem;">Door to Door</div></div></div>
                            <div class="col-6"><div class="mini-stat"><div class="l">ETD</div><div class="v" style="font-size:.95rem;">2026-07-18</div></div></div>
                            <div class="col-6"><div class="mini-stat"><div class="l">ETA</div><div class="v" style="font-size:.95rem;color:var(--emerald-dim);">2026-07-19</div></div></div>
                        </div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Packages</span><strong>4 &middot; 812 kg</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Carrier / Flight</span><strong>Saudia &middot; SV-1042</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Incoterm</span><strong>DDP &mdash; Bahrain</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Consignee</span><strong>Bahrain Logistics LLC</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Job margin</span><strong style="color:var(--emerald-dim);">18.4%</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ BILLS OF LADING ════════════════ -->
    <section id="bl" class="fk-section bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <div class="sec-icon mb-3" style="background:rgba(11,23,54,.07);color:var(--navy);"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="section-label">Module 03</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">Bills of Lading</h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        Airway bills, sea waybills and road waybills generated from the job, printed to carrier
                        specification, with house and master documents tracked separately.
                    </p>
                    <ul class="bullet-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Airway bills</strong> for air freight, including house and master AWB numbers</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Sea waybills</strong> with container details, seals and voyage information</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Road waybills</strong> for cross-border GCC road freight</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Multiple print formats</strong> matched to what each carrier and customs authority accepts</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Bilingual output</strong> &mdash; English and Arabic on the same document</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Clearance status tracking</strong> so you know exactly which document is still outstanding</span></li>
                    </ul>
                </div>
                <div class="col-lg-7">
                    <div class="panel reveal">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-bold" style="font-size:.9rem;">Document Register</div>
                            <span class="mode-chip" style="background:var(--surface);color:var(--ink-ghost);font-size:.65rem;">5 documents</span>
                        </div>
                        <div class="row-item"><strong>AWB-176-88234109</strong><span class="mode-chip" style="background:rgba(58,107,255,.1);color:var(--blue);font-size:.63rem;">House Air</span></div>
                        <div class="row-item"><strong>176-88234109</strong><span class="mode-chip" style="background:rgba(124,58,237,.1);color:var(--violet);font-size:.63rem;">Master Air</span></div>
                        <div class="row-item"><strong>SWB-SE-4471</strong><span class="mode-chip" style="background:rgba(6,182,212,.12);color:#0891b2;font-size:.63rem;">Sea Waybill</span></div>
                        <div class="row-item"><strong>CMR-2026-0912</strong><span class="mode-chip" style="background:rgba(244,185,66,.15);color:#b4801a;font-size:.63rem;">Road &mdash; Pending</span></div>
                        <div class="row-item"><strong>CD-9931/26/Q</strong><span class="mode-chip" style="background:var(--emerald-soft);color:var(--emerald-dim);font-size:.63rem;">Customs Cleared</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ BILLING & FINANCE ════════════════ -->
    <section id="finance" class="fk-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5 order-lg-2">
                    <div class="sec-icon mb-3" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-receipt"></i></div>
                    <div class="section-label">Module 04</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">Billing &amp; Finance</h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        The money side of forwarding. Every document family a forwarding office needs, all
                        posting to a real double-entry ledger you can actually audit.
                    </p>
                    <ul class="bullet-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Proforma invoices</strong> for advances and deposit billing before the cargo moves</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Customer invoices</strong> and <strong>supplier invoices</strong> with full line-item breakdown</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Payments and collections</strong> recorded against invoices, with multi-invoice application</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Credit and debit notes</strong> raised through the ZATCA compliant pipeline</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Journal vouchers, assets and opening balances</strong> for the accountant who needs them</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Period closing</strong> that locks a period once it is posted, with full audit history</span></li>
                    </ul>
                </div>
                <div class="col-lg-7 order-lg-1">
                    <div class="panel reveal">
                        <div class="row g-2 mb-3">
                            <div class="col-4"><div class="mini-stat"><div class="l">Receivables</div><div class="v" style="font-size:1.1rem;">1.24M</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="l">Payables</div><div class="v" style="font-size:1.1rem;">812k</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="l">Overdue</div><div class="v" style="font-size:1.1rem;color:var(--red);">96k</div></div></div>
                        </div>
                        <div class="entry-box">
                            <div>REF&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACCOUNT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CR</div>
                            <div>JV-0412&nbsp;&nbsp;AR / FREIGHT REV&nbsp;&nbsp;&nbsp;24,600.00&nbsp;&nbsp;&nbsp;&mdash;</div>
                            <div>JV-0412&nbsp;&nbsp;FREIGHT REVENUE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&mdash;&nbsp;&nbsp;&nbsp;&nbsp;21,300.00</div>
                            <div>JV-0412&nbsp;&nbsp;CARRIER PAYABLE&nbsp;&nbsp;17,800.00&nbsp;&nbsp;&nbsp;&mdash;</div>
                            <div>JV-0412&nbsp;&nbsp;VAT OUTPUT 15%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&mdash;&nbsp;&nbsp;&nbsp;&nbsp;3,300.00</div>
                        </div>
                        <p class="mb-0 mt-3" style="font-size:.76rem;color:var(--ink-ghost);">
                            Journal vouchers post straight to the ledger &mdash; no import, no re-keying.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ AI EXPENSES ════════════════ -->
    <section id="expenses" class="fk-section bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <div class="sec-icon mb-3" style="background:rgba(239,68,68,.09);color:var(--red);"><i class="bi bi-wallet2"></i></div>
                    <div class="section-label">Module 05 &middot; AI</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">AI Expense Capture</h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        The fuel receipt, the courier fee, the customs agent's invoice and the office coffee.
                        Photograph it &mdash; Flikma reads the vendor, the VAT and the amount, suggests the expense
                        head, and files the image against the entry.
                    </p>
                    <ul class="bullet-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Photo-to-entry</strong> &mdash; snap a receipt and get a structured expense record</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Vendor auto-match</strong> against your supplier list, with a flag for anything new</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Expense head suggestion</strong> based on vendor, amount and job reference</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Job allocation</strong> &mdash; charge the expense straight to the shipment it belongs to</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>VAT extracted</strong> and separated so your input tax return is already built</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Approval trail</strong> &mdash; who submitted, who approved, and when</span></li>
                    </ul>
                </div>
                <div class="col-lg-7">
                    <div class="panel reveal">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-bold" style="font-size:.9rem;">Expense Entry &middot; AI extracted</div>
                            <span class="mode-chip" style="background:rgba(79,70,229,.1);color:var(--indigo);font-size:.65rem;"><i class="bi bi-stars"></i> AI</span>
                        </div>
                        <div class="scan-field"><span class="k">Vendor</span><span class="v ok">Aldrees Petroleum</span></div>
                        <div class="scan-field"><span class="k">Expense Head</span><span class="v ok">Vehicle Fuel &amp; Lubricants</span></div>
                        <div class="scan-field"><span class="k">Job Allocation</span><span class="v">General (No Job)</span></div>
                        <div class="scan-field"><span class="k">Amount (excl. VAT)</span><span class="v">1,850.00 SAR</span></div>
                        <div class="scan-field"><span class="k">VAT 15%</span><span class="v">277.50 SAR</span></div>
                        <div class="d-flex justify-content-between align-items-center mt-3 p-3 rounded-3 bg-surface">
                            <span class="fw-bold small">Total</span>
                            <span class="fw-bold" style="color:var(--emerald-dim);">2,127.50 SAR</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3" style="font-size:.74rem;color:var(--ink-ghost);">
                            <i class="bi bi-paperclip"></i> receipt-2026-07-18.jpg &middot; attached automatically
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ AI DOCUMENT SCANNING ════════════════ -->
    <section id="ai" class="fk-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5 order-lg-2">
                    <div class="sec-icon mb-3" style="background:rgba(79,70,229,.09);color:var(--indigo);"><i class="bi bi-stars"></i></div>
                    <div class="section-label">Module 06 &middot; AI</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">AI Document Scanning</h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        Supplier bills arrive in every format imaginable &mdash; clean PDFs, phone photos, fax-quality
                        scans, multi-page tables. Flikma reads them and fills the form for you.
                    </p>
                    <ul class="bullet-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>PDF, JPG, PNG up to 20 MB</strong>, single or multi-page</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Header and line-item extraction</strong> &mdash; invoice number, dates, supplier, totals, VAT number</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Multi-engine OCR</strong> across Google Vision, OCR Space and Tesseract, picking whichever reads the document best</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>LLM structuring</strong> with your own supplier and description lists supplied as context</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Table detection</strong> for multi-line charge breakdowns</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Human in the loop</strong> &mdash; you confirm before anything is posted to the ledger</span></li>
                    </ul>
                </div>
                <div class="col-lg-7 order-lg-1">
                    <div class="panel reveal">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-file-earmark-pdf" style="font-size:1.5rem;color:var(--red);"></i>
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;">Gulf Sea Lines — INV-88214</div>
                                <div style="font-size:.7rem;color:var(--ink-ghost);">412 KB &middot; 2 pages &middot; scanned in 6.1s</div>
                            </div>
                        </div>
                        <div class="scan-field"><span class="k">Invoice No.</span><span class="v ok">INV-88214</span></div>
                        <div class="scan-field"><span class="k">Invoice Date</span><span class="v ok">2026-07-14</span></div>
                        <div class="scan-field"><span class="k">Due Date</span><span class="v">2026-08-13</span></div>
                        <div class="scan-field"><span class="k">Supplier VAT No.</span><span class="v ok">310022393500003</span></div>
                        <div class="scan-field"><span class="k">Freight &amp; Handling</span><span class="v">8,400.00 SAR</span></div>
                        <div class="scan-field"><span class="k">Grand Total</span><span class="v ok">9,660.00 SAR</span></div>
                        <div class="d-flex gap-2 mt-3">
                            <span class="btn-ghost-light" style="font-size:.78rem;padding:.4rem .9rem;cursor:default;"><i class="bi bi-magic"></i> 6 fields auto-filled</span>
                            <span class="btn-ghost-light" style="font-size:.78rem;padding:.4rem .9rem;cursor:default;"><i class="bi bi-pencil"></i> Review &amp; post</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ ZATCA ════════════════ -->
    <section id="zatca" class="fk-section bg-ink">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="section-label">Module 07 &middot; Compliance</div>
                    <h2 class="mt-2 mb-3" style="color:#fff;font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">
                        ZATCA Phase 2 &amp; Fatoora
                    </h2>
                    <p class="mb-4" style="color:rgba(255,255,255,.55);line-height:1.75;">
                        Onboarding, signing, clearance and reporting &mdash; implemented natively rather than
                        bolted on, so the compliant path is the default path.
                    </p>
                    <ul class="dark-check list-unstyled mb-0">
                        <li><i class="bi bi-check-circle-fill"></i><span>EGS onboarding: CSR generation, compliance CSID, test sweep, then production CSID</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Tax invoice for VAT-registered buyers, simplified invoice for everyone else &mdash; chosen automatically</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>UBL 2.1 XML with ECDSA/CAdES signing and XAdES signed properties</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Mandatory QR code generation for buyer-side validation</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Real-time clearance and deferred reporting, with full submission history per invoice</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span>Logistics registered as the EGS branch industry</span></li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="rounded-4 p-4 p-md-5 reveal" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);">
                        <div class="flow-step-item">
                            <span class="flow-num">1</span>
                            <div>
                                <h6 class="fw-bold mb-1" style="color:#fff;">Issue the invoice</h6>
                                <p class="mb-0" style="font-size:.85rem;color:rgba(255,255,255,.5);">Pick the job, the customer settles the type, Flikma builds the compliant document.</p>
                            </div>
                        </div>
                        <div class="flow-step-item mt-4">
                            <span class="flow-num">2</span>
                            <div>
                                <h6 class="fw-bold mb-1" style="color:#fff;">Sign &amp; stamp</h6>
                                <p class="mb-0" style="font-size:.85rem;color:rgba(255,255,255,.5);">UBL 2.1 XML is cryptographically signed and the QR payload is generated.</p>
                            </div>
                        </div>
                        <div class="flow-step-item mt-4">
                            <span class="flow-num">3</span>
                            <div>
                                <h6 class="fw-bold mb-1" style="color:#fff;">Clear or report</h6>
                                <p class="mb-0" style="font-size:.85rem;color:rgba(255,255,255,.5);">Submitted to the Fatoora gateway; the result is stored on the invoice forever.</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4" style="border-top:1px solid rgba(255,255,255,.09);">
                            <span class="mode-chip" style="background:rgba(0,201,123,.15);color:var(--emerald);font-size:.72rem;">
                                <i class="bi bi-patch-check-fill"></i> Phase 2 compliant &middot; Included on every plan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ PAYROLL ════════════════ -->
    <section id="payroll" class="fk-section bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <div class="sec-icon mb-3" style="background:rgba(124,58,237,.09);color:var(--violet);"><i class="bi bi-people-fill"></i></div>
                    <div class="section-label">Module 08</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">Payroll &amp; Attendance</h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        Your office staff are part of the cost of moving freight. Flikma runs their attendance
                        and salary, and posts the result to the ledger so the P&amp;L is honest.
                    </p>
                    <ul class="bullet-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Monthly salary processing</strong> with basic salary structures and allowances</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Attendance calendar</strong> for the whole office, per employee</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Employee loans and advances</strong> with automatic deduction schedules</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>WPS-ready export</strong> for Saudi wage protection and equivalent GCC payroll files</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Posted to the ledger</strong> &mdash; payroll shows up in the trial balance, not in a separate system</span></li>
                    </ul>
                </div>
                <div class="col-lg-7">
                    <div class="panel reveal">
                        <div class="row g-2 mb-3">
                            <div class="col-4"><div class="mini-stat"><div class="l">Employees</div><div class="v">14</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="l">Payroll Cost</div><div class="v" style="font-size:1.1rem;">96,400</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="l">Attendance</div><div class="v" style="color:var(--emerald-dim);">98%</div></div></div>
                        </div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Salaries</span><strong>82,000.00 SAR</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Housing &amp; transport</span><strong>12,400.00 SAR</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Loan deductions</span><strong>2,000.00 SAR</strong></div>
                        <div class="d-flex justify-content-between align-items-center mt-3 p-3 rounded-3 bg-surface">
                            <span class="fw-bold small">Total payable</span>
                            <span class="fw-bold" style="color:var(--emerald-dim);">96,400.00 SAR</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ REPORTS ════════════════ -->
    <section id="reports" class="fk-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5 order-lg-2">
                    <div class="sec-icon mb-3" style="background:rgba(6,182,212,.09);color:var(--cyan);"><i class="bi bi-graph-up-arrow"></i></div>
                    <div class="section-label">Module 09</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">Reports &amp; Analytics</h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        Because every module writes to one ledger, the reports are already true.
                        No month-end reconstruction, no "we'll do it in Excel".
                    </p>
                    <ul class="bullet-list">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Trial balance, balance sheet and P&amp;L</strong> &mdash; always current</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>General ledger</strong> with full drill-down to the source document</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Input tax, output tax and tax summary</strong> prepared for the ZATCA return</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Customer and supplier aging and statements</strong>, plus activity and balance summaries</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Job reports</strong> &mdash; balance, income and provisional profitability by job, customer or lane</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Sales and waybill reports</strong> for operational review</span></li>
                    </ul>
                </div>
                <div class="col-lg-7 order-lg-1">
                    <div class="panel reveal">
                        <div class="row g-2 mb-3">
                            <div class="col-4"><div class="mini-stat"><div class="l">Revenue (YTD)</div><div class="v" style="font-size:1.1rem;">4.8M</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="l">Gross Margin</div><div class="v" style="font-size:1.1rem;color:var(--emerald-dim);">17.2%</div></div></div>
                            <div class="col-4"><div class="mini-stat"><div class="l">Open Jobs</div><div class="v">14</div></div></div>
                        </div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Top lane &mdash; JED &rarr; RUH sea</span><strong>612,000 SAR</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Top customer</span><strong>Al Noor Trading</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Highest margin job</span><strong>JOB-2026-0402 &middot; 31.4%</strong></div>
                        <div class="row-item"><span style="color:var(--ink-muted);">Overdue receivables</span><strong style="color:var(--red);">96,200 SAR</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ NOT INCLUDED — HONESTY BLOCK ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="rounded-4 p-4 p-md-5 reveal" style="background:var(--surface);border:1.5px dashed var(--line-strong);">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <div class="section-label">Straight with you</div>
                        <h3 class="mt-2 mb-3" style="font-size:1.5rem;font-weight:800;">What Flikma is not</h3>
                        <p class="mb-0" style="color:var(--ink-muted);line-height:1.75;">
                            Flikma has no warehouse module, no stock picking, no put-away, and no barcode
                            receiving dock. If you need a distribution centre or a manufacturing warehouse,
                            you want a WMS &mdash; and we will tell you so rather than sell you a bad fit.
                            If you move freight across borders for a living, everything you actually need is here.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <a href="{{ url('/contact') }}" class="btn-cta-main w-100 justify-content-center">
                            Talk to a logistics specialist <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ FAQ ════════════════ -->
    <section class="fk-section">
        <div class="container" x-data="{ open: null }">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">FAQ</div>
                <h2>Questions forwarders ask us first</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 0 ? null : 0">
                            <span>Is ZATCA Phase 2 included, or is it a separate paid add-on?</span>
                            <i class="bi" :class="open === 0 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 0 && 'open'">
                            <p>It is included on every plan, including the free Starter plan. ZATCA compliance is not a
                               premium tier in Flikma &mdash; if the software issues a Saudi invoice, it issues a compliant one.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 1 ? null : 1">
                            <span>Does Flikma include a warehouse or stock control module?</span>
                            <i class="bi" :class="open === 1 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 1 && 'open'">
                            <p>No. Flikma is a logistics and freight forwarding ERP. It deliberately does not include
                               warehouse management, stock picking or put-away. We are happy to integrate with the
                               WMS you already use if you run one.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 2 ? null : 2">
                            <span>How accurate is the AI document scanning in practice?</span>
                            <i class="bi" :class="open === 2 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 2 && 'open'">
                            <p>Very good on clean digital PDFs and decent phone photos, which covers the large majority of
                               supplier bills. On poor scans or heavy handwriting it will ask you to confirm rather than
                               guess. Every field is reviewable before anything is posted to your ledger &mdash; the AI
                               never posts to the books on its own.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 3 ? null : 3">
                            <span>Can we run more than one company in Flikma?</span>
                            <i class="bi" :class="open === 3 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 3 && 'open'">
                            <p>Yes. Growth and above support multiple companies and branches with separate VAT registrations,
                               separate ledgers and consolidated group reporting. This is common for groups operating in
                               Saudi Arabia, Bahrain and the UAE simultaneously.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 4 ? null : 4">
                            <span>How long does implementation take?</span>
                            <i class="bi" :class="open === 4 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 4 && 'open'">
                            <p>A single-branch forwarder moving off spreadsheets is typically live in two to three weeks.
                               Multi-entity groups with data migration usually take four to six. See our
                               <a href="{{ url('/services') }}" class="text-emerald fw-semibold">Services page</a>
                               for the full onboarding process.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ CTA ════════════════ -->
    <section class="pb-5">
        <div class="container">
            <div class="cta-banner p-4 p-md-5 text-center reveal">
                <h2 class="mb-3">See it with your own freight</h2>
                <p class="mb-4" style="font-size:1.02rem;">
                    We'll walk you through Flikma using your lanes, your carriers and your ZATCA registration.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ route('register') }}" class="btn-hero-primary">Start Free Trial <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ url('/pricing') }}" class="btn-outline-light-fk">See Pricing</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ STICKY MODULE NAV ════════════════ -->
    <div class="module-bar d-none d-lg-block">
        <div class="container">
            <ul class="nav py-2" data-spy-nav>
                @foreach ($nav as $n)
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3" href="#{{ $n['id'] }}">
                            <i class="bi {{ $n['icon'] }}"></i> {{ $n['label'] }}
                        </a>
                    </li>
                @endforeach
                <li class="nav-item ms-auto">
                    <a class="nav-link py-2 px-3 fw-bold" style="color:var(--emerald-dim);" href="{{ url('/contact') }}">
                        Book a Demo <i class="bi bi-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

@endsection
