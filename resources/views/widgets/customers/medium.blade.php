{{-- Customers widget, medium. Data ($d), $months and $month come from App\Livewire\Widgets\Customers. --}}
@php
    $kColor = '#ea8a0c';
    $kData = $d['series'];
    $kLabels = $d['labels'];
    $kA = [$d['new'], $d['new']];
    $kB = [$d['prevNew'], $d['prevNew']];
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $kColor }}; --kbg: #fff0d9;" wire:loading.class="wd-loading" wire:target="setMonth">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-users"></i></span>
        <span class="kpi-title">{{ __('Customers') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ number_format($d['total']) }}</div>
            {!! \App\Support\WidgetFormat::pill($d['change']) !!}
            <div class="kpi-note">{{ $d['new'] . ' ' . __('New') }}</div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="bar" data-color="{{ $kColor }}"
                    data-values='@json($kData)' data-labels='@json($kLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('This month') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Last month') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
