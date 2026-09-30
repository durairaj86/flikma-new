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
                <strong>{{ __('Customer') }}:</strong> {{ $airwayBill->customer->name ?? __('N/A') }}
            </div>
            <div class="mb-2">
                <strong>{{ __('Job Reference') }}:</strong> {{ $airwayBill->job->row_no ?? __('N/A') }}
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
                        <strong>{{ __('Payment Method') }}:</strong> {{ ucfirst(str_replace('_', ' ', $airwayBill->payment_method)) ?? __('N/A') }}
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

    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-primary me-2" onclick="AIRWAYBILL.printPreview({{ $airwayBill->id }})">
            <i class="bi bi-printer me-1"></i> {{ __('Print') }}
        </button>
        <a href="/bl/airway-bill/{{ $airwayBill->id }}/create" class="btn btn-secondary">
            <i class="bi bi-pencil me-1"></i> {{ __('Edit') }}
        </a>
    </div>
</div>
