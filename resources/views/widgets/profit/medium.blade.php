{{-- Profit widget, medium. Data ($d), $months and $month come from App\Livewire\Widgets\Profit. --}}
@php
    $kColor = '#16a34a';
    $kData = $d['series'];
    $kLabels = $d['labels'];
    $kA = [\App\Support\WidgetFormat::short($d['revenue']), $d['revenue']];
    $kB = [\App\Support\WidgetFormat::short($d['expenses']), $d['expenses']];
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $kColor }}; --kbg: #dcf7e3;" wire:loading.class="wd-loading" wire:target="setMonth">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-chart-line"></i></span>
        <span class="kpi-title">{{ __('Profit') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ number_format($d['total'], 2) }}</div>
            {!! \App\Support\WidgetFormat::pill($d['change']) !!}
            <div class="kpi-note">{{ __('Margin') . ' ' . $d['margin'] . '%' }}</div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="profitbar" data-color="{{ $kColor }}"
                    data-values='@json($kData)' data-labels='@json($kLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Revenue') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Expenses') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
