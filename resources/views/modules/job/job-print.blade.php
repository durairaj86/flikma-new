@extends('includes.print-header')
@section('print-content')
    {{-- The job print: the same layout as the quotation print (logo + company, title with number, "To" and details, ruled sections,
         signatures, footer), with the job's own details. --}}
    @php
        $customer = $job->customer;
        $owner = \App\Models\User::withoutGlobalScopes()->find($job->salesperson_id ?: $job->created_by)?->name;
        $statusLabel = \App\Enums\JobEnum::tryFrom((int) $job->status)?->label();
        $clearance = $job->clearance;
        // customs details live on the job and on its clearance record: the clearance record wins where it has a value
        $customs = [
            __('HS Code') => $clearance->hs_code ?? $job->hs_code,
            __('Declaration No') => $clearance->declaration_no ?? $job->declaration_no,
            __('Customs Broker') => $clearance->customs_broker ?? $job->customs_broker,
            __('Port Clearance') => $clearance->port_clearance ?? $job->port_clearance,
            __('Bayan No') => $clearance->bayan_no ?? $job->bayan_no,
            __('Bayan Date') => showDate($clearance->bayan_date ?? $job->bayan_date),
            __('D.O No') => $clearance->do_no ?? $job->do_no,
            __('D.O Date') => showDate($clearance->do_date ?? $job->do_date),
            __('Clearance Status') => $clearance->clearance_status ?? null,
            __('Clearance Date') => showDate($clearance->clearance_date ?? null),
            __('Duty Amount') => (float) ($clearance->duty_amount ?? $job->duty_amount) ? number_format((float) ($clearance->duty_amount ?? $job->duty_amount), 2) : null,
        ];
        $hasCustoms = collect($customs)->filter(fn ($v) => filled($v))->isNotEmpty();
    @endphp

    <style>
        /* the label column of every detail table is the same width, so the values line up from one section to the next */
        .job-wrapper .table-borderless td:first-child { width: 200px; white-space: nowrap; }
    </style>

    <div class="job-wrapper">
        {{-- Watermark for a cancelled job --}}
        @if((int) $job->status === \App\Enums\JobEnum::CANCELLED->value)
            <div class="draft-watermark">{{ __('CANCELLED') }}</div>
        @endif

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end align-items-center gap-2 mb-3 no-print">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="JOB.printPreview('{{ $job->id }}')">
                <i class="bi bi-printer me-1"></i> {{ __('Print') }}
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="JOB.downloadPDF('{{ $job->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('Download PDF') }}
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

        <!-- Title Block -->
        <div class="invoice-title d-flex justify-content-between">
            <div class="fw-bold text-uppercase title">{{ __('Job') }}</div>
            <div class="">{{ $job->row_no ? '#' . $job->row_no : '' }}</div>
        </div>

        <!-- Customer Info & Job Info -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-semibold">{{ __('To,') }}</h6>
                <div><strong>{{ $customer->name ?? ($customer->name_en ?? '-') }}</strong></div>
                <div>{{ $customer->address ?? '-' }}</div>
                @if(!empty($customer->email))
                    <div>{{ __('Email') }}: {{ $customer->email }}</div>
                @endif
                @if(!empty($customer->phone))
                    <div>{{ __('Phone') }}: {{ $customer->phone }}</div>
                @endif
            </div>

            <div class="col-6">
                <div class="d-flex flex-column align-items-end gap-2">
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">{{ __('Job Date') }}:</div>
                        <div class="text-end" style="min-width: 120px;">{{ showDate($job->posted_at) ?? '-' }}</div>
                    </div>
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">{{ __('Salesperson') }}:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $owner ?: '-' }}</div>
                    </div>
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">{{ __('Department') }}:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $job->activity->name ?? '-' }}</div>
                    </div>
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">{{ __('Shipment Mode') }}:</div>
                        <div class="text-end" style="min-width: 120px;">{{ shipmentMode()[$job->shipment_mode] ?? ucfirst((string) $job->shipment_mode) ?: '-' }}</div>
                    </div>
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">{{ __('Status') }}:</div>
                        <div class="text-end" style="min-width: 120px;">
                            <span class="badge bg-warning text-dark">{{ $statusLabel ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipment Routing -->
        <div class="section">
            <h6 class="fw-semibold border-bottom pb-1 mb-3">{{ __('Shipment Routing') }}</h6>
            <table class="table table-borderless table-sm mb-0">
                <tr><td><strong>{{ __('Place of Receipt') }}:</strong></td><td>{{ $job->place_of_receipt ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('POL') }}:</strong></td><td>{{ $job->pol ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('POD') }}:</strong></td><td>{{ $job->pod ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('Place of Delivery') }}:</strong></td><td>{{ $job->place_of_delivery ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('Final Destination') }}:</strong></td><td>{{ $job->final_destination ?: '-' }}</td></tr>
                @if($job->transshipment_port)
                    <tr><td><strong>{{ __('Transshipment Port') }}:</strong></td><td>{{ $job->transshipment_port }}</td></tr>
                @endif
                <tr><td><strong>{{ __('ETD') }}:</strong></td><td>{{ showDate($job->etd) ?? '-' }}</td></tr>
                <tr><td><strong>{{ __('ETA') }}:</strong></td><td>{{ showDate($job->eta) ?? '-' }}</td></tr>
                @if($job->atd)
                    <tr><td><strong>{{ __('ATD') }}:</strong></td><td>{{ showDate($job->atd) }}</td></tr>
                @endif
                @if($job->ata)
                    <tr><td><strong>{{ __('ATA') }}:</strong></td><td>{{ showDate($job->ata) }}</td></tr>
                @endif
                @if($job->delivery_date)
                    <tr><td><strong>{{ __('Delivery') }}:</strong></td><td>{{ showDate($job->delivery_date) }}</td></tr>
                @endif
                <tr><td><strong>{{ __('Incoterm') }}:</strong></td><td>{{ $job->incoterm ?: ($job->incoterms ?: '-') }}</td></tr>
                <tr><td><strong>{{ __('Carrier') }}:</strong></td><td>{{ $job->carrier ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('Voyage / Flight No') }}:</strong></td><td>{{ $job->voyage_flight_no ?: '-' }}</td></tr>
            </table>
        </div>

        <!-- Cargo -->
        <div class="section mt-4">
            <h6 class="fw-semibold border-bottom pb-1 mb-3">{{ __('Cargo Details') }}</h6>
            <table class="table table-borderless table-sm mb-0">
                <tr><td><strong>{{ __('Commodity') }}:</strong></td><td>{{ $job->commodity ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('No. of Pieces') }}:</strong></td><td>{{ $job->no_of_pieces ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('Weight') }}:</strong></td><td>{{ (float) $job->weight ? number_format((float) $job->weight, 2) . ' kg' : '-' }}</td></tr>
                <tr><td><strong>{{ __('Volume') }}:</strong></td><td>{{ (float) $job->volume ? number_format((float) $job->volume, 3) . ' CBM' : '-' }}</td></tr>
                <tr><td><strong>{{ __('House B/L No') }}:</strong></td><td>{{ $job->hbl_number ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('AWB No') }}:</strong></td><td>{{ $job->awb_number ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('Shipping Reference') }}:</strong></td><td>{{ $job->shipping_ref ?: '-' }}</td></tr>
                <tr><td><strong>{{ __('Client Reference') }}:</strong></td><td>{{ $job->client_ref ?: '-' }}</td></tr>
                @if($job->services)
                    <tr><td><strong>{{ __('Services') }}:</strong></td><td>{{ services($job->services) }}</td></tr>
                @endif
            </table>
        </div>

        <!-- Customs & Clearance (only when there is something to show) -->
        @if($hasCustoms)
            <div class="section mt-4">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">{{ __('Customs & Clearance') }}</h6>
                <table class="table table-borderless table-sm mb-0">
                    @foreach($customs as $label => $value)
                        @if(filled($value))
                            <tr><td><strong>{{ $label }}:</strong></td><td>{{ $value }}</td></tr>
                        @endif
                    @endforeach
                </table>
            </div>
        @endif

        <!-- Containers -->
        @if($job->containers && $job->containers->count())
            <div class="section mt-4">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">{{ __('Container Details') }}</h6>
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                    <tr>
                        <th>#</th><th>{{ __('Size') }}</th><th>{{ __('Type') }}</th><th>{{ __('Container No') }}</th><th>{{ __('Seal No') }}</th>
                        <th>{{ __('Gross Wt') }}</th><th>{{ __('Net Wt') }}</th><th>CBM</th><th>{{ __('Hazardous') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($job->containers as $i => $c)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $c->container_size }}</td>
                            <td>{{ ucfirst((string) $c->container_type) }}</td>
                            <td>{{ $c->container_number ?: ($c->container_no ?: '-') }}</td>
                            <td>{{ $c->seal_number ?: ($c->seal_no ?: '-') }}</td>
                            <td>{{ $c->gross_weight }}</td>
                            <td>{{ $c->net_weight }}</td>
                            <td>{{ $c->volume }}</td>
                            <td>{{ $c->hazardous == 'Yes' ? __('Yes') : __('No') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Packages -->
        @if($job->packages && $job->packages->count())
            <div class="section">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">{{ __('Package / Goods Details') }}</h6>
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                    <tr>
                        <th>#</th><th>{{ __('Commodity') }}</th><th>{{ __('Description') }}</th><th>{{ __('HS Code') }}</th>
                        <th>{{ __('Qty') }}</th><th>L</th><th>W</th><th>H</th><th>{{ __('Weight') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($job->packages as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ commodityType()[$p->commodity_type] ?? $p->commodity_type }}</td>
                            <td>{{ $p->description_goods }}</td>
                            <td>{{ $p->hs_code }}</td>
                            <td>{{ $p->quantity }}</td>
                            <td>{{ $p->length }}</td>
                            <td>{{ $p->width }}</td>
                            <td>{{ $p->height }}</td>
                            <td>{{ $p->package_weight }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Documents -->
        @if($job->documents && $job->documents->count())
            <div class="section mt-4">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">{{ __('Documents') }}</h6>
                <table class="table table-borderless table-sm mb-0">
                    @foreach($job->documents as $doc)
                        <tr><td><strong>{{ $doc->document_type }}</strong></td><td>{{ showDate($doc->posted_date) }}</td></tr>
                    @endforeach
                </table>
            </div>
        @endif

        <!-- Notes -->
        @if($job->remarks || $job->terms)
            <div class="section mt-4">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">{{ __('Additional Information') }}</h6>
                @if($job->terms)
                    <p><strong>{{ __('Terms & Conditions') }}:</strong> {{ $job->terms }}</p>
                @endif
                @if($job->remarks)
                    <p><strong>{{ __('Remarks') }}:</strong> {{ $job->remarks }}</p>
                @endif
            </div>
        @endif

        <!-- Signature Section -->
        <div class="d-flex justify-content-between mt-5">
            <div class="text-center">
                <div style="border-top:1px solid #333;width:200px;margin:auto;margin-top:40px;"></div>
                <small>{{ __('Authorized Signature') }}</small>
            </div>
            <div class="text-center">
                <div style="border-top:1px solid #333;width:200px;margin:auto;margin-top:40px;"></div>
                <small>{{ __('Customer Signature') }}</small>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-5 pt-3 border-top text-center text-muted small">
            <div>{{ __('Email') }}: {{ companyEmail() }} | {{ __('Phone') }}: {{ companyPhone() }}</div>
        </footer>
    </div>

@endsection
