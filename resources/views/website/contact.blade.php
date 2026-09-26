@extends('website.layout')

@section('title', 'Contact Us — Book a Flikma Demo | Saudi Arabia, Bahrain & UAE')
@section('meta_description', 'Request a live Flikma demo or get in touch. Our freight forwarding software team responds within one business day. Offices in Riyadh, Manama and Dubai.')
@section('meta_keywords', 'contact logistics software Saudi Arabia, request demo freight forwarding ERP, logistics software Bahrain contact, logistics ERP UAE demo, Flikma contact')

@section('content')

    <style>
        .contact-card { border: 1.5px solid var(--line); border-radius: 20px; background: #fff; padding: 2.25rem; box-shadow: 0 20px 50px rgba(10, 15, 30, .07); }
        .info-card { border: 1.5px solid var(--line); border-radius: 16px; background: #fff; padding: 1.25rem; height: 100%; transition: all .25s; }
        .info-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(10, 15, 30, .07); }
        .info-icon { width: 40px; height: 40px; border-radius: 12px; background: var(--emerald-soft); color: var(--emerald-dim); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .bullet-list { list-style: none; padding: 0; margin: 0; }
        .bullet-list li { display: flex; align-items: flex-start; gap: .7rem; font-size: .88rem; color: var(--ink-muted); line-height: 1.65; margin-bottom: .7rem; }
        .bullet-list i { color: var(--emerald-dim); font-size: .95rem; margin-top: 4px; flex-shrink: 0; }
    </style>

    <!-- ════════════════ HERO ════════════════ -->
    <header class="page-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="hero-pill mb-3"><i class="bi bi-chat-square-text"></i> Contact</div>
                    <h1 class="mb-3">Let's talk about your freight</h1>
                    <p class="mb-0 mx-auto" style="max-width:560px;">
                        Book a live demo or send us a question. A logistics specialist &mdash; not a
                        call centre &mdash; will reply within one business day.
                    </p>
                </div>
            </div>
        </div>
    </header>

    <!-- ════════════════ CONTACT + FORM ════════════════ -->
    <section class="fk-section">
        <div class="container">
            <div class="row g-4 g-lg-5">

                <!-- LEFT: details -->
                <div class="col-lg-5">
                    <div class="section-label mb-3">Reach Us</div>
                    <h2 class="mb-4" style="font-size:clamp(1.5rem,2.6vw,2rem);font-weight:800;line-height:1.15;">
                        Three offices, one team
                    </h2>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="info-card reveal">
                            <div class="d-flex align-items-start gap-3">
                                <div class="info-icon"><i class="bi bi-telephone"></i></div>
                                <div>
                                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-ghost);">Call us</div>
                                    <a href="tel:+966595555343" class="d-block fw-semibold" style="font-size:1rem;dir:ltr;">+966 59 555 5343</a>
                                    <div style="font-size:.75rem;color:var(--ink-ghost);">Sunday&ndash;Thursday, 9:00&ndash;18:00 AST</div>
                                </div>
                            </div>
                        </div>

                        <div class="info-card reveal">
                            <div class="d-flex align-items-start gap-3">
                                <div class="info-icon"><i class="bi bi-envelope"></i></div>
                                <div>
                                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-ghost);">Email us</div>
                                    <a href="mailto:support@flikma.com" class="d-block fw-semibold" style="font-size:.95rem;">support@flikma.com</a>
                                    <div style="font-size:.75rem;color:var(--ink-ghost);">Support, sales and partnerships</div>
                                </div>
                            </div>
                        </div>

                        <div class="info-card reveal">
                            <div class="d-flex align-items-start gap-3">
                                <div class="info-icon"><i class="bi bi-geo-alt"></i></div>
                                <div>
                                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-ghost);">Offices</div>
                                    <div class="fw-semibold" style="font-size:.9rem;">Riyadh &middot; Manama &middot; Dubai</div>
                                    <div style="font-size:.75rem;color:var(--ink-ghost);">Headquarters in Riyadh, Saudi Arabia</div>
                                </div>
                            </div>
                        </div>

                        <div class="info-card reveal">
                            <div class="d-flex align-items-start gap-3">
                                <div class="info-icon"><i class="bi bi-whatsapp"></i></div>
                                <div>
                                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-ghost);">WhatsApp</div>
                                    <a href="https://wa.me/966595555343" target="_blank" rel="noopener" class="d-block fw-semibold" style="font-size:.95rem;dir:ltr;">+966 59 555 5343</a>
                                    <div style="font-size:.75rem;color:var(--ink-ghost);">Fastest for quick questions</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-4 reveal" style="background:var(--ink-panel);">
                        <h3 class="fw-bold mb-3" style="color:#fff;font-size:1.05rem;">What happens next</h3>
                        <ul class="bullet-list mb-0">
                            <li><i class="bi bi-1-circle-fill"></i><span>We read your message and match you with someone who knows freight forwarding &mdash; not a generic sales rep.</span></li>
                            <li><i class="bi bi-2-circle-fill"></i><span>You get a reply within one business day with a proposed time for a demo.</span></li>
                            <li><i class="bi bi-3-circle-fill"></i><span>The demo runs on <strong style="color:#fff;">your lanes and your carriers</strong>, not a generic sample dataset.</span></li>
                            <li><i class="bi bi-4-circle-fill"></i><span>If Flikma is not the right fit, we will say so. We would rather lose the deal than sell you the wrong system.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- RIGHT: form -->
                <div class="col-lg-7">
                    <div class="contact-card reveal">
                        <h2 class="mb-2" style="font-size:1.4rem;font-weight:800;">Request a demo</h2>
                        <p class="mb-4" style="color:var(--ink-muted);font-size:.92rem;line-height:1.7;">
                            Tell us a little about your operation so we can prepare something useful for the call.
                        </p>

                        {{-- Success --}}
                        @if (session('contact_success'))
                            <div class="alert d-flex align-items-start gap-3 mb-4" role="alert"
                                 style="background:var(--emerald-soft);border:1px solid rgba(0,201,123,.3);border-radius:14px;color:var(--ink);">
                                <i class="bi bi-check-circle-fill" style="font-size:1.3rem;color:var(--emerald-dim);"></i>
                                <div>
                                    <div class="fw-bold mb-1">Thank you &mdash; your message is with us.</div>
                                    <div style="font-size:.88rem;color:var(--ink-muted);">{{ session('contact_success') }}</div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ url('/contact') }}" novalidate>
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-fk" for="name">Full Name <span style="color:var(--red);">*</span></label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                           class="form-ctrl @error('name') is-invalid @enderror" placeholder="e.g. Ahmed Al Harbi">
                                    @error('name')
                                        <div class="invalid-feedback d-block" style="font-size:.78rem;">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-fk" for="email">Work Email <span style="color:var(--red);">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                           class="form-ctrl @error('email') is-invalid @enderror" placeholder="you@company.com">
                                    @error('email')
                                        <div class="invalid-feedback d-block" style="font-size:.78rem;">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-fk" for="company">Company</label>
                                    <input type="text" id="company" name="company" value="{{ old('company') }}"
                                           class="form-ctrl @error('company') is-invalid @enderror" placeholder="Your forwarding company">
                                    @error('company')
                                        <div class="invalid-feedback d-block" style="font-size:.78rem;">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-fk" for="phone">Phone / WhatsApp</label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                           class="form-ctrl @error('phone') is-invalid @enderror" placeholder="+966 5X XXX XXXX" dir="ltr">
                                    @error('phone')
                                        <div class="invalid-feedback d-block" style="font-size:.78rem;">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label-fk" for="interest">I am interested in</label>
                                    <select id="interest" name="interest" class="form-ctrl @error('interest') is-invalid @enderror">
                                        <option value="">Please select&hellip;</option>
                                        <option value="demo"        @selected(old('interest') === 'demo')>A live product demo</option>
                                        <option value="trial"       @selected(old('interest') === 'trial')>Starting a free trial</option>
                                        <option value="pricing"     @selected(old('interest') === 'pricing')>Pricing and plan advice</option>
                                        <option value="implementation" @selected(old('interest') === 'implementation')>Implementation &amp; data migration</option>
                                        <option value="zatca"        @selected(old('interest') === 'zatca')>ZATCA Phase 2 onboarding help</option>
                                        <option value="integration"  @selected(old('interest') === 'integration')>Custom integration / API</option>
                                        <option value="support"      @selected(old('interest') === 'support')>Existing customer support</option>
                                        <option value="partnership"  @selected(old('interest') === 'partnership')>Partnership</option>
                                        <option value="other"        @selected(old('interest') === 'other')>Something else</option>
                                    </select>
                                    @error('interest')
                                        <div class="invalid-feedback d-block" style="font-size:.78rem;">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label-fk" for="message">Message</label>
                                    <textarea id="message" name="message" rows="5"
                                              class="form-ctrl @error('message') is-invalid @enderror"
                                              placeholder="Tell us about your operation: how many entities, roughly how many shipments a month, and what is hurting you most right now?">{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback d-block" style="font-size:.78rem;">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn-cta-main w-100 justify-content-center" style="padding:.95rem 2rem;">
                                        Send message <i class="bi bi-send"></i>
                                    </button>
                                    <p class="text-center mt-3 mb-0" style="font-size:.78rem;color:var(--ink-ghost);">
                                        We use your details only to respond to this enquiry.
                                        No newsletters, no resale of your information.
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ OFFICES ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Our Offices</div>
                <h2>Where to find us</h2>
            </div>

            <div class="row g-4">
                <div class="col-md-4 reveal">
                    <div style="border:1.5px solid var(--line);border-radius:18px;background:#fff;padding:1.75rem;height:100%;">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="feat-icon-box" style="background:var(--emerald);color:var(--ink);"><i class="bi bi-geo-alt"></i></span>
                            <div>
                                <h5 class="mb-0 fw-bold" style="font-size:1.05rem;">Riyadh</h5>
                                <div style="font-size:.72rem;color:var(--emerald-dim);font-weight:600;">Headquarters</div>
                            </div>
                        </div>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            Engineering and customer operations. The team that builds and supports Flikma
                            across the region.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div style="border:1.5px solid var(--line);border-radius:18px;background:#fff;padding:1.75rem;height:100%;">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="feat-icon-box" style="background:var(--blue);color:#fff;"><i class="bi bi-geo-alt"></i></span>
                            <div>
                                <h5 class="mb-0 fw-bold" style="font-size:1.05rem;">Manama</h5>
                                <div style="font-size:.72rem;color:var(--blue);font-weight:600;">Bahrain</div>
                            </div>
                        </div>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            Customer operations for Bahraini forwarders, including ZATCA-equivalent
                            invoicing and GCC corridor support.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div style="border:1.5px solid var(--line);border-radius:18px;background:#fff;padding:1.75rem;height:100%;">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="feat-icon-box" style="background:var(--violet);color:#fff;"><i class="bi bi-geo-alt"></i></span>
                            <div>
                                <h5 class="mb-0 fw-bold" style="font-size:1.05rem;">Dubai</h5>
                                <div style="font-size:.72rem;color:var(--violet);font-weight:600;">United Arab Emirates</div>
                            </div>
                        </div>
                        <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                            Customer operations for Emirati forwarders, with on-site implementation and
                            training available across the Emirates.
                        </p>
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
                <h2>Before you get in touch</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 0 ? null : 0">
                            <span>How long does a demo take?</span>
                            <i class="bi" :class="open === 0 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 0 && 'open'">
                            <p>Roughly 30 to 45 minutes. We spend the first ten minutes on your current process,
                               then walk the full lifecycle end to end. If you send us a lane or a real scenario
                               beforehand, we will demo exactly that.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 1 ? null : 1">
                            <span>Can you show it in Arabic?</span>
                            <i class="bi" :class="open === 1 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 1 && 'open'">
                            <p>Yes. The entire interface, the invoices and the bills of lading are bilingual, and
                               our team can run the whole demo in Arabic if that is more useful for your team.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 2 ? null : 2">
                            <span>We already use a system. Is a migration realistic?</span>
                            <i class="bi" :class="open === 2 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 2 && 'open'">
                            <p>Almost always, and we run both systems in parallel for your first month so nothing
                               is switched off on day one. Tell us what you are on and we will tell you honestly
                               what the migration involves. See
                               <a href="{{ url('/services') }}" class="text-emerald fw-semibold">Services</a>.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 3 ? null : 3">
                            <span>Do you support companies outside the GCC?</span>
                            <i class="bi" :class="open === 3 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 3 && 'open'">
                            <p>Flikma is built for Saudi, Bahraini and Emirati operations and for the corridors
                               between them. It will run elsewhere, but the ZATCA pipeline and the local tax
                               handling are specific to the GCC &mdash; if you are outside it, talk to us first so
                               we can be straight about the fit.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
