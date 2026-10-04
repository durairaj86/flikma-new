{{-- Profit widget, small. Data ($d), $months and $month come from App\Livewire\Widgets\Profit. --}}
@php
    $kColor = '#16a34a';
    $kData = $d['series'];
    $kLabels = $d['labels'];
    $kA = [\App\Support\WidgetFormat::short($d['revenue']), $d['revenue']];
    $kB = [\App\Support\WidgetFormat::short($d['expenses']), $d['expenses']];
@endphp
<div class="kpi kpi-small" style="--kc: {{ $kColor }}; --kbg: #dcf7e3;" wire:loading.class="wd-loading" wire:target="setMonth">
    @php
        $kSum = max(0.0001, (float) $kA[1] + (float) $kB[1]);
        $kPct = round($kA[1] / $kSum * 100);
    @endphp
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-chart-line"></i></span>
        <span class="kpi-title">{{ __('Profit') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ number_format($d['total'], 2) }}</div>
        {!! \App\Support\WidgetFormat::pill($d['change']) !!}
    </div>
    <div class="kpi-note">{{ __('Margin') . ' ' . $d['margin'] . '%' }}</div>
    <div class="kpi-bar"><i style="width: {{ $kPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Revenue') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Expenses') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
