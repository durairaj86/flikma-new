@php
    $addr1 = collect([$customer->address1_en, $customer->address2_en])->filter()->implode(', ');
    $addr2 = collect([$customer->city_en, $customer->country])->filter()->implode(', ');
@endphp
<div class="row g-3">
    <div class="col-lg-6">
        <div class="cust-card">
            <div class="cust-card-h">{{ __('Contact Information') }}</div>
            <div class="cust-card-b">
                <dl class="cust-dl">
                    <dt>{{ __('Name') }}</dt><dd>{{ $customer->name_en }}</dd>
                    @if($customer->name_ar)<dt>{{ __('Arabic Name') }}</dt><dd>{{ $customer->name_ar }}</dd>@endif
                    <dt>{{ __('Email') }}</dt><dd>{{ $customer->email ?: '—' }}</dd>
                    <dt>{{ __('Contact') }}</dt><dd>{{ $customer->phone ?: '—' }}</dd>
                    <dt>{{ __('VAT Number') }}</dt><dd>{{ $customer->vat_number ?: '—' }}</dd>
                    <dt>{{ __('CR Number') }}</dt><dd>{{ $customer->cr_number ?: '—' }}</dd>
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
                <div>{{ collect([$customer->building_number, $customer->plot_no, $customer->postal_code])->filter()->implode(' - ') }}</div>
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
                <div class="fs-4 fw-bold text-success">{{ number_format((float) $customer->credit_limit, 2) }}</div>
            </div>
        </div>
    </div>
</div>
