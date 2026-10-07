@section('page-title', __('Credit Note'))
@section('page-subtitle', __('Credit note adjustments and refunds'))
@section('js','credit_note')
@section('hide-topbar', true)
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#creditNoteWorkflowModal"
            title="{{ __('How credit notes work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            :root{
                --cn_primary: #0b6aa0;
                --cn_card_bg: #ffffff;
                --cn_radius: 12px;
                --cn_shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            }
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
                background-color: #f8f9fa !important;
                color: #6c757d;
                font-weight: 600;
                border-bottom: 1px solid #e9ecef;
                padding: 0.65rem 1rem;
                white-space: nowrap;
            }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            .cn-title { display: none; }
            body:not(.has-top-header) .cn-title { display: block; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }

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

        <div>
            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
                <div class="d-flex align-items-center gap-2 cn-title">
                    <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                    @stack('page-title-action')
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Credit Note') }}</button>
                </div>
            </div>

            {{-- Filter Panel --}}
            <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card mt-3">
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
                                <label class="form-label fw-medium filter-label-row">{{ __('Date') }}</label>
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
                                <x-common.customers multiple></x-common.customers>
                            </div>
                            <div class="col-md-6 col-xl-4 form-filter">
                                <label class="form-label fw-medium filter-label-row">{{ __('Invoice') }}</label>
                                <select class="form-select form-select-sm" id="filter-invoice" name="invoice">
                                <option value="">{{ __('All Invoices') }}</option>
                                @foreach(\App\Models\Finance\CustomerInvoice\CustomerInvoice::where('status', 3)->get() as $invoice)
                                    <option value="{{ encodeId($invoice->id) }}">{{ $invoice->row_no ?? $invoice->id }}</option>
                                @endforeach
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

            {{-- Status Tabs --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                <div class="align-items-center flex-shrink-0">
                    <div class="gap-4">
                        <ul class="nav align-items-center" id="listTabs" role="tablist">
                            <li class="nav-item me-2">
                                <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                                    <span><i class="bi bi-collection me-1"></i> {{ __('All') }} -</span>
                                    <span class="status-count ms-2" id="tabAllCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item me-2">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                    <span><i class="bi bi-clock me-1"></i> {{ __('Draft') }} -</span>
                                    <span class="status-count ms-2" id="tabDraftCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item me-2">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                    <span><i class="bi bi-check-circle me-1"></i> {{ __('Approved') }} -</span>
                                    <span class="status-count ms-2" id="tabApprovedCount">0</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                        data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                    <span><i class="bi bi-x-circle me-1"></i> {{ __('Cancelled') }} -</span>
                                    <span class="status-count ms-2" id="tabCancelledCount">0</span>
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

            {{-- KPI Cards --}}
            <div class="row g-3 mb-3" id="summary-cards">
                <div class="col-lg-3 col-md-6">
                    <div class="cn-kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">{{ __('Total Credit Notes') }}</div>
                            <div class="kpi-value" id="allCount">0</div>
                            <div class="kpi-sub">{{ __('All statuses') }}</div>
                        </div>
                        <div class="cn-icon-circle" style="background:rgba(11,106,160,0.1);color:#0b6aa0;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="cn-kpi d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">{{ __('Total Amount') }}</div>
                            <div class="kpi-value" id="overall_sales">0.00</div>
                            <div class="kpi-sub">SAR - {{ __('Grand total') }}</div>
                        </div>
                        <div class="cn-icon-circle" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">{{ __('Draft') }}</div>
                        <div class="kpi-value text-warning" id="draftCount">0</div>
                        <div class="kpi-sub"><span id="draftTotal">0.00</span> SAR</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">{{ __('Approved') }}</div>
                        <div class="kpi-value text-success" id="approvedCount">0</div>
                        <div class="kpi-sub"><span id="approvedTotal">0.00</span> SAR</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="cn-kpi text-center">
                        <div class="kpi-label">{{ __('Cancelled') }}</div>
                        <div class="kpi-value text-danger" id="cancelledCount">0</div>
                        <div class="kpi-sub"><span id="cancelledTotal">0.00</span> SAR</div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="cn-card p-0">
                <div id="filtered-data" class="px-3 pt-3"></div>
                <table class="table align-middle mb-0 dataTable" id="dataTable">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                            <th>{{ __('Credit Note #') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Job') }}</th>
                            <th>{{ __('Invoice') }}</th>
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
            const KEY = 'credit_note_hide_summary';
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
    @include('modules.workflows.credit-note')
    @include('modules.finance.credit-note.credit-note-view')
    @include('modules.common.linked-drawer', ['mainWidth' => 60, 'subWidth' => 35])

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
                            <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Credit Note Workflow') }}
                        </h5>
                        <p class="text-muted small mb-0">{{ __('How credit notes move through your system') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3 pb-4">

                    <!-- Flowchart -->
                    <div class="d-flex flex-column align-items-center gap-0">

                        <div class="wf-box wf-neutral">
                            <i class="bi bi-receipt me-1"></i> {{ __('Select Customer Invoice & Reason') }}
                            <div class="wf-badge bg-secondary text-white">{{ __('Approved Customer Invoice') }}</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-pending">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">{{ __('STEP 1') }}</div>
                            <i class="bi bi-file-earmark-minus me-1"></i> {{ __('Create Credit Note') }}
                            <div class="wf-badge bg-warning text-dark">{{ __('Draft') }}</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-action">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">{{ __('STEP 2') }}</div>
                            <i class="bi bi-send-check me-1"></i> {{ __('Approve Credit Note') }}
                            <div class="text-muted mt-1" style="font-size:0.75rem;">{{ __('Reduces invoice balance & posts to GL') }}</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <!-- Decision -->
                        <div class="wf-box wf-decision">
                            <i class="bi bi-question-circle me-1"></i> {{ __('Credit Note Outcome') }}
                        </div>

                        <div class="d-flex justify-content-center gap-5 w-100 mt-0">
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-success">
                                    <i class="bi bi-check-circle me-1"></i> {{ __('Approved') }}
                                    <div class="wf-badge bg-success text-white">{{ __('Approved') }}</div>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-danger">
                                    <i class="bi bi-x-circle me-1"></i> {{ __('Cancelled') }}
                                    <div class="wf-badge bg-danger text-white">{{ __('Cancelled') }}</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Status legend -->
                    <hr class="mt-4">
                    <h6 class="fw-semibold text-muted mb-3 small text-uppercase">{{ __('Status Guide') }}</h6>
                    <div class="row g-2">
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fffbf0;border:1px solid #ffc107;">
                                <span class="badge bg-warning text-dark">{{ __('Draft') }}</span>
                                <small class="text-muted">{{ __('Awaiting approval') }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fff4;border:1px solid #198754;">
                                <span class="badge bg-success">{{ __('Approved') }}</span>
                                <small class="text-muted">{{ __('Posted & invoice balance reduced') }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fff5f5;border:1px solid #dc3545;">
                                <span class="badge bg-danger">{{ __('Cancelled') }}</span>
                                <small class="text-muted">{{ __('Credit note called off') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('Create Credit Note') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
