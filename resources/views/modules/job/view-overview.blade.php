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
            /*border-left: 4px solid #0d6efd;*/
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
                <i class="bi bi-printer me-1"></i> {{ __('Print') }}
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="JOB.downloadPDF('{{ $job->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('Download PDF') }}
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i> {{ __('Cancel') }}
            </button>
        </div>

        <!-- Company Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="company-logo">
                <img src="{{ companyLogo() }}" alt="{{ __('Company Logo') }}" style="max-height: 60px;">
            </div>
            <div class="company-info text-end">
                <h5 class="mb-1">{{ companyName() }}</h5>
                <small>
                    {{ companyAddress() }}<br>
                    {{ companyEmail() }} | {{ companyPhone() }}
                </small>
            </div>
        </div>

        <hr class="mb-4">

        <!-- Customer & Job Info -->
        <div class="row mb-4">
            <div class="col-6">
                <h6>{{ __('Customer Details') }}</h6>
                <div><strong>{{ $job->customer->name ?? '-' }}</strong></div>
                <div>{{ $job->customer->address ?? '-' }}</div>
                @if($job->customer->email)
                    <div>{{ __('Email:') }} {{ $job->customer->email }}</div>
                @endif
                @if($job->customer->phone)
                    <div>{{ __('Phone:') }} {{ $job->customer->phone }}</div>
                @endif
            </div>

            <div class="col-6">
                <h6>{{ __('Job Information') }}</h6>
                <table class="table table-borderless table-sm mb-0">
                    <tr><td><strong>{{ __('Job No:') }}</strong></td><td>#{{ $job->row_no }}</td></tr>
                    <tr><td><strong>{{ __('Posting Date:') }}</strong></td><td>{{ $job->posted_at ?? '-' }}</td></tr>
                    <tr><td><strong>{{ __('Salesperson:') }}</strong></td><td>{{ $job->salesperson->name ?? '-' }}</td></tr>
                    <tr><td><strong>{{ __('Shipment Mode:') }}</strong></td><td>{{ ucfirst($job->shipment_mode) }}</td></tr>
                    <tr><td><strong>{{ __('Department:') }}</strong></td><td>{{ $job->activity->name ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        <!-- Section: General Info -->
        <div class="section">
            <h6>{{ __('General Info') }}</h6>
            <div class="info-grid">
                <div><strong>{{ __('Services:') }}</strong><span>{{ services($job->services) }}</span></div>
                <div><strong>{{ __('Reference No:') }}</strong><span>{{ $job->client_reference_no ?? '-' }}</span></div>
                <div><strong>{{ __('Remarks:') }}</strong><span>{{ $job->remarks ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Section: Routing & Schedule -->
        <div class="section">
            <h6>{{ __('Routing & Schedule') }}</h6>
            <div class="info-grid">
                <div><strong>{{ __('Place of Receipt:') }}</strong><span>{{ $job->place_of_receipt ?? '-' }}</span></div>
                <div><strong>{{ __('POL:') }}</strong><span>{{ $job->pol ?? '-' }}</span></div>
                <div><strong>{{ __('POD:') }}</strong><span>{{ $job->pod ?? '-' }}</span></div>
                <div><strong>{{ __('Place of Delivery:') }}</strong><span>{{ $job->place_of_delivery ?? '-' }}</span></div>
                <div><strong>{{ __('Final Destination:') }}</strong><span>{{ $job->final_destination ?? '-' }}</span></div>
                <div><strong>{{ __('ETD:') }}</strong><span>{{ $job->etd ?? '-' }}</span></div>
                <div><strong>{{ __('ETA:') }}</strong><span>{{ $job->eta ?? '-' }}</span></div>
                <div><strong>{{ __('Transshipment Port:') }}</strong><span>{{ $job->transshipment_port ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Section: Customs & Clearance -->
        <div class="section">
            <h6>{{ __('Customs & Clearance') }}</h6>
            <div class="info-grid">
                <div><strong>{{ __('HS Code:') }}</strong><span>{{ $job->hs_code ?? '-' }}</span></div>
                <div><strong>{{ __('Declaration No:') }}</strong><span>{{ $job->declaration_no ?? '-' }}</span></div>
                <div><strong>{{ __('Broker:') }}</strong><span>{{ $job->customs_broker ?? '-' }}</span></div>
                <div><strong>{{ __('Clearance:') }}</strong><span>{{ $job->port_clearance ?? '-' }}</span></div>
                <div><strong>{{ __('Lab Clearance:') }}</strong><span>{{ $job->lab_clearance ? __('Yes') : __('No') }}</span></div>
                <div><strong>{{ __('Inspection:') }}</strong><span>{{ $job->inspection ? __('Yes') : __('No') }}</span></div>
                <div><strong>{{ __('Duty Amount:') }}</strong><span>{{ number_format($job->duty_amount, 2) }}</span></div>
                <div><strong>{{ __('Payment Date:') }}</strong><span>{{ $job->duty_payment_date ?? '-' }}</span></div>
                <div><strong>{{ __('Status:') }}</strong><span>{{ $job->clearance_status ?? '-' }}</span></div>
                <div class="col-span-3"><strong>{{ __('Remarks:') }}</strong><span>{{ $job->clearance_remarks ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Containers -->
        <div class="section">
            <h6>{{ __('Containers') }}</h6>
            @if($job->containers->count())
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                    <tr>
                        <th>#</th><th>{{ __('Size') }}</th><th>{{ __('Type') }}</th><th>{{ __('Container No') }}</th><th>{{ __('Seal No') }}</th>
                        <th>{{ __('Gross') }}</th><th>{{ __('Net') }}</th><th>{{ __('Volume') }}</th><th>{{ __('Hazardous') }}</th><th>{{ __('Temp Ctrl') }}</th><th>{{ __('Remarks') }}</th>
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
                            <td>{{ $c->hazardous == 'Yes' ? __('Yes') : __('No') }}</td>
                            <td>{{ $c->temp_controlled == 'Yes' ? __('Yes') : __('No') }}</td>
                            <td>{{ $c->remarks ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mb-0">{{ __('No containers added.') }}</p>
            @endif
        </div>

        <!-- Packages -->
        <div class="section">
            <h6>{{ __('Packages') }}</h6>
            @if($job->packages->count())
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                    <tr>
                        <th>#</th><th>{{ __('Commodity') }}</th><th>{{ __('Type') }}</th><th>{{ __('Description') }}</th><th>{{ __('HS Code') }}</th>
                        <th>{{ __('Qty') }}</th><th>{{ __('Dimensions') }}</th><th>{{ __('Weight') }}</th><th>{{ __('Volume') }}</th>
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
                <p class="text-muted mb-0">{{ __('No packages added.') }}</p>
            @endif
        </div>

        <!-- Documents -->
        <div class="section">
            <h6>{{ __('Documents') }}</h6>
            @if($job->documents->count())
                <ul class="list-group list-group-flush">
                    @foreach($job->documents as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <strong>{{ $doc->document_type }}</strong>
                                <small class="text-muted d-block">{{ $doc->posted_date }}</small>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">{{ __('View') }}</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted mb-0">{{ __('No documents uploaded.') }}</p>
            @endif
        </div>

        <!-- Footer -->
        <footer class="mt-5 pt-3 border-top text-center text-muted">
            <div class="d-flex justify-content-between">
                <div>{{ __('Email:') }} {{ companyEmail() }}</div>
                <div>{{ __('Phone:') }} {{ companyPhone() }}</div>
            </div>
        </footer>
    </div>

@endsection
