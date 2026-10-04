{{-- Medium dashboard widget: Revenue Summary (net revenue MTD) with a 7-day sparkline. --}}
@php
    $rColor = '#16a34a';
    $rLabels = ['D1', 'D2', 'D3', 'D4', 'D5', 'D6', 'D7'];
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $rColor }}; --kbg: #dcf7e3;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-sack-dollar"></i></span>
        <span class="kpi-title">{{ __('Revenue Summary') }}</span>
        <span class="kpi-month-static">{{ __('MTD') }}</span>
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ number_format($currentMonthSales, 0) }}</div>
            <div class="kpi-note">{{ __('Net revenue (MTD)') }}</div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="line" data-color="{{ $rColor }}"
                    data-values='@json($dailyRevenueData)' data-labels='@json($rLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em style="background:#16a34a"></em>{{ __('Collected') }} <b>{{ number_format($currentMonthCollected, 0) }}</b></span>
        <span><em style="background:#dc2626"></em>{{ __('Pending') }} <b>{{ number_format($currentMonthPending, 0) }}</b></span>
    </div>
</div>
