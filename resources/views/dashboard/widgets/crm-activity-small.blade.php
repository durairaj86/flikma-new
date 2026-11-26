{{-- Small widget: CRM entries this week vs last week, with the last 8 weeks as bars. --}}
@if(!userCan('sales.crm-dashboard'))
    @include('dashboard.widgets.crm-locked', ['size' => 'small', 'title' => __('CRM activity')])
@else
@php
    $caWeeks = \App\Services\Crm\CrmDashboard::forWidgets()->trend('week', 8);
    $caNow = end($caWeeks)['total'] ?? 0;
    $caPrev = $caWeeks[count($caWeeks) - 2]['total'] ?? 0;
    $caDiff = $caNow - $caPrev;
@endphp
<a href="/reports/crm-activity-summary" class="text-decoration-none text-reset d-block h-100">
    <div class="kpi kpi-small" style="--kc: #5b7db1; --kbg: #eaf1fb;">
        <div class="kpi-head">
            <span class="kpi-icon"><i class="bi bi-journal-check"></i></span>
            <span class="kpi-title">{{ __('CRM activity') }}</span>
        </div>
        <div class="kpi-value" style="margin-top: 8px;">{{ $caNow }}</div>
        <div class="kpi-note">{{ __('Entries this week') }} ·
            <span style="font-weight: 600; color: {{ $caDiff >= 0 ? '#15803d' : '#dc2626' }};">{{ $caDiff >= 0 ? '+' : '' }}{{ $caDiff }} {{ __('vs last week') }}</span>
        </div>
        <div style="position: relative; height: 38px; margin-top: 6px;">
            <canvas class="kpi-chart" data-type="bar" data-color="#5b7db1"
                    data-values='@json(array_column($caWeeks, 'total'))' data-labels='@json(array_column($caWeeks, 'label'))'></canvas>
        </div>
    </div>
</a>
@endif
