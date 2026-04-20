<div class="customer-overview card border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $supplier->name_en }}</h5>
        <span class="badge
            @switch($supplier->status)
                @case(3) bg-info @break
                @case(4) bg-danger @break
            @endswitch
        ">
            @switch($supplier->status)
                @case(3) Active @break
                @case(4) Blocked @break
            @endswitch
        </span>
    </div>

    <div class="card-body">
        <div class="row g-3 mb-3">
            {{-- Basic Info --}}
            <div class="col-md-6">
                <h6 class="text-muted">Basic Information</h6>
                <ul class="list-unstyled mb-0">
                    <li><i class="bi bi-envelope me-1"></i> {{ $supplier->email }}</li>
                    <li><i class="bi bi-telephone me-1"></i> {{ $supplier->phone }}</li>
                    <li><i class="bi bi-globe me-1"></i> {{ $supplier->website ?? 'N/A' }}</li>
                    <li><i class="bi bi-geo-alt me-1"></i> {{ $supplier->city_en }}, {{ $supplier->country }}</li>
                </ul>
            </div>

            {{-- Business Info --}}
            <div class="col-md-6">
                <h6 class="text-muted">Business Information</h6>
                <ul class="list-unstyled mb-0">
                    <li><i class="bi bi-building me-1"></i> {{ ucfirst($supplier->business_type) }}</li>
                    <li><i class="bi bi-card-text me-1"></i> VAT: {{ $supplier->vat_number ?? 'N/A' }}</li>
                    <li><i class="bi bi-currency-exchange me-1"></i> {{ $supplier->currency }}</li>
                </ul>
            </div>
        </div>

        {{-- Credit Info --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <h6 class="text-muted">Credit Information</h6>
                <ul class="list-unstyled mb-0">
                    <li><i class="bi bi-wallet2 me-1"></i> {{ number_format($supplier->credit_limit, 2) }} {{ $supplier->currency }}</li>
                    <li><i class="bi bi-clock me-1"></i> {{ $supplier->credit_days }} days</li>
                </ul>
            </div>

            {{-- Joined Date --}}
            <div class="col-md-6">
                <h6 class="text-muted">Joined</h6>
                <p class="mb-0"><i class="bi bi-calendar-check me-1"></i>{{ $supplier->created_at->format('d M, Y') }}</p>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <h6 class="text-muted">Communication</h6>
                <ul class="list-unstyled mb-0">
                    <li><span class="text-muted">Address : </span> {{ nl2br($supplier->address1_en) }}</li>
                    <li><span class="text-muted">Building Number : </span> {{ $supplier->building_number }}</li>
                    <li><span class="text-muted">Plot Number : </span> {{ $supplier->plot_no }}</li>
                    <li><span class="text-muted">Postal Code : </span> {{ $supplier->postal_code }}</li>
                </ul>
            </div>
        </div>

        {{-- Tags / Badges --}}
        <div class="mb-3">
            <h6 class="text-muted">Tags / Badges</h6>
            @forelse($supplier->tags ?? [] as $tag)
                <span class="badge bg-primary me-1">{{ $tag->name }}</span>
            @empty
                <span class="text-muted">No tags</span>
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
