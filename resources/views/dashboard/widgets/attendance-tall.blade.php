@php $d = \App\Services\Dashboard\WidgetData::snapshot('attendance'); @endphp
{{-- Large dashboard widget: today's attendance, one line per employee (people to follow up first). Extra rows scroll. --}}
@php $st = ['present' => ['bg-success', __('Present')], 'half_day' => ['bg-info text-dark', __('Half day')], 'absent' => ['bg-danger', __('Absent')], 'leave' => ['bg-secondary', __('Leave')], 'holiday' => ['bg-secondary', __('Holiday')], 'not_marked' => ['bg-light text-dark border', __('Not marked')]]; @endphp
<div class="kpi kpi-tall" style="--kc: #16a34a; --kbg: #ffffff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-calendar-check"></i></span>
        <span class="kpi-title">{{ __('Attendance') }} · {{ $d['date'] }}</span>
        <a href="{{ route('payroll.attendance.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2 kpi-viewall">{{ __('View All') }}</a>
    </div>
    <div class="kpi-tall-scroll">
        <table class="table table-sm table-hover mb-0 align-middle kpi-table">
            <thead><tr><th>{{ __('Employee') }}</th><th>{{ __('In / Out') }}</th><th class="text-end">{{ __('Status') }}</th></tr></thead>
            <tbody>
            @forelse($d['rows'] as $r)
                <tr>
                    <td class="fw-semibold text-truncate" style="max-width:140px;">{{ $r['name'] }}</td>
                    <td class="text-muted small tabular-nums">{{ $r['in'] ?? '—' }} / {{ $r['out'] ?? '—' }}</td>
                    <td class="text-end"><span class="badge {{ $st[$r['status']][0] ?? 'bg-secondary' }}">{{ $st[$r['status']][1] ?? $r['status'] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">{{ __('No employees yet.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="kpi-tall-foot">{{ __('Present') }} <b>{{ $d['present'] }}</b> · {{ __('Absent') }} <b>{{ $d['absent'] }}</b> · {{ __('Not marked') }} <b>{{ $d['not_marked'] }}</b></div>
</div>
