@extends('website.layout')

@section('title', 'Services — Flikma Logistics ERP for Saudi Arabia, Bahrain & Dubai')
@section('meta_description', 'Flikma services: implementation, data migration, training, custom development and ongoing support for freight forwarders and logistics companies in Saudi Arabia, Bahrain and Dubai.')
@section('meta_keywords', 'logistics software implementation Saudi Arabia, freight forwarding ERP support Bahrain, logistics software training Dubai, ZATCA e-invoicing setup, logistics ERP data migration')

@section('content')

    <section class="fk-gradient-bg pt-5 pb-4">
        <div class="container pt-5 pb-4 text-center">
            <span class="fk-badge mb-3"><i class="bi bi-headset"></i> Services</span>
            <h1 class="display-5 mb-3">We get you live, then we stay with you</h1>
            <p class="fs-5 text-fk-muted mx-auto" style="max-width:640px;">Software alone doesn't change how a team works. Our services team makes sure Flikma actually fits your operation from day one.</p>
        </div>
    </section>

    <section class="fk-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="fk-card h-100 p-4">
                        <div class="fk-icon-box mb-3"><i class="bi bi-rocket-takeoff"></i></div>
                        <h5>Implementation & Onboarding</h5>
                        <p class="text-fk-muted small">A dedicated implementation specialist configures your chart of accounts, masters and workflows so you're operating correctly from week one.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="fk-card h-100 p-4">
                        <div class="fk-icon-box mb-3"><i class="bi bi-arrow-left-right"></i></div>
                        <h5>Data Migration</h5>
                        <p class="text-fk-muted small">We import your existing customers, suppliers, open invoices and opening balances, so nothing is left behind on your old system.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="fk-card h-100 p-4">
                        <div class="fk-icon-box mb-3"><i class="bi bi-mortarboard"></i></div>
                        <h5>Training</h5>
                        <p class="text-fk-muted small">Role-based training for operations, sales and finance teams — live sessions plus recorded walkthroughs for new hires.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="fk-card h-100 p-4">
                        <div class="fk-icon-box mb-3"><i class="bi bi-sliders"></i></div>
                        <h5>Custom Configuration</h5>
                        <p class="text-fk-muted small">Custom report layouts, print templates, approval workflows and column sets tailored to how your team already works.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="fk-card h-100 p-4">
                        <div class="fk-icon-box mb-3"><i class="bi bi-person-check"></i></div>
                        <h5>Dedicated Account Manager</h5>
                        <p class="text-fk-muted small">A single point of contact who knows your setup, available for quarterly reviews and escalations.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="fk-card h-100 p-4">
                        <div class="fk-icon-box mb-3"><i class="bi bi-life-preserver"></i></div>
                        <h5>Priority Support</h5>
                        <p class="text-fk-muted small">Chat, email and phone support with guaranteed response times on Professional and Enterprise plans.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Process --}}
    <section class="fk-section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <div class="fk-eyebrow mb-2">Our process</div>
                <h2 class="display-6">From kickoff to go-live in four steps</h2>
            </div>
            <div class="row g-4">
                @foreach([
                    ['step' => '01', 'title' => 'Discovery', 'desc' => 'We map your current workflows, chart of accounts and document formats.'],
                    ['step' => '02', 'title' => 'Configuration', 'desc' => 'Your masters, approval flows and print templates are set up in a staging environment.'],
                    ['step' => '03', 'title' => 'Migration & Training', 'desc' => 'Historical data is imported and your team is trained role by role.'],
                    ['step' => '04', 'title' => 'Go-Live & Support', 'desc' => 'We stay hands-on through your first month-end close and beyond.'],
                ] as $s)
                    <div class="col-md-3">
                        <div class="fw-bold text-primary fs-2 opacity-50 mb-2">{{ $s['step'] }}</div>
                        <h5>{{ $s['title'] }}</h5>
                        <p class="text-fk-muted small">{{ $s['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="fk-cta-band text-white p-5 text-center">
            <h2 class="display-6 text-white mb-3">Talk to our implementation team</h2>
            <p class="text-white-50 mb-4">Tell us about your operation and we'll put together an onboarding plan.</p>
            <a href="{{ route('website.contact') }}" class="btn btn-light fw-bold px-4 py-2">Contact Us</a>
        </div>
    </section>

@endsection
