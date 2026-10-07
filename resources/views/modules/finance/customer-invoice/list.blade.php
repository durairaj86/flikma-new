@section('js','customer_invoice')
@section('page-title', __('Customer Invoice'))
@section('hide-topbar', true)
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#customerInvoiceWorkflowModal"
            title="{{ __('How customer invoices work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .bg-ci-approved { background-color: #dcfce7; }
            .text-ci-approved { color: #166534; }
            .bg-ci-draft { background-color: #fef9c3; }
            .text-ci-draft { color: #854d0e; }
            .ci-title { display: none; }
            body:not(.has-top-header) .ci-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 ci-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new" data-loader-id="{{ $job_id ?? 'list' }}">{{ __('New Customer Invoice') }}</button>
            </div>
        </div>

        <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card">

            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="filter-panel-icon"><i class="bi bi-funnel-fill"></i></span>
                    <h6 class="mb-0 fw-semibold">{{ __('Filters') }}</h6>
                </div>
            </div>

            <div class="card-body pt-3">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="row g-4">

                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Invoice Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                       value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Customer') }}</label>
                            <x-common.customers multiple="true" :value="request()->query('customer') ? [(int) request()->query('customer')] : null"></x-common.customers>
                        </div>

                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Status') }}</label>
                            <select class="tom-select avoid-filter" name="filter-status" id="filter-status" size="3" placeholder="{{ __('All Status') }}">
                                    <option value="all" selected>{{ __('All Status') }}</option>
                                    <option value="1">{{ __('Draft') }}</option>
                                    <option value="3">{{ __('Approved') }}</option>
                                    <option value="5">{{ __('Cancelled') }}</option>
                                </select>
                        </div>

                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Payment Status') }}</label>
                            <select class="tom-select avoid-filter" name="filter-payment-status" id="filter-payment-status" size="3" placeholder="{{ __('All Payments') }}">
                                    <option value="all" selected>{{ __('All Payments') }}</option>
                                    <option value="paid">{{ __('Paid') }}</option>
                                    <option value="partial">{{ __('Partially Paid') }}</option>
                                    <option value="unpaid">{{ __('Unpaid') }}</option>
                                </select>
                        </div>

                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Invoice Type') }}</label>
                            <select class="tom-select avoid-filter" name="filter-overdue" id="filter-overdue">
                                    <option value="all" selected>{{ __('All Invoices') }}</option>
                                    <option value="overdue">{{ __('Overdue Invoices') }}</option>
                                    <option value="non_due">{{ __('Non-Due Invoices') }}</option>
                                </select>
                        </div>

                    </div>

                    <div class="text-center mt-4 pt-2 border-top filter-panel-actions">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
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
            <div class="d-flex align-items-center gap-2">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                </div>
                <button class="btn btn-icon-search rounded-circle" id="toggle-summary" type="button"
                        title="{{ __('Hide Summary') }}" aria-label="{{ __('Hide Summary') }}"><i class="bi bi-eye-slash"></i></button>
                <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button"
                        title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
            </div>
        </div>

        <div class="container-fluid pb-3" id="summary-cards">
            <div class="row g-3">

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-body-tertiary px-4 py-3 h-100">
                        <h6 class="text-uppercase text-muted fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Summary') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="cardAllCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Total Invoices') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="cardApprovedCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Approved') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="cardDraftCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Draft') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-ci-approved px-4 py-3 h-100">
                        <h6 class="text-uppercase text-ci-approved fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Approved Invoices') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="total_approved_sub" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Total Amount') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="total_approved_tax" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Total Tax') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="total_approved_grand" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Net Total') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-ci-draft px-4 py-3 h-100">
                        <h6 class="text-uppercase text-ci-draft fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Draft Invoices') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="total_draft_sub" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Total Amount') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="total_draft_tax" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Total Tax') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="total_draft_grand" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Net Total') }}</small>
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

    <script>
        (function () {
            const KEY = 'customer_invoice_hide_summary';
            const box = document.getElementById('summary-cards');
            const btn = document.getElementById('toggle-summary');
            if (!box || !btn) return;
            const apply = (hide) => {
                box.classList.toggle('d-none', hide);
                btn.querySelector('i').className = 'bi ' + (hide ? 'bi-eye' : 'bi-eye-slash');
                const t = hide ? @json(__('Show Summary')) : @json(__('Hide Summary'));
                btn.title = t; btn.setAttribute('aria-label', t);
            };
            let hidden = false;
            try { hidden = localStorage.getItem(KEY) === '1'; } catch (e) {}
            apply(hidden);
            btn.addEventListener('click', () => {
                hidden = !hidden;
                try { localStorage.setItem(KEY, hidden ? '1' : '0'); } catch (e) {}
                apply(hidden);
            });
        })();
    </script>
    @include('modules.email.send-email')
    @include('modules.finance.customer-invoice.customer-invoice-view')



    @include('modules.workflows.customer-invoice')
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
