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
    <div class="invoice-no-heading mb-0">#{{ $seawayBill->row_no }}
        <span class="badge {{ $blStatus[$seawayBill->status] ?? 'bg-secondary-subtle text-secondary' }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ ucfirst(str_replace('_', ' ', $seawayBill->status ?? '-')) }}</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 linked-print"
            data-print-url="{{ url('bl/seaway/' . $seawayBill->id . '/print') }}"
            onclick="if (window.SEAWAY_BILL && SEAWAY_BILL.printPreview) { SEAWAY_BILL.printPreview('{{ $seawayBill->id }}'); }">
        <i class="bi bi-printer me-1"></i> {{ __('Print') }}
    </button>
</div>

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Left Column: Basic Information -->
        <div class="col-md-6 border-end">
            <div class="p-3">
                <h6 class="fw-bold mb-3">{{ __('Basic Information') }}</h6>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Seaway Bill No') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->row_no }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Seaway Bill Date') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->seaway_bill_date }}</div>
                </div>

                @if($seawayBill->masterBl)
                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Master B/L') }}:</div>
                    <div class="col-7 fw-medium"><a href="#" class="open-linked text-primary text-decoration-none" data-type="master_bl" data-id="{{ $seawayBill->masterBl->id }}" data-title="{{ $seawayBill->masterBl->row_no }}">{{ $seawayBill->masterBl->row_no }}</a></div>
                </div>
                @endif

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Job Reference') }}:</div>
                    <div class="col-7 fw-medium">@if($seawayBill->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $seawayBill->job_id }}" data-title="{{ $seawayBill->job->row_no ?? '' }}">{{ $seawayBill->job->row_no ?? '-' }}</a>@else - @endif</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Customer') }}:</div>
                    <div class="col-7 fw-medium">@if($seawayBill->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $seawayBill->customer_id }}" data-title="{{ $seawayBill->customer->name ?? $seawayBill->customer->name ?? '' }}">{{ $seawayBill->customer->name ?? $seawayBill->customer->name ?? '-' }}</a>@else - @endif</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Status') }}:</div>
                    <div class="col-7">
                        @if($seawayBill->status == 'pending')
                            <span class="badge bg-warning-subtle text-warning">{{ __('Pending') }}</span>
                        @elseif($seawayBill->status == 'in_transit')
                            <span class="badge bg-primary-subtle text-primary">{{ __('In Transit') }}</span>
                        @elseif($seawayBill->status == 'delivered')
                            <span class="badge bg-success-subtle text-success">{{ __('Delivered') }}</span>
                        @elseif($seawayBill->status == 'cancelled')
                            <span class="badge bg-danger-subtle text-danger">{{ __('Cancelled') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-3 border-top">
                <h6 class="fw-bold mb-3">{{ __('Vessel Information') }}</h6>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Origin Port') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->origin_port }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Destination Port') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->destination_port }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Vessel Name') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->vessel_name ?? 'N/A' }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Voyage Number') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->voyage_number ?? 'N/A' }}</div>
                </div>

                @if($seawayBill->departure_time)
                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Departure Time') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->departure_time }}</div>
                </div>
                @endif

                @if($seawayBill->arrival_time)
                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Arrival Time') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->arrival_time }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Delivery & Shipment Details -->
        <div class="col-md-6">
            <div class="p-3">
                <h6 class="fw-bold mb-3">{{ __('Delivery Information') }}</h6>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Delivery Date') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->delivery_date }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Delivery Address') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->delivery_address }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Contact Person') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->contact_person }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Contact Phone') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->contact_phone }}</div>
                </div>
            </div>

            <div class="p-3 border-top">
                <h6 class="fw-bold mb-3">{{ __('Shipment Details') }}</h6>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Shipment Type') }}:</div>
                    <div class="col-7 fw-medium">
                        @if($seawayBill->shipment_type == 'document')
                            {{ __('Document') }}
                        @elseif($seawayBill->shipment_type == 'parcel')
                            {{ __('Parcel') }}
                        @elseif($seawayBill->shipment_type == 'freight')
                            {{ __('Freight') }}
                        @endif
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Service Type') }}:</div>
                    <div class="col-7 fw-medium">
                        @if($seawayBill->service_type == 'standard')
                            {{ __('Standard') }}
                        @elseif($seawayBill->service_type == 'express')
                            {{ __('Express') }}
                        @elseif($seawayBill->service_type == 'same_day')
                            {{ __('Same Day') }}
                        @endif
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Payment Method') }}:</div>
                    <div class="col-7 fw-medium">
                        @if($seawayBill->payment_method == 'prepaid')
                            {{ __('Prepaid') }}
                        @elseif($seawayBill->payment_method == 'collect')
                            {{ __('Collect') }}
                        @elseif($seawayBill->payment_method == 'third_party')
                            {{ __('Third Party') }}
                        @endif
                    </div>
                </div>

                @if($seawayBill->special_instructions)
                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Special Instructions') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->special_instructions }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Shipment Items -->
    <div class="border-top p-3">
        <h6 class="fw-bold mb-3">{{ __('Shipment Items') }}</h6>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
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
                    @foreach($seawayBill->seawayBillSubs as $item)
                    <tr>
                        <td>{{ $item->description->name }}</td>
                        <td>{{ $item->comment }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ $item->weight }}</td>
                        <td class="text-end">{{ $item->length }} x {{ $item->width }} x {{ $item->height }}</td>
                        <td class="text-center">
                            @if($item->fragile)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                                <i class="bi bi-x-circle text-muted"></i>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Documents -->
    @if(count($seawayBill->documents) > 0)
    <div class="border-top p-3">
        <h6 class="fw-bold mb-3">{{ __('Attached Documents') }}</h6>

        <div class="row">
            @foreach($seawayBill->documents as $document)
            <div class="col-md-4 mb-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                    <a href="{{ asset('storage/' . $document->path) }}" target="_blank" class="text-decoration-none">
                        {{ $document->name }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

@php
    $blContainers = !empty($seawayBill->container_ids)
        ? \Illuminate\Support\Facades\DB::table('job_containers')->whereIn('id', $seawayBill->container_ids)->orderBy('id')->get()
        : collect();
@endphp
<div class="section">
    <h6>{{ __('Containers') }}</h6>
    @if($blContainers->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Container No') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Seal No') }}</th>
                    <th class="text-end">{{ __('Weight') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($blContainers as $bc)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $bc->container_number ?: ($bc->container_no ?: '-') }}</td>
                        <td>{{ $bc->container_size ? containerSize($bc->container_size) : '-' }}</td>
                        <td>{{ $bc->container_type ?: '-' }}</td>
                        <td>{{ $bc->seal_number ?: ($bc->seal_no ?: '-') }}</td>
                        <td class="text-end">{{ $bc->gross_weight ?: ($bc->weight ?: '-') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-3 text-muted">{{ __('No containers selected for this bill.') }}</div>
    @endif
</div>

</div>

<div class="tab-pane fade" id="blTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'bill' => __('Seaway Bill')];
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
