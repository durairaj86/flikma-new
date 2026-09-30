@extends('includes.print-header')
@section('print-content')
    <div class="invoice-wrapper bg-white position-relative" style="max-width:850px;">

        <!-- Action Buttons (screen only) -->
        <div class="d-print-none d-flex justify-content-end align-items-center gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> {{ __('Print') }}
            </button>
        </div>

        <!-- Company Header -->
        <div class="d-flex justify-content-between align-items-start mb-3 border-bottom border-dark pb-3">
            <div class="d-flex align-items-center">
                <img src="{{ companyLogo() }}" alt="{{ __('Company Logo') }}" style="height:60px;">
                <div class="ms-3">
                    <h5 class="mb-1 fw-bold text-dark">{{ companyName() }}</h5>
                    <small class="text-muted d-block">{{ companyAddress() }}</small>
                    @if($company->cr_number || $company->vat_number)
                        <small class="text-muted d-block">
                            @if($company->cr_number) {{ __('CR') }}: {{ $company->cr_number }} @endif
                            @if($company->vat_number) &nbsp; {{ __('VAT') }}: {{ $company->vat_number }} @endif
                        </small>
                    @endif
                </div>
            </div>
            <div class="text-end">
                {!! $qrImage !!}
            </div>
        </div>

        <!-- Title -->
        <div class="text-center border-bottom border-dark py-2 mb-3">
            <h3 class="fw-bold mb-0 text-uppercase">{{ __('Waybill') }}</h3>
            <div class="fs-5" dir="rtl">بيان الشحن</div>
        </div>

        <!-- Metadata -->
        <div class="row mb-3">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:140px;">{{ __('Waybill No') }}.</td>
                        <td class="fw-bold">{{ $waybill->row_no }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ __('Waybill Date') }}</td>
                        <td>{{ $waybill->waybill_date }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ __('Job No') }}.</td>
                        <td>{{ $waybill->job->row_no ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:140px;">{{ __('Delivery Date') }}</td>
                        <td>{{ $waybill->delivery_date }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ __('Status') }}</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $waybill->status ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ __('Route') }}</td>
                        <td>{{ $waybill->job->pol ?? '-' }} &rarr; {{ $waybill->job->pod ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Shipper / Consignee -->
        <div class="row mb-3">
            <div class="col-6 border-end">
                <h6 class="fw-bold text-uppercase small text-muted mb-2">{{ __('Shipper (Consignor)') }}</h6>
                <div class="fw-bold">{{ companyName() }}</div>
                <div>{{ companyAddress() }}</div>
                @if(companyPhone())
                    <div>{{ __('Phone') }}: {{ companyPhone() }}</div>
                @endif
            </div>
            <div class="col-6">
                <h6 class="fw-bold text-uppercase small text-muted mb-2">{{ __('Consignee') }}</h6>
                <div class="fw-bold">{{ $waybill->customer->name_en ?? '-' }}</div>
                <div>{{ $waybill->delivery_address ?? '-' }}</div>
                @if($waybill->contact_person)
                    <div>{{ __('Attn') }}: {{ $waybill->contact_person }}</div>
                @endif
                @if($waybill->contact_phone)
                    <div>{{ __('Phone') }}: {{ $waybill->contact_phone }}</div>
                @endif
            </div>
        </div>

        <!-- Shipment details -->
        <div class="row mb-3 text-center">
            <div class="col-4 border-end">
                <div class="text-muted small text-uppercase">{{ __('Shipment Type') }}</div>
                <div class="fw-bold text-capitalize">{{ $waybill->shipment_type ?? '-' }}</div>
            </div>
            <div class="col-4 border-end">
                <div class="text-muted small text-uppercase">{{ __('Service Type') }}</div>
                <div class="fw-bold text-capitalize">{{ str_replace('_', ' ', $waybill->service_type ?? '-') }}</div>
            </div>
            <div class="col-4">
                <div class="text-muted small text-uppercase">{{ __('Payment Method') }}</div>
                <div class="fw-bold text-capitalize">{{ str_replace('_', ' ', $waybill->payment_method ?? '-') }}</div>
            </div>
        </div>

        <!-- Goods table -->
        <table class="table-invoice table-bordered w-100 mb-3">
            <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Comment') }}</th>
                <th class="text-end">{{ __('Qty') }}</th>
                <th class="text-end">{{ __('Weight (kg)') }}</th>
                <th class="text-end">{{ __('Dimensions (L&times;W&times;H cm)') }}</th>
                <th class="text-center">{{ __('Fragile') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($waybill->waybillSubs as $i => $sub)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $descriptions[$sub->description_id] ?? '-' }}</td>
                    <td>{{ $sub->comment ?? '-' }}</td>
                    <td class="text-end">{{ $sub->quantity }}</td>
                    <td class="text-end">{{ number_format($sub->weight ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($sub->length ?? 0, 1) }} &times; {{ number_format($sub->width ?? 0, 1) }} &times; {{ number_format($sub->height ?? 0, 1) }}</td>
                    <td class="text-center">{{ $sub->fragile ? __('Yes') : __('No') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">{{ __('No items listed.') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <!-- Special instructions -->
        @if($waybill->special_instructions)
            <div class="mb-4">
                <h6 class="fw-bold small text-uppercase text-muted">{{ __('Special Instructions') }}</h6>
                <p class="mb-0">{{ $waybill->special_instructions }}</p>
            </div>
        @endif

        <!-- Declaration -->
        <div class="mb-4 small text-muted" style="border-top:1px solid #ddd; padding-top:10px;">
            <p class="mb-0">
                {{ __("I/We hereby acknowledge receipt of the above-mentioned goods in good order and condition, unless otherwise noted, and agree to the shipper's standard terms and conditions of carriage.") }}
            </p>
        </div>

        <!-- Signatures -->
        <div class="row mt-5 pt-4 text-center">
            <div class="col-4">
                <div style="border-top:1px solid #000; padding-top:6px;">{{ __('Shipper Signature') }}</div>
            </div>
            <div class="col-4">
                <div style="border-top:1px solid #000; padding-top:6px;">{{ __('Driver Signature') }}</div>
            </div>
            <div class="col-4">
                <div style="border-top:1px solid #000; padding-top:6px;">{{ __('Receiver Signature & Date') }}</div>
            </div>
        </div>

    </div>
@endsection
