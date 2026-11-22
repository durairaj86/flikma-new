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
{{-- Customer portal logins. Buttons are handled by the page script in modules/customer/show.blade.php. --}}
<div class="cust-card">
    <div class="cust-card-h d-flex justify-content-between align-items-center">
        <span>{{ __('Portal access') }}</span>
        <a href="{{ url('/portal/login') }}" target="_blank" rel="noopener" class="small fw-normal">{{ url('/portal') }}</a>
    </div>
    <div class="cust-card-b">
        <p class="small text-muted mb-2">{{ __('People of this customer can sign in to see their shipments, invoices, statement and quotations.') }}</p>
        @if($portalUsers->isNotEmpty())
            <table class="table table-sm align-middle mb-3">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Status') }}</th><th>{{ __('Last sign-in') }}</th><th></th></tr></thead>
                <tbody>
                @foreach($portalUsers as $pu)
                    <tr>
                        <td>{{ $pu->name }}</td>
                        <td>{{ $pu->email }}</td>
                        <td><span class="badge rounded-pill {{ !$pu->is_active ? 'bg-secondary-subtle text-secondary-emphasis' : ($pu->password_set_at ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis') }}">{{ $pu->statusLabel() }}</span></td>
                        <td class="small text-muted">{{ $pu->last_login_at?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                        <td class="text-end text-nowrap">
                            @if($canEditCustomer)
                                @if($pu->is_active)
                                    <button type="button" class="btn btn-sm btn-light border" data-portal-resend="{{ $pu->id }}">{{ $pu->password_set_at ? __('Send password link') : __('Resend invitation') }}</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-portal-status="{{ $pu->id }}" data-value="0">{{ __('Disable') }}</button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-success" data-portal-status="{{ $pu->id }}" data-value="1">{{ __('Enable') }}</button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
        @if($canEditCustomer)
            <form class="row g-2 align-items-end" data-portal-invite="{{ $customer->id }}" novalidate>
                <div class="col-md-4">
                    <label class="form-label small mb-1" for="portalInviteName">{{ __('Name') }}</label>
                    <input type="text" class="form-control form-control-sm" id="portalInviteName" name="name" maxlength="150" required value="{{ $portalUsers->isEmpty() ? ($customer->contact_person ?: '') : '' }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label small mb-1" for="portalInviteEmail">{{ __('Email') }}</label>
                    <input type="email" class="form-control form-control-sm" id="portalInviteEmail" name="email" maxlength="191" required value="{{ $portalUsers->isEmpty() ? $customer->email : '' }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-send me-1"></i>{{ __('Invite') }}</button>
                </div>
            </form>
        @endif
    </div>
</div>
