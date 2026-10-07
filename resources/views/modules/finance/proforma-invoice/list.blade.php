@section('js','proforma_invoice')
@section('page-title', __('Proforma Invoice'))
@section('hide-topbar', true)
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#proformaInvoiceWorkflowModal"
            title="{{ __('How proforma invoices work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .pf-title { display: none; }
            body:not(.has-top-header) .pf-title { display: block; }
            /* same light-grey header row as the Quotations table */
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 pf-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new" data-loader-id="{{ $job_id ?? 'list' }}">{{ __('New Proforma Invoice') }}</button>
            </div>
        </div>

        <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card">

            <!-- Header -->
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

                        <div class="col-md-6 col-xl-3 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Invoice Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                       value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Customer') }}</label>
                            <x-common.customers multiple></x-common.customers>
                        </div>

                        <div class="col-md-6 col-xl-2 form-filter pol-pod-select">
                            <div class="d-flex align-items-center justify-content-between filter-label-row">
                                <label class="form-label fw-medium mb-0">
                                    {{ __('POL') }} <span class="text-muted fw-normal">({{ __('Port of Loading') }})</span>
                                </label>
                                <div class="shipment-toggle">
                                    <input type="radio" class="btn-check sync-sea avoid-filter" name="shipment_mode" id="polSea" value="sea" checked>
                                    <label for="polSea">{{ __('Sea') }}</label>
                                    <input type="radio" class="btn-check sync-air avoid-filter" name="shipment_mode" id="polAir" value="air">
                                    <label for="polAir">{{ __('Air') }}</label>
                                </div>
                            </div>
                            <select id="filter-pol" name="filter-pol" class="tom-select-search" data-placeholder="{{ __('Select Port of Loading') }}">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-6 col-xl-2 pol-pod-select">
                            <div class="d-flex align-items-center justify-content-between filter-label-row">
                                <label class="form-label fw-medium mb-0">
                                    {{ __('POD') }} <span class="text-muted fw-normal">({{ __('Port of Discharge') }})</span>
                                </label>
                                <div class="shipment-toggle">
                                    <input type="radio" class="btn-check sync-sea avoid-filter" name="shipment_mode_2" id="polSea2" checked value="sea">
                                    <label for="polSea2">{{ __('Sea') }}</label>
                                    <input type="radio" class="btn-check sync-air avoid-filter" name="shipment_mode_2" id="polAir2" value="air">
                                    <label for="polAir2">{{ __('Air') }}</label>
                                </div>
                            </div>
                            <select id="filter-pod" name="filter-pod" class="tom-select-search" data-placeholder="{{ __('Select Port of Discharge') }}">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-6 col-xl-2 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Job Number') }}</label>
                            <select id="filter-job" name="filter-job" class="tom-select-search" data-placeholder="{{ __('Search Job Number') }}">
                                <option value=""></option>
                            </select>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4 pt-2 border-top filter-panel-actions">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            @if(isset($job_no))
                <h3 class="fw-bold text-muted bg-info-subtle rounded p-3">
                    {{ $job_no }}
                </h3>
            @endif
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                <span><i class="bi bi-clock me-1"></i> {{ __('Draft') }} -</span>
                                <span class="status-count ms-2" id="draftCount">0</span>
                            </button>
                        </li>

                        {{--<li class="nav-item me-2">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle me-1"></i> Confirmed -</span>
                                <span class="status-count d-flex align-items-center justify-content-center"
                                      id="confirmedCount">0</span>
                            </button>
                        </li>--}}

                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                <span><i class="bi bi-check-circle me-1"></i> {{ __('Approved') }} -</span>
                                <span class="status-count ms-2" id="approvedCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="converted">
                                <span><i class="bi bi-clipboard-check me-1"></i> {{ __('Converted to Invoice') }} -</span>
                                <span class="status-count ms-2" id="convertedCount">0</span>
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
                    <div class="rounded-3 bg-pf-approved px-4 py-3 h-100">
                        <h6 class="text-uppercase text-pf-approved fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Approved Invoices') }}</h6>
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
                    <div class="rounded-3 bg-pf-draft px-4 py-3 h-100">
                        <h6 class="text-uppercase text-pf-draft fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Draft Invoices') }}</h6>
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
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Job" data-model-size="lg">
                    <thead class="table-light">
                    <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-end">{{ __('Excl. VAT') }}</th>
                        <th class="text-end">{{ __('Tax') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th>{{ __('Date') }}</th>
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
            const KEY = 'proforma_invoice_hide_summary';
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
    @include('modules.finance.proforma-invoice.proforma-invoice-view')
    <style>
        /* Proforma Invoice is a preliminary quote, not a final invoice —
           indigo/violet tones keep it visually distinct from Customer
           Invoice's green/yellow and Supplier Invoice's amber/orange. */
        .bg-pf-approved { background-color: #ede9fe; }
        .text-pf-approved { color: #5b21b6; }
        .bg-pf-draft { background-color: #e0e7ff; }
        .text-pf-draft { color: #3730a3; }

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

    @include('modules.workflows.proforma-invoice')
</x-app-layout>
<script>


    function resetField(field) {
        if (field === 'date') {
            document.getElementById('fromDate').value = '';
            document.getElementById('toDate').value = '';
        } else if (field === 'activity') {
            document.getElementById('activityType').selectedIndex = 0;
        } else if (field === 'status') {
            document.getElementById('status').selectedIndex = 0;
        } else if (field === 'keyword') {
            document.getElementById('keyword').value = '';
        }
    }

    function resetAll() {
        resetField('date');
        resetField('activity');
        resetField('status');
        resetField('keyword');
    }



</script>
