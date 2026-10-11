@php $d = \App\Services\Dashboard\WidgetData::snapshot('punching'); @endphp
<div class="kpi kpi-medium" style="--kc: #6366f1; --kbg: #eef0ff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-fingerprint"></i></span>
        <span class="kpi-title">{{ __('Punching') }}</span>
        <span class="kpi-month-static">{{ __('Today') }}</span>
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ $d['punches'] }}</div>
        @if($d['last'])<span class="kpi-pill up">{{ __('last :t', ['t' => $d['last']]) }}</span>@endif
    </div>
    <div class="kpi-note">{{ __('punches received today') }}</div>
    <div class="kpi-bar"><i style="width: {{ $d['devices_total'] ? round($d['devices_online'] / $d['devices_total'] * 100) : 0 }}%"></i></div>
    <div class="kpi-legend kpi-legend-3">
        <span><em class="dot-a"></em>{{ __('People') }} <b>{{ $d['people'] }}</b></span>
        <span><em style="background:#16a34a"></em>{{ __('Devices online') }} <b>{{ $d['devices_online'] }}/{{ $d['devices_total'] }}</b></span>
        <span><em style="background:#f59e0b"></em>{{ __('Unmapped') }} <b>{{ $d['unmapped'] }}</b></span>
    </div>
</div>
