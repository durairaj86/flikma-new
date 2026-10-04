{{-- Total Sales widget, medium. Data ($d), $months and $month come from App\Livewire\Widgets\Sales. --}}
@php
    $kColor = '#2563eb';
    $kData = $d['series'];
    $kLabels = $d['labels'];
    $kA = [\App\Support\WidgetFormat::short($d['collected']), $d['collected']];
    $kB = [\App\Support\WidgetFormat::short($d['pending']), $d['pending']];
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $kColor }}; --kbg: #ffffff;" wire:loading.class="wd-loading" wire:target="setMonth">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-dollar-sign"></i></span>
        <span class="kpi-title">{{ __('Total Sales') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ number_format($d['total'], 2) }}</div>
            {!! \App\Support\WidgetFormat::pill($d['change']) !!}
            <div class="kpi-note">{{ $d['count'] . ' ' . __('Invoices') }}</div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="line" data-color="{{ $kColor }}"
                    data-values='@json($kData)' data-labels='@json($kLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Collected') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Pending') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
