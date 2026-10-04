{{-- Jobs widget, small. Data ($d), $months and $month come from App\Livewire\Widgets\Job. --}}
@php
    $kColor = '#2563eb';
    $kSum = max(1, (int) $d['total']);
    $kPct = round($d['completed'] / $kSum * 100);
@endphp
<div class="kpi kpi-small" style="--kc: {{ $kColor }}; --kbg: #e3efff;" wire:loading.class="wd-loading" wire:target="setMonth">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-truck-fast"></i></span>
        <span class="kpi-title">{{ __('Jobs') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ $d['total'] }}</div>
        {!! \App\Support\WidgetFormat::pill($d['change']) !!}
    </div>
    <div class="kpi-note">{{ __('Jobs') }} &middot; {{ $kPct }}% {{ __('completed') }}</div>
    <div class="kpi-bar"><i style="width: {{ $kPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Done') }} <b>{{ $d['completed'] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Active') }} <b>{{ $d['active'] }}</b></span>
    </div>
</div>
