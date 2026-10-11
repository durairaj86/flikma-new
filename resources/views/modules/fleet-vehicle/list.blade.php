@section('page-title', __('Vehicles'))
@section('page-subtitle', __('Your trucks and when their papers expire'))
@section('js','vehicle')
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-3 d-flex flex-column" style="min-width:0">
        <style>
            .mod-title { display: none; }
            body:not(.has-top-header) .mod-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #filtered-data:empty { display: none; }
            #filtered-data:not(:empty) { padding-top: 12px; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
            .mod-kpi { border-radius: 12px; padding: .85rem 1.1rem; height: 100%; }
            .mod-kpi .kpi-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; opacity: .75; }
            .mod-kpi .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
            .cell-primary { font-weight: 600; color: #212529; line-height: 1.3; }
            .cell-secondary { font-size: .75rem; color: #868e96; line-height: 1.3; }
            .late { color: #dc2626; font-weight: 600; }
        </style>

        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 mod-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Vehicle') }}</button>
            </div>
        </div>

        <form id="list-filter" class="d-none"></form>

        {{-- Tabs + search --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <ul class="nav align-items-center" id="listTabs" role="tablist">
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                        <span><i class="bi bi-collection me-1"></i> {{ __('All') }} -</span>
                        <span class="status-count ms-2" id="allCount">0</span>
                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="active">
                        <span><i class="bi bi-truck me-1"></i> {{ __('Active') }} -</span>
                        <span class="status-count ms-2" id="activeCount">0</span>
                    </button>
                </li>
                <li class="nav-item me-2">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="maintenance">
                        <span><i class="bi bi-tools me-1"></i> {{ __('Maintenance') }} -</span>
                        <span class="status-count ms-2" id="maintenanceCount">0</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="inactive">
                        <span><i class="bi bi-slash-circle me-1"></i> {{ __('Inactive') }} -</span>
                        <span class="status-count ms-2" id="inactiveCount">0</span>
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
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="container-fluid pb-3" id="summary-cards">
            <div class="row g-3">
                <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#f1f3f5;color:#343a40;"><div class="kpi-label">{{ __('Total Vehicles') }}</div><div class="kpi-value" id="cardAllCount">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#ecfdf5;color:#166534;"><div class="kpi-label">{{ __('Active') }}</div><div class="kpi-value" id="cardActiveCount">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#fef2f2;color:#991b1b;"><div class="kpi-label">{{ __('Papers Ending / Expired') }}</div><div class="kpi-value" id="cardExpiringCount">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#fff7ed;color:#9a3412;"><div class="kpi-label">{{ __('In Maintenance') }}</div><div class="kpi-value" id="cardMaintCount">0</div></div></div>
            </div>
        </div>

        <div class="border bdr-r-10 pb-3">
            <div id="filtered-data" class="px-3"></div>
            <div class="table-responsive">
                <table class="table align-middle dataTable" id="dataTable" data-title="Vehicle" data-model-size="xl">
                    <thead>
                    <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                        <th>{{ __('Vehicle') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Driver') }}</th>
                        <th>{{ __('Registration') }}</th>
                        <th>{{ __('Insurance') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        </section>
    </main>

    @include('modules.fleet-vehicle.vehicle-view')
    @include('modules.common.linked-drawer', ['mainWidth' => 50, 'subWidth' => 35])
    <script>
        (function () {
            const KEY = 'vehicle_hide_summary';
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
