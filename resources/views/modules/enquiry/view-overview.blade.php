@extends('includes.print-header')
@section('print-content')

    <style>
        .enquiry-wrapper {
            background: #fff;
            border-radius: 6px;
        }

        /* ===== Header Section ===== */
        .enquiry-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid;
            border-bottom: 1px solid;
            padding-top: .4rem;
            padding-bottom: .4rem;
            margin-bottom: 1rem;
        }
        .enquiry-header .left {
            flex: 1;
        }
        .enquiry-header .title {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .enquiry-header .right {
            text-align: right;
            font-size: 14px;
            line-height: 1.6;
        }
        .enquiry-header .right strong {
            color: #000;
        }

        /* ===== Card Layout ===== */
        .card {
            border: 1px solid #e4e8ee;
            border-radius: 6px;
        }
        .card-header {
            background: #f8f9fc;
            font-weight: 600;
            font-size: 14px;
            padding: .6rem .9rem;
            border-bottom: 1px solid #e4e8ee;
        }
        .card-body {
            padding: .9rem 1rem;
            font-size: 14px;
        }

        /* ===== Tables ===== */
        table.table {
            font-size: 13.5px;
            margin-bottom: 0;
        }
        table.table th {
            background: #f1f4f8;
            font-weight: 600;
        }
        table.table td {
            vertical-align: middle;
        }

        .badge-status {
            padding: .35rem .55rem;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }

        footer {
            font-size: 13px;
            border-top: 1px solid #eee;
            padding-top: .75rem;
            margin-top: 3rem;
        }

    </style>

    <div class="enquiry-wrapper">

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end align-items-center gap-2 mb-3 no-print">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ENQUIRY.printPreview('{{ $enquiry->id }}')">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ENQUIRY.downloadPDF('{{ $enquiry->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </button>
        </div>

        <!-- Company Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
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

        <!-- Enquiry Header -->
        <div class="enquiry-header">
            <div class="left">
                <div class="title">ENQUIRY</div>
                <div>#{{ $enquiry->row_no }}</div>
            </div>
            <div class="right">
                <div><strong>Date:</strong> {{ showDate($enquiry->created_at) }}</div>
                <div>
                    <strong>Status:</strong>
                    <span class="badge-status bg-warning text-dark">
                    {{ \App\Enums\EnquiryEnum::from($enquiry->status)->label() }}
                </span>
                </div>
            </div>
        </div>

        <!-- Customer Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">Customer Information</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6"><strong>Name:</strong> {{ $enquiry->customer->name }}</div>
                    <div class="col-md-6"><strong>Email:</strong> {{ $enquiry->customer->email ?? '-' }}</div>
                    <div class="col-md-6"><strong>Phone:</strong> {{ $enquiry->customer->phone ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Shipment Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">Shipment Details</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4"><strong>Type:</strong> {{ ucfirst($enquiry->shipment_type) }}</div>
                    <div class="col-md-4"><strong>Category:</strong> {{ ucfirst($enquiry->shipment_category) }}</div>
                    <div class="col-md-4"><strong>Weight:</strong> {{ $enquiry->weight }} kg</div>
                    <div class="col-md-4"><strong>Volume:</strong> {{ $enquiry->volume }} m³</div>
                    <div class="col-md-4"><strong>Pickup:</strong> {{ showDate($enquiry->pickup_date) }}</div>
                </div>
            </div>
        </div>

        <!-- Origin & Destination Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">POL & POD</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6"><strong>Port of Loading (POL):</strong> {{ $enquiry->pol }}</div>
                    <div class="col-md-6"><strong>Port of Discharge (POD):</strong> {{ $enquiry->pod }}</div>
                </div>
            </div>
        </div>

        <!-- Items Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">Containers / Packages</div>
            <div class="card-body p-0">
                @if($enquiry->shipment_category == 'container')
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                        <tr>
                            <th>Size</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Hazardous</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $containerSize = containerSize();
                            $containerTypes = containerTypesData();
                        @endphp
                        @foreach($enquiry->enquirySubs as $item)
                            <tr>
                                <td>{{ $containerSize[$item->container_size] ?? '' }}</td>
                                <td>{{ $containerTypes[$item->container_type] ?? '' }}</td>
                                <td>{{ $item->container_quantity }}</td>
                                <td>{{ $item->container_hazardous == 1 ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                        <tr>
                            <th>Package Type</th>
                            <th>Length</th>
                            <th>Width</th>
                            <th>Height</th>
                            <th>Weight</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $packageTypes = packageType();
                        @endphp
                        @foreach($enquiry->enquirySubs as $item)
                            <tr>
                                <td>{{ $packageTypes[$item->package_type] ?? '' }}</td>
                                <td>{{ $item->length }}</td>
                                <td>{{ $item->width }}</td>
                                <td>{{ $item->height }}</td>
                                <td>{{ $item->weight }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <!-- Notes -->
        @if($enquiry->remark)
            <div class="card shadow-sm mb-3">
                <div class="card-header">Notes</div>
                <div class="card-body">{{ $enquiry->remark }}</div>
            </div>
        @endif

        <!-- Footer -->
        <footer class="text-center text-muted">
            <div>Email: info@company.com | Phone: +91-9876543210</div>
        </footer>

    </div>

@endsection
