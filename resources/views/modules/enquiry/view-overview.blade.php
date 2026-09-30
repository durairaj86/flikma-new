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
                <i class="bi bi-printer me-1"></i> {{ __('Print') }}
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="ENQUIRY.downloadPDF('{{ $enquiry->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('Download PDF') }}
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i> {{ __('Cancel') }}
            </button>
        </div>

        <!-- Company Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
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

        <!-- Enquiry Header -->
        <div class="enquiry-header">
            <div class="left">
                <div class="title">{{ __('Enquiry') }}</div>
                <div>#{{ $enquiry->row_no }}</div>
            </div>
            <div class="right">
                <div><strong>{{ __('Date:') }}</strong> {{ showDate($enquiry->created_at) }}</div>
                <div>
                    <strong>{{ __('Status:') }}</strong>
                    <span class="badge-status bg-warning text-dark">
                    {{ \App\Enums\EnquiryEnum::from($enquiry->status)->label() }}
                </span>
                </div>
            </div>
        </div>

        <!-- Customer Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">{{ __('Customer Information') }}</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6"><strong>{{ __('Name:') }}</strong> {{ $enquiry->customer->name }}</div>
                    <div class="col-md-6"><strong>{{ __('Email:') }}</strong> {{ $enquiry->customer->email ?? '-' }}</div>
                    <div class="col-md-6"><strong>{{ __('Phone:') }}</strong> {{ $enquiry->customer->phone ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Shipment Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">{{ __('Shipment Details') }}</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4"><strong>{{ __('Type:') }}</strong> {{ ucfirst($enquiry->shipment_type) }}</div>
                    <div class="col-md-4"><strong>{{ __('Category:') }}</strong> {{ ucfirst($enquiry->shipment_category) }}</div>
                    <div class="col-md-4"><strong>{{ __('Weight:') }}</strong> {{ $enquiry->weight }} {{ __('kg') }}</div>
                    <div class="col-md-4"><strong>{{ __('Volume:') }}</strong> {{ $enquiry->volume }} m³</div>
                    <div class="col-md-4"><strong>{{ __('Pickup:') }}</strong> {{ showDate($enquiry->pickup_date) }}</div>
                </div>
            </div>
        </div>

        <!-- Origin & Destination Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">{{ __('POL & POD') }}</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6"><strong>{{ __('Port of Loading (POL):') }}</strong> {{ $enquiry->pol }}</div>
                    <div class="col-md-6"><strong>{{ __('Port of Discharge (POD):') }}</strong> {{ $enquiry->pod }}</div>
                </div>
            </div>
        </div>

        <!-- Items Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header">{{ __('Containers / Packages') }}</div>
            <div class="card-body p-0">
                @if($enquiry->shipment_category == 'container')
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                        <tr>
                            <th>{{ __('Size') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Quantity') }}</th>
                            <th>{{ __('Hazardous') }}</th>
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
                                <td>{{ $item->container_hazardous == 1 ? __('Yes') : __('No') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                        <tr>
                            <th>{{ __('Package Type') }}</th>
                            <th>{{ __('Length') }}</th>
                            <th>{{ __('Width') }}</th>
                            <th>{{ __('Height') }}</th>
                            <th>{{ __('Weight') }}</th>
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
                <div class="card-header">{{ __('Notes') }}</div>
                <div class="card-body">{{ $enquiry->remark }}</div>
            </div>
        @endif

        <!-- Footer -->
        <footer class="text-center text-muted">
            <div>{{ __('Email:') }} {{ companyEmail() }} | {{ __('Phone:') }} {{ companyPhone() }}</div>
        </footer>

    </div>

@endsection
