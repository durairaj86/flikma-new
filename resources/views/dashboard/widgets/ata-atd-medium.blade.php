{{-- Medium dashboard widget: ATA / ATD. Uses $shipmentSeries; live snapshot, no month filter. --}}
@php
    $sLabels = $shipmentSeries['weekLabels'];
    $sDatasets = [
        ['label' => __('ATA'), 'data' => $shipmentSeries['ata'], 'color' => '#0891b2'],
        ['label' => __('ATD'), 'data' => $shipmentSeries['atd'], 'color' => '#dc2626'],
    ];
@endphp
<div class="kpi kpi-medium" style="--kc: #0891b2; --kbg: #e0f7fa;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-truck"></i></span>
        <span class="kpi-title">{{ __('ATA / ATD') }}</span>
        <span class="kpi-month-static">{{ __('This week') }}</span>
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text kpi-duo">
            <div>
                <div class="kpi-value" style="color: #0891b2">{{ $ataThisWeek }}</div>
                <div class="kpi-note">{{ __('ATA This Week') }}</div>
            </div>
            <div>
                <div class="kpi-value" style="color: #dc2626">{{ $atdThisWeek }}</div>
                <div class="kpi-note">{{ __('ATD This Week') }}</div>
            </div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="dualbar" data-color="#0891b2"
                    data-datasets='@json($sDatasets)' data-values='[]' data-labels='@json($sLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em style="background:#0891b2"></em>{{ __('ATA') }} <b>{{ array_sum($shipmentSeries['ata']) }}</b></span>
        <span><em style="background:#dc2626"></em>{{ __('ATD') }} <b>{{ array_sum($shipmentSeries['atd']) }}</b></span>
    </div>
</div>
