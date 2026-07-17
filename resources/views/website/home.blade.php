<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Flikma - Logistics ERP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="css/website/style.css" rel="stylesheet">

    <link href="css/website/responsive.css" rel="stylesheet">



</head>

<body>


<!-- ==========================
Navbar
=========================== -->

@include('website.partials.nav')


<!-- =====================
Hero
====================== -->

<section class="hero-section">

    <div class="container">

        <div class="row align-items-center gy-5">

            <div class="col-lg-6">

                <div class="hero-content">


<span class="hero-badge">

Modern Cloud ERP

</span>


                    <h1>

                        The Complete Logistics &
                        Freight Management Platform

                    </h1>

                    <p>

                        Manage Freight Forwarding,
                        Transportation,
                        Custom Clearance,
                        Billing,
                        Bill of Lading,
                        Payroll
                        and ZATCA e-Invoicing
                        from one powerful system.

                    </p>


                    <div class="hero-buttons">

                        <a href="{{ route('website.contact') }}"
                           class="btn btn-primary btn-lg rounded-pill">

                            Request Demo

                        </a>

                        <a href="#"
                           class="btn btn-outline-dark btn-lg rounded-pill">

                            Watch Video

                        </a>

                    </div>



                    <div class="hero-features mt-5">

                        <div class="row">

                            <div class="col-6">

                                <div class="feature">

                                    <i class="bi bi-check-circle-fill"></i>

                                    <span>

Cloud Based

</span>

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="feature">

                                    <i class="bi bi-check-circle-fill"></i>

                                    <span>

Mobile Friendly

</span>

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="feature">

                                    <i class="bi bi-check-circle-fill"></i>

                                    <span>

Saudi ZATCA Ready

</span>

                                </div>

                            </div>

                            <div class="col-6">

                                <div class="feature">

                                    <i class="bi bi-check-circle-fill"></i>

                                    <span>

Multi Company

</span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>




            <div class="col-lg-6">

                <div class="hero-images">



                    <div class="hero-main-image">

                        <img src="img/hero/dashboard.svg"
                             class="img-fluid">

                    </div>



                    <div class="hero-image hero-img-one">

                        <img src="img/hero/ship.svg"
                             class="img-fluid">

                    </div>



                    <div class="hero-image hero-img-two">

                        <img src="img/hero/truck.svg"
                             class="img-fluid">

                    </div>



                    <div class="floating-card">

                        <div class="icon">

                            <i class="bi bi-patch-check-fill"></i>

                        </div>

                        <div>

                            <h5>

                                ZATCA Phase 2 Ready

                            </h5>

                            <p>

                                Electronic Invoice Integration

                            </p>

                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>

</section>



<!-- ZATCA Insight Section -->

<section class="zatca-insight-section py-5">

    <div class="container">

        <div class="text-center mb-5">

            <span class="section-tag">
                ZATCA PHASE 2
            </span>

            <h2 class="section-title mt-3">
                E-Invoicing Compliance Isn't Optional Anymore
            </h2>

            <p class="section-desc mx-auto">
                Saudi Arabia's ZATCA e-invoicing regulation is being rolled out to businesses in waves.
                Here's what every freight forwarder and logistics company should know before their
                integration date arrives.
            </p>

        </div>

        <div class="row g-4">

            <div class="col-lg-3 col-md-6">

                <div class="why-card">

                    <i class="bi bi-calendar2-check"></i>

                    <h5>Phased Rollout</h5>

                    <p>
                        ZATCA notifies each taxpayer group of its Phase 2 (Integration Phase)
                        date at least six months in advance, so businesses can plan ahead.
                    </p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="why-card">

                    <i class="bi bi-link-45deg"></i>

                    <h5>Direct System Integration</h5>

                    <p>
                        Invoicing systems must integrate with ZATCA's platform to generate
                        compliant XML invoices carrying a QR code and digital signature.
                    </p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="why-card">

                    <i class="bi bi-clock-history"></i>

                    <h5>Strict Reporting Windows</h5>

                    <p>
                        Simplified invoices must be reported within 24 hours of issuance, while
                        standard tax invoices require clearance before reaching the buyer.
                    </p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="why-card">

                    <i class="bi bi-shield-exclamation"></i>

                    <h5>Cost of Non-Compliance</h5>

                    <p>
                        Missing an integration wave or generating non-compliant invoices puts
                        your VAT reporting — and your operations — at risk.
                    </p>

                </div>

            </div>

        </div>

        <div class="text-center mt-5">

            <p class="section-desc mx-auto mb-4">
                Flikma generates ZATCA-compliant invoices automatically — XML, QR code, digital
                signature and audit trail included — so your team stays focused on freight,
                not paperwork.
            </p>

            <a href="{{ url('/register') }}" class="btn btn-primary rounded-pill px-4 py-3 fw-bold me-2">
                Start Free Trial
            </a>

            <a href="{{ route('website.contact') }}" class="btn btn-outline-dark rounded-pill px-4 py-3 fw-bold">
                Talk to Us About ZATCA
            </a>

        </div>

    </div>

