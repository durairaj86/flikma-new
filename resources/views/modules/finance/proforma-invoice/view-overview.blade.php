@extends('includes.print-header')
@section('print-content')

    <div class="invoice-wrapper">
        {{-- DRAFT Watermark --}}
        @if($proforma->status == 1)
            <div class="draft-watermark">{{ __('DRAFT') }}</div>
        @endif
        <!-- Action Buttons -->
        <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="PROFORMA_INVOICE.printPreview('{{ $proforma->id }}')">
                <i class="bi bi-printer me-1"></i> {{ __('Print') }}
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="PROFORMA_INVOICE.downloadPDF('{{ $proforma->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('Download PDF') }}
            </button>
            <button type="button" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> {{ __('Cancel') }}
            </button>
        </div>

        <!-- Company Header: Logo Left, Company Info Right -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="company-logo">
                <img src="{{ companyLogo() }}" alt="Company Logo" style="max-height: 60px;">
            </div>
            <div class="company-info text-end">
                <h5>{{ companyName() }}</h5>
                <small>
                    {{ companyAddress() }}<br>
                    {{ companyEmail() }} | {{ companyPhone() }}
                </small>
            </div>
        </div>
        <div class="invoice-title">{{ __('PROFORMA INVOICE') }}</div>

        <!-- Customer Info -->
        <div class="row mb-4">
            <div class="col-6">
                <h6><strong>{{ __('To,') }}</strong></h6>
                <div><strong>{{ $proforma->customer->name }}</strong></div>
                <div>{{ $proforma->customer->address ?? '-' }}</div>
                @if($proforma->customer->email)
                    <div>{{ __('Email') }}: {{ $proforma->customer->email }}</div>
                @endif
                @if($proforma->customer->phone)
                    <div>{{ __('Phone') }}: {{ $proforma->customer->phone }}</div>
                @endif
            </div>
            <div class="col-6">
                <div class="d-flex flex-column align-items-end gap-2">

                    <!-- Each row: label and value -->
                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">{{ __('Proforma No') }}:</div>
                        <div class="text-end" style="min-width: 120px;">#{{ $proforma->row_no }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">{{ __('Date') }}:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $proforma->posted_at }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">{{ __('Currency') }}:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $proforma->currency }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">{{ __('Exchange Rate') }}:</div>
                        <div class="text-end" style="min-width: 120px;">{{ number_format($proforma->currency_rate, decimals()) }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">{{ __('Job') }}:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $proforma->job_no }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width: 120px;">{{ __('Status') }}:</div>
                        <div class="text-end" style="min-width: 120px;">
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
            </div>



            <div class="col-6 text-end d-none">
                <div><strong>{{ __('Currency') }}:</strong> {{ $proforma->currency }}</div>
                <div><strong>{{ __('Exchange Rate') }}:</strong> {{ number_format($proforma->currency_rate, decimals()) }}</div>
                <div><strong>{{ __('Job') }}:</strong> {{ $proforma->job_no }}</div>
                <div><strong>{{ __('Status') }}:</strong>
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
            <div>
                <table class="table table-invoice align-middle">
                    <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>{{ __('Description') }}</th>
                        <th class="text-end">{{ __('Qty') }}</th>
                        <th class="text-end">{{ __('Unit') }}</th>
                        <th class="text-end">{{ __('Unit Price') }}</th>
                        <th class="text-end">{{ __('Tax') }}</th>
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
        <div class="total-section d-flex justify-content-between mt-4">
            <div>
                @php
                    $qrData = "Proforma Invoice: {$proforma->row_no}\n"
                        ."Customer: {$proforma->customer->name}\n"
                        ."Date: {$proforma->posted_at}\n"
                        ."Total: ".amountFormat($proforma->grand_total)." {$proforma->currency}";
                @endphp
                {!! QrCode::size(100)->generate($qrData) !!}
            </div>
            <table class="total-table">
                <tr>
                    <td><strong>{{ __('Subtotal') }}</strong></td>
                    <td class="text-end">{{ amountFormat($proforma->sub_total) }}</td>
                </tr>
                <tr>
                    <td><strong>{{ __('Tax') }}</strong></td>
                    <td class="text-end">{{ amountFormat($proforma->tax_total) }}</td>
                </tr>
                @if($proforma->discount_total > 0)
                    <tr>
                        <td><strong>{{ __('Discount') }}</strong></td>
                        <td class="text-end">-{{ amountFormat($proforma->discount_total) }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>{{ __('Grand Total') }}</strong>
                        @if(strtoupper($proforma->currency) !== 'SAR')
                            <div class="currency-note">{{ amountFormat($proforma->currency_rate) }} SAR</div>
                        @endif
                    </td>
                    <td class="text-end">
                        {{ amountFormat($proforma->grand_total) }} {{ $proforma->currency }}
                        @if(strtoupper($proforma->currency) !== 'SAR')
                            @php $converted = $proforma->grand_total * $proforma->currency_rate; @endphp
                            <div class="currency-note">{{ amountFormat($converted) }} SAR</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Amount in Words -->
        <div class="mt-2">
            <strong>{{ __('Amount in Words') }}:</strong>
            <span>{{ amountInWords(round($proforma->grand_total, 2)) }} {{ $proforma->currency }}</span>
        </div>

        <!-- Terms -->
        @if($proforma->terms)
            <div class="terms-box mt-4">
                <h6 class="fw-semibold mb-2">{{ __('Terms & Conditions') }}</h6>
                <p class="mb-0">{{ $proforma->terms }}</p>
            </div>
        @endif

        <!-- Footer -->
        <footer class="mt-5 pt-3 border-top text-center text-muted small">
            <div class="d-flex justify-content-between">
                <div>{{ __('Email') }}: {{ companyEmail() }}</div>
                <div>{{ __('Phone') }}: {{ companyPhone() }}</div>
            </div>
        </footer>

    </div>

@endsection

