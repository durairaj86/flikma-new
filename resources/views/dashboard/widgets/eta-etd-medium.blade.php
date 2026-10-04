{{-- Medium dashboard widget: ETA / ETD. Uses $shipmentSeries; live snapshot, no month filter. --}}
@php
    $sLabels = $shipmentSeries['upcomingLabels'];
    $sDatasets = [
        ['label' => __('ETA'), 'data' => $shipmentSeries['eta'], 'color' => '#2563eb'],
        ['label' => __('ETD'), 'data' => $shipmentSeries['etd'], 'color' => '#16a34a'],
    ];
@endphp
<div class="kpi kpi-medium" style="--kc: #2563eb; --kbg: #e3efff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-ship"></i></span>
        <span class="kpi-title">{{ __('ETA / ETD') }}</span>
        <span class="kpi-month-static">{{ __('Next 7 days') }}</span>
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text kpi-duo">
            <div>
                <div class="kpi-value" style="color: #2563eb">{{ $etaToday }}</div>
                <div class="kpi-note">{{ __('ETA Today') }}</div>
            </div>
            <div>
                <div class="kpi-value" style="color: #16a34a">{{ $etdTomorrow }}</div>
                <div class="kpi-note">{{ __('ETD Tomorrow') }}</div>
            </div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="dualbar" data-color="#2563eb"
                    data-datasets='@json($sDatasets)' data-values='[]' data-labels='@json($sLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em style="background:#2563eb"></em>{{ __('ETA') }} <b>{{ array_sum($shipmentSeries['eta']) }}</b></span>
        <span><em style="background:#16a34a"></em>{{ __('ETD') }} <b>{{ array_sum($shipmentSeries['etd']) }}</b></span>
    </div>
</div>
