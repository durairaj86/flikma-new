{{-- Small widget: quote -> confirmed this quarter (the CRM Dashboard's conversion). --}}
@if(!userCan('sales.crm-dashboard'))
    @include('dashboard.widgets.crm-locked', ['size' => 'small', 'title' => __('Win rate')])
@else
@php
    $cwQuarter = collect(\App\Services\Crm\CrmDashboard::forWidgets()->conversion())->get(2);
    $cwRate = $cwQuarter['quote_rate'] ?? null;
@endphp
<a href="/reports/win-loss" class="text-decoration-none text-reset d-block h-100">
    <div class="kpi kpi-small" style="--kc: #16a34a; --kbg: #e9f8ef;">
        <div class="kpi-head">
            <span class="kpi-icon"><i class="bi bi-trophy"></i></span>
            <span class="kpi-title">{{ __('Win rate') }}</span>
        </div>
        <div class="kpi-value" style="margin-top: 8px;">{{ $cwRate === null ? '—' : round($cwRate) . '%' }}</div>
        <div class="kpi-note">{{ __('Quotes confirmed this quarter') }}</div>
        <div class="kpi-bar"><i style="width: {{ min(100, (int) round($cwRate ?? 0)) }}%"></i></div>
        <div class="kpi-legend">
            <span><em style="background:#c2410c"></em>{{ __('Quotes sent') }} <b>{{ $cwQuarter['quotes'] ?? 0 }}</b></span>
            <span><em style="background:#16a34a"></em>{{ __('Confirmed') }} <b>{{ $cwQuarter['confirmed'] ?? 0 }}</b></span>
        </div>
    </div>
</a>
@endif
