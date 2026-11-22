{{-- Small dashboard widget: the signed-in user's CRM follow-ups (overdue / today / next 7 days). --}}
@php
    $fuToday0 = \App\Models\Crm\CrmActivity::localNowNaive()->startOfDay();
    $fuBase = \App\Models\Crm\CrmActivity::openFollowUps()->where('assigned_to', auth()->id());
    $fuOverdue = (clone $fuBase)->where('follow_up_at', '<', $fuToday0)->count();
    $fuToday = (clone $fuBase)->whereDate('follow_up_at', $fuToday0)->count();
    $fuWeek = (clone $fuBase)->whereBetween('follow_up_at', [$fuToday0->copy()->addDay(), $fuToday0->copy()->addDays(7)->endOfDay()])->count();
    $fuTotal = $fuOverdue + $fuToday + $fuWeek;
@endphp
<a href="/sales/follow-ups" class="text-decoration-none text-reset d-block h-100">
    <div class="kpi kpi-small" style="--kc: #7c3aed; --kbg: #f1ebfe;">
        <div class="kpi-head">
            <span class="kpi-icon"><i class="bi bi-alarm"></i></span>
            <span class="kpi-title">{{ __('My Follow-ups') }}</span>
        </div>
        <div class="kpi-duo" style="gap:14px;margin-top:8px;">
            <div>
                <div class="kpi-value" style="color: #dc2626">{{ $fuOverdue }}</div>
                <div class="kpi-note">{{ __('Overdue') }}</div>
            </div>
            <div>
                <div class="kpi-value" style="color: #7c3aed">{{ $fuToday }}</div>
                <div class="kpi-note">{{ __('Today') }}</div>
            </div>
        </div>
        <div class="kpi-bar"><i style="width: {{ $fuTotal ? round(($fuOverdue + $fuToday) / $fuTotal * 100) : 0 }}%"></i></div>
        <div class="kpi-legend">
            <span><em style="background:#7c3aed"></em>{{ __('Next 7 days') }} <b>{{ $fuWeek }}</b></span>
        </div>
    </div>
</a>
