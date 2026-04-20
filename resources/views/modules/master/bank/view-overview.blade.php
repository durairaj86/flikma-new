<div class="bank-overview mb-4">
    <!-- Header -->
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
        <div class="d-flex align-items-center">
            <div class="avatar bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width:45px;height:45px;">
                <i class="bi bi-bank fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-semibold">{{ $bank->bank_name }}</h5>
                <small class="text-muted">{{ $bank->branch_name ?? 'Branch Info N/A' }}</small>
            </div>
        </div>
        <span class="badge rounded-pill
            @switch($bank->status)
                @case('1') bg-success-subtle text-success @break
                @case('0') bg-secondary-subtle text-secondary @break
            @endswitch
        ">
            @switch($bank->status)
                @case('1') Active @break
                @case('0') Inactive @break
            @endswitch
        </span>
    </div>

    <!-- Body -->
    <div class="card-body border-top pt-3">

        <div class="row g-4 mb-3">
            <!-- Account Info -->
            <div class="col-md-6">
                <h6 class="section-title text-uppercase text-muted mb-2">Account Information</h6>
                <div class="info-list">
                    <div><i class="bi bi-person-badge me-2 text-primary"></i> <strong>Account Holder:</strong> {{ $bank->account_holder }}</div>
                    <div><i class="bi bi-credit-card-2-front me-2 text-primary"></i> <strong>A/C Number:</strong> {{ $bank->account_number }}</div>
                    <div><i class="bi bi-upc-scan me-2 text-primary"></i> <strong>SWIFT:</strong> {{ $bank->swift_code ?? 'N/A' }}</div>
                    <div><i class="bi bi-upc me-2 text-primary"></i> <strong>IBAN:</strong> {{ $bank->iban_code ?? 'N/A' }}</div>
                    <div><i class="bi bi-currency-exchange me-2 text-primary"></i> <strong>Currency:</strong> {{ strtoupper($bank->currency ?? 'N/A') }}</div>
                </div>
            </div>

            <!-- Bank Info -->
            <div class="col-md-6">
                <h6 class="section-title text-uppercase text-muted mb-2">Bank Details</h6>
                <div class="info-list">
                    <div><i class="bi bi-bank2 me-2 text-primary"></i> <strong>Bank:</strong> {{ $bank->bank_name }}</div>
                    <div><i class="bi bi-geo-alt me-2 text-primary"></i> <strong>Branch:</strong> {{ $bank->branch_name ?? 'N/A' }}</div>
                    <div><i class="bi bi-geo me-2 text-primary"></i> <strong>Address:</strong> {{ $bank->bank_address ?? 'N/A' }}</div>
                    <div><i class="bi bi-flag me-2 text-primary"></i> <strong>Country:</strong> {{ $bank->country ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <hr class="my-2">

        <!-- Additional Info -->
        <div class="row g-4 mb-3">
            <div class="col-md-6">
                <h6 class="section-title text-uppercase text-muted mb-2">Additional Details</h6>
                <div class="info-list">
                    <div><i class="bi bi-wallet2 me-2 text-primary"></i> <strong>Account Type:</strong> {{ ucfirst($bank->account_type ?? 'N/A') }}</div>
                    <div><i class="bi bi-calendar-check me-2 text-primary"></i> <strong>Added On:</strong> {{ $bank->created_at?->format('d M, Y') }}</div>
                </div>
            </div>
        </div>

        <hr class="my-2">

        <!-- Notes -->
        <div>
            <h6 class="section-title text-uppercase text-muted mb-2">Notes</h6>
            <div class="bg-light p-3 rounded border small text-muted">
                {{ $bank->notes ?? 'No additional information available.' }}
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
