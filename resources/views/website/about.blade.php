@extends('website.layout')

@section('title', 'About Flikma — Saudi Logistics Software for Vision 2030')
@section('meta_description', 'Flikma builds AI-powered logistics software for freight forwarders across Saudi Arabia, Bahrain and the UAE. Learn about our story, our mission, our values and how we support Saudi Vision 2030 and the digital logistics economy.')
@section('meta_keywords', 'Flikma about, Saudi logistics software company, Saudi Vision 2030 logistics software, freight forwarding software Saudi Arabia, GCC logistics technology, Saudi logistics ERP')

@section('content')

    <style>
        .timeline { position: relative; padding-left: 0; }
        .tl-item { display: flex; gap: 1.5rem; position: relative; padding-bottom: 2.5rem; }
        .tl-item:last-child { padding-bottom: 0; }
        .tl-item::after { content: ''; position: absolute; left: 23px; top: 50px; bottom: 8px; width: 1px; background: var(--line-strong); }
        .tl-item:last-child::after { display: none; }
        .tl-dot { width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0; background: var(--emerald); color: var(--ink); font-weight: 800; font-size: .8rem; display: flex; align-items: center; justify-content: center; z-index: 1; }
        .tl-dot.dim { background: #fff; border: 2px solid var(--emerald); color: var(--emerald-dim); }
        .value-card { border: 1.5px solid var(--line); border-radius: 18px; background: #fff; padding: 1.75rem; height: 100%; transition: all .25s; }
        .value-card:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(10, 15, 30, .07); }
        .bullet-list { list-style: none; padding: 0; margin: 0; }
        .bullet-list li { display: flex; align-items: flex-start; gap: .7rem; font-size: .92rem; color: var(--ink-muted); line-height: 1.7; margin-bottom: .75rem; }
        .bullet-list i { color: var(--emerald-dim); font-size: .95rem; margin-top: 4px; flex-shrink: 0; }
        .bullet-list strong { color: var(--ink); font-weight: 600; }
        .pillar-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.09); border-radius: 16px; padding: 1.5rem; height: 100%; }
    </style>

    <!-- ════════════════ HERO ════════════════ -->
    <header class="page-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-pill mb-3"><i class="bi bi-buildings"></i> About Flikma</div>
                    <h1 class="mb-3">We build logistics software for the Kingdom's trade.</h1>
                    <p class="mb-4" style="max-width:580px;">
                        Flikma is a Saudi software company serving freight forwarders and 3PLs across the GCC.
                        We exist because the software on the market was written by accountants, and freight
                        forwarding is not an accounting problem.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ url('/contact') }}" class="btn-hero-primary">Get in Touch <i class="bi bi-arrow-right"></i></a>
                        <a href="{{ url('/why-flikma') }}" class="btn-hero-outline">Why Flikma</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--emerald-dim);">Riyadh</div><div class="chip-label">Headquarters</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--blue);">GCC</div><div class="chip-label">Operating region</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--indigo);">3</div><div class="chip-label">Countries live</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--violet);">2030</div><div class="chip-label">Vision aligned</div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ════════════════ OUR STORY ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="section-label">Our Story</div>
                    <h2 class="mt-2 mb-4" style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                        Started by people who were tired of retyping the same documents twice
                    </h2>
                    <p style="color:var(--ink-muted);line-height:1.8;">
                        The team behind Flikma came out of the forwarding industry. Not from enterprise software,
                        not from a bank &mdash; from freight offices in Riyadh and Dubai, watching operations staff
                        spend their days copying data between a quoting spreadsheet, a carrier portal and an
                        accounting package that had never heard of a bill of lading.
                    </p>
                    <p style="color:var(--ink-muted);line-height:1.8;">
                        The insight was simple and slightly uncomfortable: the bottleneck in freight forwarding is
                        not the trucks, the ships or the planes. It is the paperwork, and specifically the act of
                        a human being transcribing a document that already exists in a perfectly legible form.
                    </p>
                    <p style="color:var(--ink-muted);line-height:1.8;">
                        So we built the system around the shipment instead of around the ledger, and we made
                        document interpretation a first-class capability rather than an experimental feature.
                        Saudi Arabia made the timing right &mdash; ZATCA Phase 2 arrived and turned compliance
                        from a spreadsheet exercise into a hard requirement that every forwarder had to meet.
                    </p>
                    <p class="mb-0" style="color:var(--ink-muted);line-height:1.8;">
                        Today Flikma runs freight operations and finance for logistics companies in Saudi Arabia,
                        Bahrain and the UAE. We are still deliberately a logistics product, and we are still
                        allergic to features our customers do not need.
                    </p>
                </div>

                <div class="col-lg-6">
                    <div class="timeline reveal">
                        <div class="tl-item">
                            <span class="tl-dot">01</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.02rem;">The frustration</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    Forwarders in the GCC running three systems that did not talk to each other,
                                    and staff retyping carrier invoices at the end of every week.
                                </p>
                            </div>
                        </div>
                        <div class="tl-item">
                            <span class="tl-dot">02</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.02rem;">The first release</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    A freight ERP with the job file at the centre &mdash; enquiry, quotation, job,
                                    bill of lading, invoice, collection &mdash; and a real double-entry ledger behind it.
                                </p>
                            </div>
                        </div>
                        <div class="tl-item">
                            <span class="tl-dot">03</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.02rem;">ZATCA Phase 2</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    Not bolted on &mdash; built into the invoice pipeline with UBL 2.1 signing,
                                    QR stamping and real-time clearance. Included on every plan, including free.
                                </p>
                            </div>
                        </div>
                        <div class="tl-item">
                            <span class="tl-dot">04</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.02rem;">The AI layer</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    Document scanning and AI expense capture shipped &mdash; removing the
                                    transcription step that started all of this.
                                </p>
                            </div>
                        </div>
                        <div class="tl-item">
                            <span class="tl-dot dim">05</span>
                            <div>
                                <h5 class="feat-title mb-1" style="font-size:1.02rem;">Where we are now</h5>
                                <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                    Live across Saudi Arabia, Bahrain and the UAE &mdash; and still a logistics
                                    product, still deliberately without a warehouse module.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ MISSION & VISION 2030 ════════════════ -->
    <section class="fk-section bg-ink">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <div class="section-label">Mission &amp; Vision 2030</div>
                    <h2 class="mt-2 mb-4" style="color:#fff;font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                    Digitised trade runs on better software
                    </h2>
                    <p class="mb-4" style="color:rgba(255,255,255,.55);line-height:1.8;">
                        Saudi Vision 2030 is not a slogan our customers have to be sold on. It is the context
                        they already operate in: electronic invoices mandated by ZATCA, electronic customs
                        declarations, a logistics sector being formalised and expanded, and a Vision 2030 ambition
                        to make the Kingdom a regional logistics hub.
                    </p>
                    <p class="mb-0" style="color:rgba(255,255,255,.55);line-height:1.8;">
                        Software is infrastructure. If the paperwork is slow, the trade is slow. Our mission is to
                        make the administrative layer of GCC logistics fast, accurate and invisible &mdash; so
                        the freight itself can move.
                    </p>
                </div>

                <div class="col-lg-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="pillar-card">
                                <i class="bi bi-file-earmark-check" style="font-size:1.5rem;color:var(--emerald);"></i>
                                <h5 class="fw-bold mt-3 mb-2" style="color:#fff;font-size:1rem;">Digital Invoicing</h5>
                                <p class="mb-0" style="font-size:.82rem;color:rgba(255,255,255,.5);line-height:1.65;">
                                    Supporting the Kingdom's shift to mandatory electronic invoicing, with
                                    compliant clearance that is a by-product of normal operation rather than a
                                    separate project.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="pillar-card">
                                <i class="bi bi-box-arrow-in-down" style="font-size:1.5rem;color:var(--emerald);"></i>
                                <h5 class="fw-bold mt-3 mb-2" style="color:#fff;font-size:1rem;">Digital Customs</h5>
                                <p class="mb-0" style="font-size:.82rem;color:rgba(255,255,255,.5);line-height:1.65;">
                                    Positioned for fully electronic customs declarations, producing clean
                                    structured data from the job file rather than re-keying declarations by hand.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="pillar-card">
                                <i class="bi bi-globe-americas" style="font-size:1.5rem;color:var(--emerald);"></i>
                                <h5 class="fw-bold mt-3 mb-2" style="color:#fff;font-size:1rem;">Regional Hub</h5>
                                <p class="mb-0" style="font-size:.82rem;color:rgba(255,255,255,.5);line-height:1.65;">
                                    Built for the corridors that matter: Saudi&ndash;Bahrain, Saudi&ndash;UAE and
                                    the Red Sea &rarr; Gulf lanes, with multi-entity support across the region.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="pillar-card">
                                <i class="bi bi-lightning-charge" style="font-size:1.5rem;color:var(--emerald);"></i>
                                <h5 class="fw-bold mt-3 mb-2" style="color:#fff;font-size:1rem;">Private Sector</h5>
                                <p class="mb-0" style="font-size:.82rem;color:rgba(255,255,255,.5);line-height:1.65;">
                                    Enabling Saudi logistics SMEs to compete regionally on accuracy and speed
                                    rather than on manual effort.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ VALUES ════════════════ -->
    <section class="fk-section">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Our Values</div>
                <h2>Four rules we do not break</h2>
                <p>These decide what we build, what we refuse to build, and how we handle your data.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="value-card">
                        <div class="feat-icon-box mb-3" style="background:var(--emerald-soft);color:var(--emerald-dim);"><i class="bi bi-bullseye"></i></div>
                        <h5 class="feat-title mb-2" style="font-size:1.05rem;">Precision over features</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            A module we do not offer is cheaper than a broken one we do. We would rather tell
                            you to buy a WMS than ship you warehouse logic we cannot support.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="value-card">
                        <div class="feat-icon-box mb-3" style="background:rgba(79,70,229,.09);color:var(--indigo);"><i class="bi bi-universal-access"></i></div>
                        <h5 class="feat-title mb-2" style="font-size:1.05rem;">Arabic is not optional</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            If your customers, your carriers and the customs counter all work in Arabic, the
                            software has to. Bilingual is the baseline, not a localised edition.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="value-card">
                        <div class="feat-icon-box mb-3" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-shield-check"></i></div>
                        <h5 class="feat-title mb-2" style="font-size:1.05rem;">Compliance by default</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            A legal requirement should never be a paid upgrade. ZATCA Phase 2 is in the base
                            product, and it will stay there.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="value-card">
                        <div class="feat-icon-box mb-3" style="background:rgba(6,182,212,.09);color:var(--cyan);"><i class="bi bi-person-check"></i></div>
                        <h5 class="feat-title mb-2" style="font-size:1.05rem;">Humans stay in charge</h5>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            AI reads and pre-fills. A person confirms before anything reaches the ledger. No
                            automated posting to your books, ever.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ PRESENCE ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <div class="section-label">Where We Are</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                        Built here, supported here
                    </h2>
                    <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                        We are a regional company serving a regional market. That means support during your
                        business hours, in your language, by people who understand a bill of lading.
                    </p>
                    <ul class="bullet-list mb-0">
                        <li><i class="bi bi-geo-alt-fill"></i><span><strong>Riyadh, Saudi Arabia</strong> &mdash; headquarters and engineering</span></li>
                        <li><i class="bi bi-geo-alt-fill"></i><span><strong>Manama, Bahrain</strong> &mdash; customer operations</span></li>
                        <li><i class="bi bi-geo-alt-fill"></i><span><strong>Dubai, UAE</strong> &mdash; customer operations</span></li>
                        <li><i class="bi bi-clock-fill"></i><span><strong>Gulf business hours</strong> &mdash; Sunday to Thursday, with 24-hour response on paid plans</span></li>
                    </ul>
                </div>

                <div class="col-lg-7">
                    <div class="row g-3 reveal">
                        <div class="col-12">
                            <div class="p-4 rounded-4" style="background:var(--surface);border:1.5px solid var(--line);">
                                <div class="d-flex flex-wrap align-items-center gap-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="feat-icon-box" style="background:var(--emerald);color:var(--ink);"><i class="bi bi-geo-alt"></i></span>
                                        <div>
                                            <div class="fw-bold" style="font-size:.95rem;">Riyadh, Saudi Arabia</div>
                                            <div style="font-size:.75rem;color:var(--ink-ghost);">Headquarters &middot; Engineering</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="feat-icon-box" style="background:var(--blue);color:#fff;"><i class="bi bi-geo-alt"></i></span>
                                        <div>
                                            <div class="fw-bold" style="font-size:.95rem;">Manama, Bahrain</div>
                                            <div style="font-size:.75rem;color:var(--ink-ghost);">Customer Operations</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="feat-icon-box" style="background:var(--violet);color:#fff;"><i class="bi bi-geo-alt"></i></span>
                                        <div>
                                            <div class="fw-bold" style="font-size:.95rem;">Dubai, UAE</div>
                                            <div style="font-size:.75rem;color:var(--ink-ghost);">Customer Operations</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 rounded-4 h-100" style="background:var(--ink);">
                                <i class="bi bi-telephone" style="font-size:1.4rem;color:var(--emerald);"></i>
                                <div class="fw-bold mt-3 mb-1" style="color:#fff;font-size:.9rem;">Call us</div>
                                <a href="tel:+966595555343" class="d-block" style="font-size:.85rem;color:rgba(255,255,255,.6);" dir="ltr">+966 59 555 5343</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 rounded-4 h-100" style="background:var(--ink);">
                                <i class="bi bi-envelope" style="font-size:1.4rem;color:var(--emerald);"></i>
                                <div class="fw-bold mt-3 mb-1" style="color:#fff;font-size:.9rem;">Email us</div>
                                <a href="mailto:support@flikma.com" class="d-block" style="font-size:.85rem;color:rgba(255,255,255,.6);">support@flikma.com</a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 rounded-4 h-100" style="background:var(--ink);">
                                <i class="bi bi-calendar-check" style="font-size:1.4rem;color:var(--emerald);"></i>
                                <div class="fw-bold mt-3 mb-1" style="color:#fff;font-size:.9rem;">Book a demo</div>
                                <a href="{{ url('/contact') }}" class="d-block" style="font-size:.85rem;color:rgba(255,255,255,.6);">See Flikma live &rarr;</a>
                            </div>
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
                <h2 class="mb-3">Let's talk about your freight</h2>
                <p class="mb-4" style="font-size:1.02rem;">
                    Whether you are moving off spreadsheets, replacing a legacy ERP, or just tired of
                    retyping documents &mdash; we would like to hear about it.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ url('/contact') }}" class="btn-hero-primary">Contact Us <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ url('/features') }}" class="btn-outline-light-fk">Explore Features</a>
                </div>
            </div>
        </div>
    </section>

@endsection
