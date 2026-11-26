{{-- Medium widget: the open customer lines longest without contact (the Customer Tracker's stale lines). --}}
@if(!userCan('sales.crm-dashboard'))
    @include('dashboard.widgets.crm-locked', ['size' => 'medium', 'title' => __('No contact')])
@else
@php
    $csDays = \App\Services\Crm\CrmSettings::get('stale_days');
    $csAll = \App\Services\Crm\CrmDashboard::forWidgets()->lines()->where('stale', true);
    // never contacted first, then the longest silence
    $csTop = $csAll->sortByDesc(fn($l) => $l['days_since'] ?? PHP_INT_MAX)->take(5);
@endphp
<div class="kpi kpi-medium" style="--kc: #dc2626; --kbg: #fdecec;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-person-exclamation"></i></span>
        <span class="kpi-title">{{ __('No contact :n+ days', ['n' => $csDays]) }}</span>
        <a href="/sales/tracker" class="kpi-month-static text-decoration-none">{{ $csAll->count() }} {{ __('lines') }} ›</a>
    </div>
    <div class="kpi-list">
        @forelse($csTop as $l)
            <a href="{{ $l['type'] === 'customer' ? url('customers/' . $l['id']) : url('prospects') }}" class="kpi-list-row text-decoration-none">
                <span class="kpi-list-name">{{ $l['name'] }}
                    <small>{{ $l['last_action'] ? $l['last_action'] . ' · ' . date('d-m-Y', strtotime($l['last_at'])) : __('No contact yet') }}</small>
                </span>
                <span style="font-weight: 700; color: #dc2626; white-space: nowrap;">{{ $l['days_since'] === null ? __('Never') : __(':n days', ['n' => $l['days_since']]) }}</span>
            </a>
        @empty
            <div class="kpi-list-row"><span class="text-muted">{{ __('Every customer was contacted recently.') }}</span></div>
        @endforelse
    </div>
</div>
@endif
