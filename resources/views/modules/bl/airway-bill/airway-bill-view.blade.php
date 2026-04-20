<div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ $airwayBill->row_no }}</h5>
        <div>
            <span class="badge {{ $airwayBill->status == 'pending' ? 'bg-warning' : ($airwayBill->status == 'in_transit' ? 'bg-primary' : ($airwayBill->status == 'delivered' ? 'bg-success' : 'bg-danger')) }}">
                {{ ucfirst($airwayBill->status) }}
            </span>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="mb-2">
                <strong>Customer:</strong> {{ $airwayBill->customer->name ?? 'N/A' }}
            </div>
            <div class="mb-2">
                <strong>Job Reference:</strong> {{ $airwayBill->job->row_no ?? 'N/A' }}
            </div>
            <div class="mb-2">
                <strong>Airway Bill Date:</strong> {{ $airwayBill->airway_bill_date ? date('d/m/Y', strtotime($airwayBill->airway_bill_date)) : 'N/A' }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-2">
                <strong>Delivery Date:</strong> {{ $airwayBill->delivery_date ? date('d/m/Y', strtotime($airwayBill->delivery_date)) : 'N/A' }}
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">Flight Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>Origin Airport:</strong> {{ $airwayBill->origin_airport ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Destination Airport:</strong> {{ $airwayBill->destination_airport ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>Carrier:</strong> {{ $airwayBill->carrier ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Flight Number:</strong> {{ $airwayBill->flight_number ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Departure Time:</strong> {{ $airwayBill->departure_time ? date('d/m/Y H:i', strtotime($airwayBill->departure_time)) : 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Arrival Time:</strong> {{ $airwayBill->arrival_time ? date('d/m/Y H:i', strtotime($airwayBill->arrival_time)) : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">Delivery Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>Delivery Address:</strong><br>
                        {{ $airwayBill->delivery_address ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2">
                        <strong>Contact Person:</strong> {{ $airwayBill->contact_person ?? 'N/A' }}
                    </div>
                    <div class="mb-2">
                        <strong>Contact Phone:</strong> {{ $airwayBill->contact_phone ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">Shipment Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-2">
                        <strong>Shipment Type:</strong> {{ ucfirst($airwayBill->shipment_type) ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2">
                        <strong>Service Type:</strong> {{ ucfirst($airwayBill->service_type) ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2">
                        <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $airwayBill->payment_method)) ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">Items</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Weight</th>
                            <th>Dimensions</th>
                            <th>Fragile</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($airwayBill->airwayBillSubs) > 0)
                            @foreach($airwayBill->airwayBillSubs as $item)
                                <tr>
                                    <td>{{ $item->description->description ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->weight }} kg</td>
                                    <td>{{ $item->length }}x{{ $item->width }}x{{ $item->height }} cm</td>
                                    <td>{{ $item->fragile ? 'Yes' : 'No' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center">No items found</td>
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
                <h6 class="mb-0">Special Instructions</h6>
            </div>
            <div class="card-body">
                {{ $airwayBill->special_instructions }}
            </div>
        </div>
    @endif

    @if(count($airwayBill->documents) > 0)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Attachments</h6>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($airwayBill->documents as $document)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $document->name }}</span>
                            <a href="{{ asset('storage/' . $document->path) }}" target="_blank" class="btn btn-sm btn-primary">View</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-primary me-2" onclick="AIRWAYBILL.printPreview({{ $airwayBill->id }})">
            <i class="bi bi-printer me-1"></i> Print
        </button>
        <a href="/bl/airway-bill/{{ $airwayBill->id }}/create" class="btn btn-secondary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
    </div>
</div>
