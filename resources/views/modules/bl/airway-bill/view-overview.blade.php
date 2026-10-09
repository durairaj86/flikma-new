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
    <div class="invoice-no-heading mb-0">#{{ $airwayBill->row_no }}
        <span class="badge {{ $blStatus[$airwayBill->status] ?? 'bg-secondary-subtle text-secondary' }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ ucfirst(str_replace('_', ' ', $airwayBill->status ?? '-')) }}</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 linked-print"
            data-print-url="{{ url('bl/airway-bill/' . $airwayBill->id . '/print') }}"
            onclick="if (window.AIRWAY_BILL && AIRWAY_BILL.printPreview) { AIRWAY_BILL.printPreview('{{ $airwayBill->id }}'); }">
        <i class="bi bi-printer me-1"></i> {{ __('Print') }}
    </button>
</div>

<div>
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="mb-2">
                <strong>{{ __('Customer') }}:</strong> @if($airwayBill->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $airwayBill->customer_id }}" data-title="{{ $airwayBill->customer->name ?? $airwayBill->customer->name ?? '' }}">{{ $airwayBill->customer->name ?? $airwayBill->customer->name ?? '-' }}</a>@else - @endif
            </div>
            @if($airwayBill->masterBl)
            <div class="mb-2">
                <strong>{{ __('Master B/L') }}:</strong> <a href="#" class="open-linked text-primary text-decoration-none" data-type="master_bl" data-id="{{ $airwayBill->masterBl->id }}" data-title="{{ $airwayBill->masterBl->row_no }}">{{ $airwayBill->masterBl->row_no }}</a>
            </div>
            @endif
            <div class="mb-2">
                <strong>{{ __('Job Reference') }}:</strong> @if($airwayBill->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $airwayBill->job_id }}" data-title="{{ $airwayBill->job->row_no ?? '' }}">{{ $airwayBill->job->row_no ?? '-' }}</a>@else - @endif
            </div>
            <div class="mb-2">
                <strong>{{ __('Airway Bill Date') }}:</strong> {{ $airwayBill->airway_bill_date ? date('d/m/Y', strtotime($airwayBill->airway_bill_date)) : __('N/A') }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-2">
                <strong>{{ __('Delivery Date') }}:</strong> {{ $airwayBill->delivery_date ? date('d/m/Y', strtotime($airwayBill->delivery_date)) : __('N/A') }}
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">{{ __('Flight Information') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>{{ __('Origin Airport') }}:</strong> {{ $airwayBill->origin_airport ?? __('N/A') }}
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('Destination Airport') }}:</strong> {{ $airwayBill->destination_airport ?? __('N/A') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>{{ __('Carrier') }}:</strong> {{ $airwayBill->carrier ?? __('N/A') }}
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('Flight Number') }}:</strong> {{ $airwayBill->flight_number ?? __('N/A') }}
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('Departure Time') }}:</strong> {{ $airwayBill->departure_time ? date('d/m/Y H:i', strtotime($airwayBill->departure_time)) : __('N/A') }}
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('Arrival Time') }}:</strong> {{ $airwayBill->arrival_time ? date('d/m/Y H:i', strtotime($airwayBill->arrival_time)) : __('N/A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">{{ __('Delivery Information') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>{{ __('Delivery Address') }}:</strong><br>
                        {{ $airwayBill->delivery_address ?? __('N/A') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>{{ __('Contact Person') }}:</strong> {{ $airwayBill->contact_person ?? __('N/A') }}
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('Contact Phone') }}:</strong> {{ $airwayBill->contact_phone ?? __('N/A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">{{ __('Shipment Details') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-2">
                        <strong>{{ __('Shipment Type') }}:</strong> {{ ucfirst($airwayBill->shipment_type) ?? __('N/A') }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2">
                        <strong>{{ __('Service Type') }}:</strong> {{ ucfirst($airwayBill->service_type) ?? __('N/A') }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2">
                        <strong>{{ __('Payment Method') }}:</strong> {{ ucfirst(str_replace('_', ' ', $airwayBill->payment_method ?? '')) ?? __('N/A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">{{ __('Items') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Quantity') }}</th>
                            <th>{{ __('Weight') }}</th>
                            <th>{{ __('Dimensions') }}</th>
                            <th>{{ __('Fragile') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($airwayBill->airwayBillSubs) > 0)
                            @foreach($airwayBill->airwayBillSubs as $item)
                                <tr>
                                    <td>{{ $item->description->description ?? __('N/A') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->weight }} kg</td>
                                    <td>{{ $item->length }}x{{ $item->width }}x{{ $item->height }} cm</td>
                                    <td>{{ $item->fragile ? __('Yes') : __('No') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center">{{ __('No items found') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($airwayBill->special_instructions)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">{{ __('Special Instructions') }}</h6>
            </div>
            <div class="card-body">
                {{ $airwayBill->special_instructions }}
            </div>
        </div>
    @endif

    @if(count($airwayBill->documents) > 0)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">{{ __('Attachments') }}</h6>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($airwayBill->documents as $document)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $document->name }}</span>
                            <a href="{{ asset('storage/' . $document->path) }}" target="_blank" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

</div>

@php
    $blContainers = !empty($airwayBill->container_ids)
        ? \Illuminate\Support\Facades\DB::table('job_containers')->whereIn('id', $airwayBill->container_ids)->orderBy('id')->get()
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
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'bill' => __('Airway Bill')];
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
