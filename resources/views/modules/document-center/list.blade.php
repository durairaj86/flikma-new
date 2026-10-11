@section('page-title', __('Documents'))
@section('page-subtitle', __('Every document in one place, with expiry dates'))
@section('js','document_center')
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3">
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
            <div class="d-flex align-items-center gap-2 mod-title"><h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4></div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Document') }}</button>
            </div>
        </div>

        <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card mt-3">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2"><span class="filter-panel-icon"><i class="bi bi-funnel-fill"></i></span><h6 class="mb-0 fw-semibold">{{ __('Filters') }}</h6></div>
            </div>
            <div class="card-body pt-3">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Belongs To') }}</label>
                            <select name="owner_type" class="tom-select">
                                <option value="">{{ __('All') }}</option>
                                @foreach(\App\Http\Controllers\Documents\DocumentCenterController::OWNERS as $k => $o)
                                    <option value="{{ $k }}">{{ __($o[1]) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Type') }}</label>
                            <select name="doc_type" class="tom-select">
                                <option value="">{{ __('All') }}</option>
                                @foreach(\App\Http\Controllers\Documents\DocumentCenterController::TYPES as $k => $label)
                                    <option value="{{ $k }}">{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-4 pt-2 border-top filter-panel-actions">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter"><i class="bi bi-search me-1"></i> {{ __('Search') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <ul class="nav align-items-center" id="listTabs" role="tablist">
                @foreach(['all' => ['bi-collection', 'All'], 'valid' => ['bi-check-circle', 'Valid'], 'expiring' => ['bi-hourglass-split', 'Expiring Soon'], 'expired' => ['bi-x-circle', 'Expired']] as $id => [$ic, $lab])
                    <li class="nav-item {{ $loop->last ? '' : 'me-2' }}">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="{{ $id }}">
                            <span><i class="bi {{ $ic }} me-1"></i> {{ __($lab) }} -</span>
                            <span class="status-count ms-2" id="{{ $id }}Count">0</span>
                        </button>
                    </li>
                @endforeach
            </ul>
            <div class="d-flex align-items-center gap-2">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5" placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                </div>
                <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button" title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
            </div>
        </div>

        <div class="container-fluid pb-3" id="summary-cards">
            <div class="row g-3">
                <div class="col-6 col-lg-3"><div class="mod-kpi bg-body-tertiary"><div class="kpi-label">{{ __('Documents') }}</div><div class="kpi-value" id="cardAll">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#ecfdf5;color:#166534;"><div class="kpi-label">{{ __('Valid') }}</div><div class="kpi-value" id="cardValid">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#fff7ed;color:#9a3412;"><div class="kpi-label">{{ __('Expiring in 30 Days') }}</div><div class="kpi-value" id="cardSoon">0</div></div></div>
                <div class="col-6 col-lg-3"><div class="mod-kpi" style="background:#fef2f2;color:#991b1b;"><div class="kpi-label">{{ __('Expired') }}</div><div class="kpi-value" id="cardExpired">0</div></div></div>
            </div>
        </div>

        <div class="shadow bdr-r-10 pb-3 flex-grow-1">
            <div id="filtered-data" class="px-3"></div>
            <div class="table-responsive">
                <table class="table align-middle dataTable" id="dataTable" data-title="Document" data-model-size="lg">
                    <thead>
                    <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                        <th>{{ __('Document') }}</th>
                        <th>{{ __('Belongs To') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Expiry') }}</th>
                        <th>{{ __('Added') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
