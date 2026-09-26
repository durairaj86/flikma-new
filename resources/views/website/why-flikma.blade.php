@extends('website.layout')

@section('title', 'Why Flikma — Built for Freight Forwarders, Not Adapted From Accounting')
@section('meta_description', 'Why logistics companies switch to Flikma: purpose-built freight forwarding ERP with AI document scanning, AI expense capture, ZATCA Phase 2 and multi-entity support. See the comparison, the security model and the results.')
@section('meta_keywords', 'freight forwarding ERP comparison, logistics software review, why switch logistics software, freight forwarding software Saudi Arabia, ZATCA compliant logistics software, AI logistics software')

@section('content')

    <style>
        .vs-row { border: 1.5px solid var(--line); border-radius: 18px; overflow: hidden; background: #fff; }
        .vs-head { display: grid; grid-template-columns: 1.4fr 1.1fr 1.1fr; background: var(--ink-panel); color: #fff; font-size: .72rem; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; }
        .vs-head > div { padding: 1rem 1.25rem; }
        .vs-head .ours { color: var(--emerald); }
        .vs-body { display: grid; grid-template-columns: 1.4fr 1.1fr 1.1fr; border-top: 1px solid var(--line); font-size: .87rem; }
        .vs-body > div { padding: .95rem 1.25rem; color: var(--ink-muted); }
        .vs-body .label { color: var(--ink); font-weight: 600; }
        .vs-body .ours { background: rgba(0, 201, 123, .04); }
        .vs-body .ours i { color: var(--emerald-dim); }
        .vs-body .theirs i { color: var(--ink-ghost); }
        @media (max-width: 767.98px) {
            .vs-head, .vs-body { grid-template-columns: 1fr; }
            .vs-head > div:not(:first-child) { display: none; }
            .vs-body > div { border-top: 1px dashed var(--line); }
            .vs-body > div:first-child { border-top: 0; }
            .vs-body .ours::before, .vs-body .theirs::before { display: block; font-size: .64rem; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; margin-bottom: .2rem; }
            .vs-body .ours::before { content: 'Flikma'; color: var(--emerald-dim); }
            .vs-body .theirs::before { content: 'Typical alternative'; color: var(--ink-ghost); }
        }
        .bullet-list { list-style: none; padding: 0; margin: 0; }
        .bullet-list li { display: flex; align-items: flex-start; gap: .7rem; font-size: .9rem; color: var(--ink-muted); line-height: 1.65; margin-bottom: .7rem; }
        .bullet-list i { color: var(--emerald-dim); font-size: .95rem; margin-top: 4px; flex-shrink: 0; }
        .bullet-list strong { color: var(--ink); font-weight: 600; }
        .pillar { border: 1.5px solid var(--line); border-radius: 18px; background: #fff; padding: 2rem 1.75rem; height: 100%; transition: all .25s; }
        .pillar:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(10, 15, 30, .07); }
    </style>

    <!-- ════════════════ HERO ════════════════ -->
    <header class="page-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-pill mb-3"><i class="bi bi-stars"></i> Why Flikma</div>
                    <h1 class="mb-3">Accounting software was never built for freight.</h1>
                    <p class="mb-4" style="max-width:580px;">
                        Most logistics companies run an accounting package with a logistics module bolted on, then
                        spend their lives translating between the two. Flikma was built the other way around:
                        the shipment is the centre, and the ledger simply follows it.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ url('/contact') }}" class="btn-hero-primary">Book a Live Demo <i class="bi bi-arrow-right"></i></a>
                        <a href="#comparison" class="btn-hero-outline">See the comparison</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="rounded-4 p-4 reveal" style="background:var(--ink-panel);">
                        <div class="section-label mb-3" style="color:var(--emerald);">The Short Version</div>
                        <p class="mb-4" style="color:rgba(255,255,255,.7);font-size:1rem;line-height:1.7;">
                            If your biggest cost is a person retyping documents, and your biggest risk is a
                            missed ZATCA deadline, you are using the wrong category of software.
                        </p>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2" style="font-size:.85rem;color:rgba(255,255,255,.6);"><i class="bi bi-x-circle" style="color:var(--red);"></i> Documents retyped by hand</div>
                            <div class="d-flex align-items-center gap-2" style="font-size:.85rem;color:rgba(255,255,255,.6);"><i class="bi bi-x-circle" style="color:var(--red);"></i> e-invoicing as a separate add-on</div>
                            <div class="d-flex align-items-center gap-2" style="font-size:.85rem;color:rgba(255,255,255,.6);"><i class="bi bi-x-circle" style="color:var(--red);"></i> Warehouse features you never use</div>
                            <div class="d-flex align-items-center gap-2 pt-2 mt-1" style="border-top:1px solid rgba(255,255,255,.1);font-size:.85rem;color:#fff;font-weight:600;"><i class="bi bi-check-circle-fill" style="color:var(--emerald);"></i> One platform, freight-first</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ════════════════ FOUR PILLARS ════════════════ -->
    <section class="fk-section">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Our Four Beliefs</div>
                <h2>What we think a logistics ERP should be</h2>
            </div>

            <div class="row g-4">
                <div class="col-md-6 reveal">
                    <div class="pillar">
                        <div class="feat-icon-box mb-3" style="background:var(--emerald-soft);color:var(--emerald-dim);"><i class="bi bi-box-arrow-in-down-left"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.2rem;">The shipment is the source of truth</h4>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.75;">
                            Not the invoice. Not the spreadsheet. The job file is the record of what happened,
                            and every document, charge and ledger entry hangs off it. When something does not
                            add up, there is one place to look.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 reveal">
                    <div class="pillar">
                        <div class="feat-icon-box mb-3" style="background:rgba(79,70,229,.09);color:var(--indigo);"><i class="bi bi-cpu"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.2rem;">Software should do the typing</h4>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.75;">
                            Humans should verify, not transcribe. Every document entering your business has
                            been transcribed by someone, at some point, usually twice. AI removes that step
                            entirely and leaves the human judgement where it is actually valuable.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 reveal">
                    <div class="pillar">
                        <div class="feat-icon-box mb-3" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-patch-check"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.2rem;">Compliance is not a feature</h4>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.75;">
                            ZATCA Phase 2 is a legal obligation, not a competitive feature to be upsold. It is
                            in the base product, on the free plan, because a Saudi company should never have to
                            ask whether compliance is switched on.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 reveal">
                    <div class="pillar">
                        <div class="feat-icon-box mb-3" style="background:rgba(6,182,212,.09);color:var(--cyan);"><i class="bi bi-bullseye"></i></div>
                        <h4 class="feat-title mb-2" style="font-size:1.2rem;">Do not sell what you do not need</h4>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.75;">
                            Flikma has no warehouse module, because most of our customers do not run one and
                            paying for put-away logic you never touch is a tax on your business. We would
                            rather be a precise fit than a bloated one.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ COMPARISON ════════════════ -->
    <section id="comparison" class="fk-section bg-white">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Comparison</div>
                <h2>Flikma vs. a typical accounting-plus-logistics setup</h2>
                <p>The categories below are the ones logistics teams tell us matter most. If your current system
                   handles these well, keep it &mdash; genuinely.</p>
            </div>

            <div class="vs-row reveal">
                <div class="vs-head">
                    <div>Capability</div>
                    <div class="ours">Flikma</div>
                    <div>Typical alternative</div>
                </div>

                <div class="vs-body">
                    <div class="label">Enquiry to quotation to job</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> One continuous record</div>
                    <div class="theirs"><i class="bi bi-dash-circle"></i> Split across tools</div>
                </div>
                <div class="vs-body">
                    <div class="label">Supplier invoice data entry</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> AI extracts and pre-fills</div>
                    <div class="theirs"><i class="bi bi-x-circle"></i> Manual retyping</div>
                </div>
                <div class="vs-body">
                    <div class="label">Receipt and expense capture</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> Photograph &rarr; posted</div>
                    <div class="theirs"><i class="bi bi-x-circle"></i> Manual, after the fact</div>
                </div>
                <div class="vs-body">
                    <div class="label">ZATCA Phase 2 e-invoicing</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> Included on all plans</div>
                    <div class="theirs"><i class="bi bi-dash-circle"></i> Often a paid module</div>
                </div>
                <div class="vs-body">
                    <div class="label">Invoice type selection</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> Automatic from customer</div>
                    <div class="theirs"><i class="bi bi-dash-circle"></i> Manual selection, error-prone</div>
                </div>
                <div class="vs-body">
                    <div class="label">Job profitability visibility</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> Live, per leg</div>
                    <div class="theirs"><i class="bi bi-dash-circle"></i> Only after month-end</div>
                </div>
                <div class="vs-body">
                    <div class="label">Airway bill &amp; sea waybill printing</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> Native, multiple formats</div>
                    <div class="theirs"><i class="bi bi-dash-circle"></i> Third-party tool or Word</div>
                </div>
                <div class="vs-body">
                    <div class="label">Multi-entity across the GCC</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> Built in, consolidated</div>
                    <div class="theirs"><i class="bi bi-dash-circle"></i> Separate databases or add-ons</div>
                </div>
                <div class="vs-body">
                    <div class="label">Arabic and English documents</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> Bilingual throughout</div>
                    <div class="theirs"><i class="bi bi-dash-circle"></i> Often English only</div>
                </div>
                <div class="vs-body">
                    <div class="label">Warehouse &amp; stock control</div>
                    <div class="ours"><i class="bi bi-dash-circle"></i> Deliberately not included</div>
                    <div class="theirs"><i class="bi bi-check-circle-fill"></i> Often bundled, often unused</div>
                </div>
                <div class="vs-body">
                    <div class="label">Typical time to first cleared invoice</div>
                    <div class="ours"><i class="bi bi-check-circle-fill"></i> 2&ndash;3 weeks</div>
                    <div class="theirs"><i class="bi bi-dash-circle"></i> 2&ndash;6 months</div>
                </div>
            </div>

            <p class="text-center mt-3 mb-0 small text-ink-ghost reveal">
                The "typical alternative" column reflects the feedback of forwarders who came to us from other systems.
                Your mileage will vary &mdash; that is what the
                <a href="{{ url('/contact') }}" class="text-emerald fw-semibold">demo</a> is for.
            </p>
        </div>
    </section>

    <!-- ════════════════ RESULTS ════════════════ -->
    <section id="results" class="fk-section">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Customer Results</div>
                <h2>What changes in the first ninety days</h2>
                <p>Aggregate figures reported by customers after their first full quarter on Flikma.
                   Individual results vary by volume and process.</p>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-6 col-lg-3 reveal">
                    <div class="stat-chip h-100">
                        <div class="chip-value" style="color:var(--emerald-dim);">70%</div>
                        <div class="chip-label">Less time on supplier bill entry</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 reveal">
                    <div class="stat-chip h-100">
                        <div class="chip-value" style="color:var(--blue);">18 hrs</div>
                        <div class="chip-label">Saved per invoice on admin</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 reveal">
                    <div class="stat-chip h-100">
                        <div class="chip-value" style="color:var(--indigo);">100%</div>
                        <div class="chip-label">ZATCA clearance first time</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 reveal">
                    <div class="stat-chip h-100">
                        <div class="chip-value" style="color:var(--violet);">3 wks</div>
                        <div class="chip-label">Average time to go-live</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 reveal">
                    <div class="feat-card p-4 h-100" style="padding:2rem 1.75rem;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="trust-avatar" style="margin-left:0;">RK</span>
                            <div style="line-height:1.2;">
                                <div style="font-weight:700;font-size:.85rem;">Operations Manager</div>
                                <div style="font-size:.72rem;color:var(--ink-ghost);">Freight forwarder, Riyadh</div>
                            </div>
                        </div>
                        <i class="bi bi-quote" style="font-size:1.6rem;color:var(--emerald);opacity:.4;"></i>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.8;">
                            "The scan feature paid for itself in the first month. Our admin used to spend two
                            full days a week typing carrier invoices. Now she reviews them."
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 reveal">
                    <div class="feat-card p-4 h-100" style="padding:2rem 1.75rem;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="trust-avatar" style="margin-left:0;">AM</span>
                            <div style="line-height:1.2;">
                                <div style="font-weight:700;font-size:.85rem;">Finance Director</div>
                                <div style="font-size:.72rem;color:var(--ink-ghost);">Logistics group, Bahrain</div>
                            </div>
                        </div>
                        <i class="bi bi-quote" style="font-size:1.6rem;color:var(--emerald);opacity:.4;"></i>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.8;">
                            "Month-end used to take nine days. The last close took two, and I did not have to
                            rebuild a single spreadsheet to get there."
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 reveal">
                    <div class="feat-card p-4 h-100" style="padding:2rem 1.75rem;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="trust-avatar" style="margin-left:0;">SD</span>
                            <div style="line-height:1.2;">
                                <div style="font-weight:700;font-size:.85rem;">Managing Director</div>
                                <div style="font-size:.72rem;color:var(--ink-ghost);">Freight forwarder, Dubai</div>
                            </div>
                        </div>
                        <i class="bi bi-quote" style="font-size:1.6rem;color:var(--emerald);opacity:.4;"></i>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.8;">
                            "I can see the margin on a job while it is still moving, not when it is finished.
                            That one change has altered how we price."
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ SECURITY ════════════════ -->
    <section id="security" class="fk-section bg-ink">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="section-label">Security &amp; Data</div>
                    <h2 class="mt-2 mb-3" style="color:#fff;font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                        Your freight data is commercially sensitive. We treat it that way.
                    </h2>
                    <p class="mb-4" style="color:rgba(255,255,255,.55);line-height:1.75;">
                        A forwarder's data reveals your customers, your carriers, your rates and your margins.
                        Losing it, or leaking it, is an existential event. These are the controls we run.
                    </p>
                    <ul class="dark-check list-unstyled mb-0">
                        <li><i class="bi bi-shield-lock-fill"></i><span><strong style="color:#fff;">Tenant isolation</strong> &mdash; every company gets a scoped data layer; one tenant can never read another's records</span></li>
                        <li><i class="bi bi-shield-lock-fill"></i><span><strong style="color:#fff;">Role-based permissions</strong> at module level, enforced server-side on every request</span></li>
                        <li><i class="bi bi-shield-lock-fill"></i><span><strong style="color:#fff;">Full activity logging</strong> &mdash; every change is attributable and time-stamped</span></li>
                        <li><i class="bi bi-shield-lock-fill"></i><span><strong style="color:#fff;">Two-factor authentication</strong> and optional SSO on Enterprise</span></li>
                        <li><i class="bi bi-shield-lock-fill"></i><span><strong style="color:#fff;">Document isolation</strong> &mdash; scanned files processed in a separate queue, never used to train shared models</span></li>
                        <li><i class="bi bi-shield-lock-fill"></i><span><strong style="color:#fff;">Backups and recovery</strong> with monitored uptime and restore testing</span></li>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="h-100 p-4 rounded-4" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);">
                                <i class="bi bi-building-lock" style="font-size:1.6rem;color:var(--emerald);"></i>
                                <h6 class="fw-bold mt-3 mb-1" style="color:#fff;font-size:.95rem;">Private Cloud</h6>
                                <p class="mb-0" style="font-size:.78rem;color:rgba(255,255,255,.45);line-height:1.6;">Dedicated tenancy in a Saudi-region data centre.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h-100 p-4 rounded-4" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);">
                                <i class="bi bi-hdd-network" style="font-size:1.6rem;color:var(--emerald);"></i>
                                <h6 class="fw-bold mt-3 mb-1" style="color:#fff;font-size:.95rem;">On-Premise</h6>
                                <p class="mb-0" style="font-size:.78rem;color:rgba(255,255,255,.45);line-height:1.6;">Available on Enterprise for regulated customers.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h-100 p-4 rounded-4" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);">
                                <i class="bi bi-journal-check" style="font-size:1.6rem;color:var(--emerald);"></i>
                                <h6 class="fw-bold mt-3 mb-1" style="color:#fff;font-size:.95rem;">Audit Logs</h6>
                                <p class="mb-0" style="font-size:.78rem;color:rgba(255,255,255,.45);line-height:1.6;">Exportable history for internal and external audit.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h-100 p-4 rounded-4" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);">
                                <i class="bi bi-key" style="font-size:1.6rem;color:var(--emerald);"></i>
                                <h6 class="fw-bold mt-3 mb-1" style="color:#fff;font-size:.95rem;">SSO &amp; 2FA</h6>
                                <p class="mb-0" style="font-size:.78rem;color:rgba(255,255,255,.45);line-height:1.6;">SAML single sign-on and mandatory 2FA on Enterprise.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ SWITCHING ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="section-label">Switching</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                        Moving is less painful than you think
                    </h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        The scariest part of changing freight software is not the software &mdash; it is the fear of
                        losing a decade of job history and outstanding customer balances. That is the part we
                        take off you.
                    </p>
                    <ul class="bullet-list mb-4">
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Run both systems in parallel</strong> for your first month &mdash; nothing is switched off on day one</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Open jobs migrate, not just masters</strong> &mdash; in-transit shipments keep their history</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Aging carries over exactly</strong> &mdash; every open receivable and payable with its original invoice date</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>Trial balance reconciled</strong> before we declare the migration done</span></li>
                        <li><i class="bi bi-check-circle-fill"></i><span><strong>ZATCA onboarding runs in parallel</strong>, so your first month of compliant invoicing starts clean</span></li>
                    </ul>
                    <a href="{{ url('/services') }}" class="btn-hero-outline">Read about our process <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="col-lg-6">
                    <div class="panel reveal" style="border:1.5px solid var(--line);border-radius:18px;background:var(--surface);padding:2rem;">
                        <div class="row text-center g-3">
                            <div class="col-4">
                                <div style="font-size:1.9rem;font-weight:800;color:var(--emerald-dim);">2&ndash;3</div>
                                <div style="font-size:.76rem;color:var(--ink-ghost);">Weeks to live</div>
                            </div>
                            <div class="col-4">
                                <div style="font-size:1.9rem;font-weight:800;color:var(--blue);">1</div>
                                <div style="font-size:.76rem;color:var(--ink-ghost);">Month in parallel</div>
                            </div>
                            <div class="col-4">
                                <div style="font-size:1.9rem;font-weight:800;color:var(--indigo);">0</div>
                                <div style="font-size:.76rem;color:var(--ink-ghost);">Data lost</div>
                            </div>
                        </div>
                        <div class="text-center mt-4 pt-3" style="border-top:1px solid var(--line);">
                            <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.7;">
                                Most forwarders tell us the migration was the least stressful part of the project.
                                The nervous part before it is completely normal.
                            </p>
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
                <h2 class="mb-3">Convince us you need something else</h2>
                <p class="mb-4" style="font-size:1.02rem;">
                    Bring your hardest workflow to the demo. If Flikma cannot do it, we will tell you
                    &mdash; and point you at something that can.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ url('/contact') }}" class="btn-hero-primary">Book a Live Demo <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ url('/features') }}" class="btn-outline-light-fk">Explore the Modules</a>
                </div>
            </div>
        </div>
    </section>

@endsection
