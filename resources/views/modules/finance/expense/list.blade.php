@section('page-title', __('Expenses'))
@section('hide-topbar', true)
@section('js','expense')
@section('extra-js','customer')
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .ex-title { display: none; }
            body:not(.has-top-header) .ex-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
            /* Expense = money going out: amber tones, like the Supplier Invoice summary cards. */
            .bg-ex-approved { background-color: #fef3c7; }
            .text-ex-approved { color: #92400e; }
            .bg-ex-draft { background-color: #ffedd5; }
            .text-ex-draft { color: #9a3412; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 ex-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Expense') }}</button>
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
                            <label class="form-label fw-medium filter-label-row">{{ __('Expense Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                       value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Supplier') }}</label>
                            <x-common.suppliers multiple></x-common.suppliers>
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
                                class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                                <span><i class="bi bi-clock me-1"></i> {{ __('Draft') }} -</span>
                                <span class="status-count ms-2" id="pendingCount">0</span>
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
                            <div class="col-4"><div id="cardAllCount" class="fs-4 fw-bold mb-0">0</div><small class="text-muted">{{ __('Total Expenses') }}</small></div>
                            <div class="col-4"><div id="cardApprovedCount" class="fs-4 fw-bold mb-0">0</div><small class="text-muted">{{ __('Approved') }}</small></div>
                            <div class="col-4"><div id="cardDraftCount" class="fs-4 fw-bold mb-0">0</div><small class="text-muted">{{ __('Draft') }}</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-ex-approved px-4 py-3 h-100">
                        <h6 class="text-uppercase text-ex-approved fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Approved Expenses') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4"><div id="total_approved_sub" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Total Amount') }}</small></div>
                            <div class="col-4"><div id="total_approved_tax" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Total Tax') }}</small></div>
                            <div class="col-4"><div id="total_approved_grand" class="fs-4 fw-bold mb-0">0.00</div><small class="text-muted">{{ __('Net Total') }}</small></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-ex-draft px-4 py-3 h-100">
                        <h6 class="text-uppercase text-ex-draft fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Draft Expenses') }}</h6>
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
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable" data-module-url="expense" data-title="expense" data-model-size="lg"
                       data-min-height="min-height:75vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>{{ __('Expense No') }}</th>
                        <th>{{ __('Expense Date') }}</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-end">{{ __('Base Amount') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.finance.expense.expense-view')
    @include('modules.common.linked-drawer', ['mainWidth' => 60, 'subWidth' => 35])
    <script>
        (function () {
            const KEY = 'expense_hide_summary';
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
</x-app-layout>
