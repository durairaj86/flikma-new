@section('page-title', __('Jobs'))
@section('hide-topbar', true)
@section('js','job')
@section('extra-js','customer')
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#jobWorkflowModal"
            title="{{ __('How jobs work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .job-title { display: none; }
            body:not(.has-top-header) .job-title { display: block; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 job-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Job') }}</button>
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

                        <div class="col-md-6 col-xl-2 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Job Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                       value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-2 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Customer') }}</label>
                            <x-common.customers multiple></x-common.customers>
                        </div>

                        <div class="col-md-6 col-xl-2 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Carrier') }}</label>
                            <input type="text" class="form-control" id="filter-carrier" name="filter_carrier" placeholder="{{ __('Search carrier') }}">
                        </div>

                        <div class="col-md-6 col-xl-3 form-filter pol-pod-select">
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

                        <div class="col-md-6 col-xl-3 pol-pod-select">
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

        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                                <span><i class="bi bi-clock me-1"></i> {{ __('Pending') }} -</span>
                                <span class="status-count ms-2" id="pendingCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="completed">
                                <span><i class="bi bi-check-circle me-1"></i> {{ __('Completed') }} -</span>
                                <span class="status-count ms-2" id="completedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle me-1"></i> {{ __('Cancelled') }} -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="trashed">
                                <span><i class="bi bi bi-trash me-1"></i> {{ __('Trashed') }} -</span>
                                <span class="status-count ms-2" id="trashedCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search jobs...') }}" aria-label="{{ __('Search jobs...') }}">
                </div>
                <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button"
                        title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
@if($fixedColumns ?? false)
                <button class="btn btn-icon-search rounded-circle" id="jobAiBtn" type="button"
                        title="{{ __('AI Assistant') }}" aria-label="{{ __('AI Assistant') }}"><i class="bi bi-stars"></i></button>
@endif
@unless($fixedColumns ?? false)
                <button class="btn btn-icon-search rounded-circle" id="columnSettingsBtn" type="button"
                        title="{{ __('Column Settings') }}" aria-label="{{ __('Column Settings') }}"><i class="bi bi-columns-gap"></i></button>
@endunless
            </div>
        </div>

        @if($fixedColumns ?? false)
        <style>
            .jq { border: 1px solid #e9ecef; border-radius: 12px; padding: .7rem .9rem; background: #fff; cursor: pointer; text-align: left; width: 100%; transition: .15s; }
            .jq:hover { border-color: #adb5bd; transform: translateY(-1px); }
            .jq.active { border-color: #0d6efd; background: #f0f7ff; box-shadow: 0 0 0 2px rgba(13,110,253,.15); }
            .jq .jq-n { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
            .jq .jq-l { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: #6c757d; }
            .jq-delayed .jq-n { color: #dc2626; } .jq-arriving .jq-n { color: #d97706; } .jq-clearance .jq-n { color: #0d6efd; } .jq-unbilled .jq-n { color: #7c3aed; }
            #jobAiBtn.active { background: #ede9fe; color: #5b21b6; border-color: #c4b5fd; }
            .job-ai { border-radius: 14px; background: linear-gradient(135deg, #f5f3ff 0%, #eff6ff 100%); border: 1px solid #e0e7ff; padding: 1rem 1.25rem; }
            .job-ai-badge { display: inline-flex; align-items: center; gap: .35rem; font-weight: 700; font-size: .8rem; color: #5b21b6; }
            .job-ai-chip { border: 1px solid #ddd6fe; background: #fff; color: #5b21b6; border-radius: 50rem; font-size: .75rem; padding: .2rem .7rem; cursor: pointer; }
            .job-ai-chip:hover { background: #ede9fe; }
            #jobAiAnswer { background: #fff; border-radius: 10px; padding: .75rem 1rem; font-size: .88rem; border: 1px solid #e0e7ff; }
            .job-prog { min-width: 150px; }
            .job-prog-bar { height: 6px; border-radius: 6px; background: #e9ecef; overflow: hidden; }
            .job-prog-bar span { display: block; height: 100%; background: #198754; border-radius: 6px; }
            .job-health { font-weight: 600; }
            .jd { background: #f8fafc; border: 1px solid #eef0f4; border-radius: 10px; padding: .35rem .55rem; margin: .25rem 0 .4rem; }
            .jd-row { display: grid; grid-template-columns: auto 1fr; gap: .5rem; justify-items: end; align-items: center; text-align: center; font-size: .78rem; padding: .12rem 0; }
            .jd-row + .jd-row { border-top: 1px dashed #e5e7eb; }
            .jd-head { font-size: .62rem; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; font-weight: 700; border-bottom: 1px solid #e5e7eb; padding-bottom: .2rem; margin-bottom: .1rem; }
            .jd-head + .jd-row { border-top: 0; }
            .jd-l { text-align: left; color: #4b5563; font-weight: 600; white-space: nowrap; }
            .jd-l i { color: #9ca3af; margin-right: .35rem; }
            .jd-t { font-weight: 800; color: #111827; }
            .jd-z { color: #d1d5db; }
            .jd-n { display: inline-block; min-width: 20px; border-radius: 6px; font-weight: 700; font-size: .72rem; line-height: 1.5; }
            .jd-n.ok { background: #dcfce7; color: #166534; } .jd-n.dr { background: #fef3c7; color: #92400e; } .jd-n.cx { background: #fee2e2; color: #991b1b; }
            .jd-one { display: flex; flex-wrap: nowrap; gap: .5rem; justify-content: space-between; padding: .4rem .5rem; }
            .jd-one .jd-g { display: inline-flex; align-items: center; gap: .2rem; font-size: .75rem; white-space: nowrap; }
            .jd-k { position: relative; font-weight: 800; font-size: .68rem; letter-spacing: .04em; color: #6b7280; cursor: default; }
            .jd-k[data-tip]:hover::after { content: attr(data-tip); position: absolute; bottom: 135%; left: 0; background: #111827; color: #fff; font-size: .7rem; font-weight: 600; white-space: nowrap; padding: .2rem .5rem; border-radius: 6px; z-index: 20; pointer-events: none; letter-spacing: 0; }
            .jd-one .jd-empty { opacity: .55; }
            .jd-g { display: inline-flex; align-items: center; gap: .3rem; }
            .jd-sep { color: #d1d5db; margin: 0 .35rem; }
            .jd-row { grid-template-columns: auto 1fr auto auto; }
            .jd-row > .jd-g:first-of-type { justify-self: end; }
            .jd-empty .jd-l { color: #9ca3af; }
            .jd-n { position: relative; cursor: default; }
            .jd-n[data-tip]:hover::after { content: attr(data-tip); position: absolute; bottom: 135%; left: 50%; transform: translateX(-50%); background: #111827; color: #fff; font-size: .7rem; font-weight: 600; white-space: nowrap; padding: .2rem .5rem; border-radius: 6px; z-index: 20; pointer-events: none; }
            .jd-n[data-tip]:hover::before { content: ''; position: absolute; bottom: 115%; left: 50%; transform: translateX(-50%); border: 5px solid transparent; border-top-color: #111827; z-index: 20; pointer-events: none; }
            .job-stage { background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; font-weight: 600; }
            @keyframes jcFlash { from { background: #dcfce7; } to { background: #fff; } }
            .jc-mode #dataTable tbody tr.jc-flash td { animation: jcFlash 1.8s ease-out; }
            .job-seg { display: flex; gap: 2px; height: 6px; }
            .job-seg span { flex: 1; border-radius: 3px; background: #e9ecef; }
            .job-seg span.done { background: #198754; }
            .job-seg span.skip { background: #f59e0b; }
            .jc-mode { box-shadow: none !important; background: transparent; }
            .jc-chip { border: 1px solid #dee2e6; background: #fff; border-radius: 8px; font-weight: 600; font-size: .8rem; padding: .25rem .9rem; }
            .jc-chip.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }
            .jc-mode #dataTable { border-collapse: separate; border-spacing: 0 12px; }
            .jc-mode #dataTable thead { display: none; }
            .jc-mode #dataTable tbody td { background: #fff; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; padding: 1rem 1.25rem; vertical-align: middle; }
            .jc-mode #dataTable tbody td:first-child { border-left: 1px solid #e5e7eb; border-radius: 12px 0 0 12px; }
            .jc-mode #dataTable tbody td:last-child { border-right: 1px solid #e5e7eb; border-radius: 0 12px 12px 0; width: 56px; text-align: center; }
            .jc-mode #dataTable tbody tr:hover td { background: #fcfcfd; }
            .jc-mode #dataTable tbody tr.jc-delayed td { border-color: #fecaca; }
            .jc-mode #dataTable tbody tr.jc-delayed td:first-child { box-shadow: inset 4px 0 0 #dc2626; }
            .jc-mode #dataTable tbody tr.jc-soon td:first-child { box-shadow: inset 4px 0 0 #f59e0b; }
            .jc-mode #dataTable tbody tr.jc-done td { border-color: #bbf7d0; }
            .jc-mode #dataTable tbody tr.jc-done td:first-child { box-shadow: inset 4px 0 0 #16a34a; }
            .jc { display: grid; grid-template-columns: 1.15fr 2.1fr 1fr 1.35fr; gap: 1.25rem; align-items: center; }
            @media (max-width: 1300px) { .jc { grid-template-columns: 1fr 1.6fr; } }
            .jc-no { font-size: 1.05rem; font-weight: 700; color: #1d4ed8; text-decoration: none; }
            .jc-dot { display: inline-flex; align-items: center; gap: .35rem; font-size: .75rem; font-weight: 600; }
            .jc-dot i { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
            .jc-route { display: flex; align-items: center; gap: .9rem; }
            .jc-city { font-size: 1.05rem; font-weight: 700; line-height: 1.15; max-width: 150px; }
            .jc-line { flex: 1; text-align: center; min-width: 120px; }
            .jc-line .jc-rail { position: relative; border-top: 2px solid #d1d5db; margin: .35rem 0; }
            .jc-line .jc-rail::before, .jc-line .jc-rail::after { content: ''; position: absolute; top: -5px; width: 8px; height: 8px; border-radius: 50%; background: #9ca3af; }
            .jc-line .jc-rail::before { left: 0; } .jc-line .jc-rail::after { right: 0; }
            .jc-line .jc-ico { position: absolute; left: 50%; top: -11px; transform: translateX(-50%); background: #fff; padding: 0 .35rem; color: #2563eb; }
            .jc-stat { display: flex; justify-content: space-between; font-size: .8rem; color: #6b7280; padding: .1rem 0; }
            .jc-stat b { color: #111827; }
            .jc-stats { border-left: 1px solid #e5e7eb; padding-left: 1.1rem; }
            .jc-act { text-align: right; }

        </style>
        <div class="pb-3">
            <div class="job-ai mb-3 d-none" id="jobAiPanel">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="job-ai-badge"><i class="bi bi-stars"></i> {{ __('AI Assistant') }}</div>
                    <div id="jobAiSummary" class="small text-secondary flex-grow-1 ms-2">{{ __('Reading your jobs...') }}</div>
                </div>
                <form id="jobAiForm" class="d-flex gap-2 mt-3">
                    <input type="text" id="jobAiInput" class="form-control rounded-pill" maxlength="500" autocomplete="off"
                           placeholder="{{ __('Ask about your jobs... e.g. which jobs need action today?') }}">
                    <button class="btn btn-primary rounded-pill px-4" type="submit"><i class="bi bi-send me-1"></i>{{ __('Ask') }}</button>
                </form>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="button" class="job-ai-chip">{{ __('What needs my attention today?') }}</button>
                    <button type="button" class="job-ai-chip">{{ __('Which jobs are late and what should I tell the customers?') }}</button>
                    <button type="button" class="job-ai-chip">{{ __('Which jobs have no invoice yet?') }}</button>
                    <button type="button" class="job-ai-chip">{{ __('Summarise this week arrivals') }}</button>
                </div>
                <div id="jobAiAnswer" class="d-none mt-3"></div>
            </div>
            <div class="row g-2" id="jobQuick">
                <div class="col-6 col-md"><button type="button" class="jq" id="jq-active" data-quick=""><div class="jq-n">0</div><div class="jq-l">{{ __('Open Jobs') }}</div></button></div>
                <div class="col-6 col-md"><button type="button" class="jq jq-delayed" id="jq-delayed" data-quick="delayed"><div class="jq-n">0</div><div class="jq-l">{{ __('Past ETA') }}</div></button></div>
                <div class="col-6 col-md"><button type="button" class="jq jq-arriving" id="jq-arriving" data-quick="arriving"><div class="jq-n">0</div><div class="jq-l">{{ __('Arriving This Week') }}</div></button></div>
                <div class="col-6 col-md"><button type="button" class="jq jq-clearance" id="jq-clearance" data-quick="clearance"><div class="jq-n">0</div><div class="jq-l">{{ __('In Clearance') }}</div></button></div>
                <div class="col-6 col-md"><button type="button" class="jq jq-unbilled" id="jq-unbilled" data-quick="unbilled"><div class="jq-n">0</div><div class="jq-l">{{ __('Not Invoiced') }}</div></button></div>
                <div class="col-6 col-md"><button type="button" class="jq" id="jq-noeta" data-quick="noeta"><div class="jq-n">0</div><div class="jq-l">{{ __('No ETA') }}</div></button></div>
            </div>
        </div>
        @else
        <div class="container-fluid pb-3">
            <div class="row g-3">

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-body-tertiary px-4 py-3 h-100">
                        <h6 class="text-uppercase text-muted fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Summary') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="summaryTotalJobs" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Total Jobs') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="summaryPendingJobs" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Pending') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="summaryCompletedJobs" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Completed') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-success-subtle px-4 py-3 h-100">
                        <h6 class="text-uppercase text-success-emphasis fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Completed Jobs') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div id="completedJobsCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Completed') }}</small>
                            </div>
                            <div class="col-6">
                                <div id="completionRate" class="fs-4 fw-bold mb-0">0%</div>
                                <small class="text-muted">{{ __('Completion Rate') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-warning-subtle px-4 py-3 h-100">
                        <h6 class="text-uppercase text-warning-emphasis fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Needs Attention') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div id="cancelledJobsCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Cancelled') }}</small>
                            </div>
                            <div class="col-6">
                                <div id="trashedJobsCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Trashed') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif

        <div class="shadow bdr-r-10 py-3 flex-grow-1 {{ ($fixedColumns ?? false) ? 'jc-mode' : '' }}">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                @if($fixedColumns ?? false)
                <div class="d-flex align-items-center gap-2 flex-wrap" id="jobModeChips">
                    <span class="text-muted small fw-semibold">{{ __('Mode') }}</span>
                    @foreach(['' => 'All', 'fcl' => 'FCL', 'lcl' => 'LCL', 'air' => 'Air', 'road' => 'Road'] as $k => $l)
                        <button type="button" class="btn btn-sm jc-chip {{ $k === '' ? 'active' : '' }}" data-mode="{{ $k }}">{{ __($l) }}</button>
                    @endforeach
                </div>
                @endif
                <div id="filtered-data">
                {{--<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small" style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button" class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close">&times;</button>
                </div>
                <div class="d-inline-flex bg-light border rounded-pill px-2 py-1 me-2 mb-2 small" style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button" class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close">&times;</button>
                </div>--}}
                </div>


                <!-- Example static label -->
                <!-- Example tag that will be dynamically created
                <div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small"
                     style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button"
                            class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close"
                            onclick="clearDateLabel()">
                        &times;
                    </button>
                </div>
                -->
                <div class="d-flex align-items-center gap-2">
                    @if($fixedColumns ?? false)
                    <span class="text-muted small fw-semibold">{{ __('Sort By') }}</span>
                    <select id="jobSort" class="form-select form-select-sm w-auto">
                        <option value="">{{ __('Newest') }}</option>
                        <option value="eta">{{ __('ETA (soonest)') }}</option>
                        <option value="oldest">{{ __('Oldest') }}</option>
                        <option value="job">{{ __('Job No') }}</option>
                    </select>
                    @endif
                    <select id="pageLength" class="form-select form-select-sm w-auto" aria-label="{{ __('Rows per page') }}">
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Empty / zero-records state (shown instead of table) -->
            <div id="jobEmptyState" class="d-none text-center py-5 px-4">
                <div id="emptyStateNoData">
                    <div class="mb-3">
                        <i class="bi bi-truck" style="font-size:3.5rem;color:#dde3ed;"></i>
                    </div>
                    <h5 class="fw-semibold text-muted mb-2">{{ __('No Jobs Yet') }}</h5>
                    <p class="text-muted small mb-4 mx-auto" style="max-width:400px;">
                        {{ __('Jobs created directly or converted from an accepted quotation will appear here.') }}
                    </p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <button class="btn btn-primary rounded-pill px-4" id="new-first">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('Create First Job') }}
                        </button>
                        <button class="btn btn-outline-secondary rounded-pill px-4"
                                data-bs-toggle="modal" data-bs-target="#jobWorkflowModal">
                            <i class="bi bi-diagram-3 me-1"></i> {{ __('How It Works') }}
                        </button>
                    </div>
                </div>
                <div id="emptyStateNoResults" class="d-none">
                    <div class="mb-3">
                        <i class="bi bi-search" style="font-size:3rem;color:#dde3ed;"></i>
                    </div>
                    <h5 class="fw-semibold text-muted mb-2">{{ __('No Results Found') }}</h5>
                    <p class="text-muted small mb-0">{{ __('Try adjusting your search or filter criteria.') }}</p>
                </div>
            </div>

            <!-- Table with scroll -->
            {{--<div class="">
                <table class="table align-middle dataTable" id="dataTable" data-title="Job" data-model-size="lg"
                       data-min-height="min-height:75vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>Job No</th>
                        <th>Customer</th>
                        <th>Services</th>
                        <th>POL</th>
                        <th>POD</th>
                        <th>Carrier / Lines</th>
                        <th class="text-end">Cus Inv.</th>
                        <th class="text-end">Job Date</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>--}}
            <div class="flex-grow-1" id="tableWrapper">
                <table class="table align-middle dataTable no-footer" id="dataTable" data-model-size="lg">
                    <thead>
                    <tr id="dtTheadRow" class="text-secondary small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;"></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="dtFooter" class="d-none d-flex justify-content-between align-items-center px-3 py-2 border-top small text-muted flex-shrink-0"></div>

            <style>
                .cs-drag-over {
                    border-color: #0d6efd !important;
                    background-color: #f0f7ff !important;
                }
                .x-small { font-size: 0.7rem; }
                /* Tooltip for priority icon */
                [title]:hover::after {
                    content: attr(title);
                    position: absolute;
                    background: #333;
                    color: #fff;
                    padding: 4px 8px;
                    font-size: 10px;
                    border-radius: 4px;
                    margin-top: -25px;
                }
                /* Clean flat table — no floating row cards, just a thin divider
                   between rows, matching the Customer Invoice list. */
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

            <!-- Rejected Quotes -->
            {{--<div class="rejected-box mt-4">
                <h6 class="fw-bold">Rejected Quotes List</h6>
                <p class="mb-1 text-muted">You rejected this quote & asked for edit.</p>
                <small class="text-muted">Date: 23 July, 2023 | ID: #241041080</small>
                <hr>
                <div class="d-flex align-items-center">
                    <div
                        class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                        style="width:40px; height:40px;">
                        H
                    </div>
                    <div>
                        <p class="mb-0 fw-bold">Himanshu Shrivastav</p>
                        <small class="text-muted">himanshu12@gmail.com</small>
                    </div>
                    <div class="ms-auto text-end">
                        <p class="mb-0">Quotation No<br><b>#131341</b></p>
                        <small class="text-muted">From DB Schenker</small>
                    </div>
                </div>
            </div>--}}
        </div>
    </main>
    @include('modules.email.send-email')
    @include('modules.job.job-view')
    @include('modules.common.linked-drawer', ['mainWidth' => 55, 'subWidth' => 40])

    @include('modules.workflows.job')

    @unless($fixedColumns ?? false)
    <!-- Column Settings Modal -->
    <div class="modal fade" id="columnSettingsModal" tabindex="-1" aria-labelledby="columnSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="columnSettingsModalLabel">
                            <i class="bi bi-columns-gap text-primary me-2"></i>{{ __('Column Settings') }}
                        </h5>
                        <p class="text-muted small mb-0">{{ __('Choose and arrange the columns shown in the job list') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0" style="min-height:500px;">
                    <div class="row g-0 h-100">

                        <!-- Left: Available Fields -->
                        <div class="col-md-4 border-end d-flex flex-column" style="max-height:520px;">
                            <div class="p-3 border-bottom bg-light">
                                <h6 class="fw-semibold mb-2 small text-uppercase text-muted">{{ __('Available Fields') }}</h6>
                                <input type="text" id="csFieldSearch" class="form-control form-control-sm rounded-pill"
                                       placeholder="{{ __('Search fields…') }}">
                            </div>
                            <div id="csFieldList" class="flex-grow-1 overflow-auto p-2"></div>
                        </div>

                        <!-- Right: Column Order -->
                        <div class="col-md-8 d-flex flex-column" style="max-height:520px;">
                            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                                <h6 class="fw-semibold mb-0 small text-uppercase text-muted">{{ __('Column Order') }}</h6>
                                <span class="text-muted" style="font-size:0.75rem;">
                                    <i class="bi bi-grip-vertical"></i> {{ __('Drag to reorder') }} &nbsp;·&nbsp;
                                    <i class="bi bi-chevron-down"></i> {{ __('Add sub-column') }}
                                </span>
                            </div>
                            <div id="csColumnList" class="flex-grow-1 overflow-auto p-3"></div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="border-top p-3 bg-light">
                        <h6 class="small fw-semibold text-muted text-uppercase mb-2">
                            <i class="bi bi-eye me-1"></i>{{ __('Preview') }}
                        </h6>
                        <div class="table-responsive" style="max-height:100px;overflow:auto;">
                            <table class="table table-sm table-bordered mb-0 text-nowrap" id="csPreviewTable">
                                <thead class="table-secondary">
                                    <tr id="csPreviewRow"></tr>
                                </thead>
                                <tbody>
                                    <tr id="csPreviewDataRow"></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill me-auto" id="csResetBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset to Default') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="csSaveBtn">
                        <i class="bi bi-check2 me-1"></i>{{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endunless
    <script>window.JOB_FIXED_COLUMNS = {{ ($fixedColumns ?? false) ? 'true' : 'false' }};</script>
</x-app-layout>
