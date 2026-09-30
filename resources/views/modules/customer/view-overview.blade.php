<div class="customer-overview card border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $customer->name_en }}</h5>
        <span class="badge
            @switch($customer->status)
                @case(1) bg-warning @break
                @case(2) bg-info @break
                @case(3) bg-success @break
                @case(4) bg-danger @break
                @case(5) bg-secondary @break
            @endswitch
        ">
            @switch($customer->status)
                @case(1) {{ __('Pending') }} @break
                @case(2) {{ __('Verified') }} @break
                @case(3) {{ __('Confirmed') }} @break
                @case(4) {{ __('Blocked') }} @break
                @case(5) {{ __('Rejected') }} @break
            @endswitch
        </span>
    </div>

    <div class="card-body">
        <div class="row g-3 mb-3">
            {{-- Basic Info --}}
            <div class="col-md-6">
                <h6 class="text-muted">{{ __('Basic Information') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li><i class="bi bi-envelope me-1"></i> {{ $customer->email }}</li>
                    <li><i class="bi bi-telephone me-1"></i> {{ $customer->phone }}</li>
                    <li><i class="bi bi-globe me-1"></i> {{ $customer->website ?? 'N/A' }}</li>
                    <li><i class="bi bi-geo-alt me-1"></i> {{ $customer->city_en }}, {{ $customer->country }}</li>
                </ul>
            </div>

            {{-- Business Info --}}
            <div class="col-md-6">
                <h6 class="text-muted">{{ __('Business Information') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li><i class="bi bi-building me-1"></i> {{ ucfirst($customer->business_type) }}</li>
                    <li><i class="bi bi-card-text me-1"></i> {{ __('VAT') }}: {{ $customer->vat_number ?? 'N/A' }}</li>
                    <li><i class="bi bi-currency-exchange me-1"></i> {{ $customer->currency }}</li>
                </ul>
            </div>
        </div>

        {{-- Credit Info --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <h6 class="text-muted">{{ __('Credit Information') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li>
                        <i class="bi bi-wallet2 me-1"></i> {{ number_format($customer->credit_limit, 2) }} {{ $customer->currency }}
                    </li>
                    <li><i class="bi bi-clock me-1"></i> {{ $customer->credit_days }} {{ __('days') }}</li>
                </ul>
            </div>

            {{-- Joined Date --}}
            <div class="col-md-6">
                <h6 class="text-muted">{{ __('Joined') }}</h6>
                <p class="mb-0"><i class="bi bi-calendar-check me-1"></i>{{ $customer->created_at->format('d M, Y') }}
                </p>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <h6 class="text-muted">{{ __('Communication') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li><span class="text-muted">{{ __('Address') }} : </span> {{ nl2br($customer->address1_en) }}</li>
                    <li><span class="text-muted">{{ __('Building Number') }} : </span> {{ $customer->building_number }}</li>
                    <li><span class="text-muted">{{ __('Plot Number') }} : </span> {{ $customer->plot_no }}</li>
                    <li><span class="text-muted">{{ __('Postal Code') }} : </span> {{ $customer->postal_code }}</li>
                </ul>
            </div>

            {{-- Joined Date --}}
            <div class="col-md-6">
                <h6 class="text-muted">{{ __('Logistics') }}</h6>
                <p class="mb-0"><span class="text-muted">{{ __('Preferred Shipping') }} :</span> {{ $customer->preferred_shipping }}
                </p>
                <p class="mb-0"><span class="text-muted">{{ __('Default Port') }} :</span> {{ $customer->default_port }}</p>
            </div>
        </div>

        {{-- Tags / Badges --}}
        <div class="mb-3">
            <h6 class="text-muted">{{ __('Tags / Badges') }}</h6>
            @forelse($customer->tags ?? [] as $tag)
                <span class="badge bg-primary me-1">{{ $tag->name }}</span>
            @empty
                <span class="text-muted">{{ __('No tags') }}</span>
            @endforelse
        </div>
    </div>
</div>

<style>
    .customer-overview .card-header {
        font-weight: 600;
        font-size: 1rem;
    }

    .customer-overview ul li {
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
    }

    .customer-overview ul li i {
        color: #6366F1;
        font-size: 1rem;
    }

    .customer-overview h6 {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .customer-overview .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.7em;
    }
</style>
