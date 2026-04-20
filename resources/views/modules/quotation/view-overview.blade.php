@extends('includes.print-header')
@section('print-content')

    <div class="quotation-wrapper">
        {{-- DRAFT Watermark --}}
        @if($quotation->status == 1)
            <div class="draft-watermark">DRAFT</div>
        @endif

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end align-items-center gap-2 mb-3 no-print">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="QUOTATION.printPreview('{{ $quotation->id }}')">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="QUOTATION.downloadPDF('{{ $quotation->id }}')">
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

        <!-- Title Block -->
        <div class="invoice-title d-flex justify-content-between">
            <div class="fw-bold text-uppercase title">Quotation</div>
            <div class="">#{{ $quotation->row_no }}</div>
        </div>

        <!-- Customer Info & Quotation Info -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-semibold">To,</h6>
                <div><strong>{{ $quotation->party->name ?? '-' }}</strong></div>
                <div>{{ $quotation->party->address ?? '-' }}</div>
                @if($quotation->party->email)
                    <div>Email: {{ $quotation->party->email }}</div>
                @endif
                @if($quotation->party->phone)
                    <div>Phone: {{ $quotation->party->phone }}</div>
                @endif
            </div>

            <div class="col-6">
                <div class="d-flex flex-column align-items-end gap-2">
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">Quotation Date:</div>
                        <div class="text-end" style="min-width: 120px;">{{ showDate($quotation->posted_at) }}</div>
                    </div>
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">Valid Until:</div>
                        <div class="text-end" style="min-width: 120px;">{{ showDate($quotation->valid_until) }}</div>
                    </div>
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">Prepared By:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $quotation->prepared_by ?? '-' }}</div>
                    </div>
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">Shipment Mode:</div>
                        <div class="text-end" style="min-width: 120px;">{{ shipmentMode()[$quotation->shipment_mode] ?? '-' }}</div>
                    </div>
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 140px;">Status:</div>
                        <div class="text-end" style="min-width: 120px;">
                        <span class="badge bg-warning text-dark">
                            {{ \App\Enums\QuotationEnum::from($quotation->status)->label() }}
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipment Routing -->
        <div class="section">
            <h6 class="fw-semibold border-bottom pb-1 mb-3">Shipment Routing</h6>
            <table class="table table-borderless table-sm mb-0">
                <tr><td><strong>Place of Receipt:</strong></td><td>{{ $quotation->place_of_receipt ?? '-' }}</td></tr>
                <tr><td><strong>POL:</strong></td><td>{{ $quotation->pol ?? '-' }}</td></tr>
                <tr><td><strong>POD:</strong></td><td>{{ $quotation->pod ?? '-' }}</td></tr>
                <tr><td><strong>Place of Delivery:</strong></td><td>{{ $quotation->place_of_delivery ?? '-' }}</td></tr>
                <tr><td><strong>Final Destination:</strong></td><td>{{ $quotation->final_destination ?? '-' }}</td></tr>
                <tr><td><strong>Incoterm:</strong></td><td>{{ $quotation->incoterm ?? '-' }}</td></tr>
                <tr><td><strong>Carrier:</strong></td><td>{{ $quotation->carrier ?? '-' }}</td></tr>
            </table>
        </div>

        <!-- Containers -->
        @if($quotation->containers && $quotation->containers->count())
            <div class="section mt-4">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">Container Details</h6>
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                    <tr>
                        <th>#</th><th>Size</th><th>Container No</th><th>Seal No</th>
                        <th>Gross Wt</th><th>Net Wt</th><th>CBM</th><th>Hazardous</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($quotation->containers as $i => $c)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $c->container_size }}</td>
                            <td>{{ $c->container_number ?? '-' }}</td>
                            <td>{{ $c->seal_number ?? '-' }}</td>
                            <td>{{ $c->gross_weight }}</td>
                            <td>{{ $c->net_weight }}</td>
                            <td>{{ $c->volume }}</td>
                            <td>{{ $c->hazardous }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Packages -->
        @if($quotation->packages && $quotation->packages->count())
            <div class="section">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">Package / Goods Details</h6>
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                    <tr>
                        <th>#</th><th>Commodity</th><th>Description</th><th>HS Code</th>
                        <th>L</th><th>W</th><th>H</th><th>Weight</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($quotation->packages as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ commodityType()[$p->commodity_type] ?? $p->commodity_type }}</td>
                            <td>{{ $p->description_goods }}</td>
                            <td>{{ $p->hs_code }}</td>
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

        <!-- Notes & Terms -->
        @if($quotation->terms || $quotation->notes)
            <div class="section mt-4">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">Additional Information</h6>
                @if($quotation->terms)
                    <p><strong>Terms & Conditions:</strong> {{ $quotation->terms }}</p>
                @endif
                @if($quotation->notes)
                    <p><strong>Notes:</strong> {{ $quotation->notes }}</p>
                @endif
            </div>
        @endif

        <!-- Signature Section -->
        <div class="d-flex justify-content-between mt-5">
            <div class="text-center">
                <div style="border-top:1px solid #333;width:200px;margin:auto;margin-top:40px;"></div>
                <small>Authorized Signature</small>
            </div>
            <div class="text-center">
                <div style="border-top:1px solid #333;width:200px;margin:auto;margin-top:40px;"></div>
                <small>Customer Signature</small>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-5 pt-3 border-top text-center text-muted small">
            <div>Email: info@company.com | Phone: +91-9876543210</div>
        </footer>
    </div>

@endsection
