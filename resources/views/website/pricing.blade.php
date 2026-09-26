@extends('website.layout')

@section('title', 'Pricing — Plans for Every Freight Forwarder | Flikma')
@section('meta_description', 'Transparent Flikma pricing in SAR or USD, monthly or annual. Starter is free forever. Every plan includes ZATCA Phase 2 e-invoicing and unlimited users — you pay for your operation, not your headcount.')
@section('meta_keywords', 'logistics software pricing Saudi Arabia, freight forwarding ERP cost, ZATCA invoicing software pricing, logistics software price Bahrain, freight software subscription UAE, 3PL software pricing GCC')

@section('content')

{{-- One Alpine scope wraps the whole page so the hero toggles drive the plan cards below. --}}
<div x-data="{ isUsd: false, isYearly: false }">

    <style>
        .plan-card { border: 1.5px solid var(--line); border-radius: 20px; background: #fff; padding: 2.25rem 1.85rem; height: 100%; transition: all .25s; display: flex; flex-direction: column; }
        .plan-card:hover { transform: translateY(-4px); box-shadow: 0 20px 50px rgba(10, 15, 30, .07); }
        .plan-card.featured { background: var(--ink-panel); border-color: var(--ink-panel); box-shadow: 0 28px 60px rgba(10, 15, 30, .22); }
        .plan-card.featured .plan-name, .plan-card.featured .plan-price, .plan-card.featured h5 { color: #fff; }
        .plan-card.featured .plan-desc, .plan-card.featured .plan-feats li { color: rgba(255, 255, 255, .55); }
        .plan-card.featured .plan-feats i { color: var(--emerald); }
        .plan-card.featured .plan-price small { color: rgba(255, 255, 255, .4); }
        .plan-card.featured .plan-feats li strong { color: #fff; }
        .plan-name { font-size: 1.05rem; font-weight: 700; }
        .plan-desc { font-size: .85rem; color: var(--ink-muted); min-height: 2.6rem; }
        .plan-price { font-size: 2.6rem; font-weight: 800; letter-spacing: -.045em; line-height: 1; }
        .plan-price small { font-size: .85rem; font-weight: 500; color: var(--ink-ghost); letter-spacing: 0; }
        .plan-feats { list-style: none; padding: 0; margin: 0; }
        .plan-feats li { display: flex; align-items: flex-start; gap: .55rem; font-size: .85rem; color: var(--ink-muted); margin-bottom: .6rem; line-height: 1.5; }
        .plan-feats i { color: var(--emerald-dim); font-size: .9rem; margin-top: 3px; flex-shrink: 0; }
        .plan-feats strong { color: var(--ink); font-weight: 600; }
        .popular-chip { position: absolute; top: -13px; left: 50%; transform: translateX(-50%); background: var(--emerald); color: var(--ink); font-size: .65rem; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: .3rem .85rem; border-radius: 50px; white-space: nowrap; }

        /* Currency / billing toggles */
        .cur-toggle { display: inline-flex; background: #e2e8f0; border-radius: 50px; padding: 3px; }
        .cur-item { border: 0; background: transparent; border-radius: 50px; padding: .4rem 1.1rem; font-size: .82rem; font-weight: 600; color: var(--ink-muted); transition: all .2s; }
        .cur-item.active { background: #fff; color: var(--ink); box-shadow: 0 2px 8px rgba(10, 15, 30, .1); }
        .cur-item.save { position: relative; }
        .cur-item.save::after { content: '−17%'; font-size: .6rem; font-weight: 800; margin-left: .35rem; color: var(--emerald-dim); }

        /* Comparison table */
        .comp-table { border: 1.5px solid var(--line); border-radius: 16px; overflow: hidden; background: #fff; }
        .comp-head { display: grid; grid-template-columns: 1.6fr repeat(4, 1fr); background: var(--ink-panel); color: #fff; font-size: .72rem; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
        .comp-head > div { padding: 1rem .9rem; text-align: center; }
        .comp-head > div:first-child { text-align: left; }
        .comp-head .hl { color: var(--emerald); }
        .comp-cat { padding: .8rem 1.1rem; background: var(--surface); font-size: .68rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--ink-ghost); }
        .comp-row { display: grid; grid-template-columns: 1.6fr repeat(4, 1fr); border-top: 1px solid var(--line); font-size: .85rem; }
        .comp-row > div { padding: .7rem .9rem; color: var(--ink-muted); text-align: center; }
        .comp-row > div:first-child { text-align: left; color: var(--ink); }
        .comp-row .hl { background: rgba(0, 201, 123, .04); }
        .comp-row i.yes { color: var(--emerald-dim); font-size: 1rem; }
        .comp-row i.no  { color: #cbd5e1; font-size: 1rem; }
        @media (max-width: 991.98px) {
            .comp-table { overflow-x: auto; }
            .comp-head, .comp-row { min-width: 760px; }
        }

        .trust-item { display: flex; align-items: center; gap: .7rem; font-size: .85rem; color: var(--ink-muted); }
        .trust-item i { color: var(--emerald-dim); font-size: 1.1rem; }
    </style>

    <!-- ════════════════ HERO ════════════════ -->
    <header class="page-hero" style="text-align:center;">
        <div class="container">
            <div class="mx-auto" style="max-width:700px;">
                <div class="hero-pill mb-3"><i class="bi bi-cash-stack"></i> Pricing</div>
                <h1 class="mb-3">Priced per forwarder, not per employee</h1>
                <p class="mb-4">
                    Unlimited users on every plan. You pay for the operation you run &mdash; and ZATCA Phase 2
                    plus the AI document scanning are included, not bolted on as an upgrade.
                </p>

                <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center">
                    <div class="cur-toggle" role="group" aria-label="Currency">
                        <button type="button" class="cur-item" :class="!isUsd && 'active'" @click="isUsd = false">SAR</button>
                        <button type="button" class="cur-item" :class="isUsd && 'active'" @click="isUsd = true">USD</button>
                    </div>
                    <div class="cur-toggle" role="group" aria-label="Billing period">
                        <button type="button" class="cur-item" :class="!isYearly && 'active'" @click="isYearly = false">Monthly</button>
                        <button type="button" class="cur-item save" :class="isYearly && 'active'" @click="isYearly = true">Annual</button>
                    </div>
                </div>
                <p class="mt-3 mb-0 small text-ink-ghost">1 USD &asymp; 3.75 SAR &middot; Annual billing saves two months &middot; All prices exclude VAT</p>
            </div>
        </div>
    </header>

    <!-- ════════════════ PLANS ════════════════ -->
    <section class="fk-section">
        <div class="container">

            <div class="row g-4 align-items-stretch">

                {{-- STARTER --}}
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="plan-card">
                        <div class="plan-name mb-1">Starter</div>
                        <div class="plan-desc mb-3">For new forwarders getting off spreadsheets.</div>
                        <div class="plan-price mb-1">Free</div>
                        <div class="small mb-3" style="color:var(--ink-ghost);min-height:1.4rem;">forever, no card needed</div>
                        <ul class="plan-feats">
                            <li><i class="bi bi-check2"></i> <strong>1 company</strong>, up to 2 users</li>
                            <li><i class="bi bi-check2"></i> Enquiry, quotation &amp; job files</li>
                            <li><i class="bi bi-check2"></i> Invoicing, payments &amp; collections</li>
                            <li><i class="bi bi-check2"></i> <strong>ZATCA Phase 2</strong> included</li>
                            <li><i class="bi bi-check2"></i> 50 AI document scans / month</li>
                            <li><i class="bi bi-check2"></i> Core reports</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-ghost-light text-center mt-auto mt-4 w-100">Start Free</a>
                    </div>
                </div>

                {{-- GROWTH --}}
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="plan-card">
                        <div class="plan-name mb-1">Growth</div>
                        <div class="plan-desc mb-3">For established offices with real volume.</div>
                        <div class="plan-price mb-1">
                            <span x-show="!isUsd"><span x-text="isYearly ? '333' : '399'"></span> <small>SAR / mo</small></span>
                            <span x-show="isUsd" x-cloak><span x-text="isYearly ? '89' : '106'"></span> <small>USD / mo</small></span>
                        </div>
                        <div class="small mb-3" style="color:var(--ink-ghost);min-height:1.4rem;">
                            <span x-show="isYearly">billed annually &middot; <span x-text="!isUsd ? '3,990 SAR' : '1,064 USD'"></span></span>
                            <span x-show="!isYearly">billed monthly &middot; cancel anytime</span>
                        </div>
                        <ul class="plan-feats">
                            <li><i class="bi bi-check2"></i> <strong>3 companies</strong>, unlimited users</li>
                            <li><i class="bi bi-check2"></i> Full bills of lading &amp; airway bill suite</li>
                            <li><i class="bi bi-check2"></i> <strong>500 AI scans</strong> / month</li>
                            <li><i class="bi bi-check2"></i> AI expense capture</li>
                            <li><i class="bi bi-check2"></i> Payroll &amp; attendance</li>
                            <li><i class="bi bi-check2"></i> All finance &amp; tax reports</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-ghost-light text-center mt-auto mt-4 w-100">Start Free</a>
                    </div>
                </div>

                {{-- PROFESSIONAL --}}
                <div class="col-md-6 col-lg-3 reveal" style="position:relative;">
                    <span class="popular-chip">Most Popular</span>
                    <div class="plan-card featured">
                        <div class="plan-name mb-1">Professional</div>
                        <div class="plan-desc mb-3">For multi-branch and multi-entity groups.</div>
                        <div class="plan-price mb-1">
                            <span x-show="!isUsd"><span x-text="isYearly ? '666' : '799'"></span> <small>SAR / mo</small></span>
                            <span x-show="isUsd" x-cloak><span x-text="isYearly ? '178' : '213'"></span> <small>USD / mo</small></span>
                        </div>
                        <div class="small mb-3" style="color:rgba(255,255,255,.4);min-height:1.4rem;">
                            <span x-show="isYearly">billed annually &middot; <span x-text="!isUsd ? '7,990 SAR' : '2,130 USD'"></span></span>
                            <span x-show="!isYearly">billed monthly &middot; cancel anytime</span>
                        </div>
                        <ul class="plan-feats">
                            <li><i class="bi bi-check2"></i> <strong>Unlimited companies</strong>, unlimited users</li>
                            <li><i class="bi bi-check2"></i> <strong>Unlimited AI document scans</strong></li>
                            <li><i class="bi bi-check2"></i> Unlimited AI expense capture</li>
                            <li><i class="bi bi-check2"></i> Consolidated group reporting</li>
                            <li><i class="bi bi-check2"></i> API access &amp; webhooks</li>
                            <li><i class="bi bi-check2"></i> Priority support</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-emerald text-center mt-auto mt-4 w-100">Start Free</a>
                    </div>
                </div>

                {{-- ENTERPRISE --}}
                <div class="col-md-6 col-lg-3 reveal">
                    <div class="plan-card">
                        <div class="plan-name mb-1">Enterprise</div>
                        <div class="plan-desc mb-3">For groups with bespoke integration needs.</div>
                        <div class="plan-price mb-1" style="font-size:2rem;">Custom</div>
                        <div class="small mb-3" style="color:var(--ink-ghost);min-height:1.4rem;">annual agreement</div>
                        <ul class="plan-feats">
                            <li><i class="bi bi-check2"></i> Everything in Professional</li>
                            <li><i class="bi bi-check2"></i> Private cloud or on-premise</li>
                            <li><i class="bi bi-check2"></i> Custom AI model tuning</li>
                            <li><i class="bi bi-check2"></i> SSO, audit logs, 2FA</li>
                            <li><i class="bi bi-check2"></i> Custom development sprints</li>
                            <li><i class="bi bi-check2"></i> Named implementation lead</li>
                        </ul>
                        <a href="{{ url('/contact') }}" class="btn-ghost-light text-center mt-auto mt-4 w-100">Contact Sales</a>
                    </div>
                </div>
            </div>

            <!-- Trust strip -->
            <div class="row g-3 justify-content-center mt-5 pt-4 reveal" style="border-top:1px solid var(--line);">
                <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-credit-card-2-front"></i> No credit card to start</div></div>
                <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-arrow-counterclockwise"></i> Cancel any time</div></div>
                <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-people"></i> Unlimited users, always</div></div>
                <div class="col-6 col-md-3"><div class="trust-item"><i class="bi bi-shield-check"></i> ZATCA in every plan</div></div>
            </div>
        </div>
    </section>

    <!-- ════════════════ COMPARISON TABLE ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Compare</div>
                <h2>Every feature, side by side</h2>
            </div>

            <div class="comp-table reveal">
                <div class="comp-head">
                    <div>Feature</div>
                    <div>Starter</div>
                    <div>Growth</div>
                    <div class="hl">Professional</div>
                    <div>Enterprise</div>
                </div>

                <div class="comp-cat">Freight Operations</div>
                <div class="comp-row">
                    <div>Companies / entities</div>
                    <div>1</div><div>3</div><div class="hl">Unlimited</div><div>Unlimited</div>
                </div>
                <div class="comp-row">
                    <div>Users</div>
                    <div>2</div><div>Unlimited</div><div class="hl">Unlimited</div><div>Unlimited</div>
                </div>
                <div class="comp-row">
                    <div>Enquiry, quotation &amp; job files</div>
                    <div><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>
                <div class="comp-row">
                    <div>Multi-leg air, sea &amp; road tracking</div>
                    <div><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>
                <div class="comp-row">
                    <div>Airway bills, sea &amp; road waybills</div>
                    <div><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>

                <div class="comp-cat">AI &amp; Documents</div>
                <div class="comp-row">
                    <div>AI document scans / month</div>
                    <div>50</div><div>500</div><div class="hl">Unlimited</div><div>Unlimited</div>
                </div>
                <div class="comp-row">
                    <div>AI expense capture (photo receipt)</div>
                    <div><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>
                <div class="comp-row">
                    <div>Custom AI model tuning</div>
                    <div><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-dash-circle no"></i></div>
                    <div class="hl"><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>

                <div class="comp-cat">Finance &amp; Compliance</div>
                <div class="comp-row">
                    <div><strong>ZATCA Phase 2 e-invoicing</strong></div>
                    <div><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>
                <div class="comp-row">
                    <div>Credit &amp; debit notes</div>
                    <div><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>
                <div class="comp-row">
                    <div>Full tax &amp; trial balance reports</div>
                    <div><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>

                <div class="comp-cat">Payroll &amp; Platform</div>
                <div class="comp-row">
                    <div>Payroll &amp; attendance</div>
                    <div><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>
                <div class="comp-row">
                    <div>API access &amp; webhooks</div>
                    <div><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-dash-circle no"></i></div>
                    <div class="hl"><i class="bi bi-check-circle-fill yes"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>
                <div class="comp-row">
                    <div>SSO / SAML &amp; mandatory 2FA</div>
                    <div><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-dash-circle no"></i></div>
                    <div class="hl"><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>
                <div class="comp-row">
                    <div>On-premise deployment</div>
                    <div><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-dash-circle no"></i></div>
                    <div class="hl"><i class="bi bi-dash-circle no"></i></div><div><i class="bi bi-check-circle-fill yes"></i></div>
                </div>

                <div class="comp-cat">Support</div>
                <div class="comp-row">
                    <div>Support response target</div>
                    <div>3 business days</div><div>1 business day</div><div class="hl">4 hours</div><div>4 hours + named lead</div>
                </div>
                <div class="comp-row">
                    <div>Implementation services</div>
                    <div>Guided</div><div>Assisted</div><div class="hl">Assisted</div><div>Managed</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ WHAT'S ALWAYS INCLUDED ════════════════ -->
    <section class="fk-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <div class="section-label">Never An Add-on</div>
                    <h2 class="mt-2 mb-3" style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;">
                        Included on the free plan too
                    </h2>
                    <p class="mb-0" style="color:var(--ink-muted);line-height:1.75;">
                        Some things are too important to gate. Every Flikma account &mdash; including a free
                        Starter account &mdash; includes the following, with no upgrade required.
                    </p>
                </div>
                <div class="col-lg-7">
                    <div class="row g-3 reveal">
                        <div class="col-md-6">
                            <div class="d-flex gap-3 p-3 h-100" style="background:var(--ink-panel);border-radius:16px;">
                                <i class="bi bi-shield-check" style="font-size:1.5rem;color:var(--emerald);"></i>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#fff;font-size:.95rem;">Full ZATCA Phase 2</h6>
                                    <p class="mb-0" style="font-size:.8rem;color:rgba(255,255,255,.5);line-height:1.6;">
                                        UBL 2.1 signing, QR stamping, clearance and reporting. On every plan.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 p-3 h-100" style="background:var(--ink-panel);border-radius:16px;">
                                <i class="bi bi-translate" style="font-size:1.5rem;color:var(--emerald);"></i>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#fff;font-size:.95rem;">Arabic &amp; English</h6>
                                    <p class="mb-0" style="font-size:.8rem;color:rgba(255,255,255,.5);line-height:1.6;">
                                        Bilingual interface, invoices and bills of lading. Not an edition.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 p-3 h-100" style="background:var(--ink-panel);border-radius:16px;">
                                <i class="bi bi-arrow-repeat" style="font-size:1.5rem;color:var(--emerald);"></i>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#fff;font-size:.95rem;">Backups &amp; Updates</h6>
                                    <p class="mb-0" style="font-size:.8rem;color:rgba(255,255,255,.5);line-height:1.6;">
                                        Nightly backups, monitoring and product updates at no extra cost.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 p-3 h-100" style="background:var(--ink-panel);border-radius:16px;">
                                <i class="bi bi-chat-square-text" style="font-size:1.5rem;color:var(--emerald);"></i>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#fff;font-size:.95rem;">Support</h6>
                                    <p class="mb-0" style="font-size:.8rem;color:rgba(255,255,255,.5);line-height:1.6;">
                                        You are never left without an answer, whatever plan you are on.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ FAQ ════════════════ -->
    <section class="fk-section bg-white">
        <div class="container" x-data="{ open: null }">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">FAQ</div>
                <h2>Pricing questions</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 0 ? null : 0">
                            <span>Is the free plan really free, or is it a trial?</span>
                            <i class="bi" :class="open === 0 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 0 && 'open'">
                            <p>It is free, permanently, for a single-company operation with up to two users.
                               It is not a time-limited trial. You get ZATCA Phase 2, the core modules and 50 AI
                               document scans a month. Plenty of small forwarders run on it permanently.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 1 ? null : 1">
                            <span>Why is pricing per company rather than per user?</span>
                            <i class="bi" :class="open === 1 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 1 && 'open'">
                            <p>Because software that charges per head discourages you from giving everyone an
                               account &mdash; and a forwarding office that runs on shared logins has no audit
                               trail at all. Unlimited users is both fairer and safer.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 2 ? null : 2">
                            <span>What happens if we exceed our AI scan allowance?</span>
                            <i class="bi" :class="open === 2 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 2 && 'open'">
                            <p>We will tell you before you hit the limit, and you can add scans to your plan at
                               any time. We will not silently charge you an overage fee, and you will never have
                               a document rejected because a counter ran out.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 3 ? null : 3">
                            <span>Can we pay in USD or through a Bahrain / UAE entity?</span>
                            <i class="bi" :class="open === 3 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 3 && 'open'">
                            <p>Yes. We invoice in SAR or USD, and we can contract through a Saudi, Bahraini or
                               Emirati entity. Multi-entity customers with several VAT registrations should
                               talk to us &mdash; consolidated billing is usually simpler for them.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 4 ? null : 4">
                            <span>What is included in the ZATCA onboarding?</span>
                            <i class="bi" :class="open === 4 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 4 && 'open'">
                            <p>The guided path is free on every plan and covered by our documentation. The
                               hands-on service &mdash; where we generate the CSR, run the simulation, activate
                               your production CSID and verify your first clearance with you &mdash; is part of
                               our Assisted implementation package. See
                               <a href="{{ url('/services') }}" class="text-emerald fw-semibold">Services</a>.</p>
                        </div>
                    </div>

                    <div class="faq-item reveal">
                        <button class="faq-q" @click="open = open === 5 ? null : 5">
                            <span>Can we change plans later?</span>
                            <i class="bi" :class="open === 5 ? 'bi-dash-circle text-emerald' : 'bi-plus-circle text-ink-ghost'"></i>
                        </button>
                        <div class="faq-answer" :class="open === 5 && 'open'">
                            <p>Yes, at any time, in both directions. Upgrades take effect immediately. Downgrades
                               take effect at the end of your current billing period, and we will never delete
                               your data because a plan changed &mdash; it stays, read-only, until you return.</p>
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
                <h2 class="mb-3">Not sure which plan fits?</h2>
                <p class="mb-4" style="font-size:1.02rem;">
                    Tell us your entity count, your monthly shipment volume and how many people would need an
                    account. We will tell you the smallest plan that works &mdash; even if that is the free one.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ url('/contact') }}" class="btn-hero-primary">Talk to Sales <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ route('register') }}" class="btn-outline-light-fk">Start Free Trial</a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection
