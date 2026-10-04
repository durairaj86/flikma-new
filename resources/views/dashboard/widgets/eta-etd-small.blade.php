{{-- Small dashboard widget: ETA / ETD. Uses $shipmentSeries; live snapshot, no month filter. --}}
@php
    $sLabels = $shipmentSeries['upcomingLabels'];
    $sDatasets = [
        ['label' => __('ETA'), 'data' => $shipmentSeries['eta'], 'color' => '#2563eb'],
        ['label' => __('ETD'), 'data' => $shipmentSeries['etd'], 'color' => '#16a34a'],
    ];
    $sTotal = array_sum($shipmentSeries['eta']) + array_sum($shipmentSeries['etd']);
    $sPct = $sTotal > 0 ? round(array_sum($shipmentSeries['eta']) / $sTotal * 100) : 0;
@endphp
<div class="kpi kpi-small" style="--kc: #2563eb; --kbg: #e3efff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-ship"></i></span>
        <span class="kpi-title">ETA / ETD</span>
    </div>
    <div class="kpi-note" style="margin-top:2px;">{{ __('Next 7 days') }}</div>
    <div class="kpi-duo" style="gap:14px;margin-top:8px;">
        <div>
            <div class="kpi-value" style="color: #2563eb">{{ $etaToday }}</div>
            <div class="kpi-note">{{ __('ETA Today') }}</div>
        </div>
        <div>
            <div class="kpi-value" style="color: #16a34a">{{ $etdTomorrow }}</div>
            <div class="kpi-note">{{ __('ETD Tomorrow') }}</div>
        </div>
    </div>
    <div class="kpi-bar"><i style="width: {{ $sPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em style="background:#dc2626"></em>{{ __('Overdue') }} <b>{{ $shipmentExtra['overdueEta'] }}</b></span>
        <span><em style="background:#f59e0b"></em>{{ __('Late dep.') }} <b>{{ $shipmentExtra['lateDeparture'] }}</b></span>
    </div>
</div>
