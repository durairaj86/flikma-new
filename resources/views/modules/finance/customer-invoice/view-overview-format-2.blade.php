@extends('includes.print-header')
@section('print-content')
    <div class="invoice-wrapper bg-white position-relative">

        {{-- DRAFT Watermark --}}
        @if($customerInvoice->status == 1)
            <div class="draft-watermark">DRAFT</div>
        @endif

        <!-- Action Buttons (screen only) -->
        <div class="d-print-none d-flex justify-content-end align-items-center gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="CUSTOMER_INVOICE.printPreview('{{ $customerInvoice->id }}')">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="CUSTOMER_INVOICE.downloadPDF('{{ $customerInvoice->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </button>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3">
            <div class="d-flex align-items-center">
                <img src="{{ asset('img/logo.png') }}" alt="Company Logo" style="height:70px;">
                <div class="ms-3">
                    <h5 class="mb-1 fw-bold text-dark">Flikma Networks Ltd.</h5>
                    <small class="text-muted">
                        123 Business Street, DAMMAM<br>
                        Saudi Arabia - 600001
                    </small>
                </div>
            </div>

            <div class="text-end">
                <h3 class="text-uppercase fw-bold text-primary mb-1">Invoice</h3>
                <h5 class="fw-bold">#{{ $customerInvoice->row_no }}</h5>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="row mb-4">
            <div class="col-6">
                <h6><strong>To,</strong></h6>
                <div><strong>{{ $customerInvoice->customer->name }}</strong></div>
                <div>{{ $customerInvoice->customer->address ?? '-' }}</div>
                @if($customerInvoice->customer->email)
                    <div>Email: {{ $customerInvoice->customer->email }}</div>
                @endif
                @if($customerInvoice->customer->phone)
                    <div>Phone: {{ $customerInvoice->customer->phone }}</div>
                @endif
            </div>
            <div class="col-6">
                <div class="d-flex flex-column align-items-end gap-2">

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Invoice No:</div>
                        <div class="text-end" style="min-width: 120px;">#{{ $customerInvoice->row_no }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Invoice Date:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $customerInvoice->invoice_date }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Due Date:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $customerInvoice->due_at }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Currency:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $customerInvoice->currency }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Exchange Rate:</div>
                        <div class="text-end" style="min-width: 120px;">{{ number_format($customerInvoice->currency_rate, decimals()) }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Job:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $customerInvoice->job_no }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">Status:</div>
                        <div class="text-end" style="min-width: 120px;">
                            <span class="badge bg-warning text-dark">
                                {{ \App\Enums\CustomerInvoiceEnum::from($customerInvoice->status)->label() }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Item Details -->
        @if($customerInvoice->customerInvoiceSubs && $customerInvoice->customerInvoiceSubs->count())
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
                    @foreach($customerInvoice->customerInvoiceSubs as $item)
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
                    $qrData = "Customer Invoice: {$customerInvoice->row_no}\n"
                        ."Customer: {$customerInvoice->customer->name}\n"
                        ."Date: {$customerInvoice->invoice_date}\n"
                        ."Total: ".amountFormat($customerInvoice->grand_total)." {$customerInvoice->currency}";
                @endphp
                {!! QrCode::size(100)->generate($qrData) !!}
            </div>
            <table class="total-table">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="text-end">{{ amountFormat($customerInvoice->sub_total) }}</td>
                </tr>
                <tr>
                    <td><strong>Tax</strong></td>
                    <td class="text-end">{{ amountFormat($customerInvoice->tax_total) }}</td>
                </tr>
                @if($customerInvoice->discount_total > 0)
                    <tr>
                        <td><strong>Discount</strong></td>
                        <td class="text-end">-{{ amountFormat($customerInvoice->discount_total) }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>Grand Total</strong>
                        @if(strtoupper($customerInvoice->currency) !== 'SAR')
                            <div class="currency-note">{{ amountFormat($customerInvoice->currency_rate) }} SAR</div>
                        @endif
                    </td>
                    <td class="text-end">
                        {{ amountFormat($customerInvoice->grand_total) }} {{ $customerInvoice->currency }}
                        @if(strtoupper($customerInvoice->currency) !== 'SAR')
                            @php $converted = $customerInvoice->grand_total * $customerInvoice->currency_rate; @endphp
                            <div class="currency-note">{{ amountFormat($converted) }} SAR</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Amount in Words -->
        <div class="mt-2">
            <strong>Amount in Words:</strong>
            <span>{{ amountInWords(round($customerInvoice->grand_total, 2)) }} {{ $customerInvoice->currency }}</span>
        </div>

        <!-- Terms -->
        @if($customerInvoice->terms)
            <div class="terms-box mt-4">
                <h6 class="fw-semibold mb-2">Terms & Conditions</h6>
                <p class="mb-0">{{ $customerInvoice->terms }}</p>
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
