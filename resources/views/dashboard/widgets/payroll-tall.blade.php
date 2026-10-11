@php $d = \App\Services\Dashboard\WidgetData::snapshot('payroll'); @endphp
{{-- Large dashboard widget: this month's payroll, one line per employee. Extra rows scroll. --}}
<div class="kpi kpi-tall" style="--kc: #0ea5e9; --kbg: #ffffff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-cash-coin"></i></span>
        <span class="kpi-title">{{ __('Payroll') }} · {{ $d['month'] }}</span>
        <a href="{{ route('payroll.runs.index') }}" class="btn btn-sm btn-outline-secondary py-0 px-2 kpi-viewall">{{ __('View All') }}</a>
    </div>
    <div class="kpi-tall-scroll">
        <table class="table table-sm table-hover mb-0 align-middle kpi-table">
            <thead><tr><th>{{ __('Employee') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Net Payable') }}</th></tr></thead>
            <tbody>
            @forelse($d['rows'] as $r)
                <tr>
                    <td class="fw-semibold text-truncate" style="max-width:140px;">{{ $r['name'] }}</td>
                    <td><span class="badge {{ $r['status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $r['status'] === 'paid' ? __('Paid') : __('Pending') }}</span></td>
                    <td class="text-end fw-bold">{{ number_format($r['net'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">{{ __('Payroll is not generated for this month yet.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="kpi-tall-foot">{{ __('Paid') }} <b>{{ number_format($d['paid_total'], 0) }}</b> · {{ __('Pending') }} <b>{{ number_format($d['pending_total'], 0) }}</b></div>
</div>
