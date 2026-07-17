<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Every Flikma feature — sales pipeline, job management, invoicing, accounting, reporting, payroll and ZATCA compliance built for freight forwarders.">

    <title>Features - Flikma Logistics ERP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('css/website/style.css') }}" rel="stylesheet">

    <link href="{{ asset('css/website/responsive.css') }}" rel="stylesheet">

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

        <span class="section-tag">Product Features</span>

        <h1 class="mt-3">

            Every Module A Freight Forwarder Needs

        </h1>

        <p class="page-header-desc mx-auto">

            From the first customer enquiry to the final bank reconciliation, Flikma is
            built specifically for logistics and freight-forwarding operations.

        </p>

    </div>

</section>


<section class="features-group-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">SALES & CRM</span>
            <h2 class="section-title mt-3">Capture Demand And Turn It Into Revenue</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-envelope-paper"></i></div>
                    <h5>Enquiries</h5>
                    <p>Log customer enquiries with routing, cargo and activity details from the first contact.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <h5>Quotations</h5>
                    <p>Build multi-charge quotations with revisions, approvals and one-click conversion to a job.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-person-plus"></i></div>
                    <h5>Prospect Management</h5>
                    <p>Track leads separately from confirmed customers until they are ready to convert.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-person-badge"></i></div>
                    <h5>Salesperson Tracking</h5>
                    <p>Attribute every enquiry, quotation and job to the salesperson who owns the relationship.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-group-section bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">OPERATIONS & SHIPPING</span>
            <h2 class="section-title mt-3">Run The Physical Movement Of Cargo</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-briefcase"></i></div>
                    <h5>Job Management</h5>
                    <p>One record per shipment &mdash; origin/destination, carrier, containers, packages and milestones.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-airplane"></i></div>
                    <h5>Airway Bill</h5>
                    <p>Generate and track air cargo airway bills with delivery status and history.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-water"></i></div>
                    <h5>Seaway Bill</h5>
                    <p>Manage ocean freight seaway bills alongside container and vessel details.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-file-earmark-ruled"></i></div>
                    <h5>Waybill</h5>
                    <p>Cover land transport with dedicated waybill documents and delivery confirmation.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-group-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">FINANCE & ACCOUNTING</span>
            <h2 class="section-title mt-3">A Real Double-Entry Ledger Underneath Every Document</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-clipboard-data"></i></div>
                    <h5>Customer Invoicing</h5>
                    <p>Draft, approve and send customer invoices that post straight to the general ledger.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-file-earmark-ruled"></i></div>
                    <h5>Supplier Invoicing</h5>
                    <p>Capture supplier costs against jobs with full VAT handling.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-clipboard-check"></i></div>
                    <h5>Proforma Invoices</h5>
                    <p>Send proforma invoices ahead of final billing without touching the ledger.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-file-earmark-minus"></i></div>
                    <h5>Credit Notes</h5>
                    <p>Issue credit notes that automatically reverse revenue and VAT on the original invoice.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-cash-coin"></i></div>
                    <h5>Payments & Collections</h5>
                    <p>Record partial or full payments/collections against one or many invoices at once.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-diagram-2"></i></div>
                    <h5>Chart of Accounts</h5>
                    <p>Fully hierarchical, multi-level chart of accounts shared across every module.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-journal-text"></i></div>
                    <h5>Journal Vouchers</h5>
                    <p>Post manual adjusting entries for the transactions your business needs outside standard flows.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-building"></i></div>
                    <h5>Fixed Assets & Opening Balances</h5>
                    <p>Track company assets and bring in your existing balances when you switch to Flikma.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-group-section bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">REPORTS & ANALYTICS</span>
            <h2 class="section-title mt-3">Know Your Numbers The Moment You Need Them</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-table"></i></div>
                    <h5>Trial Balance & Financials</h5>
                    <p>Trial Balance, Balance Sheet, Profit & Loss and General Ledger, always current.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-receipt"></i></div>
                    <h5>Tax Summary</h5>
                    <p>Input &amp; Output VAT reports reconciled straight from the ledger, ready for filing.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-hourglass-split"></i></div>
                    <h5>Aging Reports</h5>
                    <p>Customer &amp; supplier aging, per-account or company-wide, with configurable buckets.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-graph-up"></i></div>
                    <h5>Job Profitability</h5>
                    <p>Job Balance, Job Income and Provisional Reports compare budgeted vs actual cost and sales.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-person-lines-fill"></i></div>
                    <h5>Statements & Activity</h5>
                    <p>Customer/supplier statements and activity reports for account reconciliation.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                    <h5>Print, PDF & Excel Everywhere</h5>
                    <p>Every report and document exports to a polished PDF or Excel file in one click.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-group-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">PAYROLL & HR</span>
            <h2 class="section-title mt-3">Keep Your Team Paid Accurately And On Time</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-calendar-check"></i></div>
                    <h5>Attendance</h5>
                    <p>Track daily attendance feeding directly into monthly payroll runs.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-wallet2"></i></div>
                    <h5>Basic & Monthly Salary</h5>
                    <p>Configure basic salary structures and run monthly payroll with full audit history.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-bank"></i></div>
                    <h5>Employee Loans</h5>
                    <p>Issue and automatically recover employee loans through payroll deductions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-group-section bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-tag">COMPLIANCE & PLATFORM</span>
            <h2 class="section-title mt-3">Enterprise-Grade Foundations, Built In</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-shield-check"></i></div>
                    <h5>ZATCA E-Invoicing</h5>
                    <p>Built-in compliance for Saudi Arabia's e-invoicing regulations on every approved invoice.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-building-gear"></i></div>
                    <h5>Multi-Company</h5>
                    <p>Run multiple legal entities from one account, each fully isolated from the others.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-layout-text-sidebar-reverse"></i></div>
                    <h5>Customizable Columns</h5>
                    <p>Every list adapts to how your team works &mdash; show, hide and reorder columns per user.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-tile h-100">
                    <div class="feature-tile-icon"><i class="bi bi-shield-lock"></i></div>
                    <h5>Department User Rights</h5>
                    <p>Control what each department can view, create, edit or delete, module by module.</p>
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

                See It Running With Your Own Data

            </h2>

            <p>

                Book a walkthrough and we'll show you Flikma configured for your operation.

            </p>

            <a href="{{ url('/register') }}"
               class="btn btn-light btn-lg rounded-pill">

                Create Your Free Account

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