</section>



<!-- Statistics Section Starts in index-p2.html -->
<!-- =========================================
Statistics
========================================= -->

<section class="workflow-section py-5">

    <div class="container">

        <div class="text-center mb-5">

            <span class="section-tag">
                HOW IT WORKS
            </span>

            <h2 class="section-title mt-3">
                From Enquiry To Payment — One Connected Workflow
            </h2>

            <p class="section-desc mx-auto">
                Every quotation, job, invoice and payment in Flikma is linked to the record before it,
                so your team spends less time re-entering data and more time moving cargo.
            </p>

        </div>

        <div class="row g-4">

            <div class="col-lg col-md-6">

                <div class="workflow-step">

                    <div class="workflow-step-number">1</div>

                    <h5>Enquiry & Quotation</h5>

                    <p>Capture customer enquiries and convert them into itemized quotations in a few clicks.</p>

                </div>

            </div>

            <div class="col-lg col-md-6">

                <div class="workflow-step">

                    <div class="workflow-step-number">2</div>

                    <h5>Job Creation</h5>

                    <p>Approved quotations become live jobs with costing, documents and status tracking.</p>

                </div>

            </div>

            <div class="col-lg col-md-6">

                <div class="workflow-step">

                    <div class="workflow-step-number">3</div>

                    <h5>Invoicing</h5>

                    <p>Generate customer and supplier invoices directly from job data — no duplicate entry.</p>

                </div>

            </div>

            <div class="col-lg col-md-6">

                <div class="workflow-step">

                    <div class="workflow-step-number">4</div>

                    <h5>Payments & Collections</h5>

                    <p>Record payments and collections against invoices, with automatic ledger posting.</p>

                </div>

            </div>

            <div class="col-lg col-md-6">

                <div class="workflow-step">

                    <div class="workflow-step-number">5</div>

                    <h5>Reports</h5>

                    <p>Trial balance, ageing, job profitability and more — always in sync with your operational data.</p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
Solutions
========================================= -->

