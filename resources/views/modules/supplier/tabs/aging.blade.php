<div class="cust-card">
    <div class="cust-card-h">{{ __('Payable Aging') }}</div>
    <div class="cust-card-b">
        <p class="text-muted small">{{ __('Aging breakdown of outstanding bills.') }}</p>
        <div class="row g-3 text-center">
            @foreach(['current' => 'Current Days', 'b1' => '1-30 Days', 'b2' => '31-60 Days', 'b3' => '61-90 Days', 'b4' => '90+ Days'] as $k => $label)
                <div class="col"><div class="border rounded p-3 bg-light">
                    <div class="small fw-bold text-uppercase text-muted">{{ __($label) }}</div>
                    <div class="fs-5 fw-bold {{ $k !== 'current' && $buckets[$k] > 0 ? 'text-danger' : '' }}">{{ number_format($buckets[$k], 2) }}</div>
                </div></div>
            @endforeach
        </div>
        @if(abs($unapplied) >= 0.005)
            <div class="d-flex justify-content-between border-top mt-3 pt-3 small">
                <span class="text-muted">{{ __('Open invoices') }}</span><b>{{ number_format($invoiceTotal, 2) }}</b>
            </div>
            <div class="d-flex justify-content-between small">
                <span class="text-muted">{{ __('Unapplied advances / credit notes / adjustments') }}</span><b>{{ number_format($unapplied, 2) }}</b>
            </div>
            <div class="d-flex justify-content-between small border-top mt-1 pt-1">
                <span class="fw-bold">{{ __('Balance due (matches Statement)') }}</span><b>{{ number_format($invoiceTotal + $unapplied, 2) }}</b>
            </div>
        @endif
    </div>
</div>
