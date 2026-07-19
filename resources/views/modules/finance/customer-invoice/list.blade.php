@section('js','customer_invoice')
@section('page-title','Customer Invoice')
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#customerInvoiceWorkflowModal"
            title="How customer invoices work">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">How it works</span>
    </button>
@endpush
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div id="filterPanel" class="card shadow-sm border-0 d-none">

            <!-- Header -->
            <div class="card-header bg-light border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">Advanced Filters</h6>
                </div>
            </div>

            <div class="card-body">

                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <!-- Date Range Section -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row g-3 align-items-end">

                            {{--<div class="col-md-2">
                                <label class="form-label fw-medium">Date Range</label>
                                <select class="tom-select avoid-filter" id="presetDateRange">
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

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Invoice Date</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                           value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                    <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                           value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                                </div>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Customer</label>
                                <x-common.customers multiple="true" :value="request()->query('customer') ? [(int) request()->query('customer')] : null"></x-common.customers>
                            </div>

                            <div class="col-md-2 form-filter">
                                <label class="form-label fw-medium">Status</label>
                                <select class="tom-select avoid-filter" name="filter-status" id="filter-status" size="3" placeholder="All Status">
                                    <option value="all" selected>All Status</option>
                                    <option value="1">Draft</option>
                                    <option value="3">Approved</option>
                                    <option value="5">Cancelled</option>
                                </select>
                            </div>

                            <div class="col-md-2 form-filter">
                                <label class="form-label fw-medium">Payment Status</label>
                                <select class="tom-select avoid-filter" name="filter-payment-status" id="filter-payment-status" size="3" placeholder="All Payments">
                                    <option value="all" selected>All Payments</option>
                                    <option value="paid">Paid</option>
                                    <option value="partial">Partially Paid</option>
                                    <option value="unpaid">Unpaid</option>
                                </select>
                            </div>

                            <div class="col-md-2 form-filter">
                                <label class="form-label fw-medium">Invoice Type</label>
                                <select class="tom-select avoid-filter" name="filter-overdue" id="filter-overdue">
                                    <option value="all" selected>All Invoices</option>
                                    <option value="overdue">Overdue Invoices</option>
                                    <option value="non_due">Non-Due Invoices</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- Action buttons -->
                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0">
                @if(isset($job_no))
                    <h3 class="fw-bold text-muted bg-info-subtle rounded p-3">
                        {{ $job_no }}
                    </h3>
                @endif

                {{--<div class="gap-4">
                    <ul class="nav status-tabs align-items-center border-bottom" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                <span><i class="bi bi-clock text-warning me-1"></i> Draft -</span>
                                <span class="status-count ms-2" id="draftCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                <span><i class="bi bi-check-circle me-1"></i> Approved -</span>
                                <span class="status-count ms-2" id="approvedCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle"></i> Cancelled -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>

                    </ul>
                </div>--}}
            </div>
            <div class="d-flex justify-content-between">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-primary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        Filter
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new" data-loader-id="{{ $job_id ?? 'list' }}">New
                    Customer Invoice
                </button>
            </div>
        </div>


        <div class="container-fluid pb-3">
            <div class="row g-3">

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-body-tertiary px-4 py-3 h-100">
                        <h6 class="text-uppercase text-muted fw-semibold small mb-3" style="letter-spacing:.03em;">Summary</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="allCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">Total Invoices</small>
                            </div>
                            <div class="col-4">
                                <div id="approvedCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">Approved</small>
                            </div>
                            <div class="col-4">
                                <div id="draftCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">Draft</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-success-subtle px-4 py-3 h-100">
                        <h6 class="text-uppercase text-success-emphasis fw-semibold small mb-3" style="letter-spacing:.03em;">Approved Invoices</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="total_approved_sub" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">Total Amount</small>
                            </div>
                            <div class="col-4">
                                <div id="total_approved_tax" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">Total Tax</small>
                            </div>
                            <div class="col-4">
                                <div id="total_approved_grand" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">Net Sales</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-warning-subtle px-4 py-3 h-100">
                        <h6 class="text-uppercase text-warning-emphasis fw-semibold small mb-3" style="letter-spacing:.03em;">Draft Invoices</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="total_draft_sub" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">Total Amount</small>
                            </div>
                            <div class="col-4">
                                <div id="total_draft_tax" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">Total Tax</small>
                            </div>
                            <div class="col-4">
                                <div id="total_draft_grand" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">Net Sales</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
            </div>

            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Job" data-model-size="lg">
                    <thead>
                    <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                        <th>Invoice #</th>
                        <th>Job</th>
                        <th>Customer</th>
                        <th class="text-end">Excl. VAT</th>
                        <th class="text-end">Tax</th>
                        <th class="text-end">Balance Due</th>
                        <th>Dates</th>
                        <th class="text-end">Aging</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.email.send-email')
    @include('modules.finance.customer-invoice.customer-invoice-view')

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

    <!-- Customer Invoice Workflow Modal -->
    <div class="modal fade" id="customerInvoiceWorkflowModal" tabindex="-1" aria-labelledby="customerInvoiceWorkflowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="customerInvoiceWorkflowModalLabel">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>Customer Invoice Workflow
                        </h5>
                        <p class="text-muted small mb-0">How customer invoices move through your system</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3 pb-4">

                    <!-- Flowchart -->
                    <div class="d-flex flex-column align-items-center gap-0">

                        <div class="wf-box wf-pending">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 1</div>
                            <i class="bi bi-file-earmark-plus me-1"></i> Create Customer Invoice
                            <div class="wf-badge bg-secondary text-white">Draft</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-action">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 2</div>
                            <i class="bi bi-send me-1"></i> Send to Customer
                            <div class="wf-badge bg-info text-white">Sent</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <!-- Decision -->
                        <div class="wf-box wf-decision">
                            <i class="bi bi-question-circle me-1"></i> Approval Decision
                        </div>

                        <div class="d-flex justify-content-center gap-5 w-100 mt-0">

                            <!-- Left: Approved path -->
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-success">
                                    <i class="bi bi-check-circle me-1"></i> Approved
                                    <div class="wf-badge bg-success text-white">Approved</div>
                                </div>
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-job">
                                    <i class="bi bi-cash-coin me-1"></i> Ready for Collection
                                    <div class="wf-badge text-white" style="background:#0dcaf0;">Collections Module</div>
                                </div>
                            </div>

                            <!-- Right: Rejected path -->
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-danger">
                                    <i class="bi bi-x-circle me-1"></i> Rejected
                                    <div class="wf-badge bg-danger text-white">Rejected</div>
                                </div>
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-danger">
                                    <i class="bi bi-slash-circle me-1"></i> Cancelled
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
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f8f9fa;border:1px solid #6c757d;">
                                <span class="badge bg-secondary">Draft</span>
                                <small class="text-muted">Being prepared</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0f7ff;border:1px solid #0d6efd;">
                                <span class="badge bg-info text-white">Sent</span>
                                <small class="text-muted">Awaiting customer response</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fff4;border:1px solid #198754;">
                                <span class="badge bg-success">Approved</span>
                                <small class="text-muted">Ready for collection</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fff5f5;border:1px solid #dc3545;">
                                <span class="badge bg-danger">Rejected</span>
                                <small class="text-muted">Customer declined</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fff5f5;border:1px solid #dc3545;">
                                <span class="badge bg-danger">Cancelled</span>
                                <small class="text-muted">Withdrawn</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fdff;border:1px solid #0dcaf0;">
                                <span class="badge" style="background:#0dcaf0;">Converted</span>
                                <small class="text-muted">Turned into a collection</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> Create Customer Invoice
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<style>
    /* Clean flat table — no floating row cards, just a thin divider
       between rows, matching a standard finance-app list. */
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

    .x-small {
        font-size: 0.65rem;
    }
</style>

@if(request()->query('customer'))
    <script>
        // Deep-linked from a customer's "Find invoices" action — reveal the
        // Advanced Filters panel so it's obvious the list is already filtered.
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var panel = document.getElementById('filterPanel');
                if (panel && panel.classList.contains('d-none')) {
                    document.getElementById('filter-box')?.click();
                }
            }, 300);
        });
    </script>
@endif
