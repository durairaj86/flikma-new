<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Flikma documentation — how every module works, from customers and quotations to invoices, payroll and reports.">

    <title>Documentation - Flikma Logistics ERP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('css/website/style.css') }}" rel="stylesheet">

    <link href="{{ asset('css/website/responsive.css') }}" rel="stylesheet">

    <link href="{{ asset('css/website/documentation.css') }}" rel="stylesheet">

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

        <span class="section-tag">Documentation</span>

        <h1 class="mt-3">
            How Flikma Works, Module By Module
        </h1>

        <p class="page-header-desc mx-auto">
            A practical walkthrough of every part of the software — what each module
            does, the key fields you'll fill in, and how data flows from one screen
            to the next.
        </p>

    </div>

</section>


<div class="container docs-layout">

    <!-- =====================
    Sidebar TOC
    ====================== -->
    <aside class="docs-toc d-none d-lg-block">

        <h6>On this page</h6>

        <ul>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-customers" aria-expanded="true">
                    <span>Customers</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse show" id="toc-customers">
                    <li><a href="#customers-list">Customer List</a></li>
                    <li><a href="#customers-statement">Customer Statement</a></li>
                    <li><a href="#customers-aging">Customer Aging</a></li>
                    <li><a href="#customers-aging-all">Customer Aging (All)</a></li>
                    <li><a href="#prospects">Prospects</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-suppliers" aria-expanded="true">
                    <span>Suppliers / Agents</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse show" id="toc-suppliers">
                    <li><a href="#suppliers-list">Supplier List</a></li>
                    <li><a href="#suppliers-statement">Supplier Statement</a></li>
                    <li><a href="#suppliers-aging">Supplier Aging</a></li>
                    <li><a href="#suppliers-aging-all">Supplier Aging (All)</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-sales" aria-expanded="false">
                    <span>Sales: Enquiry & Quotation</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-sales">
                    <li><a href="#enquiries">Enquiries</a></li>
                    <li><a href="#quotations">Quotations</a></li>
                    <li><a href="#sales-overview">Sales Overview</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-operations" aria-expanded="false">
                    <span>Jobs</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-operations">
                    <li><a href="#jobs">Jobs</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-finances" aria-expanded="false">
                    <span>Invoices</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-finances">
                    <li><a href="#invoice-proforma">Proforma Invoice</a></li>
                    <li><a href="#invoice-supplier">Supplier Invoice</a></li>
                    <li><a href="#invoice-customer">Customer Invoice</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-adjustments" aria-expanded="false">
                    <span>Adjustments: Credit Notes</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-adjustments">
                    <li><a href="#credit-notes">Credit Notes</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-expenses-assets" aria-expanded="false">
                    <span>Expenses & Assets</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-expenses-assets">
                    <li><a href="#expenses">Expenses</a></li>
                    <li><a href="#assets">Assets</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-accounts" aria-expanded="false">
                    <span>Chart of Accounts</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-accounts">
                    <li><a href="#chart-of-accounts">Chart of Accounts</a></li>
                    <li><a href="#journal-vouchers">Journal Vouchers</a></li>
                    <li><a href="#opening-balance">Opening Balance</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-transactions" aria-expanded="false">
                    <span>Payments & Collections</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-transactions">
                    <li><a href="#payments">Payments</a></li>
                    <li><a href="#collections">Collections</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-bl" aria-expanded="false">
                    <span>Bill of Lading</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-bl">
                    <li><a href="#airway-bill">Airway Bill</a></li>
                    <li><a href="#seaway-bill">Seaway Bill</a></li>
                    <li><a href="#waybill">Waybill</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-payroll" aria-expanded="false">
                    <span>Payroll</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-payroll">
                    <li><a href="#attendance">Attendance</a></li>
                    <li><a href="#basic-salary">Basic Salary</a></li>
                    <li><a href="#monthly-salary">Monthly Salary</a></li>
                    <li><a href="#employee-loan">Employee Loan</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-reports" aria-expanded="false">
                    <span>Reports</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-reports">
                    <li><a href="#job-reports">Job Reports</a></li>
                    <li><a href="#operations-reports">Operations Reports</a></li>
                    <li><a href="#finance-reports">Finance Reports</a></li>
                    <li><a href="#tax-reports">Tax Reports</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-inventory" aria-expanded="false">
                    <span>Items</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-inventory">
                    <li><a href="#items">Items</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-masters-rights" aria-expanded="false">
                    <span>Masters & User Rights</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-masters-rights">
                    <li><a href="#users">Users</a></li>
                    <li><a href="#departments">Departments (User Rights)</a></li>
                    <li><a href="#transport-directory">Transport Directory</a></li>
                    <li><a href="#predefined-data">Predefined Data</a></li>
                    <li><a href="#banks">Banks</a></li>
                    <li><a href="#descriptions">Descriptions</a></li>
                    <li><a href="#units">Units</a></li>
                    <li><a href="#salesperson">Salesperson</a></li>
                </ul>
            </li>
            <li>
                <button class="docs-toc-group" type="button" data-bs-toggle="collapse" data-bs-target="#toc-settings" aria-expanded="false">
                    <span>Settings</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="docs-toc-sub collapse " id="toc-settings">
                    <li><a href="#settings-account">Account</a></li>
                    <li><a href="#settings-company">Manage Business</a></li>
                    <li><a href="#settings-invoice">Invoice Settings</a></li>
                    <li><a href="#settings-zatca">Zatca Integration</a></li>
                </ul>
            </li>
        </ul>

    </aside>

    <!-- =====================
    Content
    ====================== -->
    <div class="docs-content">

        <div class="docs-intro" id="getting-started">
            <p>
                <strong>Getting started.</strong> When you register a company at
                <a href="{{ url('/register') }}">/register</a>, that account becomes
                the <strong>Super User</strong> &mdash; the owner of the company's data.
                The Super User always has full access to every module and is never
                restricted by department rights.
            </p>
            <p>
                Every other user you create under <strong>Masters &rarr; Users</strong>
                is assigned to a <strong>Department</strong>, and that department's
                rights (configured under <strong>Masters &rarr; Departments</strong>)
                decide which modules they can view, create, edit or delete in. See
                "Masters &amp; User Rights" below for details.
            </p>
        </div>

        <div class="docs-module" id="customers">
            <span class="docs-module-badge"><i class="bi bi-person-lines-fill"></i> Masters</span>
            <h2>Customers</h2>
            <p class="docs-desc">The customer master holds every party you bill. Once created, a customer is reused across enquiries, jobs, invoices and statements &mdash; you enter it once and it flows through the whole system.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-customers-suppliers.svg') }}" alt="Customer lifecycle flow">
            </div>
            <div class="docs-leaf" id="customers-list">
                <h4>Customer List</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-customers-list.svg') }}" alt="Customer List flow">
                </div>
                <p>This is where you build your customer database. Each customer record captures their legal name in English and Arabic, billing currency, and full contact and address details.</p>
                <p>If the customer is a VAT-registered business, Flikma also asks for their commercial registration and VAT numbers plus a ZATCA-compliant address, so every invoice you send them is fully compliant from day one. Unregistered customers (individuals or small businesses) skip that extra detail.</p>
            </div>
            <div class="docs-leaf" id="customers-statement">
                <h4>Customer Statement</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-customers-statement.svg') }}" alt="Customer Statement flow">
                </div>
                <p>A running account history for a single customer &mdash; every invoice raised and every payment received, in date order, so you can see exactly what they owe and why at a glance. It's the document you'd hand a customer if they ever asked "what do we owe you and why?"</p>
            </div>
            <div class="docs-leaf" id="customers-aging">
                <h4>Customer Aging</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-customers-aging.svg') }}" alt="Customer Aging flow">
                </div>
                <p>Shows how overdue each customer's outstanding balance is, split into aging buckets (current, 30, 60, 90+ days). It's the fastest way to spot who needs a follow-up call. Use it before your monthly collections call to know exactly who to chase first.</p>
            </div>
            <div class="docs-leaf" id="customers-aging-all">
                <h4>Customer Aging (All)</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-customers-aging-all.svg') }}" alt="Customer Aging (All) flow">
                </div>
                <p>The same aging view as above, but consolidated across your entire customer base in one report &mdash; useful for a company-wide collections review. It's the report to run at month-end when you want one number for total customer exposure.</p>
            </div>
            <div class="docs-leaf" id="prospects">
                <h4>Prospects</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-prospects.svg') }}" alt="Prospects flow">
                </div>
                <p>A holding area for leads that haven't become full customers yet. You can log a prospect at the enquiry stage and only promote them to a proper customer record once commercial terms are agreed.</p>
            </div>
        </div>

        <div class="docs-module" id="suppliers">
            <span class="docs-module-badge"><i class="bi bi-truck"></i> Masters</span>
            <h2>Suppliers / Agents</h2>
            <p class="docs-desc">Suppliers, carriers and agents are billed through Supplier Invoices and settled through Payments. The record shape mirrors Customers, just on the money-out side of the business.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-customers-suppliers.svg') }}" alt="Supplier lifecycle flow">
            </div>
            <div class="docs-leaf" id="suppliers-list">
                <h4>Supplier List</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-suppliers-list.svg') }}" alt="Supplier List flow">
                </div>
                <p>Your directory of carriers, agents and vendors. Each record captures their legal name, currency, and contact details, plus commercial registration and VAT numbers for VAT-registered suppliers. Keeping this up to date means every job and invoice can pull the right supplier details automatically instead of retyping them.</p>
            </div>
            <div class="docs-leaf" id="suppliers-statement">
                <h4>Supplier Statement</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-suppliers-statement.svg') }}" alt="Supplier Statement flow">
                </div>
                <p>A running account history for a single supplier &mdash; every bill they've raised against you and every payment you've made to them. Handy when a supplier disputes a balance &mdash; you can pull up the exact history in seconds.</p>
            </div>
            <div class="docs-leaf" id="suppliers-aging">
                <h4>Supplier Aging</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-suppliers-aging.svg') }}" alt="Supplier Aging flow">
                </div>
                <p>Shows how much you owe each supplier and how overdue it is, bucketed by age, so you never miss a payment deadline. It's the report to check before your payment run, so nothing slips past its due date.</p>
            </div>
            <div class="docs-leaf" id="suppliers-aging-all">
                <h4>Supplier Aging (All)</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-suppliers-aging-all.svg') }}" alt="Supplier Aging (All) flow">
                </div>
                <p>The same aging buckets consolidated across every supplier at once &mdash; a full picture of your payables. Run it whenever you need a single view of everything the business currently owes.</p>
            </div>
        </div>

        <div class="docs-module" id="sales">
            <span class="docs-module-badge"><i class="bi bi-cart"></i> Sales</span>
            <h2>Sales: Enquiry & Quotation</h2>
            <p class="docs-desc">Sales work starts as an Enquiry capturing what the customer needs shipped, becomes a priced Quotation, and an approved quotation converts directly into a Job &mdash; nothing gets re-typed along the way.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-sales.svg') }}" alt="Enquiry to quotation flow">
            </div>
            <div class="docs-leaf" id="enquiries">
                <h4>Enquiries</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-enquiries.svg') }}" alt="Enquiries flow">
                </div>
                <p>Log a shipment requirement as soon as a customer or prospect asks for a quote. You record the shipping mode (air, sea or land), origin and destination ports, cargo weight and volume, container or package details, and how long the enquiry stays valid.</p>
                <p>An enquiry is the starting point of the sales pipeline &mdash; nothing is priced yet, you're just capturing the requirement before it becomes a formal quotation.</p>
            </div>
            <div class="docs-leaf" id="quotations">
                <h4>Quotations</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-quotations.svg') }}" alt="Quotations flow">
                </div>
                <p>Turn an enquiry into a priced offer. You add charge lines with descriptions and amounts, choose who's billed and who's supplying the service, and print or send the quotation to the customer for approval.</p>
                <p>Once the customer approves, the quotation converts directly into a Job with a single click &mdash; the pricing and cargo details carry over automatically.</p>
            </div>
            <div class="docs-leaf" id="sales-overview">
                <h4>Sales Overview</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-sales-overview.svg') }}" alt="Sales Overview flow">
                </div>
                <p>A read-only dashboard of your sales performance &mdash; total sales, collected vs. outstanding amounts, average invoice value, trends over time, performance by service type and region, salesperson leaderboards, and your top customers and items.</p>
                <p>You can switch the view between this month, last month, and this year to spot trends quickly.</p>
            </div>
        </div>

        <div class="docs-module" id="operations">
            <span class="docs-module-badge"><i class="bi bi-gear"></i> Operations</span>
            <h2>Jobs</h2>
            <p class="docs-desc">A Job is the operational record for a shipment, created from an approved quotation. It carries cargo, party and shipping details, and is the source record for both customer and supplier invoicing.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-operations.svg') }}" alt="Job operations flow">
            </div>
            <div class="docs-leaf" id="jobs">
                <h4>Jobs</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-jobs.svg') }}" alt="Jobs flow">
                </div>
                <p>The heart of day-to-day operations. A job tracks who the shipment is for, the salesperson responsible, the type of cargo and service, carrier and reference numbers, weight, volume and piece count, and full shipper and consignee details with pickup information.</p>
                <p>Everything downstream reads from the job: the Bill of Lading is issued against it, the customer and supplier invoices are generated from it, and every job-based report (profitability, balance, income) is built from its data.</p>
            </div>
        </div>

        <div class="docs-module" id="finances">
            <span class="docs-module-badge"><i class="bi bi-wallet2"></i> Finances</span>
            <h2>Invoices</h2>
            <p class="docs-desc">Once a job is complete, generate a Customer Invoice for what you billed and a Supplier Invoice for what your vendors billed you. A Proforma Invoice is available for sending an estimate before the final invoice. Every posted invoice flows into the Chart of Accounts automatically.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-finances-invoice.svg') }}" alt="Invoice flow">
            </div>
            <div class="docs-leaf" id="invoice-proforma">
                <h4>Proforma Invoice</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-invoice-proforma.svg') }}" alt="Proforma Invoice flow">
                </div>
                <p>A preview invoice you can send to a customer before committing to the final bill &mdash; useful for getting sign-off on pricing ahead of time. It doesn't post to your accounts, so nothing is affected financially until you raise the real invoice.</p>
            </div>
            <div class="docs-leaf" id="invoice-supplier">
                <h4>Supplier Invoice</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-invoice-supplier.svg') }}" alt="Supplier Invoice flow">
                </div>
                <p>Records what a carrier, agent or vendor billed you for their part of a job. It's matched against the job it belongs to, so your true cost per shipment is always visible next to the revenue it generated.</p>
            </div>
            <div class="docs-leaf" id="invoice-customer">
                <h4>Customer Invoice</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-invoice-customer.svg') }}" alt="Customer Invoice flow">
                </div>
                <p>The final bill sent to your customer, built directly from the job's cargo and pricing details so nothing has to be re-typed. Every customer invoice is generated ZATCA Phase 2 ready, with the QR code, digital signature and XML data Saudi e-invoicing regulations require built in automatically.</p>
                <p>Once issued, the invoice posts straight to the Chart of Accounts and becomes available for Payments, Collections and every finance report.</p>
            </div>
        </div>

        <div class="docs-module" id="adjustments">
            <span class="docs-module-badge"><i class="bi bi-arrow-left-right"></i> Finances</span>
            <h2>Adjustments: Credit Notes</h2>
            <p class="docs-desc">When an invoice needs to be reduced &mdash; a dispute, a return, or a pricing correction &mdash; a Credit Note adjusts the customer's balance without editing the original invoice, keeping a clean audit trail.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-adjustments.svg') }}" alt="Credit note flow">
            </div>
            <div class="docs-leaf" id="credit-notes">
                <h4>Credit Notes</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-credit-notes.svg') }}" alt="Credit Notes flow">
                </div>
                <p>Issue a full or partial credit against a specific customer invoice, with a reason recorded for the audit trail. The customer's outstanding balance updates immediately, while the original invoice stays untouched as the historical record of what was actually billed. Because it's a separate document rather than an edited invoice, your audit trail always shows what was originally billed and what was later adjusted.</p>
            </div>
        </div>

        <div class="docs-module" id="expenses-assets">
            <span class="docs-module-badge"><i class="bi bi-receipt"></i> Finances</span>
            <h2>Expenses & Assets</h2>
            <p class="docs-desc">Expenses record day-to-day costs, whether billable back to a customer or an internal overhead. Assets maintain a fixed-asset register with automatic depreciation scheduling.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-expenses-assets.svg') }}" alt="Expenses and assets flow">
            </div>
            <div class="docs-leaf" id="expenses">
                <h4>Expenses</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-expenses.svg') }}" alt="Expenses flow">
                </div>
                <p>Log a cost against a job or as a general overhead, split across multiple line items with their own accounts, quantities and tax. Flag an expense as billable and it becomes recoverable from the customer; otherwise it's tracked purely as a cost to the business.</p>
                <p>Expenses track their own payment status, so you always know what's been settled and what's still outstanding.</p>
            </div>
            <div class="docs-leaf" id="assets">
                <h4>Assets</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-assets.svg') }}" alt="Assets flow">
                </div>
                <p>Maintain a register of the equipment, vehicles and other fixed assets your company owns, recording what you paid, when you acquired it, and its expected useful life. Flikma then generates a month-by-month depreciation schedule for you automatically, and tracks each asset's status through its lifecycle &mdash; current, in use, or retired.</p>
            </div>
        </div>

        <div class="docs-module" id="accounts">
            <span class="docs-module-badge"><i class="bi bi-diagram-3"></i> Finances</span>
            <h2>Chart of Accounts</h2>
            <p class="docs-desc">Every invoice, payment and collection posts automatically to the Chart of Accounts. Accounts are organized in parent/child groups &mdash; Asset, Liability, Income, Expense, Equity &mdash; so your Trial Balance, Balance Sheet and Profit & Loss stay accurate without manual journal entries for routine transactions.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-accounts.svg') }}" alt="Chart of accounts flow">
            </div>
            <div class="docs-leaf" id="chart-of-accounts">
                <h4>Chart of Accounts</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-chart-of-accounts.svg') }}" alt="Chart of Accounts flow">
                </div>
                <p>Your full ledger structure, organized as parent accounts with child sub-accounts underneath. This is the backbone every financial report in Flikma is built from &mdash; you rarely need to touch it day-to-day, since invoices, payments and collections post to it automatically. You'll typically set this up once during onboarding and rarely need to revisit it.</p>
            </div>
            <div class="docs-leaf" id="journal-vouchers">
                <h4>Journal Vouchers</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-journal-vouchers.svg') }}" alt="Journal Vouchers flow">
                </div>
                <p>For anything that doesn't come from an invoice or transaction, a journal voucher lets you post a manual double-entry directly to the ledger &mdash; correcting an error, recording a one-off adjustment, and so on.</p>
                <p>Vouchers go through an approve/disapprove workflow (a reason is required to disapprove one), and can be printed once approved for your records.</p>
            </div>
            <div class="docs-leaf" id="opening-balance">
                <h4>Opening Balance</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-opening-balance.svg') }}" alt="Opening Balance flow">
                </div>
                <p>When you first move onto Flikma, this is where you enter your starting position &mdash; existing customer and supplier balances and general ledger figures &mdash; as a one-time opening journal, so your books are accurate from day one rather than starting at zero.</p>
            </div>
        </div>

        <div class="docs-module" id="transactions">
            <span class="docs-module-badge"><i class="bi bi-cash-coin"></i> Transactions</span>
            <h2>Payments & Collections</h2>
            <p class="docs-desc">Payments record money going out to suppliers; Collections record money coming in from customers. Both support full or partial amounts against one or more open invoices, and update the relevant bank or cash account the moment they're saved.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-transactions.svg') }}" alt="Payments and collections flow">
            </div>
            <div class="docs-leaf" id="payments">
                <h4>Payments</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-payments.svg') }}" alt="Payments flow">
                </div>
                <p>Settle one or more open supplier invoices in a single transaction, choosing which bank or cash account the money comes from. You can pay an invoice in full or in part &mdash; a partial payment simply leaves the remaining balance open for next time. Because it works across multiple invoices at once, you can clear a whole batch of supplier bills in one transaction instead of one at a time.</p>
            </div>
            <div class="docs-leaf" id="collections">
                <h4>Collections</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-collections.svg') }}" alt="Collections flow">
                </div>
                <p>Record money received from a customer against one or more of their open invoices, in full or in part. As soon as it's saved, the customer's outstanding balance and your bank or cash account both update together &mdash; no separate reconciliation step needed. Because it works across multiple invoices at once, you can clear several outstanding customer invoices in one transaction instead of one at a time.</p>
            </div>
        </div>

        <div class="docs-module" id="bl">
            <span class="docs-module-badge"><i class="bi bi-file-earmark-text"></i> Documentation</span>
            <h2>Bill of Lading</h2>
            <p class="docs-desc">Airway Bills, Seaway Bills and Waybills are generated against a confirmed job and shared with the customer as proof of shipment.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-bl.svg') }}" alt="Bill of lading flow">
            </div>
            <div class="docs-leaf" id="airway-bill">
                <h4>Airway Bill</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-airway-bill.svg') }}" alt="Airway Bill flow">
                </div>
                <p>The shipping document for air freight jobs. It records the origin and destination airports, the type and urgency of the shipment, how it's being paid for, and full delivery contact details, ready to print and hand to the customer as proof of dispatch. It's typically the last document generated before the shipment physically leaves, so accuracy here matters.</p>
            </div>
            <div class="docs-leaf" id="seaway-bill">
                <h4>Seaway Bill</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-seaway-bill.svg') }}" alt="Seaway Bill flow">
                </div>
                <p>The equivalent shipping document for sea freight jobs, capturing vessel and voyage details in place of flight information, but otherwise working exactly like the Airway Bill. Everything else about issuing and printing it works exactly like the Airway Bill.</p>
            </div>
            <div class="docs-leaf" id="waybill">
                <h4>Waybill</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-waybill.svg') }}" alt="Waybill flow">
                </div>
                <p>The equivalent shipping document for road and courier jobs &mdash; a lighter-weight version of the same idea, sized for local delivery runs. It covers the same proof-of-shipment purpose as the Airway and Seaway Bill, just for shorter, local moves.</p>
            </div>
        </div>

        <div class="docs-module" id="payroll">
            <span class="docs-module-badge"><i class="bi bi-person-badge"></i> Payroll</span>
            <h2>Payroll</h2>
            <p class="docs-desc">Track daily attendance, define each employee's basic salary, run the monthly salary process, and manage employee loans with repayment tracked over time.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-payroll.svg') }}" alt="Payroll flow">
            </div>
            <div class="docs-leaf" id="attendance">
                <h4>Attendance</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-attendance.svg') }}" alt="Attendance flow">
                </div>
                <p>A daily register per employee &mdash; present, absent, late, half-day or on leave &mdash; that feeds into the monthly salary calculation. It's usually the first thing your payroll team touches each day, since everything downstream depends on it being accurate.</p>
            </div>
            <div class="docs-leaf" id="basic-salary">
                <h4>Basic Salary</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-basic-salary.svg') }}" alt="Basic Salary flow">
                </div>
                <p>The standing salary figure for each employee, with an effective date and an active/inactive status, used as the base every monthly salary run starts from. Changing an employee's basic salary here doesn't rewrite history &mdash; it only affects salary runs from the effective date forward.</p>
            </div>
            <div class="docs-leaf" id="monthly-salary">
                <h4>Monthly Salary</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-monthly-salary.svg') }}" alt="Monthly Salary flow">
                </div>
                <p>The actual salary processed for an employee in a given month, including how and when it was paid, and whether it's still pending, already paid, or cancelled. Because it's tracked separately from Basic Salary, you can see exactly what was actually paid each month even if the base salary changes later.</p>
            </div>
            <div class="docs-leaf" id="employee-loan">
                <h4>Employee Loan</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-employee-loan.svg') }}" alt="Employee Loan flow">
                </div>
                <p>Track a loan advanced to an employee against a set number of installments. As each installment is paid, the remaining balance and installment count update automatically, so you always know how much is left to recover.</p>
            </div>
        </div>

        <div class="docs-module" id="reports">
            <span class="docs-module-badge"><i class="bi bi-bar-chart-line"></i> Reports</span>
            <h2>Reports</h2>
            <p class="docs-desc">Reports are grouped by Job, Operations and Finance. Every report can be filtered by date range or party, viewed on-screen, and exported the same way: Print, PDF, or Excel.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-reports.svg') }}" alt="Reports flow">
            </div>
            <div class="docs-leaf" id="job-reports">
                <h4>Job Reports</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-job-reports.svg') }}" alt="Job Reports flow">
                </div>
                <p>Three complementary views of job performance: the overall Job Report, a Job Balance Report showing what's still outstanding per job, and a Job Income Report showing profitability per job. Together they answer the three questions every operations manager asks: what happened, what's still owed, and did we make money.</p>
            </div>
            <div class="docs-leaf" id="operations-reports">
                <h4>Operations Reports</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-operations-reports.svg') }}" alt="Operations Reports flow">
                </div>
                <p>The Sales Report gives a view of revenue over time, and the Customer Activity Report shows how active (or quiet) each customer has been &mdash; useful for spotting accounts that need attention. The Customer Balance Summary lists every customer's opening balance, invoiced amount, amount received and closing balance for the period, all in one table &mdash; a fast way to see who owes what without opening individual statements.</p>
            </div>
            <div class="docs-leaf" id="finance-reports">
                <h4>Finance Reports</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-finance-reports.svg') }}" alt="Finance Reports flow">
                </div>
                <p>The standard financial statement set: Trial Balance, Balance Sheet, Profit & Loss, and the General Ledger for drilling into any individual account's transaction history. These are the reports your accountant or auditor will ask for first, and they're always in sync with the ledger since nothing here is entered manually.</p>
            </div>
            <div class="docs-leaf" id="tax-reports">
                <h4>Tax Reports</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-tax-reports.svg') }}" alt="Tax Reports flow">
                </div>
                <p>Tax Summary, Input Tax and Output Tax &mdash; the VAT reporting views you need to prepare and file your periodic tax return. Having these ready at any time removes the month-end scramble to reconstruct VAT figures from raw invoices.</p>
            </div>
        </div>

        <div class="docs-module" id="inventory">
            <span class="docs-module-badge"><i class="bi bi-box-seam"></i> Inventory</span>
            <h2>Items</h2>
            <p class="docs-desc">The item master is used on expense and sales lines wherever a priced product or service line is needed.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-inventory.svg') }}" alt="Inventory items flow">
            </div>
            <div class="docs-leaf" id="items">
                <h4>Items</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-items.svg') }}" alt="Items flow">
                </div>
                <p>Define a reusable product or service line with its own cost and selling price, linked to the accounts it should post to. Once created, an item can be dropped into any invoice or expense line instead of typing the same description and pricing out every time &mdash; and because both prices are stored, your margin on that item is always visible.</p>
            </div>
        </div>

        <div class="docs-module" id="masters-rights">
            <span class="docs-module-badge"><i class="bi bi-shield-lock"></i> Masters</span>
            <h2>Masters & User Rights</h2>
            <p class="docs-desc">Master Data holds every reference list the app depends on. Departments are where access control lives: each department has its own view, create, edit and delete rights per module, and every user inherits the rights of the department they belong to.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-masters-rights.svg') }}" alt="Masters and user rights flow">
            </div>
            <div class="docs-leaf" id="users">
                <h4>Users</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-users.svg') }}" alt="Users flow">
                </div>
                <p>Create the people who log into your company's Flikma account. Each user is assigned to a department (which decides what they can access), a role, and their own login credentials.</p>
            </div>
            <div class="docs-leaf" id="departments">
                <h4>Departments (User Rights)</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-departments.svg') }}" alt="Departments (User Rights) flow">
                </div>
                <p>This is where you control who can do what. Create a department &mdash; Sales, Accounts, Operations, whatever matches how your team is organized &mdash; and then decide, module by module, whether that department can view, create, edit or delete records there.</p>
                <p>Every user you add belongs to one department and automatically inherits its rights. The one exception is the Super User: the account that originally registered the company always has full access everywhere and is never limited by department rights, no matter how restrictive they are for everyone else.</p>
            </div>
            <div class="docs-leaf" id="transport-directory">
                <h4>Transport Directory</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-transport-directory.svg') }}" alt="Transport Directory flow">
                </div>
                <p>Reference lists of seaports and airports that populate the origin/destination dropdowns on enquiries, jobs and bills of lading, so your team picks from a consistent list instead of typing free text. Keeping this list current saves your team from typing (and mistyping) the same port names over and over.</p>
            </div>
            <div class="docs-leaf" id="predefined-data">
                <h4>Predefined Data</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-predefined-data.svg') }}" alt="Predefined Data flow">
                </div>
                <p>A collection of reference lists used across sales and operations: the logistics services and activities you offer, standard package codes, container types, Incoterms, and currencies. Most of these are simple lookups; services and activities can be extended as your business grows. Container Types, Incoterms and Currencies come pre-loaded as standard reference values, while Services and Activities are yours to extend as you add capabilities.</p>
            </div>
            <div class="docs-leaf" id="banks">
                <h4>Banks</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-banks.svg') }}" alt="Banks flow">
                </div>
                <p>Your company's bank account directory &mdash; the accounts that appear as payment and collection sources everywhere else in the app, along with the transfer details (IBAN, SWIFT) needed for wire instructions. Every payment and collection you record has to point at one of these accounts, so this list should be set up before you process your first transaction.</p>
            </div>
            <div class="docs-leaf" id="descriptions">
                <h4>Descriptions</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-descriptions.svg') }}" alt="Descriptions flow">
                </div>
                <p>A catalog of reusable line-item descriptions, each linked to the sales and purchase accounts it should post to. It keeps your invoice wording consistent and makes sure the right account gets credited or debited every time, without your team having to remember which account goes with which line item.</p>
            </div>
            <div class="docs-leaf" id="units">
                <h4>Units</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-units.svg') }}" alt="Units flow">
                </div>
                <p>Your units of measure &mdash; kilograms, cartons, pieces, and so on &mdash; used wherever a quantity is entered on an item or invoice line. Getting this right once means every quantity typed elsewhere in the app is consistent and comparable.</p>
            </div>
            <div class="docs-leaf" id="salesperson">
                <h4>Salesperson</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-salesperson.svg') }}" alt="Salesperson flow">
                </div>
                <p>A simple directory of your sales team, used to attribute jobs and invoices to the person who sold them, and to power the salesperson leaderboard on the Sales Overview report. It's a lightweight list on purpose &mdash; just enough to attribute performance without turning it into a second HR system.</p>
            </div>
        </div>

        <div class="docs-module" id="settings">
            <span class="docs-module-badge"><i class="bi bi-sliders"></i> Settings</span>
            <h2>Settings</h2>
            <p class="docs-desc">Your personal account, company identity, invoice print behaviour, and Saudi ZATCA e-invoicing integration &mdash; each on its own tab in the Settings sidebar.</p>
            <div class="docs-flowchart">
                <img src="{{ asset('img/docs/flow-settings.svg') }}" alt="Settings flow">
            </div>
            <div class="docs-leaf" id="settings-account">
                <h4>Account</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-settings-account.svg') }}" alt="Account flow">
                </div>
                <p>Your own personal profile &mdash; separate from the company-wide settings below. Every logged-in user, from the Super User to a department employee, has one: update your display name, phone number, profile photo, and password from here. Because it's tied to you personally rather than the company, changing it never affects what anyone else in your team sees.</p>
            </div>
            <div class="docs-leaf" id="settings-company">
                <h4>Manage Business</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-settings-company.svg') }}" alt="Manage Business flow">
                </div>
                <p>Your company's identity as it appears on every invoice and official document: business name in English and Arabic, registered address, commercial registration and VAT numbers, default terms, and your logo and authorized signature. Get this right once at setup and you won't need to touch it again unless your business details change.</p>
            </div>
            <div class="docs-leaf" id="settings-invoice">
                <h4>Invoice Settings</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-settings-invoice.svg') }}" alt="Invoice Settings flow">
                </div>
                <p>Controls exactly what shows up on your printed and emailed invoices &mdash; the color theme, which optional columns appear (item descriptions, alternate units, discounts), and which logistics details (AWB/HBL numbers, Incoterm, port of loading/discharge, voyage or flight number) are relevant to how your business ships. Because these are toggles rather than a redesign, you can tailor the layout to your business without needing a developer.</p>
            </div>
            <div class="docs-leaf" id="settings-zatca">
                <h4>Zatca Integration</h4>
                <div class="docs-leaf-flow">
                    <img src="{{ asset('img/docs/leaf/flow-settings-zatca.svg') }}" alt="Zatca Integration flow">
                </div>
                <p>One-time setup to register your company (or an individual branch) for Saudi ZATCA Phase 2 e-invoicing &mdash; your tax registration details and branch address. Once registered, every customer invoice you issue is generated with the compliant QR code and digital signature ZATCA requires automatically. This is a one-time setup per branch &mdash; once it's done, ZATCA compliance happens automatically on every invoice from then on.</p>
            </div>
        </div>

    </div>

</div>





<!-- ==========================================
CTA
========================================== -->

<section class="cta-section">

    <div class="container">

        <div class="cta-box">

            <h2>
                Ready To See It In Your Own Data?
            </h2>

            <p>
                Create your free account and walk through your first quotation to invoice today.
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

<script>
    (function () {
        const links = document.querySelectorAll('.docs-toc a');
        const sections = Array.from(links).map(a => document.querySelector(a.getAttribute('href')));

        function setActive() {
            let currentIndex = 0;
            sections.forEach((section, i) => {
                if (section && section.getBoundingClientRect().top - 120 <= 0) {
                    currentIndex = i;
                }
            });
            links.forEach((a, i) => a.classList.toggle('active', i === currentIndex));
        }

        document.addEventListener('scroll', setActive);
        setActive();
    })();
</script>

</body>

</html>
