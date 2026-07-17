@extends('website.layout')

@section('title', 'Features — Flikma Logistics ERP')
@section('meta_description', 'Explore every Flikma feature — sales pipeline, job management, invoicing, accounting, reporting, payroll and ZATCA compliance built for freight forwarders.')

@section('content')

    <section class="fk-gradient-bg pt-5 pb-4">
        <div class="container pt-5 pb-4 text-center">
            <span class="fk-badge mb-3"><i class="bi bi-grid-3x3-gap"></i> Product Features</span>
            <h1 class="display-5 mb-3">Every module a freight forwarder needs</h1>
            <p class="fs-5 text-fk-muted mx-auto" style="max-width:640px;">From the first customer enquiry to the final bank reconciliation, Flikma is built specifically for logistics and freight-forwarding operations.</p>
        </div>
    </section>

    @php
        $groups = [
            [
                'title' => 'Sales & CRM',
                'icon' => 'bi-people',
                'desc' => 'Capture demand and turn it into revenue.',
                'items' => [
                    ['icon' => 'bi-envelope-paper', 'title' => 'Enquiries', 'desc' => 'Log customer enquiries with routing, cargo and activity details from the first contact.'],
                    ['icon' => 'bi-file-earmark-text', 'title' => 'Quotations', 'desc' => 'Build multi-charge quotations with revisions, approvals and one-click conversion to a job.'],
                    ['icon' => 'bi-person-plus', 'title' => 'Prospect Management', 'desc' => 'Track leads separately from confirmed customers until they are ready to convert.'],
                    ['icon' => 'bi-person-badge', 'title' => 'Salesperson Tracking', 'desc' => 'Attribute every enquiry, quotation and job to the salesperson who owns the relationship.'],
                ],
            ],
            [
                'title' => 'Operations & Shipping',
                'icon' => 'bi-truck',
                'desc' => 'Run the physical movement of cargo.',
                'items' => [
                    ['icon' => 'bi-briefcase', 'title' => 'Job Management', 'desc' => 'One record per shipment — origin/destination, carrier, containers, packages and milestones.'],
                    ['icon' => 'bi-airplane', 'title' => 'Airway Bill', 'desc' => 'Generate and track air cargo airway bills with delivery status and history.'],
                    ['icon' => 'bi-water', 'title' => 'Seaway Bill', 'desc' => 'Manage ocean freight seaway bills alongside container and vessel details.'],
                    ['icon' => 'bi-file-earmark-ruled', 'title' => 'Waybill', 'desc' => 'Cover land transport with dedicated waybill documents and delivery confirmation.'],
                ],
            ],
            [
                'title' => 'Finance & Accounting',
                'icon' => 'bi-cash-stack',
                'desc' => 'A real double-entry ledger underneath every document.',
                'items' => [
                    ['icon' => 'bi-clipboard-data', 'title' => 'Customer Invoicing', 'desc' => 'Draft, approve and send customer invoices that post straight to the general ledger.'],
                    ['icon' => 'bi-file-earmark-ruled', 'title' => 'Supplier Invoicing', 'desc' => 'Capture supplier costs against jobs with full VAT handling.'],
                    ['icon' => 'bi-clipboard-check', 'title' => 'Proforma Invoices', 'desc' => 'Send proforma invoices ahead of final billing without touching the ledger.'],
                    ['icon' => 'bi-file-earmark-minus', 'title' => 'Credit Notes', 'desc' => 'Issue credit notes that automatically reverse revenue and VAT on the original invoice.'],
                    ['icon' => 'bi-cash-coin', 'title' => 'Payments & Collections', 'desc' => 'Record partial or full payments/collections against one or many invoices at once.'],
                    ['icon' => 'bi-diagram-2', 'title' => 'Chart of Accounts', 'desc' => 'Fully hierarchical, multi-level chart of accounts shared across every module.'],
                    ['icon' => 'bi-journal-text', 'title' => 'Journal Vouchers', 'desc' => 'Post manual adjusting entries for the transactions your business needs outside standard flows.'],
                    ['icon' => 'bi-building', 'title' => 'Fixed Assets & Opening Balances', 'desc' => 'Track company assets and bring in your existing balances when you switch to Flikma.'],
                ],
            ],
            [
                'title' => 'Reports & Analytics',
                'icon' => 'bi-bar-chart-line',
                'desc' => 'Know your numbers the moment you need them.',
                'items' => [
                    ['icon' => 'bi-table', 'title' => 'Trial Balance & Financials', 'desc' => 'Trial Balance, Balance Sheet, Profit & Loss and General Ledger, always current.'],
                    ['icon' => 'bi-receipt', 'title' => 'Tax Summary', 'desc' => 'Input &amp; Output VAT reports reconciled straight from the ledger, ready for filing.'],
                    ['icon' => 'bi-hourglass-split', 'title' => 'Aging Reports', 'desc' => 'Customer &amp; supplier aging, per-account or company-wide, with configurable buckets.'],
                    ['icon' => 'bi-graph-up', 'title' => 'Job Profitability', 'desc' => 'Job Balance, Job Income and Provisional Reports compare budgeted vs actual cost and sales.'],
                    ['icon' => 'bi-person-lines-fill', 'title' => 'Statements & Activity', 'desc' => 'Customer/supplier statements and activity reports for account reconciliation.'],
                    ['icon' => 'bi-file-earmark-spreadsheet', 'title' => 'Print, PDF & Excel Everywhere', 'desc' => 'Every report and document exports to a polished PDF or Excel file in one click.'],
                ],
            ],
            [
                'title' => 'Payroll & HR',
                'icon' => 'bi-people-fill',
                'desc' => 'Keep your team paid accurately and on time.',
                'items' => [
                    ['icon' => 'bi-calendar-check', 'title' => 'Attendance', 'desc' => 'Track daily attendance feeding directly into monthly payroll runs.'],
                    ['icon' => 'bi-wallet2', 'title' => 'Basic & Monthly Salary', 'desc' => 'Configure basic salary structures and run monthly payroll with full audit history.'],
                    ['icon' => 'bi-bank', 'title' => 'Employee Loans', 'desc' => 'Issue and automatically recover employee loans through payroll deductions.'],
                ],
            ],
            [
                'title' => 'Compliance & Platform',
                'icon' => 'bi-shield-check',
                'desc' => 'Enterprise-grade foundations, built in.',
                'items' => [
                    ['icon' => 'bi-shield-check', 'title' => 'ZATCA E-Invoicing', 'desc' => 'Built-in compliance for Saudi Arabia\'s e-invoicing regulations on every approved invoice.'],
                    ['icon' => 'bi-currency-exchange', 'title' => 'Multi-Currency', 'desc' => 'Transact in any currency with live exchange rates converted to your base currency automatically.'],
                    ['icon' => 'bi-building-gear', 'title' => 'Multi-Company', 'desc' => 'Run multiple legal entities from one account, each fully isolated from the others.'],
                    ['icon' => 'bi-layout-text-sidebar-reverse', 'title' => 'Customizable Columns', 'desc' => 'Every list adapts to how your team works — show, hide and reorder columns per user.'],
                ],
            ],
        ];
    @endphp

    @foreach($groups as $group)
        <section class="fk-section {{ $loop->even ? 'bg-light' : '' }}">
            <div class="container">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="fk-icon-box"><i class="bi {{ $group['icon'] }}"></i></div>
                    <div>
                        <h2 class="h3 mb-0">{{ $group['title'] }}</h2>
                        <p class="text-fk-muted mb-0 small">{{ $group['desc'] }}</p>
                    </div>
                </div>
                <div class="row g-4 mt-2">
                    @foreach($group['items'] as $item)
                        <div class="col-md-6 col-lg-3">
                            <div class="fk-card h-100 p-4">
                                <div class="fk-icon-box mb-3" style="width:44px;height:44px;font-size:1.15rem;"><i class="bi {{ $item['icon'] }}"></i></div>
                                <h6 class="fw-bold">{{ $item['title'] }}</h6>
                                <p class="text-fk-muted small mb-0">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach

    <section class="container pb-5">
        <div class="fk-cta-band text-white p-5 text-center">
            <h2 class="display-6 text-white mb-3">See it running with your own data</h2>
            <p class="text-white-50 mb-4">Book a walkthrough and we'll show you Flikma configured for your operation.</p>
            <a href="{{ url('/register') }}" class="btn btn-light fw-bold px-4 py-2">Start Your Free Trial</a>
        </div>
    </section>

@endsection
