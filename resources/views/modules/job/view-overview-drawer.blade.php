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
        border-left: 4px solid #0d6efd;
        margin-bottom: 1rem;
    }
    table.table {
        font-size: 13.5px;
    }
    table.table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .list-group-item {
        font-size: 14px;
        padding: 10px 0;
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
    .info-grid .col-span-2 {
        grid-column: span 2;
    }
</style>

<!-- General Tab -->
<div class="tab-pane fade show active" id="jobGeneralTab" role="tabpanel">

    <div class="section">
        <h6>Customer &amp; Job Information</h6>
        <div class="info-grid">
            <div><strong>Customer:</strong><span>{{ $job->customer->name_en ?? '-' }}</span></div>
            <div><strong>Job No:</strong><span>#{{ $job->row_no }}</span></div>
            <div><strong>Email:</strong><span>{{ $job->customer->email ?? '-' }}</span></div>
            <div><strong>Posting Date:</strong><span>{{ $job->posted_at ?? '-' }}</span></div>
            <div><strong>Phone:</strong><span>{{ $job->customer->phone ?? '-' }}</span></div>
            <div><strong>Shipment Mode:</strong><span>{{ ucfirst($job->shipment_mode ?? '-') }}</span></div>
            <div class="col-span-2"><strong>Activity:</strong><span>{{ $job->activity->name ?? '-' }}</span></div>
        </div>
    </div>

    <div class="section">
        <h6>General Info</h6>
        <div class="info-grid">
            <div class="col-span-2"><strong>Services:</strong><span>{{ services($job->services) }}</span></div>
            <div><strong>Reference No:</strong><span>{{ $job->client_reference_no ?? '-' }}</span></div>
            <div class="col-span-2"><strong>Remarks:</strong><span>{{ $job->remarks ?? '-' }}</span></div>
        </div>
    </div>

    <div class="section">
        <h6>Routing &amp; Schedule</h6>
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

    <div class="section">
        <h6>Customs &amp; Clearance</h6>
        <div class="info-grid">
            <div><strong>HS Code:</strong><span>{{ $job->hs_code ?? '-' }}</span></div>
            <div><strong>Declaration No:</strong><span>{{ $job->declaration_no ?? '-' }}</span></div>
            <div><strong>Broker:</strong><span>{{ $job->customs_broker ?? '-' }}</span></div>
            <div><strong>Clearance:</strong><span>{{ $job->port_clearance ?? '-' }}</span></div>
            <div><strong>Lab Clearance:</strong><span>{{ $job->lab_clearance ? 'Yes' : 'No' }}</span></div>
            <div><strong>Inspection:</strong><span>{{ $job->inspection ? 'Yes' : 'No' }}</span></div>
            <div><strong>Duty Amount:</strong><span>{{ number_format($job->duty_amount ?? 0, 2) }}</span></div>
            <div><strong>Payment Date:</strong><span>{{ $job->duty_payment_date ?? '-' }}</span></div>
            <div class="col-span-2"><strong>Status:</strong><span>{{ $job->clearance_status ?? '-' }}</span></div>
            <div class="col-span-2"><strong>Remarks:</strong><span>{{ $job->clearance_remarks ?? '-' }}</span></div>
        </div>
    </div>

</div>

<!-- Container Tab -->
<div class="tab-pane fade" id="jobContainerTab" role="tabpanel">
    @if($job->containers->count())
        <div class="table-responsive">
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
                        <td>{{ ucfirst($c->container_type ?? '') }}</td>
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
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-box-seam fs-2 mb-2 d-block"></i>
            No containers added to this job.
        </div>
    @endif
</div>

<!-- Package Tab -->
<div class="tab-pane fade" id="jobPackageTab" role="tabpanel">
    @if($job->packages->count())
        <div class="table-responsive">
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
                        <td>{{ $p->length }} &times; {{ $p->width }} &times; {{ $p->height }}</td>
                        <td>{{ $p->package_weight }}</td>
                        <td>{{ $p->volume }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-boxes fs-2 mb-2 d-block"></i>
            No packages added to this job.
        </div>
    @endif
</div>

<!-- Documents Tab -->
<div class="tab-pane fade" id="jobDocumentsTab" role="tabpanel">
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
        <div class="text-center py-5 text-muted">
            <i class="bi bi-paperclip fs-2 mb-2 d-block"></i>
            No documents uploaded for this job.
        </div>
    @endif
</div>
