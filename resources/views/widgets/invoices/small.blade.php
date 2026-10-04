{{-- Invoices widget, small. Data ($d), $months and $month come from App\Livewire\Widgets\Invoices. --}}
@php
    $kColor = '#0d9488';
    $kData = [$d['approved'], $d['draft']];
    $kLabels = [];
    $kA = [$d['approved'], $d['approved']];
    $kB = [$d['draft'], $d['draft']];
@endphp
<div class="kpi kpi-small" style="--kc: {{ $kColor }}; --kbg: #d6fbe8;" wire:loading.class="wd-loading" wire:target="setMonth">
    @php
        $kSum = max(0.0001, (float) $kA[1] + (float) $kB[1]);
        $kPct = round($kA[1] / $kSum * 100);
    @endphp
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-file-invoice"></i></span>
        <span class="kpi-title">{{ __('Invoices') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ number_format($d['count']) }}</div>
        {!! \App\Support\WidgetFormat::pill($d['change']) !!}
    </div>
    <div class="kpi-note">{{ __('Due') . ': ' . $d['due'] }}</div>
    <div class="kpi-bar"><i style="width: {{ $kPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Approved') }} <b>{{ $kA[0] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Draft') }} <b>{{ $kB[0] }}</b></span>
    </div>
</div>
