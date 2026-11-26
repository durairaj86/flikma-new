{{-- Medium widget: open customer lines by status (donut) and the value of open quotes. --}}
@if(!userCan('sales.crm-dashboard'))
    @include('dashboard.widgets.crm-locked', ['size' => 'medium', 'title' => __('Pipeline by status')])
@else
@php
    $cpLines = \App\Services\Crm\CrmDashboard::forWidgets()->lines();
    $cpColors = ['Prospect' => '#94a3b8', 'Following Up' => '#5b7db1', 'RFQ Received' => '#e69138', 'Quoted' => '#f59e0b', 'Negotiating' => '#b45309',
        'Confirmed' => '#16a34a', 'Active' => '#0f766e', 'On Hold' => '#dc2626'];
    $cpGroups = collect($cpColors)->map(fn($c, $st) => $cpLines->where('status', $st)->count())->filter();
    $cpQuoteValue = $cpLines->whereIn('status', ['Quoted', 'Negotiating'])->sum('selling');
    $cpCurrency = \Illuminate\Support\Facades\DB::table('companies')->where('id', companyId())->value('base_currency') ?: 'SAR';
@endphp
<a href="/sales/pipeline" class="text-decoration-none text-reset d-block h-100">
    <div class="kpi kpi-medium" style="--kc: #f59e0b; --kbg: #fff7e6;">
        <div class="kpi-head">
            <span class="kpi-icon"><i class="bi bi-kanban"></i></span>
            <span class="kpi-title">{{ __('Pipeline by status') }}</span>
            <span class="kpi-month-static">{{ __('Live') }}</span>
        </div>
        <div class="kpi-medium-body">
            <div class="kpi-medium-text">
                <div class="kpi-value">{{ $cpLines->count() }}</div>
                <div class="kpi-note">{{ __('Open customer lines') }}</div>
                <span class="kpi-pill up">{{ amountFormat($cpQuoteValue) }} {{ $cpCurrency }} {{ __('quoted') }}</span>
            </div>
            <div class="kpi-medium-chart">
                <canvas class="kpi-chart" data-type="donut" data-color="#f59e0b"
                        data-names='@json($cpGroups->keys()->map(fn($s) => __($s))->values())'
                        data-colors='@json($cpGroups->keys()->map(fn($s) => $cpColors[$s])->values())'
                        data-values='@json($cpGroups->values())' data-labels='[]'></canvas>
            </div>
        </div>
        <div class="kpi-legend kpi-legend-3">
            @forelse($cpGroups->sortDesc()->take(3) as $st => $n)
                <span><em style="background: {{ $cpColors[$st] }}"></em>{{ __($st) }} <b>{{ $n }}</b></span>
            @empty
                <span class="text-muted">{{ __('No open lines yet.') }}</span>
            @endforelse
        </div>
    </div>
</a>
@endif
