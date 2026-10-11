@php $d = \App\Services\Dashboard\WidgetData::snapshot('payroll'); @endphp
<div class="kpi kpi-medium" style="--kc: #0ea5e9; --kbg: #e8f4fd;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-cash-coin"></i></span>
        <span class="kpi-title">{{ __('Payroll') }}</span>
        <span class="kpi-month-static">{{ $d['month'] }}</span>
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ number_format($d['net_total'], 0) }}</div>
        <span class="kpi-pill up">{{ $d['paid_pct'] }}% {{ __('paid') }}</span>
    </div>
    <div class="kpi-note">{{ $d['count'] ? __(':p of :t salaries paid', ['p' => $d['paid_count'], 't' => $d['count']]) : __('Payroll is not generated for this month yet.') }}</div>
    <div class="kpi-bar"><i style="width: {{ $d['paid_pct'] }}%"></i></div>
    <div class="kpi-legend kpi-legend-3">
        <span><em class="dot-a"></em>{{ __('Paid') }} <b>{{ number_format($d['paid_total'], 0) }}</b></span>
        <span><em class="dot-b"></em>{{ __('Pending') }} <b>{{ number_format($d['pending_total'], 0) }}</b></span>
        <span><em style="background:#f59e0b"></em>{{ __('Loan recovery') }} <b>{{ number_format($d['loan_total'], 0) }}</b></span>
    </div>
</div>
