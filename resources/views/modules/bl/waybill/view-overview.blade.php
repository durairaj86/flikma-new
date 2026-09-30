<div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">{{ __('Waybill Overview') }}</h4>
        <div>
            <button class="btn btn-sm btn-outline-primary me-2" onclick="WAYBILL.printPreview({{ $waybill->id }})">
                <i class="bi bi-printer"></i> {{ __('Print') }}
            </button>
        </div>
    </div>

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
                        <div class="fw-semibold">{{ $waybill->customer->name_en ?? '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">{{ __('Job No') }}</label>
                        <div class="fw-semibold">{{ $waybill->job->row_no ?? '-' }}</div>
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
