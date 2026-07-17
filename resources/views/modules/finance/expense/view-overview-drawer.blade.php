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
    <h6>Vendor/Customer &amp; Expense Information</h6>
    <div class="info-grid">
        @if($expense->vendor)
            <div><strong>Vendor:</strong><span>{{ $expense->vendor->name_en ?? '-' }}</span></div>
        @elseif($expense->customer)
            <div><strong>Customer:</strong><span>{{ $expense->customer->name_en ?? '-' }}</span></div>
        @else
            <div><strong>Party:</strong><span>-</span></div>
        @endif
        <div><strong>Expense No:</strong><span>#{{ $expense->row_no }}</span></div>
        <div><strong>Reference No:</strong><span>{{ $expense->reference_number ?? '-' }}</span></div>
        <div><strong>Expense Date:</strong><span>{{ showDate($expense->posted_at) }}</span></div>
        <div><strong>Job:</strong><span>{{ $expense->job->job_no ?? '-' }}</span></div>
        <div><strong>Payment Mode:</strong><span>{{ paymentModes()[$expense->payment_mode] ?? '-' }}</span></div>
        <div><strong>Currency:</strong><span>{{ strtoupper($expense->currency) }} (rate {{ number_format($expense->currency_rate, decimals()) }})</span></div>
        <div><strong>Billable:</strong><span>{{ $expense->is_billable ? 'Yes' : 'No' }}</span></div>
        <div><strong>Status:</strong><span>{{ \App\Enums\ExpenseEnum::tryFrom($expense->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>Expense Items</h6>
    @if($expense->expenseSubs && $expense->expenseSubs->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Account</th>
                    <th>Employee</th>
                    <th>Comment</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Line Total</th>
                    <th>Tax Code</th>
                    <th class="text-end">Tax %</th>
                    <th class="text-end">Total (Incl. Tax)</th>
                </tr>
                </thead>
                <tbody>
                @foreach($expense->expenseSubs as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->account->name ?? '-' }}</td>
                        <td>{{ $employees[$item->employee_id] ?? '-' }}</td>
                        <td>{{ $item->comment ?? '-' }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, decimals()) }}</td>
                        <td class="text-end">{{ number_format($item->line_total, decimals()) }}</td>
                        <td>{{ $item->tax_code ?? '-' }}</td>
                        <td class="text-end">{{ $item->tax_percent }}%</td>
                        <td class="text-end">{{ number_format($item->total_with_tax ?? $item->total, decimals()) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted">No line items on this expense.</div>
    @endif
</div>

<div class="section">
    <h6>Totals</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr>
            <td><strong>Amount (Excl. VAT)</strong></td>
            <td class="text-end">{{ number_format($expense->base_sub_total, decimals()) }}</td>
        </tr>
        <tr>
            <td><strong>Tax</strong></td>
            <td class="text-end">{{ number_format($expense->base_tax_total, decimals()) }}</td>
        </tr>
        <tr class="table-secondary">
            <td><strong>Grand Total</strong></td>
            <td class="text-end fw-bold">{{ number_format($expense->grand_total, decimals()) }} {{ strtoupper($expense->currency) }}</td>
        </tr>
        <tr>
            <td><strong>Paid Amount</strong></td>
            <td class="text-end">{{ number_format($expense->paid_amount ?? 0, decimals()) }} {{ strtoupper($expense->currency) }}</td>
        </tr>
        <tr class="table-secondary">
            <td><strong>Balance</strong></td>
            <td class="text-end fw-bold">
                {{ number_format(($expense->grand_total ?? 0) - ($expense->paid_amount ?? 0), decimals()) }} {{ strtoupper($expense->currency) }}
            </td>
        </tr>
    </table>
</div>

@if($expense->notes)
    <div class="section">
        <h6>Notes</h6>
        <p class="mb-0">{{ $expense->notes }}</p>
    </div>
@endif

<div class="section">
    <h6>Documents</h6>
    @if($expense->documents && $expense->documents->count())
        <ul class="list-group list-group-flush">
            @foreach($expense->documents as $doc)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <strong>{{ $doc->document_type }}</strong>
                        <small class="text-muted d-block">{{ $doc->posted_date }}</small>
                    </div>
                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">View</a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="text-center py-4 text-muted">No documents uploaded for this expense.</div>
    @endif
</div>
