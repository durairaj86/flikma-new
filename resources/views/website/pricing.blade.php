<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Simple, transparent Flikma pricing for freight forwarders of every size in Saudi Arabia, Bahrain and Dubai — Starter, Modern and Deluxe plans.">
    <meta name="keywords" content="logistics software pricing Saudi Arabia, freight forwarding ERP cost, logistics software price Bahrain, logistics ERP subscription Dubai, ZATCA invoicing software pricing">

    <title>Pricing - Flikma Logistics ERP for Saudi Arabia, Bahrain & Dubai</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('css/website/style.css') }}" rel="stylesheet">

    <link href="{{ asset('css/website/responsive.css') }}" rel="stylesheet">

    <link href="{{ asset('css/website/pricing.css') }}" rel="stylesheet">

</head>

<body>


<!-- ==========================
Navbar
=========================== -->

@include('website.partials.nav')


<!-- =====================
Page Header
====================== -->

<section class="page-header">

    <div class="container text-center">

        <span class="section-tag">Pricing</span>

        <h1 class="mt-3">

            Simple, Transparent Pricing<br>That Grows With You

        </h1>

        <p class="page-header-desc mx-auto">

            No setup fees. No hidden charges. Cancel anytime. Choose the plan that fits your
            logistics operation today and scale up whenever you're ready.

        </p>

    </div>

</section>


<!-- =====================
Pricing Plans
====================== -->

@php
    $plans = [
        [
            'name' => 'Starter',
            'icon' => 'bi-rocket-takeoff',
            'price' => '299',
            'desc' => 'For small forwarders getting off spreadsheets.',
            'featured' => false,
            'items' => ['Up to 5 users', 'Enquiry, Quotation & Job management', 'Customer & Supplier Invoicing', 'Core financial reports', 'Email support'],
        ],
        [
            'name' => 'Modern',
            'icon' => 'bi-lightning-charge',
            'price' => '699',
            'desc' => 'For growing teams that need full financial control.',
            'featured' => true,
            'items' => ['Up to 25 users', 'Everything in Starter', 'Bill of Lading (Airway/Seaway/Waybill)', 'ZATCA Phase 2 e-invoicing', 'Payroll module', 'Priority chat & phone support'],
        ],
        [
            'name' => 'Deluxe',
            'icon' => 'bi-gem',
            'price' => 'Custom',
            'desc' => 'For multi-branch and multi-company operations.',
            'featured' => false,
            'items' => ['Unlimited users', 'Everything in Modern', 'Multi-company consolidation', 'Custom report & print templates', 'Dedicated account manager', 'Onboarding & data migration included'],
        ],
    ];
@endphp

<section class="pricing-section">

    <div class="container">

        <div class="row g-4 justify-content-center">

            @foreach($plans as $plan)
                <div class="col-lg-4 col-md-6">

                    <div class="plan-card {{ $plan['featured'] ? 'featured' : '' }} h-100">

                        @if($plan['featured'])
                            <span class="plan-badge">Most Popular</span>
                        @endif

                        <div class="plan-icon">
                            <i class="bi {{ $plan['icon'] }}"></i>
                        </div>

                        <div class="plan-name">{{ $plan['name'] }}</div>

                        <div class="plan-price">
                            @if($plan['price'] === 'Custom')
                                Custom
                            @else
                                SAR {{ $plan['price'] }}<span>/month</span>
                            @endif
                        </div>

                        <p class="plan-desc">{{ $plan['desc'] }}</p>

                        <a href="{{ $plan['price'] === 'Custom' ? route('website.contact') : url('/register') }}" class="btn {{ $plan['featured'] ? 'btn-primary' : 'btn-outline-dark' }} rounded-pill w-100 py-3 fw-bold">
                            {{ $plan['price'] === 'Custom' ? 'Contact Sales' : 'Start Free Trial' }}
                        </a>

                        <ul class="plan-features">
                            @foreach($plan['items'] as $item)
                                <li><i class="bi bi-check-circle-fill"></i> {{ $item }}</li>
                            @endforeach
                        </ul>

                    </div>

                </div>
            @endforeach

        </div>

    </div>

</section>


<!-- =====================
Comparison Table
====================== -->

