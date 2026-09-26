@extends('website.layout')

@section('title', 'Documentation — Guides for Every Flikma Module')
@section('meta_description', 'Flikma documentation: step-by-step guides for freight operations, bills of lading, billing and finance, AI document scanning, AI expense capture, ZATCA Phase 2 onboarding, payroll and reporting.')
@section('meta_keywords', 'Flikma documentation, freight forwarding software help, ZATCA onboarding guide, logistics ERP user guide, AI document scanning guide, freight software tutorial')

@section('content')

    @php
        $groups = [
            'Getting Started' => [
                ['id' => 'overview',   'icon' => 'bi-speedometer2',  'title' => 'System Overview',      'body' => 'Flikma is built around one object: the job. Understanding how a job file holds the shipment, its documents, its charges and its ledger entries makes the rest of the system obvious.'],
                ['id' => 'workflow',   'icon' => 'bi-diagram-3',     'title' => 'The Freight Workflow', 'body' => 'Enquiry, quotation, job, bill of lading, invoice, collection. Each stage feeds the next, and nothing is re-keyed. This is the single most important page to read first.'],
            ],
            'Sales & Quotation' => [
                ['id' => 'crm',        'icon' => 'bi-people',        'title' => 'Customers &amp; Parties', 'body' => 'Create customers, prospects and suppliers. Set credit limits, currency, payment terms and the VAT registration number that will drive your ZATCA invoice type.'],
                ['id' => 'quotation',  'icon' => 'bi-file-earmark-text', 'title' => 'Enquiries &amp; Quotations', 'body' => 'Capture the request, build a multi-leg quotation with real carrier rates, set an expiry, and convert the won quote into a job in one click.'],
            ],
            'Operations' => [
                ['id' => 'operations', 'icon' => 'bi-globe-americas', 'title' => 'Jobs &amp; Shipments', 'body' => 'Track every leg with POL, POD, place of receipt and final destination. Record containers, packages and batches, and set the milestone dates your customer asks about.'],
                ['id' => 'bl',         'icon' => 'bi-file-earmark-check', 'title' => 'Bills of Lading', 'body' => 'Generate airway bills, sea waybills and road waybills from the job, in the print format the carrier accepts, in English and Arabic.'],
            ],
            'Finance' => [
                ['id' => 'finance',    'icon' => 'bi-receipt',       'title' => 'Invoicing &amp; Collections', 'body' => 'Proforma invoices for advances, customer invoices, supplier invoices, credit notes, payments and collections — all posting to the ledger.'],
                ['id' => 'reports',    'icon' => 'bi-graph-up-arrow', 'title' => 'Reports &amp; Tax', 'body' => 'Trial balance, P&L, aging, input and output tax. Because every module writes to one ledger, these reports are current rather than reconstructed.'],
            ],
            'AI &amp; Automation' => [
                ['id' => 'ai',         'icon' => 'bi-stars',         'title' => 'AI Document Scanning', 'body' => 'Upload a supplier invoice and let AI extract the header, line items, VAT number and totals. Review before it posts. Never posts to the ledger automatically.'],
                ['id' => 'expenses',   'icon' => 'bi-wallet2',       'title' => 'AI Expense Capture', 'body' => 'Photograph a receipt, let AI identify the vendor and the expense head, allocate it to a job if it belongs to one, and post it.'],
            ],
            'Compliance &amp; HR' => [
                ['id' => 'zatca',      'icon' => 'bi-shield-check',  'title' => 'ZATCA Phase 2 Onboarding', 'body' => 'From CSR generation to a verified first clearance. Run the steps in order — the gateway will reject anything out of sequence.'],
                ['id' => 'payroll',    'icon' => 'bi-people-fill',   'title' => 'Payroll &amp; Attendance', 'body' => 'Basic salary structures, monthly payroll, attendance and employee loans, posted straight to the ledger.'],
            ],
        ];
    @endphp

    <style>
        .doc-sidebar { position: sticky; top: 86px; max-height: calc(100vh - 110px); overflow-y: auto; border: 1.5px solid var(--line); border-radius: 16px; background: #fff; padding: 1.25rem; }
        .doc-sidebar::-webkit-scrollbar { width: 4px; }
        .doc-sidebar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 50px; }
        .sidebar-group-label { font-size: .62rem; font-weight: 700; letter-spacing: 1.3px; text-transform: uppercase; color: var(--ink-ghost); margin: 1.1rem 0 .5rem; }
        .sidebar-group-label:first-of-type { margin-top: .25rem; }
        .sidebar-link { display: flex; align-items: center; gap: .6rem; font-size: .85rem; font-weight: 500; color: var(--ink-muted); padding: .5rem .7rem; border-left: 2px solid transparent; border-radius: 0 8px 8px 0; transition: all .18s; }
        .sidebar-link:hover { background: var(--surface); color: var(--ink); }
        .sidebar-link.active { background: rgba(0, 201, 123, .07); border-left-color: var(--emerald); color: var(--emerald-dim); font-weight: 600; }
        .sidebar-link i { font-size: .95rem; width: 1.1rem; text-align: center; flex-shrink: 0; }
        .doc-search { position: relative; margin-bottom: .5rem; }
        .doc-search input { width: 100%; background: var(--surface); border: 1.5px solid transparent; border-radius: 10px; padding: .6rem .75rem .6rem 2.1rem; font-size: .85rem; }
        .doc-search input:focus { outline: none; border-color: var(--emerald); background: #fff; box-shadow: 0 0 0 4px var(--emerald-glow); }
        .doc-search i { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: var(--ink-ghost); font-size: .9rem; pointer-events: none; }
        .doc-card { border: 1.5px solid var(--line); border-radius: 16px; background: #fff; padding: 1.5rem; height: 100%; transition: all .25s; }
        .doc-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(10, 15, 30, .07); }
        .doc-tip { background: rgba(0, 201, 123, .07); border-left: 3px solid var(--emerald); border-radius: 0 10px 10px 0; padding: .85rem 1rem; font-size: .85rem; color: var(--ink-muted); line-height: 1.65; }
        .doc-tip strong { color: var(--ink); }
        .step-list { counter-reset: ds; list-style: none; padding: 0; margin: 0; }
        .step-list li { counter-increment: ds; display: flex; gap: .85rem; align-items: flex-start; margin-bottom: .8rem; font-size: .88rem; color: var(--ink-muted); line-height: 1.65; }
        .step-list li::before { content: counter(ds); flex-shrink: 0; width: 22px; height: 22px; border-radius: 50%; background: var(--emerald-soft); color: var(--emerald-dim); font-size: .7rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin-top: 1px; }
        .step-list strong { color: var(--ink); font-weight: 600; }
    </style>

    <!-- ════════════════ HERO ════════════════ -->
    <header class="page-hero">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="hero-pill mb-3"><i class="bi bi-journal-text"></i> Documentation</div>
                    <h1 class="mb-3">Everything you need to run Flikma</h1>
                    <p class="mb-0" style="max-width:600px;">
                        Guides for every module, written for the people who actually use them &mdash; the
                        operations coordinator, the documentation clerk and the finance manager.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="row g-3">
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--emerald-dim);">14</div><div class="chip-label">Guides</div></div></div>
                        <div class="col-6"><div class="stat-chip"><div class="chip-value" style="color:var(--blue);">AR/EN</div><div class="chip-label">Bilingual</div></div></div>
                    </div>
                    <a href="{{ url('/contact') }}" class="btn-hero-primary w-100 mt-3 justify-content-center">Still stuck? Ask us <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </header>

    <section class="fk-section">
        <div class="container">
            <div class="row g-4">

                <!-- ══════ SIDEBAR ══════ -->
                <div class="col-lg-3">
                    <div class="doc-sidebar" data-spy-nav>
                        <div class="doc-search">
                            <i class="bi bi-search"></i>
                            <input type="search" id="docFilter" placeholder="Filter guides..." aria-label="Filter documentation">
                        </div>

                        @foreach ($groups as $group => $items)
                            <div class="sidebar-group-label doc-item">{{ $group }}</div>
                            @foreach ($items as $item)
                                <a href="#{{ $item['id'] }}" class="sidebar-link doc-item">
                                    <i class="bi {{ $item['icon'] }}"></i>
                                    <span>{!! $item['title'] !!}</span>
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <!-- ══════ CONTENT ══════ -->
                <div class="col-lg-9">

                    {{-- START HERE --}}
                    <section id="overview" class="mb-5">
                        <div class="rounded-4 p-4 p-md-5 reveal" style="background:var(--ink);">
                            <div class="row g-4 align-items-center">
                                <div class="col-lg-8">
                                    <div class="section-label mb-3" style="color:var(--emerald);">Start Here</div>
                                    <h2 class="mb-3" style="color:#fff;font-size:1.6rem;font-weight:800;">One object runs this system</h2>
                                    <p class="mb-0" style="color:rgba(255,255,255,.6);line-height:1.75;">
                                        A <strong style="color:#fff;">job</strong> is a shipment. It is created from a
                                        won quotation and it holds everything: the route and its legs, the carrier and
                                        voyage, the containers and packages, the parties, the documents, the buy and
                                        sell charges, and the invoice at the end.
                                    </p>
                                    <p class="mb-0 mt-3" style="color:rgba(255,255,255,.6);line-height:1.75;">
                                        Every screen in Flikma is either creating the job, adding to it, or reporting
                                        on it. If you ever wonder where something belongs, the answer is almost
                                        always the job file.
                                    </p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex flex-column gap-2">
                                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(255,255,255,.05);font-size:.82rem;color:rgba(255,255,255,.75);"><span class="mode-chip" style="background:var(--emerald);color:var(--ink);font-size:.62rem;">1</span> Enquiry arrives</div>
                                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(255,255,255,.05);font-size:.82rem;color:rgba(255,255,255,.75);"><span class="mode-chip" style="background:var(--emerald);color:var(--ink);font-size:.62rem;">2</span> You quote it</div>
                                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(255,255,255,.05);font-size:.82rem;color:rgba(255,255,255,.75);"><span class="mode-chip" style="background:var(--emerald);color:var(--ink);font-size:.62rem;">3</span> Convert &rarr; job</div>
                                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(255,255,255,.05);font-size:.82rem;color:rgba(255,255,255,.75);"><span class="mode-chip" style="background:var(--emerald);color:var(--ink);font-size:.62rem;">4</span> Documents &amp; charges</div>
                                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(255,255,255,.05);font-size:.82rem;color:rgba(255,255,255,.75);"><span class="mode-chip" style="background:var(--emerald);color:var(--ink);font-size:.62rem;">5</span> Invoice &rarr; ZATCA</div>
                                        <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(255,255,255,.05);font-size:.82rem;color:rgba(255,255,255,.75);"><span class="mode-chip" style="background:var(--emerald);color:var(--ink);font-size:.62rem;">6</span> Collect &amp; close</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- WORKFLOW --}}
                    <section id="workflow" class="mb-5">
                        <h2 class="mb-2" style="font-size:1.5rem;font-weight:800;">The Freight Workflow</h2>
                        <p class="mb-4" style="color:var(--ink-muted);line-height:1.75;">
                            The six stages of a shipment in Flikma, and what happens at each one.
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4 reveal">
                                <div class="doc-card">
                                    <div class="mode-chip mb-3" style="background:var(--emerald-soft);color:var(--emerald-dim);font-size:.63rem;">STAGE 1</div>
                                    <h5 class="feat-title mb-2" style="font-size:1rem;">Enquiry</h5>
                                    <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                        Capture what the customer actually asked for: mode, origin, destination,
                                        cargo, dimensions, deadline. Attach their email if it arrived that way.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 reveal">
                                <div class="doc-card">
                                    <div class="mode-chip mb-3" style="background:rgba(58,107,255,.1);color:var(--blue);font-size:.63rem;">STAGE 2</div>
                                    <h5 class="feat-title mb-2" style="font-size:1rem;">Quotation</h5>
                                    <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                        Build leg by leg with real carrier rates, add your margin, set validity.
                                        When accepted, convert &mdash; the quote becomes the job, charges and all.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 reveal">
                                <div class="doc-card">
                                    <div class="mode-chip mb-3" style="background:rgba(6,182,212,.12);color:#0891b2;font-size:.63rem;">STAGE 3</div>
                                    <h5 class="feat-title mb-2" style="font-size:1rem;">Job</h5>
                                    <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                        The live shipment. Add containers, packages and batches; set ETD, ETA and
                                        actuals; record the shipper, consignee and notify party.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 reveal">
                                <div class="doc-card">
                                    <div class="mode-chip mb-3" style="background:rgba(11,23,54,.08);color:var(--navy);font-size:.63rem;">STAGE 4</div>
                                    <h5 class="feat-title mb-2" style="font-size:1rem;">Documents</h5>
                                    <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                        Print the airway bill, sea waybill or road waybill from the job. Track which
                                        documents are still outstanding before the cutoff.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 reveal">
                                <div class="doc-card">
                                    <div class="mode-chip mb-3" style="background:rgba(0,201,123,.1);color:var(--emerald-dim);font-size:.63rem;">STAGE 5</div>
                                    <h5 class="feat-title mb-2" style="font-size:1rem;">Invoice</h5>
                                    <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                        Issue a proforma for the deposit, then the final invoice. Flikma decides tax
                                        versus simplified from the customer and clears it with ZATCA.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 reveal">
                                <div class="doc-card">
                                    <div class="mode-chip mb-3" style="background:rgba(124,58,237,.1);color:var(--violet);font-size:.63rem;">STAGE 6</div>
                                    <h5 class="feat-title mb-2" style="font-size:1rem;">Collection</h5>
                                    <p class="small mb-0" style="color:var(--ink-muted);line-height:1.7;">
                                        Record receipts against the invoice, watch the aging, and close the job with
                                        its final margin visible.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <hr style="border-color:var(--line);">

                    {{-- CRM --}}
                    <section id="crm" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-people"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">Customers &amp; Parties</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">Sales &amp; Quotation</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            Customers, prospects and suppliers all live here, and each carries the data that drives
                            everything downstream.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Create the customer</strong> with their legal name, trading name and billing branch.</span></li>
                            <li><span><strong>Enter their VAT number</strong> if they are registered. This single field determines whether Flikma issues a tax invoice or a simplified invoice — getting it wrong causes a ZATCA rejection.</span></li>
                            <li><span><strong>Set credit limit, currency and payment terms</strong> so the system can flag customers who exceed their limit.</span></li>
                            <li><span><strong>Add suppliers and carriers</strong> with their rates and terms. Existing customers and suppliers can be imported from CSV.</span></li>
                        </ol>
                        <div class="doc-tip">
                            <strong>Tip:</strong> A customer with a VAT number is treated as a B2B buyer and receives a
                            tax invoice requiring ZATCA clearance. Leave the VAT number empty and they receive a
                            simplified invoice. The system decides automatically — you never choose by hand.
                        </div>
                    </section>

                    {{-- QUOTATION --}}
                    <section id="quotation" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-file-earmark-text"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">Enquiries &amp; Quotations</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">Sales &amp; Quotation</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            The quotation wizard walks you through a shipment leg by leg, so a multi-leg move
                            (JED &rarr; RUH, or DXB &rarr; BAH &rarr; JED) is built without re-entering the lane each time.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Log the enquiry</strong> with the customer's requirement and the deadline they gave you.</span></li>
                            <li><span><strong>Add each leg</strong> &mdash; mode, origin, destination, carrier, service level and transit time.</span></li>
                            <li><span><strong>Enter charges per leg</strong> against your own logistics service codes, so margin is calculated rather than guessed.</span></li>
                            <li><span><strong>Set the validity date</strong> and send the quotation to the customer.</span></li>
                            <li><span><strong>Mark it won and convert to a job</strong> &mdash; charges, containers and packages carry across, nothing is retyped.</span></li>
                        </ol>
                        <div class="doc-tip">
                            <strong>Tip:</strong> Record why you lost a lost quotation. After a few months Flikma will
                            show you the lanes and rates where you are consistently uncompetitive.
                        </div>
                    </section>

                    {{-- OPERATIONS --}}
                    <section id="operations" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:var(--emerald-soft);color:var(--emerald-dim);"><i class="bi bi-globe-americas"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">Jobs &amp; Shipments</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">Operations</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            The job is the live shipment. This is where the cargo actually is, and it is the screen
                            your operations team will live in.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Set the route</strong> &mdash; POL, POD, place of receipt, place of delivery and final destination, plus the shipment mode.</span></li>
                            <li><span><strong>Choose the Incoterm</strong> (2020 rules). It determines who is responsible for duties, and it flows onto the invoice and the B/L.</span></li>
                            <li><span><strong>Add containers, packages and batches</strong> with dimensions, weight, seal numbers and marks.</span></li>
                            <li><span><strong>Set the schedule</strong> &mdash; ETD, ETA, then ATD and ATA once the cargo actually moves. Overdue milestones are highlighted automatically.</span></li>
                            <li><span><strong>Record the parties</strong> &mdash; shipper, consignee, notify party and agent &mdash; which carry straight onto the bill of lading.</span></li>
                            <li><span><strong>Watch the margin</strong> as buy rates and sell rates are added. It updates live, before the job closes.</span></li>
                        </ol>
                    </section>

                    {{-- BILLS OF LADING --}}
                    <section id="bl" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:rgba(11,23,54,.07);color:var(--navy);"><i class="bi bi-file-earmark-check"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">Bills of Lading</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">Operations</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            Carrier documents are generated from the job, not typed from scratch. Flikma pulls the
                            parties, the route, the cargo and the references across for you.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Open the job</strong> and go to its documents tab.</span></li>
                            <li><span><strong>Choose the document type</strong> &mdash; airway bill for air, sea waybill for sea, road waybill for cross-border road.</span></li>
                            <li><span><strong>Review the pulled-through data</strong> and correct anything the carrier requires in a different format.</span></li>
                            <li><span><strong>Select a print format</strong> matching what the carrier and customs accept. All formats print in English and Arabic.</span></li>
                            <li><span><strong>Print or download the PDF</strong>, then mark the document as issued so outstanding items are tracked.</span></li>
                        </ol>
                        <div class="doc-tip">
                            <strong>Tip:</strong> For air freight, issue the house AWB and the master AWB as separate
                            documents. Customs often needs them independently.
                        </div>
                    </section>

                    {{-- FINANCE --}}
                    <section id="finance" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:rgba(58,107,255,.09);color:var(--blue);"><i class="bi bi-receipt"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">Invoicing &amp; Collections</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">Finance</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            Six document families, all posting to one ledger. Nothing needs re-importing from another system.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Proforma invoice</strong> &mdash; bill the customer up front for the deposit. It is not a ZATCA document.</span></li>
                            <li><span><strong>Customer invoice</strong> &mdash; the final bill, drawn from the job's sell charges. This is the ZATCA document.</span></li>
                            <li><span><strong>Supplier invoice</strong> &mdash; what the carrier bills you. Scan it in rather than typing it, see below.</span></li>
                            <li><span><strong>Payment or collection</strong> &mdash; record money received, applying it to one or many invoices.</span></li>
                            <li><span><strong>Credit or debit note</strong> &mdash; for corrections after invoicing, reported through the ZATCA pipeline.</span></li>
                        </ol>
                    </section>

                    {{-- AI SCANNING --}}
                    <section id="ai" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:rgba(79,70,229,.09);color:var(--indigo);"><i class="bi bi-stars"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">AI Document Scanning</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">AI &amp; Automation</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            This is the feature that gives you the time back. Scan first, review second, post third.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Open a supplier invoice</strong> and click <strong>Scan</strong>. Alternatively, use the document scanner from any job.</span></li>
                            <li><span><strong>Upload the file</strong> &mdash; PDF, JPG or PNG, up to 20 MB, single or multi-page.</span></li>
                            <li><span><strong>Let Flikma read it.</strong> It extracts the invoice number, invoice and due dates, supplier, supplier VAT number, the charge lines, the subtotal, VAT and grand total.</span></li>
                            <li><span><strong>Review the pre-filled form.</strong> Every field is editable. AI highlights what it is confident about so you can scan quickly.</span></li>
                            <li><span><strong>Confirm and post.</strong> The document is attached to the invoice permanently, and the entry goes to the ledger.</span></li>
                        </ol>
                        <div class="doc-tip">
                            <strong>Important:</strong> Flikma will never post to your ledger without a human confirming.
                            If a field is wrong, correct it — the correction is what teaches the suggestion next time.
                            Your supplier and description lists are supplied to the AI as context, so the longer you
                            use it the better your own data fits it.
                        </div>
                    </section>

                    {{-- AI EXPENSES --}}
                    <section id="expenses" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:rgba(239,68,68,.09);color:var(--red);"><i class="bi bi-wallet2"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">AI Expense Capture</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">AI &amp; Automation</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            For the small spending that never justifies a purchase order &mdash; fuel, couriers,
                            customs agent fees, port charges and receipts.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Photograph the receipt</strong> or upload an image file.</span></li>
                            <li><span><strong>Flikma identifies the vendor</strong> and matches it against your supplier list, flagging anything new for you to confirm.</span></li>
                            <li><span><strong>Accept the expense head suggestion</strong> or pick your own.</span></li>
                            <li><span><strong>Allocate it to a job</strong> if it belongs to a specific shipment, or leave it as a general expense.</span></li>
                            <li><span><strong>Post it.</strong> The VAT is separated automatically, so your input tax position is already built when you file.</span></li>
                        </ol>
                    </section>

                    {{-- ZATCA --}}
                    <section id="zatca" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:var(--emerald);color:var(--ink);"><i class="bi bi-shield-check"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">ZATCA Phase 2 Onboarding</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">Compliance &amp; HR</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            Follow these steps in order. The Fatoora gateway rejects anything submitted out of
                            sequence, and a rejected onboarding has to start over.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Enter your company and branch details</strong> on the ZATCA registration screen, including your VAT registration number. Flikma registers your branch industry as <em>Logistic</em>.</span></li>
                            <li><span><strong>Generate the CSR</strong> and upload the resulting certificate into the portal.</span></li>
                            <li><span><strong>Request the compliance CSID</strong> from the simulation environment and save it here.</span></li>
                            <li><span><strong>Run the test invoices</strong> required by ZATCA and confirm they are accepted.</span></li>
                            <li><span><strong>Request the production CSID</strong> using the compliance token, and save it.</span></li>
                            <li><span><strong>Issue your first live invoice</strong> and verify the clearance response and the QR code.</span></li>
                        </ol>
                        <div class="doc-tip">
                            <strong>Tip:</strong> Start this in parallel with your configuration, not after it. Onboarding
                            is gated by the ZATCA side, so it is usually the longest pole in a go-live.
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-3">
                            <a href="https://zatca.gov.sa" target="_blank" rel="noopener" class="btn-ghost-light">
                                Official ZATCA portal <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            <a href="{{ url('/services') }}" class="btn-ghost-light">
                                Get help with onboarding <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </section>

                    {{-- PAYROLL --}}
                    <section id="payroll" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:rgba(124,58,237,.09);color:var(--violet);"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">Payroll &amp; Attendance</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">Compliance &amp; HR</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            The cost of running the office belongs in the P&amp;L, so Flikma runs payroll inside the
                            same system rather than bolting it on afterwards.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Set up each employee's basic salary</strong>, including housing and transport allowances.</span></li>
                            <li><span><strong>Record attendance</strong> on the monthly calendar, so overtime and absences are visible.</span></li>
                            <li><span><strong>Add employee loans</strong> with a deduction schedule that Flikma applies automatically.</span></li>
                            <li><span><strong>Run monthly payroll</strong>, review the totals, and approve.</span></li>
                            <li><span><strong>Export for payment</strong> in a WPS-ready format for Saudi payroll, and the equivalent file for Bahrain and the UAE.</span></li>
                            <li><span><strong>Post to the ledger</strong> so the cost lands in the trial balance in the same period.</span></li>
                        </ol>
                    </section>

                    {{-- REPORTS --}}
                    <section id="reports" class="mb-5 pt-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="feat-icon-box" style="background:rgba(6,182,212,.09);color:var(--cyan);"><i class="bi bi-graph-up-arrow"></i></div>
                            <div>
                                <h2 class="mb-0" style="font-size:1.4rem;font-weight:800;">Reports &amp; Tax</h2>
                                <div style="font-size:.78rem;color:var(--ink-ghost);">Finance</div>
                            </div>
                        </div>
                        <p class="mb-3" style="color:var(--ink-muted);line-height:1.75;">
                            Every module writes to the same ledger, so these reports are always current rather
                            than reconstructed at month-end.
                        </p>
                        <ol class="step-list mb-3">
                            <li><span><strong>Trial balance, balance sheet and P&amp;L</strong> &mdash; check these before you tell anyone the books are closed.</span></li>
                            <li><span><strong>General ledger</strong> &mdash; drill from any line down to the invoice or journal that created it.</span></li>
                            <li><span><strong>Input tax, output tax and tax summary</strong> &mdash; your input tax comes straight from scanned supplier bills, so the return assembles itself.</span></li>
                            <li><span><strong>Customer and supplier aging</strong> plus statements, to run your collections.</span></li>
                            <li><span><strong>Job reports</strong> &mdash; balance, income and provisional margin by job, customer or lane.</span></li>
                            <li><span><strong>Sales and waybill reports</strong> for operational review.</span></li>
                        </ol>
                    </section>

                    <hr style="border-color:var(--line);">

                    <!-- Quick reference grid -->
                    <section class="pt-2">
                        <h2 class="mb-4" style="font-size:1.4rem;font-weight:800;">Quick reference</h2>
                        <div class="row g-3">
                            <div class="col-md-4 reveal">
                                <div class="doc-card">
                                    <i class="bi bi-shield-check" style="font-size:1.4rem;color:var(--emerald-dim);"></i>
                                    <h6 class="feat-title mt-2 mb-1" style="font-size:.95rem;">ZATCA registration</h6>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.6;">Settings &rarr; ZATCA &rarr; Register</p>
                                </div>
                            </div>
                            <div class="col-md-4 reveal">
                                <div class="doc-card">
                                    <i class="bi bi-stars" style="font-size:1.4rem;color:var(--indigo);"></i>
                                    <h6 class="feat-title mt-2 mb-1" style="font-size:.95rem;">Document scanning</h6>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.6;">Finance &rarr; Supplier Invoices &rarr; Scan</p>
                                </div>
                            </div>
                            <div class="col-md-4 reveal">
                                <div class="doc-card">
                                    <i class="bi bi-wallet2" style="font-size:1.4rem;color:var(--red);"></i>
                                    <h6 class="feat-title mt-2 mb-1" style="font-size:.95rem;">Expense capture</h6>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.6;">Finance &rarr; Expenses &rarr; New</p>
                                </div>
                            </div>
                            <div class="col-md-4 reveal">
                                <div class="doc-card">
                                    <i class="bi bi-file-earmark-text" style="font-size:1.4rem;color:var(--blue);"></i>
                                    <h6 class="feat-title mt-2 mb-1" style="font-size:.95rem;">Quotation wizard</h6>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.6;">Sales &rarr; Quotations &rarr; New</p>
                                </div>
                            </div>
                            <div class="col-md-4 reveal">
                                <div class="doc-card">
                                    <i class="bi bi-file-earmark-check" style="font-size:1.4rem;color:var(--navy);"></i>
                                    <h6 class="feat-title mt-2 mb-1" style="font-size:.95rem;">Bills of lading</h6>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.6;">Operations &rarr; Job &rarr; Documents</p>
                                </div>
                            </div>
                            <div class="col-md-4 reveal">
                                <div class="doc-card">
                                    <i class="bi bi-people-fill" style="font-size:1.4rem;color:var(--violet);"></i>
                                    <h6 class="feat-title mt-2 mb-1" style="font-size:.95rem;">Payroll</h6>
                                    <p class="mb-0 small" style="color:var(--ink-muted);line-height:1.6;">Payroll &rarr; Monthly Salary</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </section>

    <!-- ════════════════ CTA ════════════════ -->
    <section class="pb-5">
        <div class="container">
            <div class="cta-banner p-4 p-md-5 text-center reveal">
                <h2 class="mb-3">Cannot find what you need?</h2>
                <p class="mb-4" style="font-size:1.02rem;">
                    Documentation only goes so far. Our team will walk your specific process with you &mdash;
                    on-site in Riyadh, Manama or Dubai, or remotely.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ url('/contact') }}" class="btn-hero-primary">Contact Support <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ url('/services') }}" class="btn-outline-light-fk">View Services</a>
                </div>
            </div>
        </div>
    </section>

@section('extra_scripts')
    <script>
        /* Live-filter the documentation sidebar. */
        (function () {
            var input = document.getElementById('docFilter');
            if (!input) return;
            input.addEventListener('input', function () {
                var q = input.value.toLowerCase().trim();
                document.querySelectorAll('.doc-item').forEach(function (el) {
                    el.style.display = (!q || el.textContent.toLowerCase().indexOf(q) !== -1) ? '' : 'none';
                });
                // Hide a group heading when every link underneath it is filtered out.
                document.querySelectorAll('.sidebar-group-label').forEach(function (head) {
                    if (!q) { head.style.display = ''; return; }
                    var sib = head.nextElementSibling, any = false;
                    while (sib && !sib.classList.contains('sidebar-group-label')) {
                        if (sib.classList.contains('doc-item') && sib.style.display !== 'none') any = true;
                        sib = sib.nextElementSibling;
                    }
                    head.style.display = any ? '' : 'none';
                });
            });
        })();
    </script>
@endsection
