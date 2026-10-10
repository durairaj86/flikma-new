@section('page-title', __('Arrival Notices'))
@section('page-subtitle', __('Tell consignees their cargo is arriving, with the notice and notification letters'))
@section('js','arrival_notice')
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .an-title { display: none; }
            body:not(.has-top-header) .an-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
            .an-kpi { border-radius: 12px; padding: .85rem 1.1rem; height: 100%; }
            .an-kpi .kpi-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; opacity: .75; }
            .an-kpi .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
            .an-route { white-space: nowrap; }
            .cell-primary { font-weight: 600; color: #212529; line-height: 1.3; }
            .cell-secondary { font-size: .75rem; color: #868e96; line-height: 1.3; }
            .late { color: #dc2626; font-weight: 600; }
        </style>

        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 an-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Arrival Notice') }}</button>
            </div>
        </div>

        {{-- Filters --}}
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
                            <label class="form-label fw-medium filter-label-row">{{ __('Order Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                       value="{{ \Carbon\Carbon::today()->subMonths(3)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-5 form-filter">
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

        {{-- Tabs + search --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <ul class="nav align-items-center" id="listTabs" role="tablist">
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                        <span><i class="bi bi-collection me-1"></i> {{ __('All') }} -</span>
                        <span class="status-count ms-2" id="allCount">0</span>
                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                        <span><i class="bi bi-pencil me-1"></i> {{ __('Draft') }} -</span>
                        <span class="status-count ms-2" id="draftCount">0</span>
                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="notified">
                        <span><i class="bi bi-megaphone me-1"></i> {{ __('Notified') }} -</span>
                        <span class="status-count ms-2" id="notifiedCount">0</span>
                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="collected">
                        <span><i class="bi bi-check2-circle me-1"></i> {{ __('D.O Collected') }} -</span>
                        <span class="status-count ms-2" id="collectedCount">0</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                        <span><i class="bi bi-x-circle me-1"></i> {{ __('Cancelled') }} -</span>
                        <span class="status-count ms-2" id="cancelledCount">0</span>
                    </button>
                </li>
            </ul>
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

        {{-- Summary cards --}}
        <div class="container-fluid pb-3" id="summary-cards">
            <div class="row g-3">
                <div class="col-6 col-lg-3"><div class="an-kpi bg-body-tertiary"><div class="kpi-label">{{ __('Total Notices') }}</div><div class="kpi-value" id="cardAllCount">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="an-kpi" style="background:#fff7ed;color:#9a3412;"><div class="kpi-label">{{ __('Draft') }}</div><div class="kpi-value" id="cardDraftCount">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="an-kpi" style="background:#eff6ff;color:#1e40af;"><div class="kpi-label">{{ __('Notified') }}</div><div class="kpi-value" id="cardNotifiedCount">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="an-kpi" style="background:#fef2f2;color:#991b1b;"><div class="kpi-label">{{ __('Arriving in 3 Days, Not Notified') }}</div><div class="kpi-value" id="cardUrgentCount">0</div></div></div>
            </div>
        </div>

        <div class="shadow bdr-r-10 pb-3 flex-grow-1">
            <div id="filtered-data" class="px-3 pt-3"></div>
            <div class="table-responsive">
                <table class="table align-middle dataTable" id="dataTable" data-title="Arrival Notice" data-model-size="xl">
                    <thead>
                    <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                        <th>{{ __('Arrival Notice') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Vessel / B/L') }}</th>
                        <th>{{ __('Route') }}</th>
                        <th>{{ __('ETA / Notified') }}</th>
                        <th>{{ __('Cargo') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    @include('modules.arrival-notice.arrival-notice-view')
    <iframe id="print-frame" style="display:none;"></iframe>
    @include('modules.common.linked-drawer', ['mainWidth' => 60, 'subWidth' => 35])
    <script>
        (function () {
            const KEY = 'arrival_notice_hide_summary';
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
