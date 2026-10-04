{{-- Total Sales widget, small. Data ($d), $months and $month come from App\Livewire\Widgets\Sales. --}}
@php
    $kColor = '#2563eb';
    $kData = $d['series'];
    $kLabels = $d['labels'];
    $kA = [\App\Support\WidgetFormat::short($d['collected']), $d['collected']];
    $kB = [\App\Support\WidgetFormat::short($d['pending']), $d['pending']];
@endphp
<div class="kpi kpi-small" style="--kc: {{ $kColor }}; --kbg: #ffffff;" wire:loading.class="wd-loading" wire:target="setMonth">
    @php
        $kSum = max(0.0001, (float) $kA[1] + (float) $kB[1]);
        $kPct = round($kA[1] / $kSum * 100);
    @endphp
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-dollar-sign"></i></span>
        <span class="kpi-title">{{ __('Total Sales') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ number_format($d['total'], 2) }}</div>
        {!! \App\Support\WidgetFormat::pill($d['change']) !!}
    </div>
    <div class="kpi-note">{{ $d['count'] . ' ' . __('Invoices') }}</div>
    <div class="kpi-bar"><i style="width: {{ $kPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Collected') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Pending') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
