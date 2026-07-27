@section('page-title','Credit Note')
@section('page-sub-title','Manage credit notes and adjustments')
@section('js','credit_note')
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#creditNoteWorkflowModal"
            title="How credit notes work">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">How it works</span>
    </button>
@endpush
<x-app-layout>
    <div class="bg-light py-4">
        <style>
            :root{
                --cn_primary: #0b6aa0;
                --cn_card_bg: #ffffff;
                --cn_radius: 12px;
                --cn_shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            }
            body { background: #f8fafc; }
            .cn-card {
                background: var(--cn_card_bg);
                border-radius: var(--cn_radius);
                box-shadow: var(--cn_shadow);
                border: 1px solid rgba(0,0,0,0.04);
                padding: 1rem 1.25rem;
            }
            .cn-kpi {
                background: var(--cn_card_bg);
                border-radius: var(--cn_radius);
                box-shadow: var(--cn_shadow);
                border: 1px solid rgba(0,0,0,0.04);
                padding: .9rem 1rem;
                transition: box-shadow .2s;
            }
            .cn-kpi:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
            .cn-kpi .kpi-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
            .cn-kpi .kpi-value { font-size: 1.35rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
            .cn-kpi .kpi-sub { font-size: .72rem; color: #94a3b8; }
            .cn-icon-circle { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
            .cn-table th { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; border-bottom-width: 1px; }
            .cn-table td { font-size: .82rem; vertical-align: middle; color: #1e293b; }
            .cn-filter-bar {
                background: var(--cn_card_bg);
                border-radius: var(--cn_radius);
                box-shadow: var(--cn_shadow);
                border: 1px solid rgba(0,0,0,0.04);
                padding: .6rem 1rem;
            }

            /* Clean flat table — no floating row cards, just a thin divider
               between rows, matching the redesigned invoice list pages. */
            #dataTable {
                border-collapse: collapse;
            }

            #dataTable thead th {
                background-color: #fff;
                color: #6c757d;
                font-weight: 600;
                border-bottom: 1px solid #e9ecef;
                padding: 0.65rem 1rem;
                white-space: nowrap;
            }

            #dataTable tbody td {
                padding: 0.65rem 1rem;
                border-bottom: 1px solid #f1f3f5;
                vertical-align: middle;
            }

            #dataTable tbody tr:hover td {
                background-color: #fafbfc;
            }

            #dataTable tbody tr:last-child td {
                border-bottom: none;
            }

            /* Two-line cell convention: bold primary line, muted small caption */
            .cell-primary {
                font-weight: 600;
                color: #212529;
                line-height: 1.3;
            }

            .cell-secondary {
                font-size: 0.75rem;
                color: #868e96;
                line-height: 1.3;
            }
        </style>

        <div class="container-fluid px-lg-5">

            {{-- Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0" style="color:#0f172a;">Credit Notes</h4>
                    <p class="text-muted mb-0 small">Credit note adjustments and refunds</p>
                </div>
                <div class="d-flex gap-2 mt-2 mt-sm-0">
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3" id="filter-box">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" id="new">
                        <i class="bi bi-plus-lg me-1"></i> New Credit Note
                    </button>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row g-3 mb-3">
                <div class="col-lg-3 col-md-6">
                    <div class="cn-kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Total Credit Notes</div>
                            <div class="kpi-value" id="allCount">0</div>
                            <div class="kpi-sub">All statuses</div>
                        </div>
                        <div class="cn-icon-circle" style="background:rgba(11,106,160,0.1);color:#0b6aa0;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="cn-kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Total Amount</div>
                            <div class="kpi-value" id="overall_sales">0.00</div>
                            <div class="kpi-sub">SAR - Grand total</div>
                        </div>
                        <div class="cn-icon-circle" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">Draft</div>
                        <div class="kpi-value text-warning" id="draftCount">0</div>
                        <div class="kpi-sub"><span id="draftTotal">0.00</span> SAR</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">Approved</div>
                        <div class="kpi-value text-success" id="approvedCount">0</div>
                        <div class="kpi-sub"><span id="approvedTotal">0.00</span> SAR</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">Cancelled</div>
                        <div class="kpi-value text-danger" id="cancelledCount">0</div>
                        <div class="kpi-sub"><span id="cancelledTotal">0.00</span> SAR</div>
                    </div>
                </div>
            </div>

            {{-- Filter Panel --}}
            <div id="filterPanel" class="cn-card mb-3 d-none">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="row g-3 align-items-end">
                        {{--<div class="col-md-2">
                            <label class="form-label fw-medium small">Date Range</label>
                            <select class="form-select form-select-sm" id="presetDateRange">
                                <option value="">Custom</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="thisMonth">This Month</option>
                                <option value="lastMonth">Last Month</option>
                                <option value="thisQuarter">This Quarter</option>
                                <option value="lastQuarter">Last Quarter</option>
                                <option value="thisYear">This Year</option>
                                <option value="lastYear">Last Year</option>
                            </select>
                        </div>--}}
                        <div class="col-md-3">
                            <label class="form-label fw-medium small">From Date</label>
                            <input type="date" class="form-control form-control-sm from-date" id="filter-from-date" name="filter-from-date"
                                   value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium small">To Date</label>
                            <input type="date" class="form-control form-control-sm to-date" id="filter-to-date" name="filter-to-date"
                                   value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium small">Customer</label>
                            <x-common.customers multiple></x-common.customers>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium small">Invoice</label>
                            <select class="form-select form-select-sm" id="filter-invoice" name="invoice">
                                <option value="">All Invoices</option>
                                @foreach(\App\Models\Finance\CustomerInvoice\CustomerInvoice::where('status', 3)->get() as $invoice)
                                    <option value="{{ encodeId($invoice->id) }}">{{ $invoice->row_no ?? $invoice->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary btn-sm px-4 rounded-pill" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            {{-- Status Tabs --}}
            <div class="d-flex justify-content-between align-items-start py-3">
                <div class="align-items-center flex-shrink-0">
                    <div class="gap-4">
                        <ul class="nav align-items-center" id="listTabs" role="tablist">
                            <li class="nav-item me-2">
                                <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                                    <span><i class="bi bi-collection me-1"></i> All -</span>
                                    <span class="status-count ms-2" id="tabAllCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item me-2">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                    <span><i class="bi bi-clock me-1"></i> Draft -</span>
                                    <span class="status-count ms-2" id="tabDraftCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item me-2">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                    <span><i class="bi bi-check-circle me-1"></i> Approved -</span>
                                    <span class="status-count ms-2" id="tabApprovedCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                    <span><i class="bi bi-x-circle me-1"></i> Cancelled -</span>
                                    <span class="status-count ms-2" id="tabCancelledCount">0</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="pt-2">
                    <div class="search-box position-relative" style="min-width:200px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control form-control-sm rounded-pill ps-5" placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="cn-card p-0">
                <table class="table align-middle mb-0 dataTable" id="dataTable">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                            <th>Credit Note #</th>
                            <th>Customer</th>
                            <th>Job</th>
                            <th>Invoice</th>
                            <th class="text-end">Excl. VAT</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Total</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

    @include('modules.email.send-email')
    @include('modules.finance.credit-note.credit-note-view')

    {{-- Print frame --}}
    <iframe id="print-frame" style="display:none;"></iframe>

    <style>
        /* Workflow flowchart */
        .wf-box {
            border: 2px solid;
            border-radius: 10px;
            padding: 10px 20px;
            text-align: center;
            min-width: 170px;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.4;
        }
        .wf-box.wf-neutral  { border-color: #6c757d; background: #f8f9fa;  color: #495057; }
        .wf-box.wf-pending  { border-color: #ffc107; background: #fffbf0;  color: #856404; }
        .wf-box.wf-action   { border-color: #0d6efd; background: #f0f7ff;  color: #084298; }
        .wf-box.wf-success  { border-color: #198754; background: #f0fff4;  color: #0f5132; }
        .wf-box.wf-danger   { border-color: #dc3545; background: #fff5f5;  color: #842029; }
        .wf-box.wf-job      { border-color: #0dcaf0; background: #f0fdff;  color: #055160; }
        .wf-box.wf-decision { border-color: #6f42c1; background: #f8f0ff;  color: #432874; }
        .wf-arrow { color: #adb5bd; font-size: 1.3rem; line-height: 1.3; text-align: center; }
        .wf-badge { font-size: 0.7rem; border-radius: 20px; padding: 2px 8px; display: inline-block; margin-top: 4px; }
    </style>

    <!-- Credit Note Workflow Modal -->
    <div class="modal fade" id="creditNoteWorkflowModal" tabindex="-1" aria-labelledby="creditNoteWorkflowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="creditNoteWorkflowModalLabel">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>Credit Note Workflow
                        </h5>
                        <p class="text-muted small mb-0">How credit notes move through your system</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3 pb-4">

                    <!-- Flowchart -->
                    <div class="d-flex flex-column align-items-center gap-0">

                        <div class="wf-box wf-neutral">
                            <i class="bi bi-receipt me-1"></i> Select Customer Invoice &amp; Reason
                            <div class="wf-badge bg-secondary text-white">Approved Customer Invoice</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-pending">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 1</div>
                            <i class="bi bi-file-earmark-minus me-1"></i> Create Credit Note
                            <div class="wf-badge bg-warning text-dark">Draft</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-action">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 2</div>
                            <i class="bi bi-send-check me-1"></i> Approve Credit Note
                            <div class="text-muted mt-1" style="font-size:0.75rem;">Reduces invoice balance &amp; posts to GL</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <!-- Decision -->
                        <div class="wf-box wf-decision">
                            <i class="bi bi-question-circle me-1"></i> Credit Note Outcome
                        </div>

                        <div class="d-flex justify-content-center gap-5 w-100 mt-0">
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-success">
                                    <i class="bi bi-check-circle me-1"></i> Approved
                                    <div class="wf-badge bg-success text-white">Approved</div>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-danger">
                                    <i class="bi bi-x-circle me-1"></i> Cancelled
                                    <div class="wf-badge bg-danger text-white">Cancelled</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Status legend -->
                    <hr class="mt-4">
                    <h6 class="fw-semibold text-muted mb-3 small text-uppercase">Status Guide</h6>
                    <div class="row g-2">
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fffbf0;border:1px solid #ffc107;">
                                <span class="badge bg-warning text-dark">Draft</span>
                                <small class="text-muted">Awaiting approval</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fff4;border:1px solid #198754;">
                                <span class="badge bg-success">Approved</span>
                                <small class="text-muted">Posted &amp; invoice balance reduced</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fff5f5;border:1px solid #dc3545;">
                                <span class="badge bg-danger">Cancelled</span>
                                <small class="text-muted">Credit note called off</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> Create Credit Note
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