<section class="solutions-section py-5">

    <div class="container">

        <div class="text-center mb-5">

            <span class="section-tag">
                OUR SOLUTIONS
            </span>

            <h2 class="section-title mt-3">
                One Platform For Every Part Of Your Logistics Business
            </h2>

            <p class="section-desc">
                From the first enquiry to the final report &mdash; six connected
                modules, not six different tools.
            </p>

        </div>

        <div class="row g-4">

            <div class="col-lg-4 col-md-6">

                <div class="solution-card h-100">

                    <div class="icon">
                        <i class="bi bi-cart"></i>
                    </div>

                    <h4>Sales & Job Management</h4>

                    <p>
                        Capture enquiries, price them as quotations, and convert
                        approved quotes straight into operational jobs &mdash;
                        nothing gets re-typed between steps.
                    </p>

                    <ul>
                        <li>Enquiry & Quotation builder</li>
                        <li>One-click convert to Job</li>
                        <li>Full cargo & shipment tracking</li>
                    </ul>

                    <a href="{{ route('website.documentation') }}#sales" class="read-more">
                        Learn More
                        <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

            </div>

            <div class="col-lg-4 col-md-6">

                <div class="solution-card h-100">

                    <div class="icon bg-success">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <h4>Bill of Lading</h4>

                    <p>
                        Generate Airway Bills, Seaway Bills and Waybills directly
                        from a confirmed job, ready to print and share as proof
                        of shipment.
                    </p>

                    <ul>
                        <li>Airway, Seaway & Waybill documents</li>
                        <li>Auto-filled from job data</li>
                        <li>Print-ready customer layout</li>
                    </ul>

                    <a href="{{ route('website.documentation') }}#bl" class="read-more">
                        Learn More
                        <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

            </div>

            <div class="col-lg-4 col-md-6">

                <div class="solution-card h-100">

                    <div class="icon bg-danger">
                        <i class="bi bi-receipt"></i>
                    </div>

                    <h4>Invoicing & Billing</h4>

                    <p>
                        Raise proforma, customer and supplier invoices straight
                        from job data, with credit notes and a full chart of
                        accounts behind every transaction.
                    </p>

                    <ul>
                        <li>Proforma, Customer & Supplier invoices</li>
                        <li>Credit notes with full audit trail</li>
                        <li>Automatic ledger posting</li>
                    </ul>

                    <a href="{{ route('website.documentation') }}#finances" class="read-more">
                        Learn More
                        <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

            </div>

            <div class="col-lg-4 col-md-6">

                <div class="solution-card h-100">

                    <div class="icon bg-warning">
                        <i class="bi bi-cash-coin"></i>
                    </div>

                    <h4>Payments & Collections</h4>

                    <p>
                        Settle supplier invoices and record customer receipts
                        &mdash; in full or in part &mdash; with your bank and
                        cash balances updated the moment you save.
                    </p>

                    <ul>
                        <li>Full or partial payments</li>
                        <li>Multi-invoice settlement in one go</li>
                        <li>Bank & cash balances always current</li>
                    </ul>

                    <a href="{{ route('website.documentation') }}#transactions" class="read-more">
                        Learn More
                        <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

            </div>

            <div class="col-lg-4 col-md-6">

                <div class="solution-card h-100">

                    <div class="icon bg-info">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </div>

                    <h4>ZATCA Phase 2 Compliance</h4>

                    <p>
                        Every customer invoice is generated with the QR code,
                        digital signature and XML data Saudi e-invoicing
                        regulations require &mdash; built in automatically.
                    </p>

                    <ul>
                        <li>XML generation & digital signature</li>
                        <li>QR code on every invoice</li>
                        <li>One-time branch registration</li>
                    </ul>

                    <a href="{{ route('website.documentation') }}#settings-zatca" class="read-more">
                        Learn More
                        <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

            </div>

            <div class="col-lg-4 col-md-6">

                <div class="solution-card h-100">

                    <div class="icon bg-dark">
                        <i class="bi bi-person-badge"></i>
                    </div>

                    <h4>Payroll</h4>

                    <p>
                        Track attendance, calculate monthly salaries, and manage
                        employee loans, all from one connected payroll module.
                    </p>

                    <ul>
                        <li>Daily attendance register</li>
                        <li>Automated monthly salary run</li>
                        <li>Employee loan & installment tracking</li>
                    </ul>

                    <a href="{{ route('website.documentation') }}#payroll" class="read-more">
                        Learn More
                        <i class="bi bi-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =========================================
Dashboard Showcase
========================================= -->

<section class="dashboard-section py-5">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <img src="img/dashboard/dashboard-main.svg"
                     class="img-fluid rounded-4 shadow-lg">

            </div>

            <div class="col-lg-6">

<span class="section-tag">

POWERFUL ERP

</span>

                <h2 class="mt-3">

                    See Your Whole Operation At A Glance

                </h2>

                <p>

                    Jobs, invoices and finances in one unified dashboard &mdash; no switching between tools.

                </p>

                <div class="feature-list mt-5">

                    <div class="feature-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <div>

                            <h5>Live Dashboard</h5>

                            <p>Real-time KPIs and Analytics</p>

                        </div>

                    </div>

                    <div class="feature-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <div>

                            <h5>Shipment Tracking</h5>

                            <p>Track Every Shipment Instantly</p>

                        </div>

                    </div>

                    <div class="feature-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <div>

                            <h5>Billing Automation</h5>

                            <p>Create invoices in seconds.</p>

                        </div>

                    </div>

                    <div class="feature-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <div>

                            <h5>Role-Based Access</h5>

                            <p>Department rights control what each user can see and do.</p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ==========================================
Compliance Section
========================================== -->

