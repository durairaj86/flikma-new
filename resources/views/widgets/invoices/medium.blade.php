{{-- Invoices widget, medium. Data ($d), $months and $month come from App\Livewire\Widgets\Invoices. --}}
@php
    $kColor = '#0d9488';
    $kData = [$d['approved'], $d['draft']];
    $kLabels = [];
    $kA = [$d['approved'], $d['approved']];
    $kB = [$d['draft'], $d['draft']];
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $kColor }}; --kbg: #d6fbe8;" wire:loading.class="wd-loading" wire:target="setMonth">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-file-invoice"></i></span>
        <span class="kpi-title">{{ __('Invoices') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ number_format($d['count']) }}</div>
            {!! \App\Support\WidgetFormat::pill($d['change']) !!}
            <div class="kpi-note">{{ __('Due') . ': ' . $d['due'] }}</div>
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
