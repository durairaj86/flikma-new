@php $d = \App\Services\Dashboard\WidgetData::snapshot('punching'); @endphp
{{-- Large dashboard widget: today's punches from the machines, newest first. Extra rows scroll. --}}
<div class="kpi kpi-tall" style="--kc: #6366f1; --kbg: #ffffff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-fingerprint"></i></span>
        <span class="kpi-title">{{ __('Punching') }} · {{ __('Today') }}</span>
        <a href="{{ route('payroll.punch-log') }}" class="btn btn-sm btn-outline-secondary py-0 px-2 kpi-viewall">{{ __('View All') }}</a>
    </div>
    <div class="kpi-tall-scroll">
        <table class="table table-sm table-hover mb-0 align-middle kpi-table">
            <thead><tr><th>{{ __('Time') }}</th><th>{{ __('Employee') }}</th><th class="text-end">{{ __('In / Out') }}</th></tr></thead>
            <tbody>
            @forelse($d['rows'] as $r)
                <tr>
                    <td class="tabular-nums fw-semibold">{{ $r['time'] }}</td>
                    <td><div class="text-truncate" style="max-width:130px;">{{ $r['name'] }}</div><small class="text-muted">{{ $r['device'] }}</small></td>
                    <td class="text-end">
                        @if($r['status'] === 'unmapped')<span class="badge bg-warning text-dark">{{ __('Unmapped') }}</span>
                        @elseif($r['dir'] === 'in')<span class="badge bg-success">{{ __('IN') }}</span>
                        @elseif($r['dir'] === 'out')<span class="badge bg-secondary">{{ __('OUT') }}</span>
                        @else<span class="badge bg-light text-dark border">—</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">{{ __('No punches yet today.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="kpi-tall-foot">{{ __('Punches') }} <b>{{ $d['punches'] }}</b> · {{ __('Devices online') }} <b>{{ $d['devices_online'] }}/{{ $d['devices_total'] }}</b> · {{ __('Unmapped') }} <b>{{ $d['unmapped'] }}</b></div>
</div>
