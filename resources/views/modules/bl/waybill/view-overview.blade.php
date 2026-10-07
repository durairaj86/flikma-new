@php
    $blStatus = ['pending' => 'bg-warning-subtle text-warning', 'in_transit' => 'bg-primary-subtle text-primary', 'delivered' => 'bg-success-subtle text-success', 'cancelled' => 'bg-danger-subtle text-danger'];
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
    /* two-sided time frame: Enquiry / Job on the right, Quotation / bill on the left */
    .bl-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .bl-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .bl-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .bl-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .bl-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .bl-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .bl-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .bl-timeline .t-label { font-weight: 600; color: #0f172a; }
    .bl-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#blDetailsTab" type="button" role="tab">
            <i class="bi bi-file-earmark-richtext me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#blTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="blDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $waybill->row_no }}
        <span class="badge {{ $blStatus[$waybill->status] ?? 'bg-secondary-subtle text-secondary' }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ ucfirst(str_replace('_', ' ', $waybill->status ?? '-')) }}</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 linked-print"
            data-print-url="{{ url('bl/waybill/' . $waybill->id . '/print') }}"
            onclick="if (window.WAYBILL && WAYBILL.printPreview) { WAYBILL.printPreview('{{ $waybill->id }}'); }">
        <i class="bi bi-printer me-1"></i> {{ __('Print') }}
    </button>
</div>

<div>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Waybill No') }}</label>
                        <div class="fw-semibold">{{ $waybill->row_no }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Customer') }}</label>
                        <div class="fw-semibold">@if($waybill->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $waybill->customer_id }}" data-title="{{ $waybill->customer->name_en ?? $waybill->customer->name ?? '' }}">{{ $waybill->customer->name_en ?? $waybill->customer->name ?? '-' }}</a>@else - @endif</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Job No') }}</label>
                        <div class="fw-semibold">@if($waybill->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $waybill->job_id }}" data-title="{{ $waybill->job->row_no ?? '' }}">{{ $waybill->job->row_no ?? '-' }}</a>@else - @endif</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Waybill Date') }}</label>
                        <div class="fw-semibold">{{ $waybill->waybill_date }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Delivery Date') }}</label>
                        <div class="fw-semibold">{{ $waybill->delivery_date }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Status') }}</label>
                        @php
                            $statusColors = ['pending' => 'warning', 'in_transit' => 'info', 'delivered' => 'success'];
                            $statusColor = $statusColors[$waybill->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $waybill->status ?? '-')) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">{{ __('Delivery Information') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Delivery Address') }}</label>
                        <div>{{ $waybill->delivery_address ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Contact Person') }}</label>
                        <div>{{ $waybill->contact_person ?? '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Contact Phone') }}</label>
                        <div>{{ $waybill->contact_phone ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">{{ __('Shipment Details') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Shipment Type') }}</label>
                        <div class="text-capitalize">{{ $waybill->shipment_type ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Service Type') }}</label>
                        <div class="text-capitalize">{{ str_replace('_', ' ', $waybill->service_type ?? '-') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Payment Method') }}</label>
                        <div class="text-capitalize">{{ str_replace('_', ' ', $waybill->payment_method ?? '-') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">{{ __('Items') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Comment') }}</th>
                            <th class="text-end">{{ __('Quantity') }}</th>
                            <th class="text-end">{{ __('Weight (kg)') }}</th>
                            <th class="text-end">{{ __('Dimensions (cm)') }}</th>
                            <th class="text-center">{{ __('Fragile') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waybill->waybillSubs as $sub)
                            <tr>
                                <td>{{ $descriptions[$sub->description_id] ?? '-' }}</td>
                                <td>{{ $sub->comment ?? '-' }}</td>
                                <td class="text-end">{{ $sub->quantity }}</td>
                                <td class="text-end">{{ number_format($sub->weight ?? 0, 1) }}</td>
                                <td class="text-end">{{ number_format($sub->length ?? 0, 0) }} x {{ number_format($sub->width ?? 0, 0) }} x {{ number_format($sub->height ?? 0, 0) }}</td>
                                <td class="text-center">
                                    @if($sub->fragile)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">{{ __('No items on this waybill.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">{{ __('Special Instructions') }}</h5>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $waybill->special_instructions ?: __('No special instructions.') }}</p>
        </div>
    </div>
</div>
</div>

<div class="tab-pane fade" id="blTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'bill' => __('Waybill')];
        @endphp
        <ul class="bl-timeline">
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
