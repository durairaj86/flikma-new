@section('page-title', __('Customer Tracker'))
@section('js','crm_tracker')
@section('hide-topbar', true)
@php($statusCategory = ['Prospect' => 'outreach', 'Following Up' => 'outreach', 'RFQ Received' => 'quote', 'Quoted' => 'quote', 'Negotiating' => 'quote',
    'Confirmed' => 'won', 'Active' => 'won', 'On Hold' => 'lost', 'Lost' => 'lost', 'Inactive' => 'waiting'])
<x-app-layout>
    <style>
        #dataTable thead th:first-child { border-top-left-radius: 10px; }
        #dataTable thead th:last-child { border-top-right-radius: 10px; }
        #dataTable td { font-size: .82rem; vertical-align: top; }
        .trk-kpi { border: 1px solid #e5e7eb; border-radius: 10px; padding: 6px 14px; font-size: .8rem; color: #6b7280; }
        .trk-kpi b { color: #111827; font-size: .95rem; }
        .trk-rank { display: inline-block; min-width: 1.6rem; text-align: center; border-radius: 6px; font-weight: 700; padding: 1px 6px; background: #eef2ff; color: #3730a3; }
        .trk-rank.r-A { background: #dcfce7; color: #166534; } .trk-rank.r-B { background: #dbeafe; color: #1e40af; }
        .trk-rank.r-C { background: #fef3c7; color: #92400e; } .trk-rank.r-D { background: #f3f4f6; color: #4b5563; }
        .trk-late { color: #b91c1c; font-weight: 600; }
    </style>
    <main class="gmail-content bg-white px-3">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-2">
            <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="/sales/pipeline" class="btn btn-outline-secondary rounded-pill px-3"><i class="bi bi-kanban me-1"></i>{{ __('Pipeline') }}</a>
                <button type="button" class="btn btn-outline-success rounded-pill px-3" id="trkExport"><i class="bi bi-file-excel me-1"></i>{{ __('Excel') }}</button>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap pb-2">
            <div class="trk-kpi">{{ __('Lines') }}: <b id="kpiLines">0</b></div>
            <div class="trk-kpi">{{ __('No contact :n+ days', ['n' => \App\Services\Crm\CrmSettings::get('stale_days')]) }}: <b id="kpiStale" class="text-danger">0</b></div>
            <div class="trk-kpi">{{ __('Planned, overdue') }}: <b id="kpiOverdue" class="text-danger">0</b></div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap pb-3" id="trkFilters">
            <div style="width:190px"><select id="trkShow" class="tom-select" aria-label="{{ __('Show') }}">
                <option value="open">{{ __('Open lines') }}</option>
                <option value="attention">{{ __('Needs attention') }}</option>
                <option value="all">{{ __('All, incl. lost / inactive') }}</option>
            </select></div>
            <div style="width:180px"><select id="trkSalesperson" class="tom-select" data-live-search="true" aria-label="{{ __('Salesperson') }}">
                @if(!$own || count($salespersons) > 1)
                    <option value="">{{ $own ? __('All mine') : __('All Salesmen') }}</option>
                @endif
                @foreach($salespersons as $sp)
                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                @endforeach
            </select></div>
            <div style="width:130px"><select id="trkRank" class="tom-select" aria-label="{{ __('Rank') }}">
                <option value="">{{ __('All ranks') }}</option>
                @foreach(array_keys(\App\Services\Crm\CrmSettings::RANKS) as $r)
                    <option value="{{ $r }}">{{ __('Rank') }} {{ $r }}</option>
                @endforeach
            </select></div>
            <div style="width:170px"><select id="trkStatus" class="tom-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(\App\Services\Crm\CustomerTracker::STATUSES as $st)
                    <option value="{{ $st }}">{{ __($st) }}</option>
                @endforeach
            </select></div>
            <div class="search-box position-relative ms-auto">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="customSearch" class="form-control rounded-pill ps-5" style="width:220px"
                       placeholder="{{ __('Search name, contact, route...') }}" aria-label="{{ __('Search name, contact, route...') }}">
            </div>
        </div>
        <script>window.TRACKER_STATUS_STYLE = @json(collect($statusCategory)->map(fn($c) => \App\Models\Crm\CrmAction::badgeStyle($c)));</script>
        <div class="border bdr-r-10 pb-3 flex-grow-1 table-responsive">
            <table class="table align-middle dataTable" id="dataTable" data-title="{{ __('Customer Tracker') }}">
                <thead class="table-light sticky-top bg-white">
                <tr>
                    <th>#</th>
                    <th>{{ __('Customer / Company') }}</th>
                    <th>{{ __('Rank') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Sector / Route') }}</th>
                    <th>{{ __('Handled By') }}</th>
                    <th class="text-end">{{ __('Selling / Margin') }}</th>
                    <th class="text-end" title="{{ __('Touches x :t + RFQs x :r + Jobs x :j', ['t' => \App\Services\Crm\CrmSettings::get('points_touch'), 'r' => \App\Services\Crm\CrmSettings::get('points_rfq'), 'j' => \App\Services\Crm\CrmSettings::get('points_job')]) }}">{{ __('Score') }}</th>
                    <th>{{ __('Last Entry') }}</th>
                    <th>{{ __('Next Planned') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </main>
</x-app-layout>
