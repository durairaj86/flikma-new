@section('page-title', __('Collections'))
@section('js','collection')
@section('hide-topbar', true)
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#collectionWorkflowModal"
            title="{{ __('How collections work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
<x-app-layout>
    <!-- Main Content -->
    <main class="gmail-content bg-white px-3">
        <style>
            .col-title { display: none; }
            body:not(.has-top-header) .col-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            /* Collection = money coming in: green tones, like the Customer Invoice summary cards. */
            .bg-col-approved { background-color: #dcfce7; }
            .text-col-approved { color: #166534; }
            .bg-col-draft { background-color: #fef9c3; }
            .text-col-draft { color: #854d0e; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 col-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Collection') }}</button>
            </div>
        </div>

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
                            <label class="form-label fw-medium filter-label-row">{{ __('Collection Date') }}</label>
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
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                <span><i class="bi bi-clock me-1"></i> {{ __('Draft') }} -</span>
                                <span class="status-count ms-2" id="draftCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                <span><i class="bi bi-check-circle me-1"></i> {{ __('Approved') }} -</span>
                                <span class="status-count ms-2" id="approvedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle me-1"></i> {{ __('Cancelled') }} -</span>
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
                            <div class="col-4"><div id="cardAllCount" class="fs-4 fw-bold mb-0">0</div><small class="text-muted">{{ __('Total Collections') }}</small></div>
                            <div class="col-4"><div id="cardApprovedCount" class="fs-4 fw-bold mb-0">0</div><small class="text-muted">{{ __('Approved') }}</small></div>
                            <div class="col-4"><div id="cardDraftCount" class="fs-4 fw-bold mb-0">0</div><small class="text-muted">{{ __('Draft') }}</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-col-approved px-4 py-3 h-100">
                        <h6 class="text-uppercase text-col-approved fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Approved Collections') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4"><div id="total_approved_sub" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Total Amount') }}</small></div>
                            <div class="col-4"><div id="total_approved_tax" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Total Tax') }}</small></div>
                            <div class="col-4"><div id="total_approved_grand" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Net Total') }}</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-col-draft px-4 py-3 h-100">
                        <h6 class="text-uppercase text-col-draft fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Draft Collections') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4"><div id="total_draft_sub" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Total Amount') }}</small></div>
                            <div class="col-4"><div id="total_draft_tax" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Total Tax') }}</small></div>
                            <div class="col-4"><div id="total_draft_grand" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Net Total') }}</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
            </div>
            <div class="">
                <table class="table align-middle dataTable" id="dataTable">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Collection No') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Collection Date') }}</th>
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Reference No') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Disapproval Reason Modal -->
        <div class="modal fade" id="disapprovalReasonModal" tabindex="-1" aria-labelledby="disapprovalReasonModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disapprovalReasonModalLabel">{{ __('Disapproval Reason') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <form id="disapprovalReasonForm">
                            <input type="hidden" id="collection_id" name="collection_id">
                            <div class="mb-3">
                                <label for="reason" class="form-label">{{ __('Reason for Disapproval') }}</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-danger" id="submitDisapprovalReason">{{ __('Submit') }}</button>
                    </div>
                </div>
            </div>
        </div>

        @include('modules.transaction.collection.collection-view')
    </main>
    @include('modules.common.linked-drawer', ['mainWidth' => 60, 'subWidth' => 35])
    <script>
        (function () {
            const KEY = 'collection_hide_summary';
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

    <style>
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
        .wf-box.wf-decision { border-color: #6f42c1; background: #f8f0ff;  color: #432874; }
        .wf-arrow { color: #adb5bd; font-size: 1.3rem; line-height: 1.3; text-align: center; }
        .wf-badge { font-size: 0.7rem; border-radius: 20px; padding: 2px 8px; display: inline-block; margin-top: 4px; }
    </style>

    <!-- Collection Workflow Modal -->
    <div class="modal fade" id="collectionWorkflowModal" tabindex="-1" aria-labelledby="collectionWorkflowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="collectionWorkflowModalLabel">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Collection Workflow') }}
                        </h5>
                        <p class="text-muted small mb-0">{{ __('How customer collections move through your system') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3 pb-4">

                    <!-- Flowchart -->
                    <div class="d-flex flex-column align-items-center gap-0">

                        <div class="wf-box wf-neutral">
                            <i class="bi bi-receipt me-1"></i> {{ __('Select Customer & Invoices') }}
                            <div class="wf-badge bg-secondary text-white">{{ __('Approved Customer Invoices') }}</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-pending">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">{{ __('STEP 1') }}</div>
                            <i class="bi bi-cash-coin me-1"></i> {{ __('Create Collection') }}
                            <div class="wf-badge bg-warning text-dark">{{ __('Draft') }}</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-action">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">{{ __('STEP 2') }}</div>
                            <i class="bi bi-send-check me-1"></i> {{ __('Approve Collection') }}
                            <div class="text-muted mt-1" style="font-size:0.75rem;">{{ __('Reduces invoice balance & posts to GL') }}</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <!-- Decision -->
                        <div class="wf-box wf-decision">
                            <i class="bi bi-question-circle me-1"></i> {{ __('Collection Outcome') }}
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
                                <small class="text-muted">{{ __('Collection called off') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('Create Collection') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
