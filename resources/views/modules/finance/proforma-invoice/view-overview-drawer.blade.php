<style>
    .section {
        margin-bottom: 1.5rem;
    }
    .section h6 {
        font-size: 14px;
        font-weight: 600;
        background: #f7f7f9;
        padding: 8px 10px;
        border-radius: 4px;
        /*border-left: 4px solid #0d6efd;*/
        margin-bottom: 1rem;
    }
    table.table {
        font-size: 13px;
    }
    table.table th {
        background: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem 1.5rem;
        font-size: 13.5px;
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
    .total-table td {
        padding: 4px 10px;
        font-size: 13.5px;
    }
</style>

<div class="section">
    <h6>{{ __('Customer & Proforma Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Customer') }}:</strong><span>{{ $proforma->customer->name ?? '-' }}</span></div>
        <div><strong>{{ __('Proforma No') }}:</strong><span>#{{ $proforma->row_no }}</span></div>
        <div><strong>{{ __('Email') }}:</strong><span>{{ $proforma->customer->email ?? '-' }}</span></div>
        <div><strong>{{ __('Posted Date') }}:</strong><span>{{ $proforma->posted_at }}</span></div>
        <div><strong>{{ __('Phone') }}:</strong><span>{{ $proforma->customer->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Expiry Date') }}:</strong><span>{{ $proforma->expiry_at }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>{{ $proforma->job_no }}</span></div>
        <div><strong>{{ __('Reference No') }}:</strong><span>{{ $proforma->reference_no ?? '-' }}</span></div>
        <div><strong>{{ __('Currency') }}:</strong><span>{{ $proforma->currency }} ({{ __('rate') }} {{ number_format($proforma->currency_rate, decimals()) }})</span></div>
        <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\ProformaInvoiceEnum::tryFrom($proforma->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Line Items') }}</h6>
    @if($proforma->proformaInvoiceSubs && $proforma->proformaInvoiceSubs->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Comment') }}</th>
                    <th class="text-end">{{ __('Qty') }}</th>
                    <th class="text-end">{{ __('Unit Price') }}</th>
                    <th class="text-end">{{ __('Line Total') }}</th>
                    <th>{{ __('Tax Code') }}</th>
                    <th class="text-end">{{ __('Tax %') }}</th>
                    <th class="text-end">{{ __('Tax Amount') }}</th>
                    <th class="text-end">{{ __('Total (Incl. Tax)') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($proforma->proformaInvoiceSubs as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $descriptions[$item->description_id] ?? $item->description }}</td>
                        <td>{{ $item->comment ?? '-' }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, decimals()) }}</td>
                        <td class="text-end">{{ number_format($item->line_total, decimals()) }}</td>
                        <td>{{ $item->tax_code ?? '-' }}</td>
                        <td class="text-end">{{ $item->tax_percent }}%</td>
                        <td class="text-end">{{ number_format($item->tax_amount, decimals()) }}</td>
                        <td class="text-end">{{ number_format($item->total_with_tax ?? $item->total, decimals()) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted">{{ __('No line items on this proforma invoice.') }}</div>
    @endif
</div>

<div class="section">
    <h6>{{ __('Totals') }}</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr>
            <td><strong>{{ __('Subtotal') }}</strong></td>
            <td class="text-end">{{ amountFormat($proforma->sub_total) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Tax') }}</strong></td>
            <td class="text-end">{{ amountFormat($proforma->tax_total) }}</td>
        </tr>
        @if($proforma->discount > 0)
            <tr>
                <td><strong>{{ __('Discount') }}</strong></td>
                <td class="text-end">-{{ amountFormat($proforma->discount) }}</td>
            </tr>
        @endif
        @if($proforma->shipping_charge > 0)
            <tr>
                <td><strong>{{ __('Shipping Charge') }}</strong></td>
                <td class="text-end">{{ amountFormat($proforma->shipping_charge) }}</td>
            </tr>
        @endif
        <tr class="table-secondary">
            <td><strong>{{ __('Grand Total') }}</strong></td>
            <td class="text-end fw-bold">{{ amountFormat($proforma->grand_total) }} {{ $proforma->currency }}</td>
        </tr>
    </table>
</div>

@if($proforma->notes)
    <div class="section">
        <h6>{{ __('Notes') }}</h6>
        <p class="mb-0">{{ $proforma->notes }}</p>
    </div>
@endif

@if($proforma->terms)
    <div class="section">
        <h6>{{ __('Terms & Conditions') }}</h6>
        <p class="mb-0">{{ $proforma->terms }}</p>
    </div>
@endif
