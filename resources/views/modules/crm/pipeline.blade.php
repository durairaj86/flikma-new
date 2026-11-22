@section('page-title', __('Sales Pipeline'))
@section('js','crm_pipeline')
@section('hide-topbar', true)
<x-app-layout>
    <style>
        .crm-board { display: grid; grid-template-columns: repeat(5, minmax(230px, 1fr)); gap: 12px; overflow-x: auto; padding-bottom: 8px; }
        .crm-col { background: #f6f7f9; border-radius: 10px; display: flex; flex-direction: column; min-height: 60vh; max-height: calc(100vh - 230px); }
        .crm-col-head { padding: 10px 12px 8px; border-bottom: 1px solid #e5e7eb; }
        .crm-col-head .crm-col-title { font-weight: 600; font-size: .9rem; }
        .crm-col-head .crm-col-meta { font-size: .78rem; color: #6b7280; }
        .crm-col-head .crm-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-inline-end: 6px; }
        .crm-col-body { padding: 8px; overflow-y: auto; flex: 1; }
        .crm-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 8px 10px; margin-bottom: 8px; cursor: pointer; font-size: .82rem; outline: none; }
        .crm-card:hover { border-color: #93c5fd; }
        .crm-card:focus { border-color: #0d6efd; box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .2); }
        .crm-card .crm-card-name { font-weight: 600; color: #111827; }
        .crm-card .crm-card-no { color: #6b7280; font-size: .75rem; }
        .crm-card .crm-chip { font-size: .7rem; border-radius: 10px; padding: 1px 7px; background: #eef2ff; color: #3730a3; white-space: nowrap; }
        .crm-card .crm-chip.overdue { background: #fee2e2; color: #b91c1c; }
        .crm-card .crm-chip.today { background: #fef3c7; color: #92400e; }
        .crm-card .crm-chip.none { background: #f3f4f6; color: #6b7280; }
        .crm-card .crm-chip.lost { background: #fee2e2; color: #991b1b; }
        .crm-empty { color: #9ca3af; font-size: .8rem; text-align: center; padding: 16px 0; }
        .crm-more { color: #6b7280; font-size: .75rem; text-align: center; }
        .crm-kpi { border: 1px solid #e5e7eb; border-radius: 10px; padding: 6px 14px; font-size: .8rem; color: #6b7280; }
        .crm-kpi b { color: #111827; font-size: .95rem; }
        .crm-tl-icon { width: 28px; height: 28px; border-radius: 50%; background: #eef2ff; color: #3730a3; display: flex; align-items: center; justify-content: center; }
        #crmDrawer { width: 520px; max-width: 100%; }
        /* while a modal is open (Log Activity from the drawer) the drawer goes below the modal (1055) and its backdrop */
        body.modal-open #crmDrawer { z-index: 1050; }
    </style>
    <main class="gmail-content bg-white px-3">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-2">
            <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="crmSearch" class="form-control rounded-pill ps-5" style="width:220px"
                           placeholder="{{ __('Search name or number...') }}" aria-label="{{ __('Search name or number...') }}">
                </div>
                <div id="crmFilters" class="d-flex align-items-center gap-2">
                <div style="width:190px"><select id="crmSalesperson" class="tom-select" data-live-search="true" aria-label="{{ __('Salesperson') }}">
                    {{-- a salesman only has his own records here (the server holds him to them too) --}}
                    @if(!$own || count($salespersons) > 1)
                        <option value="">{{ $own ? __('All mine') : __('All Salesmen') }}</option>
                    @endif
                    @foreach($salespersons as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select></div>
                <div style="width:190px"><select id="crmDays" class="tom-select" aria-label="{{ __('Won / lost in') }}">
                    <option value="30">{{ __('Won / lost: 30 days') }}</option>
                    <option value="90" selected>{{ __('Won / lost: 90 days') }}</option>
                    <option value="180">{{ __('Won / lost: 180 days') }}</option>
                    <option value="365">{{ __('Won / lost: 1 year') }}</option>
                </select></div>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new" title="Alt+N">{{ __('Log Activity') }}</button>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap pb-3" id="crmKpis">
            <div class="crm-kpi">{{ __('Open quotations') }}: <b id="kpiQuoted">0</b></div>
            <div class="crm-kpi">{{ __('Won') }}: <b id="kpiWon">0</b></div>
            <div class="crm-kpi">{{ __('Win rate') }}: <b id="kpiWinRate">—</b></div>
            <div class="crm-kpi text-muted small align-self-center border-0">{{ __('Arrow keys move between cards, Enter opens one.') }}</div>
        </div>

        <div class="crm-board" id="crmBoard">
            @foreach([
                'lead' => ['label' => __('Leads'), 'color' => '#9ca3af', 'hint' => __('Prospects without an enquiry')],
                'enquiry' => ['label' => __('Enquiries'), 'color' => '#3b82f6', 'hint' => __('Pending and confirmed')],
                'quoted' => ['label' => __('Quoted'), 'color' => '#f59e0b', 'hint' => __('Waiting for the customer')],
                'won' => ['label' => __('Won'), 'color' => '#10b981', 'hint' => __('Accepted or converted to a job')],
                'lost' => ['label' => __('Lost'), 'color' => '#ef4444', 'hint' => __('Cancelled, expired or lost')],
            ] as $key => $col)
                <section class="crm-col" data-col="{{ $key }}" aria-label="{{ $col['label'] }}">
                    <div class="crm-col-head">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="crm-col-title"><span class="crm-dot" style="background:{{ $col['color'] }}"></span>{{ $col['label'] }}</span>
                            <span class="badge rounded-pill bg-white text-dark border crm-col-count">0</span>
                        </div>
                        <div class="crm-col-meta"><span class="crm-col-value"></span> <span>{{ $col['hint'] }}</span></div>
                    </div>
                    <div class="crm-col-body"><div class="crm-empty">{{ __('Loading...') }}</div></div>
                </section>
            @endforeach
        </div>
    </main>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="crmDrawer" aria-labelledby="crmDrawerTitle">
        <div class="offcanvas-header border-bottom py-2">
            <div>
                <div class="small text-muted" id="crmDrawerKind"></div>
                <h5 class="offcanvas-title mb-0" id="crmDrawerTitle"></h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body px-4 py-3">
            <div id="crmDrawerFacts" class="small mb-3"></div>
            <div class="d-flex flex-wrap gap-2 mb-3" id="crmDrawerActions"></div>
            <div class="fw-semibold small text-muted border-bottom pb-1 mb-1">{{ __('Activity') }}</div>
            <div id="crmDrawerTimeline"></div>
        </div>
    </div>
</x-app-layout>
