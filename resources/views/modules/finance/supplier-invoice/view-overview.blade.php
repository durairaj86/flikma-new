@extends('includes.print-header')
@section('print-content')

    <div class="invoice-wrapper">
        {{-- DRAFT Watermark --}}
        @if($supplierInvoice->status == 1)
            <div class="draft-watermark">DRAFT</div>
        @endif
        <!-- Action Buttons -->
        <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="SUPPLIER_INVOICE.printPreview('{{ $supplierInvoice->id }}')">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="SUPPLIER_INVOICE.downloadPDF('{{ $supplierInvoice->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </button>
        </div>

        <!-- Company Header: Logo Left, Company Info Right -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="company-logo">
                <img src="{{ asset('img/logo.png') }}" alt="Company Logo" style="max-height: 65px;">
            </div>
            <div class="company-info text-end">
                <h5>{{ 'Flikma Networks Ltd.' }}</h5>
                <small>
                    {{ '123 Business Street, City, Country' }}<br>
                    {{ 'info@company.com' }} | {{ '+91-9876543210' }}
                </small>
            </div>
        </div>
        <div class="invoice-title">SUPPLIER INVOICE</div>

        <!-- Supplier Info -->
        <div class="row mb-4">
            <div class="col-6">
                <h6><strong>To,</strong></h6>
                <div><strong>{{ $supplierInvoice->supplier->name }}</strong></div>
                <div>{{ $supplierInvoice->supplier->address ?? '-' }}</div>
                @if($supplierInvoice->supplier->email)
                    <div>Email: {{ $supplierInvoice->supplier->email }}</div>
                @endif
                @if($supplierInvoice->supplier->phone)
                    <div>Phone: {{ $supplierInvoice->supplier->phone }}</div>
                @endif
            </div>
            <div class="col-6">
                <div class="d-flex flex-column align-items-end gap-2">

                    <!-- Each row: label and value -->
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Supplier Invoice No:</div>
                        <div class="text-end" style="min-width: 120px;">#{{ $supplierInvoice->row_no }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Received Invoice No:</div>
                        <div class="text-end" style="min-width: 120px;">#{{ $supplierInvoice->invoice_number }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Invoice Date:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $supplierInvoice->invoice_date }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Due Date:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $supplierInvoice->due_at }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Currency:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $supplierInvoice->currency }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Exchange Rate:</div>
                        <div class="text-end" style="min-width: 120px;">{{ number_format($supplierInvoice->currency_rate, decimals()) }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Job:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $supplierInvoice->job_no }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Status:</div>
                        <div class="text-end" style="min-width: 120px;">
                <span class="badge
                    @if($supplierInvoice->status == 1) bg-warning text-dark
                    @elseif($supplierInvoice->status == 2) bg-success
                    @elseif($supplierInvoice->status == 3) bg-info text-dark
                    @elseif($supplierInvoice->status == 4) bg-danger
                    @elseif($supplierInvoice->status == 5) bg-primary @endif">
                    {{ \App\Enums\ProformaInvoiceEnum::from($supplierInvoice->status)->label() }}
                </span>
                        </div>
                    </div>

                </div>
            </div>



            <div class="col-6 text-end d-none">
                <div><strong>Currency:</strong> {{ $supplierInvoice->currency }}</div>
                <div><strong>Exchange Rate:</strong> {{ number_format($supplierInvoice->currency_rate, decimals()) }}</div>
                <div><strong>Job:</strong> {{ $supplierInvoice->job_no }}</div>
                <div><strong>Status:</strong>
                    <span class="badge
                @if($supplierInvoice->status == 1) bg-warning text-dark
                @elseif($supplierInvoice->status == 2) bg-success
                @elseif($supplierInvoice->status == 3) bg-info text-dark
                @elseif($supplierInvoice->status == 4) bg-danger
                @elseif($supplierInvoice->status == 5) bg-primary @endif">
                {{ \App\Enums\ProformaInvoiceEnum::from($supplierInvoice->status)->label() }}
            </span>
                </div>
            </div>
        </div>

        <!-- Item Details -->
        @if($supplierInvoice->supplierInvoiceSubs && $supplierInvoice->supplierInvoiceSubs->count())
            <div>
                <table class="table table-invoice align-middle">
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
                    @foreach($supplierInvoice->supplierInvoiceSubs as $item)
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
        <div class="total-section d-flex justify-content-between mt-4">
            <div>
                @php
                    $qrData = "Proforma Invoice: {$supplierInvoice->row_no}\n"
                        ."Customer: {$supplierInvoice->supplier->name}\n"
                        ."Date: {$supplierInvoice->invoice_date}\n"
                        ."Total: ".amountFormat($supplierInvoice->grand_total)." {$supplierInvoice->currency}";
                @endphp
                {!! QrCode::size(100)->generate($qrData) !!}
            </div>
            <table class="total-table">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="text-end">{{ amountFormat($supplierInvoice->sub_total) }}</td>
                </tr>
                <tr>
                    <td><strong>Tax</strong></td>
                    <td class="text-end">{{ amountFormat($supplierInvoice->tax_total) }}</td>
                </tr>
                @if($supplierInvoice->discount_total > 0)
                    <tr>
                        <td><strong>Discount</strong></td>
                        <td class="text-end">-{{ amountFormat($supplierInvoice->discount_total) }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>Grand Total</strong>
                        @if(strtoupper($supplierInvoice->currency) !== 'SAR')
                            <div class="currency-note">{{ amountFormat($supplierInvoice->currency_rate) }} SAR</div>
                        @endif
                    </td>
                    <td class="text-end">
                        {{ amountFormat($supplierInvoice->grand_total) }} {{ $supplierInvoice->currency }}
                        @if(strtoupper($supplierInvoice->currency) !== 'SAR')
                            @php $converted = $supplierInvoice->grand_total * $supplierInvoice->currency_rate; @endphp
                            <div class="currency-note">{{ amountFormat($converted) }} SAR</div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Paid Amount</strong></td>
                    <td class="text-end">
                        {{ amountFormat($supplierInvoice->paid_amount ?? 0) }} {{ $supplierInvoice->currency }}
                    </td>
                </tr>
                <tr class="table-secondary">
                    <td><strong>Balance</strong></td>
                    <td class="text-end fw-bold">
                        {{ amountFormat(($supplierInvoice->grand_total ?? 0) - ($supplierInvoice->paid_amount ?? 0)) }} {{ $supplierInvoice->currency }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Amount in Words -->
        <div class="mt-2">
            <strong>Amount in Words:</strong>
            <span>{{ amountInWords(round($supplierInvoice->grand_total, 2)) }} {{ $supplierInvoice->currency }}</span>
        </div>

        <!-- Terms -->
        @if($supplierInvoice->terms)
            <div class="terms-box mt-4">
                <h6 class="fw-semibold mb-2">Terms & Conditions</h6>
                <p class="mb-0">{{ $supplierInvoice->terms }}</p>
            </div>
        @endif

        <!-- Footer -->
        <footer class="mt-5 pt-3 border-top text-center text-muted small">
            <div class="d-flex justify-content-between">
                <div>Email: info@company.com</div>
                <div>Phone: +91-9876543210</div>
            </div>
        </footer>

    </div>

@endsection
