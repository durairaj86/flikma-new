@php $d = \App\Services\Dashboard\WidgetData::snapshot('punching'); @endphp
@php $kc = '#6366f1'; @endphp
<div class="kpi kpi-small" style="--kc: #6366f1; --kbg: #eef0ff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-fingerprint"></i></span>
        <span class="kpi-title">{{ __('Punching') }}</span>
    </div>
    <div class="kpi-main"><div class="kpi-value">{{ $d['punches'] }}</div></div>
    <div class="kpi-note">{{ $d['last'] ? __('punches today · last :t', ['t' => $d['last']]) : __('no punches yet today') }}</div>
    <div class="kpi-legend">
        <span><em class="dot-a"></em>{{ __('Devices online') }} <b>{{ $d['devices_online'] }}/{{ $d['devices_total'] }}</b></span>
        <span><em class="dot-b"></em>{{ __('Unmapped') }} <b>{{ $d['unmapped'] }}</b></span>
    </div>
</div>
