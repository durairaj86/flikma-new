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

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top py-3">

    <div class="container">

        <a class="navbar-brand fw-bold fs-3" href="{{ route('website.home') }}">

            <img src="img/logos/logo.png" {{--height="42"--}}>

        </a>

        <button class="navbar-toggler"
                data-bs-toggle="collapse"
                data-bs-target="#navbar">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse"
             id="navbar">

            <ul class="navbar-nav mx-auto">

                <li class="nav-item">

                    <a class="nav-link active"
                       href="{{ route('website.home') }}">Home</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                       href="{{ route('website.home') }}#why-flikma">Why Flikma</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                       href="#">Solutions</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                       href="#">Industries</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                       href="{{ route('website.pricing') }}">Pricing</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                       href="#">Resources</a>

                </li>

                <li class="nav-item">

                    <a class="nav-link"
                       href="{{ route('website.contact') }}">Contact</a>

                </li>

            </ul>


            <div class="d-flex gap-3">

                <a href="{{ url('/login') }}" class="btn btn-outline-dark rounded-pill px-4">

                    Login

                </a>

                <a href="{{ route('website.contact') }}" class="btn btn-primary rounded-pill px-4">

                    Book Demo

                </a>

            </div>

        </div>

    </div>

</nav>


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
                        Customer Portal,
                        Vendor Portal
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

                Everything Required To Run A Modern Logistics Business

            </h2>

            <p class="section-desc">

                One integrated cloud platform covering every operational process.

            </p>

        </div>

        <div class="row g-4 justify-content-center">

            <div class="col-lg-6">

                <div class="solution-card h-100">

                    <div class="icon">

                        <i class="bi bi-airplane-fill"></i>

                    </div>

                    <h4>

                        Freight Forwarding

                    </h4>

                    <p>

                        Manage Air Freight,
                        Sea Freight,
                        Import,
                        Export,
                        Quotation,
                        Job Costing,
                        Shipment Tracking
                        and Documentation.

                    </p>

                    <ul>

                        <li>Air Export</li>

                        <li>Air Import</li>

                        <li>Sea Export</li>

                        <li>Sea Import</li>

                        <li>Consolidation</li>

                        <li>Shipment Tracking</li>

                    </ul>

                    <a href="#" class="read-more">

                        Learn More

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="solution-card h-100">

                    <div class="icon bg-success">

                        <i class="bi bi-truck"></i>

                    </div>

                    <h4>

                        Transportation

                    </h4>

                    <p>

                        Fleet Management,
                        Vehicle Allocation,
                        Trip Planning,
                        Driver Management,
                        Fuel Monitoring,
                        Trip Costing,
                        Delivery Management.

                    </p>

                    <ul>

                        <li>Trip Sheet</li>

                        <li>GPS Tracking</li>

                        <li>Driver Portal</li>

                        <li>Fleet Maintenance</li>

                        <li>Delivery Proof</li>

                        <li>Billing</li>

                    </ul>

                    <a href="#" class="read-more">

                        Learn More

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <div class="row mt-4 g-4">

            <div class="col-lg-4">

                <div class="solution-card">

                    <div class="icon bg-danger">

                        <i class="bi bi-receipt"></i>

                    </div>

                    <h4>

                        Billing & Accounting

                    </h4>

                    <p>

                        Customer Billing,
                        Vendor Bills,
                        Credit Notes,
                        Debit Notes,
                        Journal,
                        Ledger,
                        Financial Reports.

                    </p>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="solution-card">

                    <div class="icon bg-info">

                        <i class="bi bi-file-earmark-check-fill"></i>

                    </div>

                    <h4>

                        ZATCA Phase 2

                    </h4>

                    <p>

                        Saudi Arabia Electronic Invoice
                        Generation,
                        Digital Signature,
                        XML,
                        PDF/A-3,
                        QR Code,
                        API Integration.

                    </p>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="solution-card">

                    <div class="icon bg-dark">

                        <i class="bi bi-phone-fill"></i>

                    </div>

                    <h4>

                        Customer Portal

                    </h4>

                    <p>

                        Customers can Track Shipments,
                        Invoices,
                        Statements,
                        Reports,
                        Documents
                        and Payments.

                    </p>

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

                    One Dashboard For Every Department

                </h2>

                <p>

                    Monitor your entire logistics operation from one unified dashboard.

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

                            <h5>Customer Portal</h5>

                            <p>Self-service access for customers.</p>

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

                <div class="row mt-5">

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
Why Flikma
========================================== -->

