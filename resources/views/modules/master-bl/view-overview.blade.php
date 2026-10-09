@php
    $st = [
        1 => ['label' => __('Draft'), 'class' => 'bg-warning-subtle text-warning'],
        2 => ['label' => __('Issued'), 'class' => 'bg-success-subtle text-success'],
        3 => ['label' => __('Closed'), 'class' => 'bg-primary-subtle text-primary'],
        4 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger'],
    ];
    $info = $st[(int) $master->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
    $modeLabel = ['sea' => __('Sea'), 'air' => __('Air')][$master->shipment_mode] ?? $master->shipment_mode;
    $houses = $master->shipment_mode === 'air' ? $master->airwayBills : $master->seawayBills;
    $houseType = $master->shipment_mode === 'air' ? 'airway' : 'seaway';
@endphp
<style>
    .section {
        margin-bottom: 1.5rem;
    }
    .section h6 {
        font-size: 14px;
        font-weight: 600;
        background: #f7f7f9;
        padding: 8px 10px;
        border-radius: 4px;
        /*border-left: 4px solid #0d6efd;*/
        margin-bottom: 1rem;
    }
    table.table {
        font-size: 13px;
    }
    table.table th {
        background: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem 1.5rem;
        font-size: 13.5px;
        line-height: 1.6;
    }
    .info-grid div {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px dotted #eee;
        padding-bottom: 3px;
    }
    .info-grid strong {
        color: #333;
        min-width: 140px;
        font-weight: 600;
    }
    .info-grid span {
        color: #555;
        flex: 1;
        text-align: left;
        margin-left: 8px;
    }
    .total-table td {
        padding: 4px 10px;
        font-size: 13.5px;
    }
    .invoice-no-heading {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 1rem;
    }
    /* one-sided time frame */
    .mbl-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .mbl-timeline::before { content: ''; position: absolute; left: .75rem; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .mbl-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .mbl-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .mbl-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .mbl-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .mbl-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .mbl-timeline .t-label { font-weight: 600; color: #0f172a; }
    .mbl-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
    .mbl-timeline::before { left: .75rem; margin-left: 0; }
    .mbl-timeline li, .mbl-timeline li.side-r { width: 100%; margin-left: 0; padding: 0 0 .9rem 2.5rem; text-align: left; }
    .mbl-timeline .dot, .mbl-timeline li.side-r .dot { right: auto; left: 0; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#mblDetailsTab" type="button" role="tab">
            <i class="bi bi-diagram-2 me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#mblTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="mblDetailsTab" role="tabpanel">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $master->row_no }}
        <span class="badge {{ $info['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $info['label'] }}</span>
    </div>
</div>

<div class="section">
    <h6>{{ __('Master Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Master B/L / AWB No') }}:</strong><span>{{ $master->mbl_no }}</span></div>
        <div><strong>{{ __('Mode') }}:</strong><span>{{ $modeLabel }}</span></div>
        <div><strong>{{ __('Carrier') }}:</strong><span>{{ $master->carrier->name ?? '-' }}</span></div>
        <div><strong>{{ __('Issue Date') }}:</strong><span>{{ $master->issue_date?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Vessel / Flight') }}:</strong><span>{{ $master->vessel_flight ?: '-' }}</span></div>
        <div><strong>{{ __('Voyage / Flight No') }}:</strong><span>{{ $master->voyage_no ?: '-' }}</span></div>
        <div><strong>{{ __('Loading') }}:</strong><span>{{ $master->pol }}</span></div>
        <div><strong>{{ __('Discharge') }}:</strong><span>{{ $master->pod }}</span></div>
        <div><strong>{{ __('ETD') }}:</strong><span>{{ $master->etd?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('ETA') }}:</strong><span>{{ $master->eta?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Shipper') }}:</strong><span>{{ $master->shipper ?: '-' }}</span></div>
        <div><strong>{{ __('Consignee') }}:</strong><span>{{ $master->consignee ?: '-' }}</span></div>
        <div><strong>{{ __('Freight Terms') }}:</strong><span>{{ ucfirst($master->freight_terms ?? '-') }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('House Bills') }} ({{ $houses->count() }})</h6>
    @if($houses->isEmpty())
        <p class="text-muted mb-0">{{ __('No house bills under this master yet. Use Edit to add them.') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>{{ __('House Bill') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Job') }}</th></tr></thead>
                <tbody>
                @foreach($houses as $h)
                    <tr>
                        <td><a href="#" class="open-linked text-primary text-decoration-none" data-type="{{ $houseType }}" data-id="{{ $h->id }}" data-title="{{ $h->row_no }}">{{ $h->row_no }}</a></td>
                        <td>{{ $h->customer->name_en ?? '-' }}</td>
                        <td>@if($h->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $h->job_id }}" data-title="{{ $h->job->row_no ?? '' }}">{{ $h->job->row_no ?? '-' }}</a>@else - @endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if($master->remarks)
    <div class="section"><h6>{{ __('Remarks') }}</h6><p class="mb-0">{{ $master->remarks }}</p></div>
@endif
</div>

<div class="tab-pane fade" id="mblTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <ul class="mbl-timeline">
            @foreach($timeline as $step)
                <li>
                    <span class="dot"><i class="bi {{ $step['icon'] }}"></i></span>
                    <div class="t-label">{{ $step['label'] }}</div>
                    <div class="t-meta">
                        {{ \Carbon\Carbon::parse($step['at'])->format('d-m-Y H:i') }}
                        @if($step['by']) &middot; {{ __('by') }} {{ $step['by'] }} @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
</div>
