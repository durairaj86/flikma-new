{{-- Medium widget: CRM at a glance (the CRM Dashboard's tiles), for the signed-in user's salespeople. --}}
@if(!userCan('sales.crm-dashboard'))
    @include('dashboard.widgets.crm-locked', ['size' => 'medium', 'title' => __('CRM at a glance')])
@else
@php
    $cg = \App\Services\Crm\CrmDashboard::forWidgets()->atAGlance();
    $cgStale = \App\Services\Crm\CrmSettings::get('stale_days');
@endphp
<a href="/sales/crm-dashboard" class="text-decoration-none text-reset d-block h-100">
    <div class="kpi kpi-medium" style="--kc: #4338ca; --kbg: #eef2ff;">
        <div class="kpi-head">
            <span class="kpi-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="kpi-title">{{ __('CRM at a glance') }}</span>
            <span class="kpi-month-static">{{ __('This month') }}</span>
        </div>
        <div class="kpi-legend kpi-legend-3" style="margin-top: 14px; row-gap: 14px;">
            @foreach([
                [__('Customer lines'), $cg['lines'], '#4338ca'],
                [__('RFQs received'), $cg['rfqs'], '#c2410c'],
                [__('Quotes sent'), $cg['quotes'], '#c2410c'],
                [__('Confirmed'), $cg['confirmed'], '#15803d'],
                [__('Overdue'), $cg['overdue'], $cg['overdue'] ? '#dc2626' : '#101828'],
                [__('No contact :n+ days', ['n' => $cgStale]), $cg['stale'], $cg['stale'] ? '#dc2626' : '#101828'],
            ] as [$label, $value, $color])
                <span style="display: block;">
                    <b style="display: block; font-size: 1.35rem; line-height: 1.1; color: {{ $color }};">{{ $value }}</b>
                    <small style="color: #667085;">{{ $label }}</small>
                </span>
            @endforeach
        </div>
    </div>
</a>
@endif
