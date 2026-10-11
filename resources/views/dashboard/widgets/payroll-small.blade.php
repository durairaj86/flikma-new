@php $d = \App\Services\Dashboard\WidgetData::snapshot('payroll'); @endphp
@php $kc = '#0ea5e9'; @endphp
<div class="kpi kpi-small" style="--kc: #0ea5e9; --kbg: #e8f4fd;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-cash-coin"></i></span>
        <span class="kpi-title">{{ __('Payroll') }}</span>
    </div>
    <div class="kpi-main"><div class="kpi-value">{{ number_format($d['net_total'], 0) }}</div></div>
    <div class="kpi-note">{{ $d['count'] ? __(':p of :t paid', ['p' => $d['paid_count'], 't' => $d['count']]) : __('Not generated yet') }}</div>
    <div class="kpi-bar"><i style="width: {{ $d['paid_pct'] }}%"></i></div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Paid') }} <b>{{ \App\Support\WidgetFormat::short($d['paid_total']) }}</b></span>
        <span><em class="dot-b"></em>{{ __('Pending') }} <b>{{ \App\Support\WidgetFormat::short($d['pending_total']) }}</b></span>
    </div>
</div>
