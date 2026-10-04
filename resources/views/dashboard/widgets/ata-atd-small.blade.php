{{-- Small dashboard widget: ATA / ATD. Uses $shipmentSeries; live snapshot, no month filter. --}}
@php
    $sLabels = $shipmentSeries['weekLabels'];
    $sDatasets = [
        ['label' => __('ATA'), 'data' => $shipmentSeries['ata'], 'color' => '#0891b2'],
        ['label' => __('ATD'), 'data' => $shipmentSeries['atd'], 'color' => '#dc2626'],
    ];
    $sArr = $shipmentExtra['onTime'] + $shipmentExtra['late'];
    $sPct = $sArr > 0 ? round($shipmentExtra['onTime'] / $sArr * 100) : 0;
@endphp
<div class="kpi kpi-small" style="--kc: #0891b2; --kbg: #e0f7fa;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-truck"></i></span>
        <span class="kpi-title">ATA / ATD</span>
    </div>
    <div class="kpi-note" style="margin-top:2px;">{{ __('This week') }}</div>
    <div class="kpi-duo" style="gap:14px;margin-top:8px;">
        <div>
            <div class="kpi-value" style="color: #0891b2">{{ $ataThisWeek }}</div>
            <div class="kpi-note">{{ __('ATA This Week') }}</div>
        </div>
        <div>
            <div class="kpi-value" style="color: #dc2626">{{ $atdThisWeek }}</div>
            <div class="kpi-note">{{ __('ATD This Week') }}</div>
        </div>
    </div>
    <div class="kpi-bar"><i style="width: {{ $sPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em style="background:#16a34a"></em>{{ __('On time') }} <b>{{ $shipmentExtra['onTime'] }}</b></span>
        <span><em style="background:#dc2626"></em>{{ __('Late') }} <b>{{ $shipmentExtra['late'] }}</b></span>
    </div>
</div>