<section class="comparison-section">

    <div class="container">

        <div class="text-center mb-5">

            <span class="section-tag">Compare Plans</span>

            <h2 class="section-title mt-3">See What's Included</h2>

            <p class="section-desc">A closer look at every feature across Starter, Modern and Deluxe.</p>

        </div>

        @php
            $rows = [
                ['Users included', '5', '25', 'Unlimited'],
                ['Enquiry, Quotation & Job management', true, true, true],
                ['Customer & Supplier Invoicing', true, true, true],
                ['Financial reports (Trial Balance, P&L, Balance Sheet)', true, true, true],
                ['Bill of Lading documents (Airway/Seaway/Waybill)', false, true, true],
                ['ZATCA Phase 2 e-invoicing compliance', false, true, true],
                ['Payroll module', false, true, true],
                ['Multi-company consolidation', false, false, true],
                ['Custom report & print templates', false, false, true],
                ['Dedicated account manager', false, false, true],
                ['Data migration & onboarding', false, false, true],
                ['Support', 'Email', 'Priority chat & phone', '24/7 dedicated'],
            ];
        @endphp

        <div class="table-responsive">

            <table class="comparison-table">

                <thead>

                <tr>

                    <th class="text-start">Feature</th>

                    <th>Starter</th>

                    <th class="highlight-col">Modern</th>

                    <th>Deluxe</th>

                </tr>

                </thead>

                <tbody>

                @foreach($rows as $row)
                    <tr>

                        <td class="text-start fw-semibold">{{ $row[0] }}</td>

                        <td>
                            @if(is_bool($row[1]))
                                <i class="bi {{ $row[1] ? 'bi-check-circle-fill text-check' : 'bi-dash text-muted' }}"></i>
                            @else
                                {{ $row[1] }}
                            @endif
                        </td>

                        <td class="highlight-col">
                            @if(is_bool($row[2]))
                                <i class="bi {{ $row[2] ? 'bi-check-circle-fill text-check' : 'bi-dash text-muted' }}"></i>
                            @else
                                {{ $row[2] }}
                            @endif
                        </td>

                        <td>
                            @if(is_bool($row[3]))
                                <i class="bi {{ $row[3] ? 'bi-check-circle-fill text-check' : 'bi-dash text-muted' }}"></i>
                            @else
                                {{ $row[3] }}
                            @endif
                        </td>

                    </tr>
                @endforeach

                </tbody>

            </table>

        </div>

    </div>

</section>


<!-- =====================
FAQ
====================== -->

<section class="faq-section py-5">

    <div class="container">

        <div class="text-center mb-5">

            <span class="section-tag">FAQ</span>

            <h2 class="section-title mt-3">Common Questions</h2>

        </div>

        <div class="row justify-content-center">

            <div class="col-lg-8">

                <div class="accordion" id="pricingFaq">

                    @foreach([
                        ['q' => 'Is there a free trial?', 'a' => 'Yes — every plan starts with a 14-day free trial, no credit card required.'],
                        ['q' => 'Can I switch plans later?', 'a' => 'Yes, you can upgrade or downgrade at any time and we prorate the difference automatically.'],
                        ['q' => 'Do you support multiple currencies?', 'a' => 'Every plan supports multi-currency invoicing, payments and reporting with automatic exchange rate conversion.'],
                        ['q' => 'What does implementation include?', 'a' => 'Starter and Modern plans include guided self-onboarding; Deluxe includes a dedicated implementation specialist and data migration.'],
                        ['q' => 'Is my data secure?', 'a' => 'Each company\'s data is fully isolated, encrypted in transit, and backed up daily.'],
                    ] as $i => $faq)
                        <div class="accordion-item">

                            <h2 class="accordion-header">

                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">

                                    {{ $faq['q'] }}

                                </button>

                            </h2>

                            <div id="faq{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">

                                <div class="accordion-body">{{ $faq['a'] }}</div>

                            </div>

                        </div>
                    @endforeach

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================
CTA
====================== -->

<section class="cta-section">

    <div class="container">

        <div class="cta-box">

            <h2>

                Start Your 14-Day Free Trial Today

            </h2>

            <p>

                No credit card required. Cancel anytime.

            </p>

            <a href="{{ url('/register') }}"
               class="btn btn-light btn-lg rounded-pill">

                Get Started Free

            </a>

        </div>

    </div>

</section>


<!-- ==========================================
Footer
========================================== -->

@include('website.partials.footer')



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('js/app.js') }}"></script>

</body>

</html>
