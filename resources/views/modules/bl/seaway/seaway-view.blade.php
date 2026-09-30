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

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Job Reference') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->job->row_no }}</div>
                </div>

                <div class="row mb-2">
                    <div class="col-5 text-muted">{{ __('Customer') }}:</div>
                    <div class="col-7 fw-medium">{{ $seawayBill->customer->name }}</div>
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

    <!-- Actions -->
    <div class="border-top p-3 d-flex justify-content-end">
        <button class="btn btn-sm btn-outline-secondary me-2" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> {{ __('Print') }}
        </button>
        <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='/bl/seaway/{{ $seawayBill->id }}/create'">
            <i class="bi bi-pencil me-1"></i> {{ __('Edit') }}
        </button>
    </div>
</div>
