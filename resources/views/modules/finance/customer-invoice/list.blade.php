@section('js','customer_invoice')
@section('page-title', __('Customer Invoice'))
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#customerInvoiceWorkflowModal"
            title="{{ __('How customer invoices work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div id="filterPanel" class="card shadow-sm border-0 d-none">

            <!-- Header -->
            <div class="card-header bg-light border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">{{ __('Advanced Filters') }}</h6>
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
                                <label class="form-label fw-medium">{{ __('Invoice Date') }}</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                           value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                    <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                           value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                                </div>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">{{ __('Customer') }}</label>
                                <x-common.customers multiple="true" :value="request()->query('customer') ? [(int) request()->query('customer')] : null"></x-common.customers>
                            </div>

                            <div class="col-md-2 form-filter">
                                <label class="form-label fw-medium">{{ __('Status') }}</label>
                                <select class="tom-select avoid-filter" name="filter-status" id="filter-status" size="3" placeholder="{{ __('All Status') }}">
                                    <option value="all" selected>{{ __('All Status') }}</option>
                                    <option value="1">{{ __('Draft') }}</option>
                                    <option value="3">{{ __('Approved') }}</option>
                                    <option value="5">{{ __('Cancelled') }}</option>
                                </select>
                            </div>

                            <div class="col-md-2 form-filter">
                                <label class="form-label fw-medium">{{ __('Payment Status') }}</label>
                                <select class="tom-select avoid-filter" name="filter-payment-status" id="filter-payment-status" size="3" placeholder="{{ __('All Payments') }}">
                                    <option value="all" selected>{{ __('All Payments') }}</option>
                                    <option value="paid">{{ __('Paid') }}</option>
                                    <option value="partial">{{ __('Partially Paid') }}</option>
                                    <option value="unpaid">{{ __('Unpaid') }}</option>
                                </select>
                            </div>

                            <div class="col-md-2 form-filter">
                                <label class="form-label fw-medium">{{ __('Invoice Type') }}</label>
                                <select class="tom-select avoid-filter" name="filter-overdue" id="filter-overdue">
                                    <option value="all" selected>{{ __('All Invoices') }}</option>
                                    <option value="overdue">{{ __('Overdue Invoices') }}</option>
                                    <option value="non_due">{{ __('Non-Due Invoices') }}</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- Action buttons -->
                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
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

                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                                <span><i class="bi bi-collection me-1"></i> {{ __('All') }} -</span>
                                <span class="status-count ms-2" id="allCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                <span><i class="bi bi-clock text-secondary me-1"></i> {{ __('Draft') }} -</span>
                                <span class="status-count ms-2" id="draftCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                <span><i class="bi bi-check-circle me-1"></i> {{ __('Approved') }} -</span>
                                <span class="status-count ms-2" id="approvedCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="overdue">
                                <span><i class="bi bi-exclamation-triangle text-danger me-1"></i> {{ __('Overdue Invoice') }} -</span>
                                <span class="status-count ms-2" id="overdueCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="creditnote">
                                <span><i class="bi bi-receipt-cutoff text-warning me-1"></i> {{ __('Credit Note') }} -</span>
                                <span class="status-count ms-2" id="creditnoteCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle"></i> {{ __('Cancelled') }} -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-primary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        {{ __('Filter') }}
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new" data-loader-id="{{ $job_id ?? 'list' }}">{{ __('New Customer Invoice') }}
                </button>
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
                               placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                    </div>
                </div>
            </div>

            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Job" data-model-size="lg">
                    <thead>
                    <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-end">{{ __('Excl. VAT') }}</th>
                        <th class="text-end">{{ __('Tax') }}</th>
                        <th class="text-end">{{ __('Balance Due') }}</th>
                        <th>{{ __('Dates') }}</th>
                        <th class="text-end">{{ __('Aging') }}</th>
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
        /* Workflow flowchart (customer invoice) */
        .fc { display: flex; flex-direction: column; align-items: center; font-size: .85rem; }
        .fc-node { position: relative; text-align: center; padding: 10px 16px; border: 2px solid; border-radius: 12px; min-width: 190px; max-width: 260px; line-height: 1.35; background: #fff; }
        .fc-node small { display: block; font-weight: 400; opacity: .8; font-size: .72rem; margin-top: 2px; }
        .fc-node .fc-step { display: block; font-size: .65rem; letter-spacing: .06em; text-transform: uppercase; opacity: .65; }
        .fc-start, .fc-end { border-radius: 999px; }
        .fc-draft    { border-color: #f59e0b; background: #fffbeb; color: #92400e; }
        .fc-process  { border-color: #3b82f6; background: #eff6ff; color: #1e40af; }
        .fc-system   { border-color: #8b5cf6; background: #f5f3ff; color: #5b21b6; }
        .fc-ok       { border-color: #16a34a; background: #f0fdf4; color: #166534; }
        .fc-bad      { border-color: #dc2626; background: #fef2f2; color: #991b1b; }
        .fc-money    { border-color: #0891b2; background: #ecfeff; color: #155e75; }
        .fc-decision { width: 210px; min-height: 96px; padding: 0 34px; border: 0; background: #ede9fe; color: #4c1d95; display: flex; align-items: center; justify-content: center; font-weight: 600;
            clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%); }
        .fc-line { width: 2px; height: 22px; background: #94a3b8; position: relative; margin: 0 auto; }
        .fc-line::after { content: ''; position: absolute; bottom: -1px; left: 50%; transform: translateX(-50%); border: 5px solid transparent; border-top: 7px solid #94a3b8; border-bottom: 0; }
        .fc-label { position: absolute; top: 2px; left: calc(50% + 8px); font-size: .68rem; font-weight: 700; padding: 0 6px; border-radius: 6px; background: #fff; }
        .fc-label.yes { color: #16a34a; } .fc-label.no { color: #dc2626; }
        .fc-branches { display: flex; justify-content: center; gap: 20px; width: 100%; }
        .fc-branch { position: relative; display: flex; flex-direction: column; align-items: center; padding-top: 22px; flex: 1 1 0; min-width: 0; }
        .fc-branch::before { content: ''; position: absolute; top: 0; left: 0; right: 0; border-top: 2px solid #94a3b8; }
        .fc-branch:first-child::before { left: 50%; }
        .fc-branch:last-child::before { right: 50%; }
        .fc-branch:only-child::before { display: none; }
        .fc-branch::after { content: ''; position: absolute; top: 0; left: 50%; width: 2px; height: 22px; background: #94a3b8; transform: translateX(-1px); }
        .fc-branch > .fc-tag { position: absolute; top: 2px; left: calc(50% + 8px); z-index: 1; }
        .fc-note { font-size: .75rem; color: #64748b; max-width: 330px; text-align: center; margin-top: 6px; }
        .fc-side { margin-top: 14px; padding: 8px 12px; border: 1px dashed #cbd5e1; border-radius: 10px; font-size: .78rem; color: #475569; background: #f8fafc; text-align: center; }
        @media (max-width: 575.98px) { .fc-branches { flex-direction: column; align-items: center; gap: 0; } .fc-branch::before { display: none; } .fc-branch { padding-top: 22px; } }
    </style>

    <!-- Customer Invoice Workflow Modal -->
    <div class="modal fade" id="customerInvoiceWorkflowModal" tabindex="-1" aria-labelledby="customerInvoiceWorkflowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="customerInvoiceWorkflowModalLabel">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Customer Invoice Workflow') }}
                        </h5>
                        <p class="text-muted small mb-0">{{ __('How a customer invoice moves from draft to payment') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3 pb-4">
                    <div class="fc">

                        <div class="fc-node fc-draft fc-start">
                            <span class="fc-step">{{ __('Start') }}</span>
                            <i class="bi bi-file-earmark-plus me-1"></i>{{ __('Create customer invoice') }}
                            <small>{{ __('From a job or directly') }} &middot; {{ __('number') }} DR-YY-0001</small>
                        </div>
                        <div class="fc-line"></div>

                        <div class="fc-node fc-draft">
                            <span class="badge bg-secondary mb-1">{{ __('Draft') }}</span><br>
                            <i class="bi bi-pencil-square me-1"></i>{{ __('Add charges, tax and terms') }}
                            <small>{{ __('Editable and deletable while in draft') }}</small>
                        </div>
                        <div class="fc-line"></div>

                        <div class="fc-node fc-decision">{{ __('Review outcome?') }}</div>

                        <div class="fc-branches">
                            <!-- Cancel -->
                            <div class="fc-branch">
                                <span class="fc-label no fc-tag">{{ __('Cancel') }}</span>
                                <div class="fc-node fc-bad fc-end">
                                    <i class="bi bi-slash-circle me-1"></i>{{ __('Cancelled') }}
                                    <small>{{ __('Invoice withdrawn, no ledger entries') }}</small>
                                </div>
                            </div>

                            <!-- Approve -->
                            <div class="fc-branch" style="flex: 2 1 0;">
                                <span class="fc-label yes fc-tag">{{ __('Approve') }}</span>
                                <div class="fc-node fc-decision">{{ __('ZATCA registered?') }}</div>

                                <div class="fc-branches">
                                    <div class="fc-branch">
                                        <span class="fc-label no fc-tag">{{ __('No') }}</span>
                                        <div class="fc-node fc-system">
                                            <span class="fc-step">{{ __('System') }}</span>
                                            {{ __('Assign invoice number') }} IN/YY/0001
                                            <small>{{ __('Draft number is kept for reference') }}</small>
                                        </div>
                                    </div>
                                    <div class="fc-branch">
                                        <span class="fc-label yes fc-tag">{{ __('Yes') }}</span>
                                        <div class="fc-node fc-system">
                                            <span class="fc-step">{{ __('System') }}</span>
                                            {{ __('Date set to today, number assigned') }}
                                            <small>{{ __('ZATCA date rule is checked first') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="fc-line"></div>
                                <div class="fc-node fc-system">
                                    <span class="fc-step">{{ __('System') }}</span>
                                    <i class="bi bi-journal-check me-1"></i>{{ __('Post ledger entries') }}
                                    <small>{{ __('Debit receivable, credit revenue and output VAT') }}</small>
                                </div>
                                <div class="fc-line"></div>
                                <div class="fc-node fc-decision">{{ __('ZATCA result?') }}</div>
                                <div class="fc-note">{{ __('Submitted only when ZATCA is registered; otherwise the invoice goes straight to Approved') }}</div>

                                <div class="fc-branches">
                                    <div class="fc-branch">
                                        <span class="fc-label no fc-tag">{{ __('Error') }}</span>
                                        <div class="fc-node fc-bad">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Rolled back') }}
                                            <small>{{ __('Stays in draft; fix and retry') }}</small>
                                        </div>
                                    </div>
                                    <div class="fc-branch">
                                        <span class="fc-label yes fc-tag">{{ __('Cleared / warning / skipped') }}</span>
                                        <div class="fc-node fc-ok">
                                            <span class="badge bg-success mb-1">{{ __('Approved') }}</span><br>
                                            <i class="bi bi-check-circle me-1"></i>{{ __('Invoice is final') }}
                                            <small>{{ __('QR code stored; ready to print or email') }}</small>
                                        </div>
                                        <div class="fc-line"></div>
                                        <div class="fc-node fc-money">
                                            <i class="bi bi-cash-coin me-1"></i>{{ __('Receive payment') }}
                                            <small>{{ __('Record it in the Collection module') }}</small>
                                        </div>
                                        <div class="fc-line"></div>
                                        <div class="fc-node fc-ok fc-end">
                                            <i class="bi bi-patch-check me-1"></i>{{ __('Fully collected') }}
                                            <small>{{ __('Part payments keep a balance due') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="fc-side">
                            <i class="bi bi-printer me-1"></i>{{ __('Print and email are available at any stage.') }}
                            {{ __('Changing an approved invoice to any other status removes its ledger entries.') }}
                        </div>
                    </div>

                    <hr class="mt-4">
                    <h6 class="fw-semibold text-muted mb-3 small text-uppercase">{{ __('Status Guide') }}</h6>
                    <div class="row g-2">
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fffbeb;border:1px solid #f59e0b;">
                                <span class="badge bg-secondary">{{ __('Draft') }}</span>
                                <small class="text-muted">{{ __('Being prepared, can be edited or deleted') }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fdf4;border:1px solid #16a34a;">
                                <span class="badge bg-success">{{ __('Approved') }}</span>
                                <small class="text-muted">{{ __('Numbered, posted to the ledger, ready for collection') }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fef2f2;border:1px solid #dc2626;">
                                <span class="badge bg-danger">{{ __('Cancelled') }}</span>
                                <small class="text-muted">{{ __('Withdrawn before approval') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('Create Customer Invoice') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
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
