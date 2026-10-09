@php
    $dlvStatus = [
        1 => ['label' => __('Pending'), 'class' => 'bg-warning-subtle text-warning'],
        2 => ['label' => __('Dispatched'), 'class' => 'bg-primary-subtle text-primary'],
        3 => ['label' => __('Delivered'), 'class' => 'bg-success-subtle text-success'],
        4 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger'],
    ];
    $dlvInfo = $dlvStatus[$order->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
    $late = in_array((int) $order->status, [1, 2], true) && $order->delivery_date && $order->delivery_date->lt(today());
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
    /* two-sided time frame: Enquiry / Job on the right, Quotation / Delivery on the left */
    .dlv-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .dlv-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .dlv-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .dlv-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .dlv-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .dlv-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .dlv-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .dlv-timeline .t-label { font-weight: 600; color: #0f172a; }
    .dlv-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#dlvDetailsTab" type="button" role="tab">
            <i class="bi bi-truck me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#dlvTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="dlvDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $order->row_no }}
        <span class="badge {{ $dlvInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $dlvInfo['label'] }}</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 linked-print"
            data-print-url="{{ url('operation/delivery-order/' . $order->id . '/print') }}"
            onclick="if (window.DELIVERY_ORDER && DELIVERY_ORDER.printPreview) { DELIVERY_ORDER.printPreview('{{ $order->id }}'); }">
        <i class="bi bi-printer me-1"></i> {{ __('Print') }}
    </button>
</div>

<div class="section">
    <h6>{{ __('Order Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Customer') }}:</strong><span>@if($order->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $order->customer_id }}" data-title="{{ $order->customer->name_en ?? '' }}">{{ $order->customer->name_en ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>@if($order->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $order->job_id }}" data-title="{{ $order->job->row_no ?? '' }}">{{ $order->job->row_no ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Order Date') }}:</strong><span>{{ $order->do_date?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Planned Delivery') }}:</strong><span class="{{ $late ? 'text-danger fw-semibold' : '' }}">{{ $order->delivery_date?->format('d-m-Y') ?? '-' }}@if($late) <i class="bi bi-exclamation-triangle-fill"></i> {{ __('late') }}@endif</span></div>
        <div><strong>{{ __('Delivered At') }}:</strong><span>{{ $order->delivered_at?->format('d-m-Y H:i') ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Pickup & Delivery') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Pickup Location') }}:</strong><span>{{ $order->pickup_location ?: '-' }}</span></div>
        <div><strong>{{ __('Consignee') }}:</strong><span>{{ $order->consignee ?: '-' }}</span></div>
        <div style="grid-column: 1 / -1;"><strong>{{ __('Delivery Address') }}:</strong><span>{{ $order->delivery_address ?: '-' }}</span></div>
        <div><strong>{{ __('Contact Person') }}:</strong><span>{{ $order->contact_person ?: '-' }}</span></div>
        <div><strong>{{ __('Contact Phone') }}:</strong><span>{{ $order->contact_phone ?: '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Transport') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Transporter') }}:</strong><span>{{ $order->transporter ?: '-' }}</span></div>
        <div><strong>{{ __('Vehicle No') }}:</strong><span>{{ $order->vehicle_no ?: '-' }}</span></div>
        <div><strong>{{ __('Driver') }}:</strong><span>{{ $order->driver_name ?: '-' }}</span></div>
        <div><strong>{{ __('Driver Phone') }}:</strong><span>{{ $order->driver_phone ?: '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Cargo') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Container No(s)') }}:</strong><span>{{ $order->container_no ?: '-' }}</span></div>
        <div><strong>{{ __('Packages') }}:</strong><span>{{ $order->packages ?? '-' }}</span></div>
        <div><strong>{{ __('Weight (kg)') }}:</strong><span>{{ $order->weight !== null ? number_format($order->weight, 2) : '-' }}</span></div>
        <div style="grid-column: 1 / -1;"><strong>{{ __('Description') }}:</strong><span>{{ $order->cargo_description ?: '-' }}</span></div>
    </div>
</div>

@if($order->received_by || $order->pod_notes)
    <div class="section">
        <h6>{{ __('Proof of Delivery') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Received By') }}:</strong><span>{{ $order->received_by ?: '-' }}</span></div>
            <div><strong>{{ __('POD Notes') }}:</strong><span>{{ $order->pod_notes ?: '-' }}</span></div>
        </div>
    </div>
@endif

@if($order->remarks)
    <div class="section"><h6>{{ __('Remarks') }}</h6><p class="mb-0">{{ $order->remarks }}</p></div>
@endif

</div>

<div class="tab-pane fade" id="dlvTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'delivery' => __('Delivery')];
        @endphp
        <ul class="dlv-timeline">
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
