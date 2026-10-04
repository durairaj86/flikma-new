{{-- Enquiry widget, small. Data ($d), $months and $month come from App\Livewire\Widgets\Enquiry. --}}
@php
    $kColor = '#0ea5e9';
    $kSum = max(1, (int) $d['total']);
    $kPct = round($d['confirmed'] / $kSum * 100);
@endphp
<div class="kpi kpi-small" style="--kc: {{ $kColor }}; --kbg: #e6f6fe;" wire:loading.class="wd-loading" wire:target="setMonth">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-envelope-open-text"></i></span>
        <span class="kpi-title">{{ __('Enquiry') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ $d['total'] }}</div>
        {!! \App\Support\WidgetFormat::pill($d['change']) !!}
    </div>
    <div class="kpi-note">{{ __('Enquiries') }} &middot; {{ $kPct }}% {{ __('confirmed') }}</div>
    <div class="kpi-bar"><i style="width: {{ $kPct }}%"></i></div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Confirmed') }} <b>{{ $d['confirmed'] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Pending') }} <b>{{ $d['pending'] }}</b></span>
    </div>
</div>
