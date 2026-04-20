<div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Waybill Overview</h4>
        <div>
            <button class="btn btn-sm btn-outline-primary me-2" onclick="WAYBILL.printPreview({{ request()->route('id') }})">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Waybill No</label>
                        <div class="fw-semibold">WB-2023-001</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Customer</label>
                        <div class="fw-semibold">Sample Customer</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Job No</label>
                        <div class="fw-semibold">JOB-2023-001</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Waybill Date</label>
                        <div class="fw-semibold">{{ date('d-m-Y') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Delivery Date</label>
                        <div class="fw-semibold">{{ date('d-m-Y', strtotime('+3 days')) }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Status</label>
                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Delivery Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Delivery Address</label>
                        <div>123 Main Street, City, Country, 12345</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Contact Person</label>
                        <div>John Doe</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Contact Phone</label>
                        <div>+1234567890</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Shipment Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Shipment Type</label>
                        <div>Parcel</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Service Type</label>
                        <div>Express</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="text-muted small d-block">Payment Method</label>
                        <div>Prepaid</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Items</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
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
                        <tr>
                            <td>Sample Item</td>
                            <td>Sample comment</td>
                            <td class="text-end">2</td>
                            <td class="text-end">5.0</td>
                            <td class="text-end">30 x 20 x 10</td>
                            <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Another Item</td>
                            <td>Another comment</td>
                            <td class="text-end">1</td>
                            <td class="text-end">3.5</td>
                            <td class="text-end">25 x 15 x 8</td>
                            <td class="text-center"><i class="bi bi-x-circle-fill text-danger"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Special Instructions</h5>
        </div>
        <div class="card-body">
            <p>Handle with care. Deliver during business hours only.</p>
        </div>
    </div>
</div>
