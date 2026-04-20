<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Left Column: Basic Information -->
        <div class="col-md-6 border-end">
            <div class="p-3">
                <h6 class="fw-bold mb-3">Basic Information</h6>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Seaway Bill No:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->row_no }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Seaway Bill Date:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->seaway_bill_date }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Job Reference:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->job->row_no }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Customer:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->customer->name }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Status:</div>
                    <div class="col-7">
                        @if($seawayBill->status == 'pending')
                            <span class="badge bg-warning-subtle text-warning">Pending</span>
                        @elseif($seawayBill->status == 'in_transit')
                            <span class="badge bg-primary-subtle text-primary">In Transit</span>
                        @elseif($seawayBill->status == 'delivered')
                            <span class="badge bg-success-subtle text-success">Delivered</span>
                        @elseif($seawayBill->status == 'cancelled')
                            <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-3 border-top">
                <h6 class="fw-bold mb-3">Vessel Information</h6>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Origin Port:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->origin_port }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Destination Port:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->destination_port }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Vessel Name:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->vessel_name ?? 'N/A' }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Voyage Number:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->voyage_number ?? 'N/A' }}</div>
                </div>

                @if($seawayBill->departure_time)
                <div class="row mb-2">
                    <div class="col-5 text-muted">Departure Time:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->departure_time }}</div>
                </div>
                @endif

                @if($seawayBill->arrival_time)
                <div class="row mb-2">
                    <div class="col-5 text-muted">Arrival Time:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->arrival_time }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Delivery & Shipment Details -->
        <div class="col-md-6">
            <div class="p-3">
                <h6 class="fw-bold mb-3">Delivery Information</h6>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Delivery Date:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->delivery_date }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Delivery Address:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->delivery_address }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Contact Person:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->contact_person }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Contact Phone:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->contact_phone }}</div>
                </div>
            </div>

            <div class="p-3 border-top">
                <h6 class="fw-bold mb-3">Shipment Details</h6>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Shipment Type:</div>
                    <div class="col-7 fw-medium">
                        @if($seawayBill->shipment_type == 'document')
                            Document
                        @elseif($seawayBill->shipment_type == 'parcel')
                            Parcel
                        @elseif($seawayBill->shipment_type == 'freight')
                            Freight
                        @endif
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Service Type:</div>
                    <div class="col-7 fw-medium">
                        @if($seawayBill->service_type == 'standard')
                            Standard
                        @elseif($seawayBill->service_type == 'express')
                            Express
                        @elseif($seawayBill->service_type == 'same_day')
                            Same Day
                        @endif
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">Payment Method:</div>
                    <div class="col-7 fw-medium">
                        @if($seawayBill->payment_method == 'prepaid')
                            Prepaid
                        @elseif($seawayBill->payment_method == 'collect')
                            Collect
                        @elseif($seawayBill->payment_method == 'third_party')
                            Third Party
                        @endif
                    </div>
                </div>

                @if($seawayBill->special_instructions)
                <div class="row mb-2">
                    <div class="col-5 text-muted">Special Instructions:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->special_instructions }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Shipment Items -->
    <div class="border-top p-3">
        <h6 class="fw-bold mb-3">Shipment Items</h6>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Comment</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Weight (kg)</th>
                        <th class="text-end">Dimensions (cm)</th>
                        <th class="text-center">Fragile</th>
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
        <h6 class="fw-bold mb-3">Attached Documents</h6>

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

    <!-- Actions -->
    <div class="border-top p-3 d-flex justify-content-end">
        <button class="btn btn-sm btn-outline-secondary me-2" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print
        </button>
        <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='/bl/seaway/{{ $seawayBill->id }}/create'">
            <i class="bi bi-pencil me-1"></i> Edit
        </button>
    </div>
</div>
