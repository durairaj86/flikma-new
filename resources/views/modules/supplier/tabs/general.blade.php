@php
    $addr1 = collect([$supplier->address1_en, $supplier->address2_en])->filter()->implode(', ');
    $addr2 = collect([$supplier->city_en, $supplier->country])->filter()->implode(', ');
@endphp
<div class="row g-3">
    <div class="col-lg-6">
        <div class="cust-card">
            <div class="cust-card-h">{{ __('Contact Information') }}</div>
            <div class="cust-card-b">
                <dl class="cust-dl">
                    <dt>{{ __('Name') }}</dt><dd>{{ $supplier->name_en }}</dd>
                    @if($supplier->name_ar)<dt>{{ __('Arabic Name') }}</dt><dd>{{ $supplier->name_ar }}</dd>@endif
                    <dt>{{ __('Email') }}</dt><dd>{{ $supplier->email ?: '—' }}</dd>
                    <dt>{{ __('Contact') }}</dt><dd>{{ $supplier->phone ?: '—' }}</dd>
                    <dt>{{ __('VAT Number') }}</dt><dd>{{ $supplier->vat_number ?: '—' }}</dd>
                    <dt>{{ __('CR Number') }}</dt><dd>{{ $supplier->cr_number ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="cust-card">
            <div class="cust-card-h">{{ __('Address Details') }}</div>
            <div class="cust-card-b">
                <div>{{ $addr1 ?: '—' }}</div>
                <div>{{ $addr2 }}</div>
                <div>{{ collect([$supplier->building_number, $supplier->plot_no, $supplier->postal_code])->filter()->implode(' - ') }}</div>
            </div>
        </div>
    </div>
</div>
<div class="cust-card">
    <div class="cust-card-h">{{ __('Account Overview') }}</div>
    <div class="cust-card-b">
        <div class="row text-center g-0">
            <div class="col-md-4 border-end py-2">
                <div class="small fw-bold text-uppercase text-muted">{{ __('Balance Due') }}</div>
                <div class="fs-4 fw-bold {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balanceDue, 2) }}</div>
            </div>
            <div class="col-md-4 border-end py-2">
                <div class="small fw-bold text-uppercase text-muted">{{ __('Overdue Amount') }}</div>
                <div class="fs-4 fw-bold {{ $overdue > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($overdue, 2) }}</div>
            </div>
            <div class="col-md-4 py-2">
                <div class="small fw-bold text-uppercase text-muted">{{ __('Credit Limit') }}</div>
                <div class="fs-4 fw-bold text-success">{{ number_format((float) $supplier->credit_limit, 2) }}</div>
            </div>
        </div>
    </div>
</div>
