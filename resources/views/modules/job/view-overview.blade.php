@extends('includes.print-header')
@section('print-content')

    <style>
        .section {
            margin-bottom: 2rem;
        }
        .section h6 {
            font-size: 15px;
            font-weight: 600;
            background: #f7f7f9;
            padding: 8px 10px;
            border-radius: 4px;
            border-left: 4px solid #0d6efd;
            margin-bottom: 1rem;
        }
        .section .row {
            line-height: 1.8;
            font-size: 14px;
        }
        .section .row strong {
            width: 160px;
            display: inline-block;
            color: #555;
        }
        table.table {
            font-size: 14px;
        }
        table.table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .list-group-item {
            font-size: 14px;
            padding: 10px 0;
        }
        footer {
            font-size: 13px;
        }

        /* 3-column grid layout */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem 1.5rem;
            font-size: 14px;
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
        .info-grid .col-span-3 {
            grid-column: span 3;
        }
    </style>

    <div class="invoice-wrapper">
        <!-- Action Buttons -->
        <div class="d-flex justify-content-end align-items-center gap-2 mb-3 no-print">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="JOB.printPreview('{{ $job->id }}')">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="JOB.downloadPDF('{{ $job->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </button>
        </div>

        <!-- Company Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="company-logo">
                <img src="{{ asset('img/logo.png') }}" alt="Company Logo" style="max-height: 60px;">
            </div>
            <div class="company-info text-end">
                <h5 class="mb-1">{{ 'Your Company Name' }}</h5>
                <small>
                    {{ '123 Business Street, City, Country' }}<br>
                    {{ 'info@company.com' }} | {{ '+91-9876543210' }}
                </small>
            </div>
        </div>

        <hr class="mb-4">

        <!-- Customer & Job Info -->
        <div class="row mb-4">
            <div class="col-6">
                <h6>Customer Details</h6>
                <div><strong>{{ $job->customer->name ?? '-' }}</strong></div>
                <div>{{ $job->customer->address ?? '-' }}</div>
                @if($job->customer->email)
                    <div>Email: {{ $job->customer->email }}</div>
                @endif
                @if($job->customer->phone)
                    <div>Phone: {{ $job->customer->phone }}</div>
                @endif
            </div>

            <div class="col-6">
                <h6>Job Information</h6>
                <table class="table table-borderless table-sm mb-0">
                    <tr><td><strong>Job No:</strong></td><td>#{{ $job->row_no }}</td></tr>
                    <tr><td><strong>Posting Date:</strong></td><td>{{ $job->posted_at ?? '-' }}</td></tr>
                    <tr><td><strong>Salesperson:</strong></td><td>{{ $job->salesperson->name ?? '-' }}</td></tr>
                    <tr><td><strong>Shipment Mode:</strong></td><td>{{ ucfirst($job->shipment_mode) }}</td></tr>
                    <tr><td><strong>Activity:</strong></td><td>{{ $job->activity->name ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        <!-- Section: General Info -->
        <div class="section">
            <h6>General Info</h6>
            <div class="info-grid">
                <div><strong>Services:</strong><span>{{ services($job->services) }}</span></div>
                <div><strong>Reference No:</strong><span>{{ $job->client_reference_no ?? '-' }}</span></div>
                <div><strong>Remarks:</strong><span>{{ $job->remarks ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Section: Routing & Schedule -->
        <div class="section">
            <h6>Routing & Schedule</h6>
            <div class="info-grid">
                <div><strong>Place of Receipt:</strong><span>{{ $job->place_of_receipt ?? '-' }}</span></div>
                <div><strong>POL:</strong><span>{{ $job->pol ?? '-' }}</span></div>
                <div><strong>POD:</strong><span>{{ $job->pod ?? '-' }}</span></div>
                <div><strong>Place of Delivery:</strong><span>{{ $job->place_of_delivery ?? '-' }}</span></div>
                <div><strong>Final Destination:</strong><span>{{ $job->final_destination ?? '-' }}</span></div>
                <div><strong>ETD:</strong><span>{{ $job->etd ?? '-' }}</span></div>
                <div><strong>ETA:</strong><span>{{ $job->eta ?? '-' }}</span></div>
                <div><strong>Transshipment Port:</strong><span>{{ $job->transshipment_port ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Section: Customs & Clearance -->
        <div class="section">
            <h6>Customs & Clearance</h6>
            <div class="info-grid">
                <div><strong>HS Code:</strong><span>{{ $job->hs_code ?? '-' }}</span></div>
                <div><strong>Declaration No:</strong><span>{{ $job->declaration_no ?? '-' }}</span></div>
                <div><strong>Broker:</strong><span>{{ $job->customs_broker ?? '-' }}</span></div>
                <div><strong>Clearance:</strong><span>{{ $job->port_clearance ?? '-' }}</span></div>
                <div><strong>Lab Clearance:</strong><span>{{ $job->lab_clearance ? 'Yes' : 'No' }}</span></div>
                <div><strong>Inspection:</strong><span>{{ $job->inspection ? 'Yes' : 'No' }}</span></div>
                <div><strong>Duty Amount:</strong><span>{{ number_format($job->duty_amount, 2) }}</span></div>
                <div><strong>Payment Date:</strong><span>{{ $job->duty_payment_date ?? '-' }}</span></div>
                <div><strong>Status:</strong><span>{{ $job->clearance_status ?? '-' }}</span></div>
                <div class="col-span-3"><strong>Remarks:</strong><span>{{ $job->clearance_remarks ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Containers -->
        <div class="section">
            <h6>Containers</h6>
            @if($job->containers->count())
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                    <tr>
                        <th>#</th><th>Size</th><th>Type</th><th>Container No</th><th>Seal No</th>
                        <th>Gross</th><th>Net</th><th>Volume</th><th>Hazardous</th><th>Temp Ctrl</th><th>Remarks</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($job->containers as $i => $c)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $c->container_size }}</td>
                            <td>{{ ucfirst($c->container_type) }}</td>
                            <td>{{ $c->container_number ?? '-' }}</td>
                            <td>{{ $c->seal_number ?? '-' }}</td>
                            <td>{{ $c->gross_weight }}</td>
                            <td>{{ $c->net_weight }}</td>
                            <td>{{ $c->volume }}</td>
                            <td>{{ $c->hazardous == 'Yes' ? 'Yes' : 'No' }}</td>
                            <td>{{ $c->temp_controlled == 'Yes' ? 'Yes' : 'No' }}</td>
                            <td>{{ $c->remarks ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">No containers added.</p>
            @endif
        </div>

        <!-- Packages -->
        <div class="section">
            <h6>Packages</h6>
            @if($job->packages->count())
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                    <tr>
                        <th>#</th><th>Commodity</th><th>Type</th><th>Description</th><th>HS Code</th>
                        <th>Qty</th><th>Dimensions</th><th>Weight</th><th>Volume</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($job->packages as $i => $p)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $p->commodity_type }}</td>
                            <td>{{ $p->package_type }}</td>
                            <td>{{ $p->description_goods }}</td>
                            <td>{{ $p->hs_code }}</td>
                            <td>{{ $p->quantity }}</td>
                            <td>{{ $p->length }} × {{ $p->width }} × {{ $p->height }}</td>
                            <td>{{ $p->package_weight }}</td>
                            <td>{{ $p->volume }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">No packages added.</p>
            @endif
        </div>

        <!-- Documents -->
        <div class="section">
            <h6>Documents</h6>
            @if($job->documents->count())
                <ul class="list-group list-group-flush">
                    @foreach($job->documents as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <strong>{{ $doc->document_type }}</strong>
                                <small class="text-muted d-block">{{ $doc->posted_date }}</small>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">View</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted mb-0">No documents uploaded.</p>
            @endif
        </div>

        <!-- Footer -->
        <footer class="mt-5 pt-3 border-top text-center text-muted">
            <div class="d-flex justify-content-between">
                <div>Email: info@company.com</div>
                <div>Phone: +91-9876543210</div>
            </div>
        </footer>
    </div>

@endsection
