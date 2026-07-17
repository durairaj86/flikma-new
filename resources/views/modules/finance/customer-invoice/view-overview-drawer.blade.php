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
        border-left: 4px solid #0d6efd;
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
    <h6>Customer &amp; Invoice Information</h6>
    <div class="info-grid">
        <div><strong>Customer:</strong><span>{{ $customerInvoice->customer->name ?? '-' }}</span></div>
        <div><strong>Invoice No:</strong><span>#{{ $customerInvoice->row_no }}</span></div>
        <div><strong>Email:</strong><span>{{ $customerInvoice->customer->email ?? '-' }}</span></div>
        <div><strong>Invoice Date:</strong><span>{{ $customerInvoice->invoice_date }}</span></div>
        <div><strong>Phone:</strong><span>{{ $customerInvoice->customer->phone ?? '-' }}</span></div>
        <div><strong>Due Date:</strong><span>{{ $customerInvoice->due_at }}</span></div>
        <div><strong>Job:</strong><span>{{ $customerInvoice->job_no }}</span></div>
        <div><strong>Currency:</strong><span>{{ $customerInvoice->currency }} (rate {{ number_format($customerInvoice->currency_rate, decimals()) }})</span></div>
        <div><strong>Status:</strong><span>{{ \App\Enums\CustomerInvoiceEnum::tryFrom($customerInvoice->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>Line Items</h6>
    @if($customerInvoice->customerInvoiceSubs && $customerInvoice->customerInvoiceSubs->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Comment</th>
                    <th class="text-end">Qty</th>
                    <th>Unit</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Line Total</th>
                    <th>Tax Code</th>
                    <th class="text-end">Tax %</th>
                    <th class="text-end">Tax Amount</th>
                    <th class="text-end">Total (Incl. Tax)</th>
                </tr>
                </thead>
                <tbody>
                @foreach($customerInvoice->customerInvoiceSubs as $item)
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
        <div class="text-center py-4 text-muted">No line items on this invoice.</div>
    @endif
</div>

<div class="section">
    <h6>Totals</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
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
            <td><strong>Grand Total</strong></td>
            <td class="text-end">{{ amountFormat($customerInvoice->grand_total) }} {{ $customerInvoice->currency }}</td>
        </tr>
        <tr>
            <td><strong>Paid Amount</strong></td>
            <td class="text-end">{{ amountFormat($customerInvoice->paid_amount ?? 0) }} {{ $customerInvoice->currency }}</td>
        </tr>
        <tr class="table-secondary">
            <td><strong>Balance</strong></td>
            <td class="text-end fw-bold">
                {{ amountFormat(($customerInvoice->grand_total ?? 0) - ($customerInvoice->paid_amount ?? 0)) }} {{ $customerInvoice->currency }}
            </td>
        </tr>
    </table>
</div>

@if($customerInvoice->terms)
    <div class="section">
        <h6>Terms &amp; Conditions</h6>
        <p class="mb-0">{{ $customerInvoice->terms }}</p>
    </div>
@endif
