{{-- Small dashboard widget: Cost Summary (this month) as a donut of cost shares. --}}
@php
    $cNames = [__('Material'), __('Labour'), __('Transport')];
    $cColors = ['#2563eb', '#f59e0b', '#14b8a6'];
    $cValues = [(float) $materialPercent, (float) $labourPercent, (float) $transportPercent];
    $cColor = '#7c3aed';
    $cParts = [
        [__('Material'), $materialPercent, '#2563eb'],
        [__('Labour'), $labourPercent, '#f59e0b'],
        [__('Transport'), $transportPercent, '#14b8a6'],
    ];
@endphp
<div class="kpi kpi-small" style="--kc: {{ $cColor }}; --kbg: #f1eaff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-coins"></i></span>
        <span class="kpi-title">{{ __('Cost Summary') }}</span>
    </div>
    <div class="kpi-note" style="margin-top:2px;">{{ __('This month') }}</div>
    <div class="kpi-medium-chart" style="width:100%;height:78px;margin-top:6px;">
        <canvas class="kpi-chart" style="position:absolute;inset:0;width:100%;height:100%;" data-type="donut" data-color="{{ $cColor }}"
                data-names='@json($cNames)'
                data-colors='@json($cColors)'
                data-values='@json($cValues)' data-labels='[]'></canvas>
    </div>
    <div style="margin-top:6px;">
        @foreach($cParts as $p)
            <div class="kpi-cost-row" style="font-size:.76rem;padding:2px 0;"><em style="background:{{ $p[2] }}"></em>{{ $p[0] }} <b>{{ $p[1] }}%</b></div>
        @endforeach
    </div>
</div>
