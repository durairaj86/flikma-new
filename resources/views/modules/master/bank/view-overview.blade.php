<div class="bank-overview mb-4">
    <!-- Header -->
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
        <div class="d-flex align-items-center">
            <div class="avatar bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width:45px;height:45px;">
                <i class="bi bi-bank fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-semibold">{{ $bank->bank_name }}</h5>
                <small class="text-muted">{{ $bank->branch_name ?? __('Branch Info N/A') }}</small>
            </div>
        </div>
        <span class="badge rounded-pill
            @switch($bank->status)
                @case('1') bg-success-subtle text-success @break
                @case('0') bg-secondary-subtle text-secondary @break
            @endswitch
        ">
            @switch($bank->status)
                @case('1') {{ __('Active') }} @break
                @case('0') {{ __('Inactive') }} @break
            @endswitch
        </span>
    </div>

    <!-- Body -->
    <div class="card-body border-top pt-3">

        <div class="row g-4 mb-3">
            <!-- Account Info -->
            <div class="col-md-6">
                <h6 class="section-title text-uppercase text-muted mb-2">{{ __('Account Information') }}</h6>
                <div class="info-list">
                    <div><i class="bi bi-person-badge me-2 text-primary"></i> <strong>{{ __('Account Holder:') }}</strong> {{ $bank->account_holder }}</div>
                    <div><i class="bi bi-credit-card-2-front me-2 text-primary"></i> <strong>{{ __('A/C Number:') }}</strong> {{ $bank->account_number }}</div>
                    <div><i class="bi bi-upc-scan me-2 text-primary"></i> <strong>{{ __('SWIFT:') }}</strong> {{ $bank->swift_code ?? __('N/A') }}</div>
                    <div><i class="bi bi-upc me-2 text-primary"></i> <strong>{{ __('IBAN:') }}</strong> {{ $bank->iban_code ?? __('N/A') }}</div>
                    <div><i class="bi bi-currency-exchange me-2 text-primary"></i> <strong>{{ __('Currency:') }}</strong> {{ strtoupper($bank->currency ?? __('N/A')) }}</div>
                </div>
            </div>

            <!-- Bank Info -->
            <div class="col-md-6">
                <h6 class="section-title text-uppercase text-muted mb-2">{{ __('Bank Details') }}</h6>
                <div class="info-list">
                    <div><i class="bi bi-bank2 me-2 text-primary"></i> <strong>{{ __('Bank:') }}</strong> {{ $bank->bank_name }}</div>
                    <div><i class="bi bi-geo-alt me-2 text-primary"></i> <strong>{{ __('Branch:') }}</strong> {{ $bank->branch_name ?? __('N/A') }}</div>
                    <div><i class="bi bi-geo me-2 text-primary"></i> <strong>{{ __('Address:') }}</strong> {{ $bank->bank_address ?? __('N/A') }}</div>
                    <div><i class="bi bi-flag me-2 text-primary"></i> <strong>{{ __('Country:') }}</strong> {{ $bank->country ?? __('N/A') }}</div>
                </div>
            </div>
        </div>

        <hr class="my-2">

        <!-- Additional Info -->
        <div class="row g-4 mb-3">
            <div class="col-md-6">
                <h6 class="section-title text-uppercase text-muted mb-2">{{ __('Additional Details') }}</h6>
                <div class="info-list">
                    <div><i class="bi bi-wallet2 me-2 text-primary"></i> <strong>{{ __('Account Type:') }}</strong> {{ ucfirst($bank->account_type ?? __('N/A')) }}</div>
                    <div><i class="bi bi-calendar-check me-2 text-primary"></i> <strong>{{ __('Added On:') }}</strong> {{ $bank->created_at?->format('d M, Y') }}</div>
                </div>
            </div>
        </div>

        <hr class="my-2">

        <!-- Notes -->
        <div>
            <h6 class="section-title text-uppercase text-muted mb-2">{{ __('Notes') }}</h6>
            <div class="bg-light p-3 rounded border small text-muted">
                {{ $bank->notes ?? __('No additional information available.') }}
            </div>
        </div>
    </div>
</div>

<style>
    .bank-overview .section-title {
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .bank-overview .info-list div {
        font-size: 0.9rem;
        margin-bottom: 0.45rem;
    }

    .bank-overview .avatar {
        flex-shrink: 0;
    }

    .bank-overview hr {
        opacity: 0.08;
    }

    .bank-overview strong {
        font-weight: 600;
        color: #333;
    }

    .bank-overview .card {
        border-radius: 0.75rem;
    }
</style>
