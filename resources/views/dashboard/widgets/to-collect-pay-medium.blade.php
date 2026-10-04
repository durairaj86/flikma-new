{{-- Medium dashboard widget: To Collect / To Pay (receivables vs payables) with a donut. Live snapshot. --}}
@php
    $pColor = '#0d9488';
    $pNames = [__('To Collect'), __('To Pay')];
    $pColors = ['#f59e0b', '#dc2626'];
    $pValues = [(float) $toCollect, (float) $toPay];
    $pNet = $toCollect - $toPay;
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $pColor }}; --kbg: #e0f7f1;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-money-bill-transfer"></i></span>
        <span class="kpi-title">{{ __('Payments') }}</span>
        <span class="kpi-month-static">{{ __('Live') }}</span>
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ number_format($pNet, 0) }}</div>
            <div class="kpi-note">{{ __('Net') }} ({{ __('To Collect') }} &minus; {{ __('To Pay') }})</div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="donut" data-color="{{ $pColor }}"
                    data-names='@json($pNames)' data-colors='@json($pColors)'
                    data-values='@json($pValues)' data-labels='[]'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em style="background:#f59e0b"></em>{{ __('To Collect') }} <b>₹{{ number_format($toCollect, 0) }}</b></span>
        <span><em style="background:#dc2626"></em>{{ __('To Pay') }} <b>₹{{ number_format($toPay, 0) }}</b></span>
    </div>
</div>
