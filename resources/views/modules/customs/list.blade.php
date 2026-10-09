@section('page-title', __('Customs Clearance'))
@section('page-subtitle', __('Clearance progress of every job'))
@section('js','customs')
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .cc-title { display: none; }
            body:not(.has-top-header) .cc-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
            .cc-kpi { border-radius: 12px; padding: .85rem 1.1rem; height: 100%; }
            .cc-kpi .kpi-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; opacity: .75; }
            .cc-kpi .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
            .cc-route { white-space: nowrap; }
            .cell-primary { font-weight: 600; color: #212529; line-height: 1.3; }
            .cell-secondary { font-size: .75rem; color: #868e96; line-height: 1.3; }
            .cutoff-soon { color: #dc2626; font-weight: 600; }
        </style>

        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 cc-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
            </div>
            <div class="ms-auto text-muted small d-none d-md-block">
                <i class="bi bi-info-circle me-1"></i>{{ __('Clearance records are created with the job. Open one to update its progress.') }}
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
                            <label class="form-label fw-medium filter-label-row">{{ __('Customer') }}</label>
                            <x-common.customers multiple></x-common.customers>
                        </div>
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Type of Clearance') }}</label>
                            <select class="tom-select avoid-filter" name="type_of_clearance" id="filter-type">
                                <option value="">{{ __('All') }}</option>
                                <option value="import">{{ __('Import') }}</option>
                                <option value="export">{{ __('Export') }}</option>
                                <option value="transit">{{ __('Transit') }}</option>
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
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                        <span><i class="bi bi-clock me-1"></i> {{ __('Pending') }} -</span>
                        <span class="status-count ms-2" id="pendingCount">0</span>
                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="under-process">
                        <span><i class="bi bi-hourglass-split me-1"></i> {{ __('Under Process') }} -</span>
                        <span class="status-count ms-2" id="under-processCount">0</span>
                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cleared">
                        <span><i class="bi bi-check-circle me-1"></i> {{ __('Cleared') }} -</span>
                        <span class="status-count ms-2" id="clearedCount">0</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="on-hold">
                        <span><i class="bi bi-pause-circle me-1"></i> {{ __('On Hold') }} -</span>
                        <span class="status-count ms-2" id="on-holdCount">0</span>
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
                <div class="col-6 col-lg-3">
                    <div class="cc-kpi bg-body-tertiary"><div class="kpi-label">{{ __('Total Clearances') }}</div><div class="kpi-value" id="cardAllCount">0</div></div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="cc-kpi" style="background:#fff7ed;color:#9a3412;"><div class="kpi-label">{{ __('In Progress') }}</div><div class="kpi-value" id="cardOpenCount">0</div></div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="cc-kpi" style="background:#ecfdf5;color:#166534;"><div class="kpi-label">{{ __('Cleared') }}</div><div class="kpi-value" id="cardClearedCount">0</div></div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="cc-kpi" style="background:#eff6ff;color:#1e40af;"><div class="kpi-label">{{ __('Duty (Our / Client)') }}</div><div class="kpi-value fs-5" id="cardDuty">0.00 / 0.00</div></div>
                </div>
            </div>
        </div>

        <div class="shadow bdr-r-10 pb-3 flex-grow-1">
            <div id="filtered-data" class="px-3 pt-3"></div>
            <div class="table-responsive">
                <table class="table align-middle dataTable" id="dataTable" data-title="Clearance" data-model-size="xl">
                    <thead>
                    <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Declaration / Bayan') }}</th>
                        <th>{{ __('Broker / Port') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th class="text-end">{{ __('Duty (Our / Client)') }}</th>
                        <th>{{ __('Progress') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    @include('modules.customs.customs-view')
    @include('modules.common.linked-drawer', ['mainWidth' => 60, 'subWidth' => 35])
    <script>
        (function () {
            const KEY = 'customs_hide_summary';
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
