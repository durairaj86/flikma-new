@php
    $bkStatus = [
        1 => ['label' => __('Pending'), 'class' => 'bg-warning-subtle text-warning'],
        2 => ['label' => __('Confirmed'), 'class' => 'bg-success-subtle text-success'],
        3 => ['label' => __('Shipped'), 'class' => 'bg-primary-subtle text-primary'],
        4 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger'],
    ];
    $bkInfo = $bkStatus[$booking->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
    $modeLabel = ['sea' => __('Sea'), 'air' => __('Air'), 'road' => __('Road')][$booking->shipment_mode] ?? $booking->shipment_mode;
    $cut = fn($d) => $d ? $d->format('d-m-Y H:i') : '-';
    $soon = fn($d) => $d && in_array((int) $booking->status, [1, 2], true) && $d->between(now(), now()->addDays(3));
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
    /* two-sided time frame: Enquiry / Job on the right, Quotation / Booking on the left */
    .bk-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .bk-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .bk-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .bk-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .bk-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .bk-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .bk-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .bk-timeline .t-label { font-weight: 600; color: #0f172a; }
    .bk-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#bkDetailsTab" type="button" role="tab">
            <i class="bi bi-journal-check me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#bkTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="bkDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $booking->row_no }}
        <span class="badge {{ $bkInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $bkInfo['label'] }}</span>
    </div>
</div>

<div class="section">
    <h6>{{ __('Booking Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Customer') }}:</strong><span>@if($booking->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $booking->customer_id }}" data-title="{{ $booking->customer->name_en ?? '' }}">{{ $booking->customer->name_en ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>@if($booking->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $booking->job_id }}" data-title="{{ $booking->job->row_no ?? '' }}">{{ $booking->job->row_no ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Mode') }}:</strong><span>{{ $modeLabel }}</span></div>
        <div><strong>{{ __('Booking Date') }}:</strong><span>{{ $booking->booking_date?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Carrier') }}:</strong><span>{{ $booking->carrier->name ?? '-' }}</span></div>
        <div><strong>{{ __('Carrier Booking No') }}:</strong><span>{{ $booking->booking_ref ?: '-' }}</span></div>
        <div><strong>{{ __('Vessel / Flight') }}:</strong><span>{{ $booking->vessel_flight ?: '-' }}</span></div>
        <div><strong>{{ __('Voyage / Flight No') }}:</strong><span>{{ $booking->voyage_no ?: '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Route & Schedule') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Loading') }}:</strong><span>{{ $booking->pol }}</span></div>
        <div><strong>{{ __('Discharge') }}:</strong><span>{{ $booking->pod }}</span></div>
        <div><strong>{{ __('ETD') }}:</strong><span>{{ $booking->etd?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('ETA') }}:</strong><span>{{ $booking->eta?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Cargo Cut-off') }}:</strong><span class="{{ $soon($booking->cargo_cutoff) ? 'text-danger fw-semibold' : '' }}">{{ $cut($booking->cargo_cutoff) }}@if($soon($booking->cargo_cutoff)) <i class="bi bi-exclamation-triangle-fill"></i>@endif</span></div>
        <div><strong>{{ __('Document Cut-off') }}:</strong><span class="{{ $soon($booking->doc_cutoff) ? 'text-danger fw-semibold' : '' }}">{{ $cut($booking->doc_cutoff) }}@if($soon($booking->doc_cutoff)) <i class="bi bi-exclamation-triangle-fill"></i>@endif</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Cargo') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Container Type') }}:</strong><span>{{ $booking->container_type ?: '-' }}</span></div>
        <div><strong>{{ __('Containers / Pieces') }}:</strong><span>{{ $booking->container_qty ?? '-' }}</span></div>
        <div><strong>{{ __('Gross Weight (kg)') }}:</strong><span>{{ $booking->gross_weight !== null ? number_format($booking->gross_weight, 2) : '-' }}</span></div>
        <div><strong>{{ __('Volume (CBM)') }}:</strong><span>{{ $booking->volume !== null ? number_format($booking->volume, 3) : '-' }}</span></div>
        <div style="grid-column: 1 / -1;"><strong>{{ __('Description') }}:</strong><span>{{ $booking->cargo_description ?: '-' }}</span></div>
    </div>
</div>

@if($booking->remarks)
    <div class="section">
        <h6>{{ __('Remarks') }}</h6>
        <p class="mb-0">{{ $booking->remarks }}</p>
    </div>
@endif

</div>

<div class="tab-pane fade" id="bkTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'booking' => __('Booking')];
        @endphp
        <ul class="bk-timeline">
            @foreach($timeline as $step)
                <li class="{{ in_array($step['module'], $sideRight) ? 'side-r' : 'side-l' }}">
                    <span class="dot"><i class="bi {{ $step['icon'] }}"></i></span>
                    <div class="t-mod">{{ $modLabel[$step['module']] ?? '' }}</div>
                    <div class="t-label">@if(!empty($step['link']))<a href="#" class="open-linked text-primary text-decoration-none" data-type="{{ $step['link'][0] }}" data-id="{{ $step['link'][1] }}" data-title="{{ $step['link'][2] }}">{{ $step['label'] }}</a>@else{{ $step['label'] }}@endif @if($step['meta'])<span class="text-muted fw-normal">· {{ $step['meta'] }}</span>@endif</div>
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
