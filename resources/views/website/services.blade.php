@extends('website.layout')

@section('title', 'Services — Implementation, Migration & Training for Logistics ERP | Flikma')
@section('meta_description', 'Flikma services for freight forwarders: implementation and onboarding, data migration from legacy systems, staff training, custom development, ZATCA onboarding and ongoing support across Saudi Arabia, Bahrain and the UAE.')
@section('meta_keywords', 'logistics software implementation Saudi Arabia, freight forwarding ERP data migration, ZATCA onboarding service, logistics software training Bahrain, ERP custom development GCC, freight forwarding software support UAE')

@section('content')

    <style>
        .svc-card { border: 1.5px solid var(--line); border-radius: 18px; background: #fff; padding: 2rem 1.75rem; height: 100%; transition: all .25s; }
        .svc-card:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(10,15,30,.07); }
        .svc-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; margin-bottom: 1.25rem; }
        /* Onboarding timeline */
        .step-row { display: flex; gap: 1.25rem; position: relative; padding-bottom: 2.25rem; }
        .step-row:last-child { padding-bottom: 0; }
        .step-row::after { content: ''; position: absolute; left: 21px; top: 48px; bottom: 6px; width: 1px; background: var(--line-strong); }
        .step-row:last-child::after { display: none; }
        .step-num { width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0; background: var(--emerald); color: var(--ink); font-weight: 800; font-size: .9rem; display: flex; align-items: center; justify-content: center; z-index: 1; }
        .step-row.dim .step-num { background: var(--surface); color: var(--ink-muted); border: 1.5px solid var(--line-strong); }
        .bullet-list { list-style: none; padding: 0; margin: 0; }
        .bullet-list li { display: flex; align-items: flex-start; gap: .7rem; font-size: .9rem; color: var(--ink-muted); line-height: 1.65; margin-bottom: .7rem; }
        .bullet-list i { color: var(--emerald-dim); font-size: .95rem; margin-top: 4px; flex-shrink: 0; }
        .bullet-list strong { color: var(--ink); font-weight: 600; }
        /* Comparison of engagement models */
        .eng-card { border: 1.5px solid var(--line); border-radius: 18px; background: #fff; padding: 2rem 1.75rem; height: 100%; }
        .eng-card.featured { background: var(--ink); border-color: var(--ink); }
        .eng-card.featured h4, .eng-card.featured .price { color: #fff; }
        .eng-card.featured p, .eng-card.featured .bullet-list li { color: rgba(255,255,255,.55); }
        .eng-card.featured .bullet-list i { color: var(--emerald); }
        .eng-card.featured .price small { color: rgba(255,255,255,.4); }
        .price { font-size: 2rem; font-weight: 800; letter-spacing: -.04em; line-height: 1; }
        .price small { font-size: .82rem; font-weight: 500; color: var(--ink-ghost); letter-spacing: 0; }
        .chip { display: inline-block; font-size: .68rem; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; padding: .3rem .8rem; border-radius: 50px; }
    </style>

    <!-- ════════════════ HERO ════════════════ -->
    <header class="page-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-pill mb-3"><i class="bi bi-headset"></i> Professional Services</div>
                    <h1 class="mb-3">We get you live. Then we stay with you.</h1>
                    <p class="mb-4" style="max-width:560px;">
                        Software alone does not change how a forwarding office works. Our implementation team
                        configures Flikma around your lanes, your carriers and your chart of accounts &mdash; and
                        stays on the line long after go-live.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ url('/contact') }}" class="btn-hero-primary">Talk to an Implementation Lead <i class="bi bi-arrow-right"></i></a>
                        <a href="{{ url('/documentation') }}" class="btn-hero-outline">Read the docs first</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--emerald-dim);">2&ndash;3</div><div class="chip-label">Weeks to go-live</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--blue);">100%</div><div class="chip-label">On-site training</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--indigo);">3</div><div class="chip-label">GCC countries served</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--violet);">24h</div><div class="chip-label">Support response</div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ════════════════ SERVICE GRID ════════════════ -->
    <section class="fk-section">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">What We Do</div>
                <h2>Six ways we help you get value out of Flikma</h2>
                <p>Every engagement is scoped around your operation. Nothing here is a mandatory upsell &mdash;
                   take what you need.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4 reveal">
                    <div class="svc-card">
                        <div class="svc-icon" style="background:var(--emerald-soft);color:var(--emerald-dim);"><i class="bi bi-rocket-takeoff"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.15rem;">Implementation &amp; Onboarding</h4>
                        <p class="small mb-3" style="color:var(--ink-muted);line-height:1.7;">
                            A dedicated implementation specialist configures your companies, chart of accounts,
                            logistics services, carrier rates and ZATCA registration so you are operating
                            correctly from week one &mdash; not correcting data for a month.
                        </p>
                        <ul class="bullet-list">
                            <li><i class="bi bi-check2"></i>Discovery workshop &amp; process mapping</li>
                            <li><i class="bi bi-check2"></i>Master data setup and cleansing</li>
                            <li><i class="bi bi-check2"></i>Chart of accounts and tax configuration</li>
                            <li><i class="bi bi-check2"></i>Go-live checklist and hypercare</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="svc-card">
                        <div class="svc-icon" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-arrow-left-right"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.15rem;">Data Migration</h4>
                        <p class="small mb-3" style="color:var(--ink-muted);line-height:1.7;">
                            Leaving a legacy ERP or a decade of spreadsheets? We extract, clean and load your
                            customers, suppliers, open jobs, unpaid invoices and outstanding balances so your
                            new system starts with the truth, not an empty ledger.
                        </p>
                        <ul class="bullet-list">
                            <li><i class="bi bi-check2"></i>Customers, suppliers and item masters</li>
                            <li><i class="bi bi-check2"></i>Open jobs and in-transit shipments</li>
                            <li><i class="bi bi-check2"></i>Open receivables and payables with aging</li>
                            <li><i class="bi bi-check2"></i>Trial balance reconciliation post-load</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="svc-card">
                        <div class="svc-icon" style="background:rgba(124,58,237,.09);color:var(--violet);"><i class="bi bi-mortarboard"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.15rem;">Training &amp; Enablement</h4>
                        <p class="small mb-3" style="color:var(--ink-muted);line-height:1.7;">
                            Run in your own office, in your own timezone, using your own live data. We train the
                            operations team and the finance team separately, because they need different things
                            from the same screen.
                        </p>
                        <ul class="bullet-list">
                            <li><i class="bi bi-check2"></i>On-site or remote sessions, Arabic or English</li>
                            <li><i class="bi bi-check2"></i>Role-based training for ops, finance and admins</li>
                            <li><i class="bi bi-check2"></i>Recorded walkthroughs for later hires</li>
                            <li><i class="bi bi-check2"></i>Refresher sessions included in year one</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="svc-card">
                        <div class="svc-icon" style="background:rgba(6,182,212,.09);color:var(--cyan);"><i class="bi bi-code-square"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.15rem;">Custom Development</h4>
                        <p class="small mb-3" style="color:var(--ink-muted);line-height:1.7;">
                            Every forwarder has one thing their competitors do not. We build the integration to your
                            WMS, your carrier portal, your bank, or the bespoke report your board has asked for
                            &mdash; scoped, estimated and delivered against a fixed scope.
                        </p>
                        <ul class="bullet-list">
                            <li><i class="bi bi-check2"></i>API and webhook integrations</li>
                            <li><i class="bi bi-check2"></i>Custom print formats and documents</li>
                            <li><i class="bi bi-check2"></i>Carrier and EDI connectivity</li>
                            <li><i class="bi bi-check2"></i>Custom reports and dashboards</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="svc-card">
                        <div class="svc-icon" style="background:rgba(0,201,123,.1);color:var(--emerald-dim);"><i class="bi bi-patch-check"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.15rem;">ZATCA Onboarding Support</h4>
                        <p class="small mb-3" style="color:var(--ink-muted);line-height:1.7;">
                            Phase 2 onboarding is where projects stall. We generate your CSR, run the compliance
                            check, complete the simulation, issue your production CSID and verify your first
                            clearance &mdash; alongside you, not instead of you.
                        </p>
                        <ul class="bullet-list">
                            <li><i class="bi bi-check2"></i>CSR and compliance CSID generation</li>
                            <li><i class="bi bi-check2"></i>Simulation testing and error resolution</li>
                            <li><i class="bi bi-check2"></i>Production CSID activation</li>
                            <li><i class="bi bi-check2"></i>First live clearance verification</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal">
                    <div class="svc-card">
                        <div class="svc-icon" style="background:rgba(239,68,68,.09);color:var(--red);"><i class="bi bi-life-preserver"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.15rem;">Ongoing Support &amp; Managed Ops</h4>
                        <p class="small mb-3" style="color:var(--ink-muted);line-height:1.7;">
                            Go-live is the start. Our support team covers the Gulf business hours your team works,
                            and our managed service keeps your master data, users and reports healthy so you never
                            have to think about the software.
                        </p>
                        <ul class="bullet-list">
                            <li><i class="bi bi-check2"></i>24-hour response on paid plans</li>
                            <li><i class="bi bi-check2"></i>Named account manager</li>
                            <li><i class="bi bi-check2"></i>Quarterly health reviews</li>
                            <li><i class="bi bi-check2"></i>Backup, monitoring and uptime reporting</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ ONBOARDING PROCESS ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <div class="section-label">The Process</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                        From kick-off to first cleared invoice
                    </h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        This is the actual sequence we run. No two implementations are identical, but the
                        shape is always the same, and you always know which step you are on.
                    </p>
                    <a href="{{ url('/contact') }}" class="btn-hero-outline">Book a scoping call <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="col-lg-7">
                    <div class="reveal">
                        <div class="step-row">
                            <span class="step-num">1</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.05rem;">Discovery &amp; scoping</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    Two to three hours with the people who actually move the freight. We map your
                                    current process, list the gaps and agree what "working" looks like.
                                </p>
                            </div>
                        </div>
                        <div class="step-row">
                            <span class="step-num">2</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.05rem;">Configuration</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    Companies, branches, chart of accounts, tax settings, logistics services,
                                    carrier rates, invoice numbering and ZATCA registration are configured in
                                    your tenant &mdash; not on a demo account.
                                </p>
                            </div>
                        </div>
                        <div class="step-row">
                            <span class="step-num">3</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.05rem;">Data migration</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    Masters loaded and validated, then open jobs, unpaid invoices and balances.
                                    We reconcile the trial balance until it matches your old system.
                                </p>
                            </div>
                        </div>
                        <div class="step-row">
                            <span class="step-num">4</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.05rem;">ZATCA onboarding</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    CSR &rarr; compliance CSID &rarr; simulation &rarr; production CSID. Run in
                                    parallel with configuration so you are not waiting on the gateway at go-live.
                                </p>
                            </div>
                        </div>
                        <div class="step-row">
                            <span class="step-num">5</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.05rem;">Training</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    Role-based sessions for your team, run on your data. We do not demo on
                                    sample records &mdash; you practise on your own shipments.
                                </p>
                            </div>
                        </div>
                        <div class="step-row">
                            <span class="step-num">6</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.05rem;">Go-live &amp; hypercare</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    You go live on a chosen date with our team on standby for the first two weeks.
                                    After that you move to normal support with a named account manager.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ ENGAGEMENT MODELS ════════════════ -->
    <section class="fk-section">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Engagement Models</div>
                <h2>Pick how much help you want</h2>
                <p>Every plan includes onboarding support. These are for the teams who want more of it.</p>
            </div>

            <div class="row g-4 align-items-stretch">
                <div class="col-md-4 reveal">
                    <div class="eng-card d-flex flex-column">
                        <span class="chip mb-3" style="background:var(--surface);color:var(--ink-muted);">Guided</span>
                        <h4 class="feat-title mb-1" style="font-size:1.15rem;">Self-Serve</h4>
                        <p class="small mb-3" style="color:var(--ink-muted);line-height:1.6;">Included with every subscription</p>
                        <div class="price mb-3" style="font-size:1.6rem;">Free</div>
                        <ul class="bullet-list mb-4">
                            <li><i class="bi bi-check2"></i>Full documentation and guides</li>
                            <li><i class="bi bi-check2"></i>Onboarding checklist and templates</li>
                            <li><i class="bi bi-check2"></i>Email and chat support</li>
                            <li><i class="bi bi-check2"></i>ZATCA onboarding guide</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-ghost-light text-center mt-auto">Start Free</a>
                    </div>
                </div>

                <div class="col-md-4 reveal">
                    <div class="eng-card featured d-flex flex-column">
                        <span class="chip mb-3" style="background:var(--emerald);color:var(--ink);">Most Common</span>
                        <h4 class="feat-title mb-1" style="font-size:1.15rem;">Assisted</h4>
                        <p class="small mb-3" style="color:rgba(255,255,255,.55);line-height:1.6;">One-off project, fixed scope</p>
                        <div class="price mb-3" style="font-size:1.6rem;">From 12,000 <small>SAR</small></div>
                        <ul class="bullet-list mb-4">
                            <li><i class="bi bi-check2"></i>Dedicated implementation lead</li>
                            <li><i class="bi bi-check2"></i>Data migration included</li>
                            <li><i class="bi bi-check2"></i>On-site training, 2 days</li>
                            <li><i class="bi bi-check2"></i>ZATCA onboarding hands-on</li>
                            <li><i class="bi bi-check2"></i>30 days hypercare after go-live</li>
                        </ul>
                        <a href="{{ url('/contact') }}" class="btn-emerald text-center mt-auto">Scope My Project</a>
                    </div>
                </div>

                <div class="col-md-4 reveal">
                    <div class="eng-card d-flex flex-column">
                        <span class="chip mb-3" style="background:rgba(124,58,237,.09);color:var(--violet);">Enterprise</span>
                        <h4 class="feat-title mb-1" style="font-size:1.15rem;">Managed</h4>
                        <p class="small mb-3" style="color:var(--ink-muted);line-height:1.6;">Ongoing, annual contract</p>
                        <div class="price mb-3" style="font-size:1.6rem;">Custom</div>
                        <ul class="bullet-list mb-4">
                            <li><i class="bi bi-check2"></i>Everything in Assisted</li>
                            <li><i class="bi bi-check2"></i>Named account manager</li>
                            <li><i class="bi bi-check2"></i>Custom development sprints</li>
                            <li><i class="bi bi-check2"></i>Quarterly business reviews</li>
                            <li><i class="bi bi-check2"></i>Priority 4-hour response SLA</li>
                        </ul>
                        <a href="{{ url('/contact') }}" class="btn-ghost-light text-center mt-auto">Contact Sales</a>
                    </div>
                </div>
            </div>

            <p class="text-center mt-4 mb-0 small text-ink-ghost reveal">
                Service costs are quoted per project and depend on data volume, entity count and integration scope.
                <a href="{{ url('/contact') }}" class="text-emerald fw-semibold">Ask for a fixed-price quote &rarr;</a>
            </p>
        </div>
    </section>

    <!-- ════════════════ FAQ ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container" x-data="{ open: null }">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">FAQ</div>
                <h2>About our services</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 0 ? null : 0">
                            <span>Do I have to buy the services package?</span>
                            <i class="bi" :class="open === 0 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 0 && 'open'">
                            <p>No. Every subscription includes documentation, onboarding guides and support.
                               The services packages are for teams who want a partner to do the configuration,
                               migration and training hands-on. Plenty of customers start on their own and
                               engage us later &mdash; that is completely normal.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 1 ? null : 1">
                            <span>Can you migrate from a system we have never heard of?</span>
                            <i class="bi" :class="open === 1 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 1 && 'open'">
                            <p>Almost certainly. We have migrated customers off legacy freight ERPs, generic
                               accounting packages and long-lived Excel workbooks. If you can export it as CSV or
                               SQL, we can load it. If you cannot, we will tell you what we need.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 2 ? null : 2">
                            <span>Is the training done in Arabic?</span>
                            <i class="bi" :class="open === 2 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 2 && 'open'">
                            <p>Yes. Training is available in Arabic and English, on-site in Riyadh, Manama or Dubai
                               or remotely. The software itself is bilingual throughout, including printed invoices
                               and bills of lading.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 3 ? null : 3">
                            <span>How fast can you get someone on site?</span>
                            <i class="bi" :class="open === 3 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 3 && 'open'">
                            <p>Within the Gulf we can usually have an implementation lead with you within a week of
                               agreeing scope. Remote configuration can start the same day, which is usually the
                               faster route for anything outside configuration and training.</p>
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
                <h2 class="mb-3">Let's scope your implementation</h2>
                <p class="mb-4" style="font-size:1.02rem;">
                    Tell us your entity count, your current system and your volume. We will come back with a
                    fixed-price plan and a realistic go-live date.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ url('/contact') }}" class="btn-hero-primary">Request Scoping <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ url('/pricing') }}" class="btn-outline-light-fk">See Software Pricing</a>
                </div>
            </div>
        </div>
    </section>

@endsection
