@extends('website.layout')

@section('title', 'Products — Flikma Logistics ERP for Saudi Arabia, Bahrain & UAE')
@section('meta_description', 'Flikma products: Operations, Finance, Compliance and Payroll for freight forwarders and 3PLs in Saudi Arabia, Bahrain and the UAE — one shared platform, switch on only what you need.')
@section('meta_keywords', 'logistics ERP modules Saudi Arabia, freight operations software Bahrain, finance module logistics UAE, ZATCA compliance software, payroll software logistics GCC')

@section('content')

    <style>
        .prod-panel { border: 1.5px solid var(--line); border-radius: 20px; background: #fff; padding: 2.5rem; height: 100%; box-shadow: 0 18px 44px rgba(10, 15, 30, .06); }
        .prod-list { list-style: none; padding: 0; margin: 0; }
        .prod-list li { display: flex; align-items: flex-start; gap: .65rem; font-size: .9rem; color: var(--ink-muted); line-height: 1.6; margin-bottom: .6rem; }
        .prod-list i { color: var(--emerald); margin-top: 4px; flex-shrink: 0; }
        .ai-row { border: 1.5px solid rgba(0, 201, 123, .28); border-radius: 18px; background: linear-gradient(135deg, var(--emerald-soft) 0%, #fff 70%); padding: 1.75rem; height: 100%; }
    </style>

    <!-- ════════════════ HERO ════════════════ -->
    <header class="page-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="hero-pill mb-3"><i class="bi bi-boxes"></i> Products</div>
                    <h1 class="mb-3">One platform. Four connected products.</h1>
                    <p class="mb-0 mx-auto" style="max-width:620px;">
                        Switch on what you need today and add more as you grow. Every product runs on the
                        same customers, the same jobs and the same chart of accounts &mdash; no re-keying,
                        no exports, no reconciliation.
                    </p>
                </div>
            </div>
        </div>
    </header>

    @php
        $products = [
            [
                'tag' => 'Product 01',
                'name' => 'Flikma Operations',
                'icon' => 'bi-truck',
                'color' => 'emerald',
                'desc' => 'The command centre for your forwarding workflow — from first enquiry through the bill of lading to the final delivery confirmation.',
                'items' => [
                    'Enquiries, quotations and revision history',
                    'Job management with milestone tracking',
                    'Airway Bill, Seaway Bill and Waybill',
                    'Container and package tracking',
                    'Carrier rates and cost breakdown per shipment',
                ],
                'reverse' => false,
            ],
            [
                'tag' => 'Product 02',
                'name' => 'Flikma Finance',
                'icon' => 'bi-cash-stack',
                'color' => 'blue',
                'desc' => 'A complete double-entry accounting engine purpose-built for freight revenue and cost structures, not a generic ledger with logistics bolted on.',
                'items' => [
                    'Customer and supplier invoicing',
                    'Payments, collections and credit notes',
                    'Chart of accounts and journal vouchers',
                    'Trial balance, P&L and balance sheet',
                    'Per-shipment and per-customer profitability',
                ],
                'reverse' => true,
            ],
            [
                'tag' => 'Product 03',
                'name' => 'Flikma Compliance',
                'icon' => 'bi-shield-check',
                'color' => 'violet',
                'desc' => 'Stay ahead of the tax authority without adding headcount to your finance team. Built for ZATCA Phase 2 from day one.',
                'items' => [
                    'ZATCA Phase 2 e-invoicing (Fatoora)',
                    'Simplified and standard tax invoices',
                    'Input and output VAT reporting',
                    'Multi-currency and FX handling',
                    'Full audit trail on every document',
                ],
                'reverse' => false,
            ],
            [
                'tag' => 'Product 04',
                'name' => 'Flikma Payroll',
                'icon' => 'bi-people-fill',
                'color' => 'amber',
                'desc' => 'Keep your operations and admin staff paid accurately and on time, with payroll posting straight into the same ledger.',
                'items' => [
                    'Attendance and overtime tracking',
                    'Basic and monthly salary runs',
                    'Employee loans and deductions',
                    'WPS and bank file support',
                    'Payroll postings straight to the GL',
                ],
                'reverse' => true,
            ],
        ];

        $accents = [
            'emerald' => ['bg' => 'var(--emerald)', 'fg' => 'var(--emerald-dim)', 'soft' => 'var(--emerald-soft)'],
            'blue'    => ['bg' => 'var(--blue)', 'fg' => 'var(--blue)', 'soft' => 'rgba(56,132,255,.10)'],
            'violet'  => ['bg' => 'var(--violet)', 'fg' => 'var(--violet)', 'soft' => 'rgba(139,92,246,.10)'],
            'amber'   => ['bg' => 'var(--gold)', 'fg' => '#c98a06', 'soft' => 'rgba(244,185,66,.14)'],
        ];
    @endphp

    <!-- ════════════════ PRODUCTS ════════════════ -->
    <section class="fk-section" style="padding-top:1rem;">
        <div class="container">
            <div class="row g-4">
                @foreach ($products as $p)
                    @php $a = $accents[$p['color']]; @endphp
                    <div class="col-md-6">
                        <div class="prod-panel h-100 reveal">
                            <div style="height:4px;width:52px;border-radius:99px;background:{{ $a['bg'] }};margin-bottom:1.5rem;"></div>

                            <div style="font-size:.7rem;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:{{ $a['fg'] }};">{{ $p['tag'] }}</div>

                            <div class="d-flex align-items-center gap-3 my-3">
                                <span class="feat-icon-box" style="background:{{ $a['soft'] }};color:{{ $a['bg'] }};flex-shrink:0;">
                                    <i class="bi {{ $p['icon'] }}"></i>
                                </span>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;line-height:1.15;">{{ $p['name'] }}</h2>
                            </div>

                            <p class="mb-4" style="color:var(--ink-muted);font-size:.93rem;line-height:1.75;">{{ $p['desc'] }}</p>

                            <ul class="prod-list mb-4">
                                @foreach ($p['items'] as $item)
                                    <li><i class="bi bi-check-circle-fill"></i><span>{{ $item }}</span></li>
                                @endforeach
                            </ul>

                            <a href="{{ url('/features') }}" class="btn-outline-light-fk">
                                Explore features <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ════════════════ AI LAYER ════════════════ -->
    <section class="fk-section" style="background:var(--surface);">
        <div class="container">
            <div class="section-head text-center mb-5 reveal">
                <div class="section-label">Included In Every Product Set</div>
                <h2>The AI layer that sits on top</h2>
                <p class="text-ink-muted mx-auto mb-0" style="max-width:580px;">
                    These are not separate add-ons. They read the documents your team already handles and
                    write the results back into Operations and Finance.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="ai-row reveal">
                        <span class="feat-icon-box mb-3" style="background:var(--emerald);color:var(--ink);">
                            <i class="bi bi-file-earmark-text"></i>
                        </span>
                        <h3 class="fw-bold mb-2" style="font-size:1.1rem;">AI document scanning</h3>
                        <p class="mb-0" style="color:var(--ink-muted);font-size:.9rem;line-height:1.7;">
                            Photograph or upload a bill of lading, delivery order, airway bill or POD and Flikma
                            extracts the carrier, reference, dates, ports and totals, then proposes the matching
                            job record. Your team confirms instead of retyping.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ai-row reveal">
                        <span class="feat-icon-box mb-3" style="background:var(--violet);color:#fff;">
                            <i class="bi bi-receipt"></i>
                        </span>
                        <h3 class="fw-bold mb-2" style="font-size:1.1rem;">AI expenses</h3>
                        <p class="mb-0" style="color:var(--ink-muted);font-size:.9rem;line-height:1.7;">
                            Submit a driver expense or supplier receipt and Flikma categorises it, maps it to the
                            right shipment cost line, checks it against your approval rules and posts the
                            journal &mdash; including the ZATCA handling for the resulting invoice.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ ONE SUBSCRIPTION ════════════════ -->
    <section class="fk-section bg-ink">
        <div class="container text-center reveal">
            <div class="hero-pill mx-auto mb-3" style="background:rgba(255,255,255,.08);color:#fff;"><i class="bi bi-layers"></i> One Subscription</div>
            <h2 class="mb-3" style="color:#fff;font-size:clamp(1.7rem,3.4vw,2.6rem);font-weight:800;line-height:1.1;">
                All four products. No module tax.
            </h2>
            <p class="mx-auto mb-4" style="color:rgba(255,255,255,.66);max-width:560px;font-size:1rem;line-height:1.75;">
                Pricing is based on your team size, not on how many modules you switch on. Every plan includes
                ZATCA Phase 2, the AI layer, Arabic and English, and unlimited customers and carriers.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="{{ url('/pricing') }}" class="btn-hero-primary">View pricing <i class="bi bi-arrow-right ms-1"></i></a>
                <a href="{{ url('/contact') }}" class="btn-hero-outline">Book a demo</a>
            </div>
        </div>
    </section>

@endsection
