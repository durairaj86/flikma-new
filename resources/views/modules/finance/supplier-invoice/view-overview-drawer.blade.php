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
    <h6>{{ __('Supplier & Invoice Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Supplier') }}:</strong><span>{{ $supplierInvoice->supplier->name ?? '-' }}</span></div>
        <div><strong>{{ __('Invoice No') }}:</strong><span>#{{ $supplierInvoice->row_no }}</span></div>
        <div><strong>{{ __('Email') }}:</strong><span>{{ $supplierInvoice->supplier->email ?? '-' }}</span></div>
        <div><strong>{{ __('Invoice Date') }}:</strong><span>{{ $supplierInvoice->invoice_date }}</span></div>
        <div><strong>{{ __('Phone') }}:</strong><span>{{ $supplierInvoice->supplier->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Due Date') }}:</strong><span>{{ $supplierInvoice->due_at }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>{{ $supplierInvoice->job_no }}</span></div>
        <div><strong>{{ __('Currency') }}:</strong><span>{{ $supplierInvoice->currency }} ({{ __('rate') }} {{ number_format($supplierInvoice->currency_rate, decimals()) }})</span></div>
        <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\SupplierInvoiceEnum::tryFrom($supplierInvoice->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Line Items') }}</h6>
    @if($supplierInvoice->supplierInvoiceSubs && $supplierInvoice->supplierInvoiceSubs->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Comment') }}</th>
                    <th class="text-end">{{ __('Qty') }}</th>
                    <th>{{ __('Unit') }}</th>
                    <th class="text-end">{{ __('Unit Price') }}</th>
                    <th class="text-end">{{ __('Line Total') }}</th>
                    <th>{{ __('Tax Code') }}</th>
                    <th class="text-end">{{ __('Tax %') }}</th>
                    <th class="text-end">{{ __('Tax Amount') }}</th>
                    <th class="text-end">{{ __('Total (Incl. Tax)') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($supplierInvoice->supplierInvoiceSubs as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $descriptions[$item->description_id] ?? $item->description }}</td>
                        <td>{{ $item->comment ?? '-' }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td>{{ $item->unit ?? '-' }}</td>
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
        <div class="text-center py-4 text-muted">{{ __('No line items on this invoice.') }}</div>
    @endif
</div>

<div class="section">
    <h6>{{ __('Totals') }}</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr>
            <td><strong>{{ __('Subtotal') }}</strong></td>
            <td class="text-end">{{ amountFormat($supplierInvoice->sub_total) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Tax') }}</strong></td>
            <td class="text-end">{{ amountFormat($supplierInvoice->tax_total) }}</td>
        </tr>
        <tr>
            <td>
                <strong>{{ __('Grand Total') }}</strong>
                @if(strtoupper($supplierInvoice->currency) !== 'SAR')
                    <div style="font-size:12px;color:#666;margin-top:2px;">{{ amountFormat($supplierInvoice->currency_rate) }} SAR</div>
                @endif
            </td>
            <td class="text-end">
                {{ amountFormat($supplierInvoice->grand_total) }} {{ $supplierInvoice->currency }}
                @if(strtoupper($supplierInvoice->currency) !== 'SAR')
                    @php $converted = $supplierInvoice->grand_total * $supplierInvoice->currency_rate; @endphp
                    <div style="font-size:12px;color:#666;margin-top:2px;">{{ amountFormat($converted) }} SAR</div>
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>{{ __('Paid Amount') }}</strong></td>
            <td class="text-end">{{ amountFormat($supplierInvoice->paid_amount ?? 0) }} {{ $supplierInvoice->currency }}</td>
        </tr>
        <tr class="table-secondary">
            <td><strong>{{ __('Balance') }}</strong></td>
            <td class="text-end fw-bold">
                {{ amountFormat(($supplierInvoice->grand_total ?? 0) - ($supplierInvoice->paid_amount ?? 0)) }} {{ $supplierInvoice->currency }}
            </td>
        </tr>
    </table>
</div>

@if($supplierInvoice->terms)
    <div class="section">
        <h6>{{ __('Terms & Conditions') }}</h6>
        <p class="mb-0">{{ $supplierInvoice->terms }}</p>
    </div>
@endif