<section class="why-section py-5 bg-light" id="why-flikma">

    <div class="container">

        <div class="row align-items-center g-5 mb-5">

            <div class="col-lg-6">

                <span class="section-tag">
                    WHY FLIKMA
                </span>

                <h2 class="section-title mt-3">
                    Purpose-Built For Freight Forwarders, Not Generic Accounting Software
                </h2>

                <p class="section-desc mt-4">
                    Flikma was designed specifically around how freight and logistics
                    businesses actually operate — enquiries, quotations, jobs and
                    ZATCA-compliant billing in one connected system, instead of stitching
                    together spreadsheets, a generic accounting tool and manual invoice generation.
                </p>

                <div class="row mt-4">

                    <div class="col-md-6">
                        <div class="compliance-item">
                            <i class="bi bi-check-circle-fill"></i>
                            Built for Freight & Transportation
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="compliance-item">
                            <i class="bi bi-check-circle-fill"></i>
                            ZATCA Phase 2 Included
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="compliance-item">
                            <i class="bi bi-check-circle-fill"></i>
                            Multi-Company Ready
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="compliance-item">
                            <i class="bi bi-check-circle-fill"></i>
                            Full Audit Trail
                        </div>
                    </div>

                </div>

                <div class="mt-5">

                    <a href="{{ url('/register') }}" class="btn btn-primary btn-lg rounded-pill">
                        Create Your Free Account
                    </a>

                </div>

            </div>

            <div class="col-lg-6 text-center">

                <img src="img/why/why-flikma.svg"
                     class="img-fluid rounded-4 shadow-lg">

            </div>

        </div>

        <div class="row g-4">

            <div class="col-lg-3">

                <div class="why-card">

                    <i class="bi bi-cloud-fill"></i>

                    <h5>

                        Cloud Based

                    </h5>

                    <p>

                        Access anywhere.

                    </p>

                </div>

            </div>

            <div class="col-lg-3">

                <div class="why-card">

                    <i class="bi bi-shield-check"></i>

                    <h5>

                        Secure

                    </h5>

                    <p>

                        Enterprise grade security.

                    </p>

                </div>

            </div>

            <div class="col-lg-3">

                <div class="why-card">

                    <i class="bi bi-phone"></i>

                    <h5>

                        Responsive

                    </h5>

                    <p>

                        Works on every device.

                    </p>

                </div>

            </div>

            <div class="col-lg-3">

                <div class="why-card">

                    <i class="bi bi-lightning-charge-fill"></i>

                    <h5>

                        Fast

                    </h5>

                    <p>

                        High performance ERP.

                    </p>

                </div>

            </div>

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

                Ready To Transform Your Logistics Business?

            </h2>

            <p>

                Book a personalized demo today.

            </p>

            <a href="{{ route('website.contact') }}"
               class="btn btn-light btn-lg rounded-pill">

                Book Free Demo

            </a>

        </div>

    </div>

</section>



<!-- ==========================================
Footer
========================================== -->

<footer class="footer">

    <div class="container">

        <div class="row">

            <div class="col-lg-4">

                <img src="img/logo-white.svg"
                     height="42">

                <p class="mt-4">

                    Flikma is an integrated cloud ERP for Freight,
                    Transportation,
                    Billing and ZATCA e-Invoicing.

                </p>

            </div>

            <div class="col-lg-2">

                <h5>

                    Company

                </h5>

                <ul>

                    <li><a href="#">About</a></li>

                    <li><a href="#">Careers</a></li>

                    <li><a href="{{ route('website.contact') }}">Contact</a></li>

                    <li><a href="#">Blog</a></li>

                </ul>

            </div>

            <div class="col-lg-2">

                <h5>

                    Products

                </h5>

                <ul>

                    <li><a href="#">Freight ERP</a></li>

                    <li><a href="#">Transport</a></li>

                    <li><a href="#">Billing</a></li>

                </ul>

            </div>

            <div class="col-lg-2">

                <h5>

                    Support

                </h5>

                <ul>

                    <li><a href="#">Documentation</a></li>

                    <li><a href="#">Help Center</a></li>

                    <li><a href="#">Privacy</a></li>

                    <li><a href="#">Terms</a></li>

                </ul>

            </div>

            <div class="col-lg-2">

                <h5>

                    Follow

                </h5>

                <div class="social-icons">

                    <a href="#"><i class="bi bi-facebook"></i></a>

                    <a href="#"><i class="bi bi-instagram"></i></a>

                    <a href="#"><i class="bi bi-linkedin"></i></a>

                    <a href="#"><i class="bi bi-twitter-x"></i></a>

                </div>

            </div>

        </div>

        <hr class="my-5">

        <div class="text-center">

            © 2026 Flikma. All Rights Reserved.

        </div>

    </div>

</footer>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/app.js"></script>

</body>

</html>