<section class="compliance-section py-5">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <span class="section-tag">
                    Compliance
                </span>

                <h2 class="section-title mt-3">
                    Built for Saudi Arabia & International Logistics
                </h2>

                <p class="section-desc mt-4">
                    Flikma supports ZATCA Phase 2 electronic invoicing,
                    PDF/A-3 generation, QR Codes, XML validation,
                    digital signatures and complete audit history.
                </p>

                <div class="row mt-5 g-3">

                    <div class="col">
                        <div class="workflow-step">
                            <div class="workflow-step-number" style="width:44px;height:44px;font-size:16px;">1</div>
                            <h5 style="font-size:13.5px;">Invoice Created</h5>
                        </div>
                    </div>

                    <div class="col">
                        <div class="workflow-step">
                            <div class="workflow-step-number" style="width:44px;height:44px;font-size:16px;">2</div>
                            <h5 style="font-size:13.5px;">XML Generated</h5>
                        </div>
                    </div>

                    <div class="col">
                        <div class="workflow-step">
                            <div class="workflow-step-number" style="width:44px;height:44px;font-size:16px;">3</div>
                            <h5 style="font-size:13.5px;">Digitally Signed</h5>
                        </div>
                    </div>

                    <div class="col">
                        <div class="workflow-step">
                            <div class="workflow-step-number" style="width:44px;height:44px;font-size:16px;">4</div>
                            <h5 style="font-size:13.5px;">QR Code Attached</h5>
                        </div>
                    </div>

                    <div class="col">
                        <div class="workflow-step">
                            <div class="workflow-step-number" style="width:44px;height:44px;font-size:16px;">5</div>
                            <h5 style="font-size:13.5px;">Submitted to ZATCA</h5>
                        </div>
                    </div>

                </div>

                <div class="row mt-4">

                    <div class="col-md-6">

                        <div class="compliance-item">

                            <i class="bi bi-check-circle-fill"></i>

                            PDF/A-3 Invoice

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="compliance-item">

                            <i class="bi bi-check-circle-fill"></i>

                            XML Generation

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="compliance-item">

                            <i class="bi bi-check-circle-fill"></i>

                            QR Code

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="compliance-item">

                            <i class="bi bi-check-circle-fill"></i>

                            Digital Signature

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="compliance-item">

                            <i class="bi bi-check-circle-fill"></i>

                            API Integration

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="compliance-item">

                            <i class="bi bi-check-circle-fill"></i>

                            VAT Reports

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-6 text-center">

                <img src="img/compliance/zatca-dashboard.svg"
                     class="img-fluid rounded-4 shadow-lg">

            </div>

        </div>

    </div>

</section>


<!-- ==========================================
Pricing Teaser
========================================== -->

<section class="pricing-teaser-section py-5 bg-light">

    <div class="container">

        <div class="text-center mb-5">

            <span class="section-tag">Pricing</span>

            <h2 class="section-title mt-3">
                Simple Plans That Grow With You
            </h2>

            <p class="section-desc mx-auto">
                No setup fees. No hidden charges. Cancel anytime.
            </p>

        </div>

        <div class="row g-4 justify-content-center">

            <div class="col-lg-4 col-md-6">
                <div class="pricing-teaser-card">
                    <div class="pricing-teaser-name">Starter</div>
                    <div class="pricing-teaser-price">SAR 299<span>/month</span></div>
                    <p>For small forwarders getting off spreadsheets.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="pricing-teaser-card featured">
                    <span class="pricing-teaser-badge">Most Popular</span>
                    <div class="pricing-teaser-name">Modern</div>
                    <div class="pricing-teaser-price">SAR 699<span>/month</span></div>
                    <p>For growing teams that need full financial control.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="pricing-teaser-card">
                    <div class="pricing-teaser-name">Deluxe</div>
                    <div class="pricing-teaser-price">Custom</div>
                    <p>For multi-branch and multi-company operations.</p>
                </div>
            </div>

        </div>

        <div class="text-center mt-4">

            <a href="{{ route('website.pricing') }}" class="btn btn-outline-dark rounded-pill px-4 py-3 fw-bold">
                See Full Plan Comparison
            </a>

        </div>

    </div>

</section>






<!-- ==========================================
CTA
========================================== -->

<section class="cta-section">

    <div class="container">

        <div class="cta-box">

            <h2>

                Get Started In Minutes

            </h2>

            <p>

                Create your account, add your first customer, and send your first
                ZATCA-ready invoice today.

            </p>

            <div class="cta-benefits">

                <div class="cta-benefit">
                    <i class="bi bi-check-circle-fill"></i>
                    No credit card required
                </div>

                <div class="cta-benefit">
                    <i class="bi bi-check-circle-fill"></i>
                    Guided onboarding for your team
                </div>

                <div class="cta-benefit">
                    <i class="bi bi-check-circle-fill"></i>
                    ZATCA-ready from day one
                </div>

            </div>

            <div class="mt-4 d-flex gap-3 justify-content-center flex-wrap">

                <a href="{{ url('/register') }}"
                   class="btn btn-light btn-lg rounded-pill">

                    Create Your Free Account

                </a>

                <a href="{{ route('website.contact') }}"
                   class="btn btn-outline-light btn-lg rounded-pill">

                    Book Free Demo

                </a>

            </div>

        </div>

    </div>

</section>



<!-- ==========================================
Footer
========================================== -->

@include('website.partials.footer')



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/app.js"></script>

</body>

</html>
