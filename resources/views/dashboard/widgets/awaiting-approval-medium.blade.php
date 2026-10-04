{{-- Medium dashboard widget: Awaiting Approval (draft customer invoices). Live snapshot, no month filter. --}}
@php
    $aColor = '#d97706';
    $aTop = $awaitingApproval->sortByDesc('grand_total')->take(6)->values();
    $aValues = $aTop->map(fn ($i) => (float) $i->grand_total)->all();
    $aLabels = $aTop->map(fn ($i) => $i->invoice_number ?? $i->row_no)->all();
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $aColor }}; --kbg: #fff3dc;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-clipboard-check"></i></span>
        <span class="kpi-title">{{ __('Awaiting Approval') }}</span>
        <a href="{{ route('invoices.customer') }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.75rem;"><i class="fa fa-check"></i> {{ __('Review') }}</a>
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ $awaitingApproval->count() }}</div>
            <div class="kpi-note">{{ __('Invoices') }} &middot; {{ __('Total') }} <b>{{ number_format($awaitingApprovalTotal, 0) }}</b></div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="bar" data-color="{{ $aColor }}"
                    data-values='@json($aValues)' data-labels='@json($aLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-list">
        @forelse($awaitingApproval->take(3) as $invoice)
            <div class="kpi-list-row">
                <span class="kpi-list-name">{{ $invoice->invoice_number ?? $invoice->row_no }}<small>{{ $invoice->customer->name_en ?? $invoice->customer->name ?? __('N/A') }}</small></span>
                <b>{{ number_format($invoice->grand_total, 0) }}</b>
            </div>
        @empty
            <div class="kpi-list-row text-muted justify-content-center">{{ __('No invoices awaiting approval') }}</div>
        @endforelse
    </div>
</div>
