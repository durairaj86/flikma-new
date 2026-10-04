{{-- Quotation widget, small. Data ($d), $months and $month come from App\Livewire\Widgets\Quotation. --}}
@php
    $kColor = '#7c3aed';
    $kData = [$d['completed'], $d['approved']];
    $kLabels = [];
    $kA = [$d['completed'], $d['completed']];
    $kB = [$d['approved'], $d['approved']];
@endphp
<div class="kpi kpi-small" style="--kc: {{ $kColor }}; --kbg: #ffffff;" wire:loading.class="wd-loading" wire:target="setMonth">
    @php
        $kSum = max(0.0001, (float) $kA[1] + (float) $kB[1]);
        $kPct = round($kA[1] / $kSum * 100);
    @endphp
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-file-lines"></i></span>
        <span class="kpi-title">{{ __('Quotation') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ number_format($d['total'], 2) }}</div>
        {!! \App\Support\WidgetFormat::pill($d['change']) !!}
    </div>
    <div class="kpi-note">{{ $d['count'] . ' ' . __('Quotations') }}</div>
    <div class="kpi-bar"><i style="width: {{ $kPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Completed') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Approved') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
