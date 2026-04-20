<style>
    /* ======= PRINT-FRIENDLY PROFORMA INVOICE ======= */
    .invoice-wrapper {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        font-size: 14px;
        color: #000;
    }

    .invoice-header {
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .invoice-header h4 {
        margin: 0;
        font-weight: 700;
    }

    .invoice-meta {
        text-align: right;
        line-height: 1.6;
    }

    .table-invoice th, .table-invoice td {
        padding: 6px 8px;
        vertical-align: middle;
    }

    .table-invoice thead {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .total-section {
        margin-top: 20px;
        clear: both;
    }

    .total-section table {
        width: 320px;
        float: right;
    }

    .total-section td {
        padding: 4px 8px;
    }

    .total-section tr:last-child td {
        border-top: 2px solid #000;
        font-weight: 600;
        font-size: 1rem;
    }

    .currency-note {
        font-size: 13px;
        margin-top: 5px;
        color: #444;
    }

    .terms-box {
        margin-top: 50px;
        border-top: 1px solid #ccc;
        padding-top: 15px;
    }

    .company-info h5 {
        margin-bottom: 4px;
        font-weight: 700;
    }

    .company-info small {
        line-height: 1.4;
        color: #555;
    }

    .invoice-title {
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        text-align: center;
        font-weight: 700;
        font-size: 18px;
        margin: 20px 0;
        padding: 6px 0;
        letter-spacing: 1px;
    }

    @media print {
        body {
            background: none !important;
            -webkit-print-color-adjust: exact;
        }
        .invoice-wrapper {
            border: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .btn {
            display: none !important;
        }
    }
</style>

<div class="invoice-wrapper">

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-print-preview" onclick="PROFORMA_INVOICE.printPreview('{{ $proforma->id }}')">
            <i class="bi bi-printer me-1"></i> Print
        </button>
        <button type="button" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i> Cancel
        </button>
        {{--<button type="button" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export (Excel)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
        </button>--}}
    </div>

    <!-- Company Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="company-info">
            <img src="{{ asset('images/company_logo.png') }}" alt="Company Logo" style="max-height: 60px; margin-bottom: 6px;">
            <h5>{{ 'Your Company Name' }}</h5>
            <small>
                {{ '123 Business Street, City, Country' }}<br>
                {{ 'info@company.com' }} | {{ '+91-9876543210' }}
            </small>
        </div>
        <div class="invoice-meta text-end">
            <div><strong>Proforma No:</strong> {{ $proforma->row_no }}</div>
            <div><strong>Date:</strong> {{ $proforma->posted_at }}</div>
            <div><strong>Currency:</strong> {{ $proforma->currency }}</div>
            <div><strong>Exchange Rate:</strong> {{ number_format($proforma->currency_rate, decimals()) }}</div>
        </div>
    </div>

    <!-- Title -->
    <div class="invoice-title">PROFORMA INVOICE</div>

    <!-- Customer Info -->
    <div class="row mb-4">
        <div class="col-6">
            <h6><strong>To,</strong></h6>
            <div><strong>{{ $proforma->customer->name }}</strong></div>
            <div>{{ $proforma->customer->address ?? '-' }}</div>
            @if($proforma->customer->email)
                <div>Email: {{ $proforma->customer->email }}</div>
            @endif
            @if($proforma->customer->phone)
                <div>Phone: {{ $proforma->customer->phone }}</div>
            @endif
        </div>
        <div class="col-6 text-end">
            <div><strong>Job:</strong> {{ $proforma->job_no }}</div>
            <div><strong>Status:</strong>
                <span class="badge
                    @if($proforma->status == 1) bg-warning text-dark
                    @elseif($proforma->status == 2) bg-success
                    @elseif($proforma->status == 3) bg-info text-dark
                    @elseif($proforma->status == 4) bg-danger
                    @elseif($proforma->status == 5) bg-primary @endif">
                    {{ \App\Enums\ProformaInvoiceEnum::from($proforma->status)->label() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Item Details -->
    @if($proforma->proformaInvoiceSubs && $proforma->proformaInvoiceSubs->count())
        <div class="table-responsive">
            <table class="table table-invoice table-bordered align-middle">
                <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Tax</th>
                </tr>
                </thead>
                <tbody>
                @foreach($proforma->proformaInvoiceSubs as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $descriptions[$item->description_id] }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ $item->unit }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, decimals()) }}</td>
                        <td class="text-end">{{ $item->tax_percent }}%</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Totals -->
    <div class="total-section clearfix">
        <table class="table table-borderless mb-0">
            <tbody>
            <tr>
                <td class="text-start"><strong>Subtotal</strong></td>
                <td class="text-end">{{ amountFormat($proforma->sub_total) }}</td>
            </tr>
            <tr>
                <td class="text-start"><strong>Tax</strong></td>
                <td class="text-end">{{ amountFormat($proforma->tax_total) }}</td>
            </tr>
            @if($proforma->discount_total > 0)
                <tr>
                    <td class="text-start"><strong>Discount</strong></td>
                    <td class="text-start">-{{ amountFormat($proforma->discount_total) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-start"><strong>Grand Total</strong>
                    @if(strtoupper($proforma->currency) !== 'SAR')
                        <div class="currency-note">{{ amountFormat($proforma->currency_rate) }} SAR</div>
                    @endif
                </td>
                <td class="text-end">{{ amountFormat($proforma->grand_total) }} {{ $proforma->currency }}
                    @if(strtoupper($proforma->currency) !== 'SAR')
                        @php $converted = $proforma->grand_total * $proforma->currency_rate; @endphp
                        <div class="currency-note">{{ amountFormat($converted) }} SAR</div>
                    @endif
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- Terms -->
    @if($proforma->terms)
        <div class="terms-box">
            <h6 class="fw-semibold mb-2">Terms & Conditions</h6>
            <p class="mb-0">{{ $proforma->terms }}</p>
        </div>
    @endif
</div>
