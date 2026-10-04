{{-- Customers widget, small. Data ($d), $months and $month come from App\Livewire\Widgets\Customers. --}}
@php
    $kColor = '#ea8a0c';
    $kData = $d['series'];
    $kLabels = $d['labels'];
    $kA = [$d['new'], $d['new']];
    $kB = [$d['prevNew'], $d['prevNew']];
@endphp
<div class="kpi kpi-small" style="--kc: {{ $kColor }}; --kbg: #fff0d9;" wire:loading.class="wd-loading" wire:target="setMonth">
    @php
        $kSum = max(0.0001, (float) $kA[1] + (float) $kB[1]);
        $kPct = round($kA[1] / $kSum * 100);
    @endphp
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-users"></i></span>
        <span class="kpi-title">{{ __('Customers') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ number_format($d['total']) }}</div>
        {!! \App\Support\WidgetFormat::pill($d['change']) !!}
    </div>
    <div class="kpi-note">{{ $d['new'] . ' ' . __('New') }}</div>
    <div class="kpi-bar"><i style="width: {{ $kPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('This month') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Last month') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
