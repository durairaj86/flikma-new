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
    .invoice-no-heading {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 1rem;
    }
</style>

<div class="invoice-no-heading">#{{ $customerInvoice->row_no }}</div>

<div class="section">
    <h6>{{ __('Customer & Invoice Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Customer') }}:</strong><span>{{ $customerInvoice->customer->name ?? '-' }}</span></div>
        <div><strong>{{ __('Email') }}:</strong><span>{{ $customerInvoice->customer->email ?? '-' }}</span></div>
        <div><strong>{{ __('Invoice Date') }}:</strong><span>{{ $customerInvoice->invoice_date }}</span></div>
        <div><strong>{{ __('Phone') }}:</strong><span>{{ $customerInvoice->customer->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Due Date') }}:</strong><span>{{ $customerInvoice->due_at }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>{{ $customerInvoice->job_no }}</span></div>
        <div><strong>{{ __('Currency') }}:</strong><span>{{ $customerInvoice->currency }} ({{ __('rate') }} {{ number_format($customerInvoice->currency_rate, decimals()) }})</span></div>
        <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\CustomerInvoiceEnum::tryFrom($customerInvoice->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Line Items') }}</h6>
    @if($customerInvoice->customerInvoiceSubs && $customerInvoice->customerInvoiceSubs->count())
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
        <div class="text-center py-4 text-muted">{{ __('No line items on this invoice.') }}</div>
    @endif
</div>

<div class="section">
    <h6>{{ __('Totals') }}</h6>
    @php
        $grand = (float) $customerInvoice->grand_total;
        $paid = (float) ($customerInvoice->paid_amount ?? 0);
        $isFullyPaid = $grand > 0 && $paid >= $grand;
        $isPartiallyPaid = $paid > 0 && $paid < $grand;
    @endphp
    <div class="row g-3 text-center mb-3">
        <div class="col-4">
            <div class="small text-muted text-uppercase">{{ __('Grand Total') }}</div>
            <div class="fw-bold fs-5 text-primary">{{ amountFormat($grand) }}</div>
        </div>
        <div class="col-4">
            <div class="small text-muted text-uppercase">{{ __('Paid') }}</div>
            <div class="fw-bold fs-5 text-success">{{ amountFormat($paid) }}</div>
        </div>
        <div class="col-4">
            <div class="small text-muted text-uppercase">{{ __('Balance') }}</div>
            <div class="fw-bold fs-5 {{ $balance > 0 ? 'text-danger' : 'text-success' }}">{{ amountFormat($balance) }}</div>
        </div>
    </div>
    <div class="text-center mb-3">
        @if($isFullyPaid)
            <span class="badge bg-success-subtle text-success px-3 py-2">{{ __('Fully Paid') }}</span>
        @elseif($isPartiallyPaid)
            <span class="badge bg-warning-subtle text-warning px-3 py-2">{{ __('Partially Paid') }}</span>
        @else
            <span class="badge bg-danger-subtle text-danger px-3 py-2">{{ __('Unpaid') }}</span>
        @endif
    </div>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr>
            <td><strong>{{ __('Subtotal') }}</strong></td>
            <td class="text-end">{{ amountFormat($customerInvoice->sub_total) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Tax') }}</strong></td>
            <td class="text-end">{{ amountFormat($customerInvoice->tax_total) }}</td>
        </tr>
        @if($customerInvoice->discount_total > 0)
            <tr>
                <td><strong>{{ __('Discount') }}</strong></td>
                <td class="text-end">-{{ amountFormat($customerInvoice->discount_total) }}</td>
            </tr>
        @endif
        <tr>
            <td><strong>{{ __('Grand Total') }}</strong></td>
            <td class="text-end text-primary fw-bold">{{ amountFormat($grand) }} {{ $customerInvoice->currency }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Paid Amount') }}</strong></td>
            <td class="text-end text-success fw-bold">{{ amountFormat($paid) }} {{ $customerInvoice->currency }}</td>
        </tr>
        <tr class="table-secondary">
            <td><strong>{{ __('Balance') }}</strong></td>
            <td class="text-end fw-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                {{ amountFormat($balance) }} {{ $customerInvoice->currency }}
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <h6>{{ __('Transactions') }}</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
            <tr>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Date') }}</th>
                <th class="text-end">{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td><span class="badge bg-{{ $tx['type_color'] }}-subtle text-{{ $tx['type_color'] }}-emphasis">{{ $tx['type'] }}</span></td>
                    <td>
                        @if($tx['url'])
                            <a href="{{ $tx['url'] }}" target="_blank">{{ $tx['reference'] }}</a>
                        @else
                            {{ $tx['reference'] }}
                        @endif
                    </td>
                    <td>{{ $tx['date'] }}</td>
                    <td class="text-end">{{ amountFormat($tx['amount']) }}</td>
                    <td><span class="badge bg-{{ $tx['status_color'] }}-subtle text-{{ $tx['status_color'] }}-emphasis">{{ $tx['status_label'] }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">{{ __('No collections or credit notes recorded against this invoice yet.') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($customerInvoice->terms)
    <div class="section">
        <h6>{{ __('Terms & Conditions') }}</h6>
        <p class="mb-0">{{ $customerInvoice->terms }}</p>
    </div>
@endif
