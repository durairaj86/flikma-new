{{-- Collection widget, medium. Data ($d), $months and $month come from App\Livewire\Widgets\Collection. --}}
@php
    $kColor = '#0284c7';
    $kData = [$d['approved'], $d['draft']];
    $kLabels = [];
    $kA = [\App\Support\WidgetFormat::short($d['approved']), $d['approved']];
    $kB = [\App\Support\WidgetFormat::short($d['draft']), $d['draft']];
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $kColor }}; --kbg: #e3efff;" wire:loading.class="wd-loading" wire:target="setMonth">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-hand-holding-dollar"></i></span>
        <span class="kpi-title">{{ __('Collection') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ \App\Support\WidgetFormat::short($d['total']) }}</div>
            {!! \App\Support\WidgetFormat::pill($d['change']) !!}
            <div class="kpi-note">{{ $d['count'] . ' ' . __('Collections') }}</div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="donut" data-color="{{ $kColor }}"
                    data-values='@json($kData)' data-labels='@json($kLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Approved') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Draft') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
