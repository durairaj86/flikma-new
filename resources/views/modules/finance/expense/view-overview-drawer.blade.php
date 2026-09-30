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
    <h6>{{ __('Vendor/Customer & Expense Information') }}</h6>
    <div class="info-grid">
        @if($expense->vendor)
            <div><strong>{{ __('Vendor') }}:</strong><span>{{ $expense->vendor->name_en ?? '-' }}</span></div>
        @elseif($expense->customer)
            <div><strong>{{ __('Customer') }}:</strong><span>{{ $expense->customer->name_en ?? '-' }}</span></div>
        @else
            <div><strong>{{ __('Party') }}:</strong><span>-</span></div>
        @endif
        <div><strong>{{ __('Expense No') }}:</strong><span>#{{ $expense->row_no }}</span></div>
        <div><strong>{{ __('Reference No') }}:</strong><span>{{ $expense->reference_number ?? '-' }}</span></div>
        <div><strong>{{ __('Expense Date') }}:</strong><span>{{ showDate($expense->posted_at) }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>{{ $expense->job->job_no ?? '-' }}</span></div>
        <div><strong>{{ __('Payment Mode') }}:</strong><span>{{ paymentModes()[$expense->payment_mode] ?? '-' }}</span></div>
        <div><strong>{{ __('Currency') }}:</strong><span>{{ strtoupper($expense->currency) }} ({{ __('rate') }} {{ number_format($expense->currency_rate, decimals()) }})</span></div>
        <div><strong>{{ __('Billable') }}:</strong><span>{{ $expense->is_billable ? __('Yes') : __('No') }}</span></div>
        <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\ExpenseEnum::tryFrom($expense->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Expense Items') }}</h6>
    @if($expense->expenseSubs && $expense->expenseSubs->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Account') }}</th>
                    <th>{{ __('Employee') }}</th>
                    <th>{{ __('Comment') }}</th>
                    <th class="text-end">{{ __('Qty') }}</th>
                    <th class="text-end">{{ __('Unit Price') }}</th>
                    <th class="text-end">{{ __('Line Total') }}</th>
                    <th>{{ __('Tax Code') }}</th>
                    <th class="text-end">{{ __('Tax %') }}</th>
                    <th class="text-end">{{ __('Total (Incl. Tax)') }}</th>
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
        <div class="text-center py-4 text-muted">{{ __('No line items on this expense.') }}</div>
    @endif
</div>

<div class="section">
    <h6>{{ __('Totals') }}</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr>
            <td><strong>{{ __('Amount (Excl. VAT)') }}</strong></td>
            <td class="text-end">{{ number_format($expense->base_sub_total, decimals()) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Tax') }}</strong></td>
            <td class="text-end">{{ number_format($expense->base_tax_total, decimals()) }}</td>
        </tr>
        <tr class="table-secondary">
            <td><strong>{{ __('Grand Total') }}</strong></td>
            <td class="text-end fw-bold">{{ number_format($expense->grand_total, decimals()) }} {{ strtoupper($expense->currency) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Paid Amount') }}</strong></td>
            <td class="text-end">{{ number_format($expense->paid_amount ?? 0, decimals()) }} {{ strtoupper($expense->currency) }}</td>
        </tr>
        <tr class="table-secondary">
            <td><strong>{{ __('Balance') }}</strong></td>
            <td class="text-end fw-bold">
                {{ number_format(($expense->grand_total ?? 0) - ($expense->paid_amount ?? 0), decimals()) }} {{ strtoupper($expense->currency) }}
            </td>
        </tr>
    </table>
</div>

@if($expense->notes)
    <div class="section">
        <h6>{{ __('Notes') }}</h6>
        <p class="mb-0">{{ $expense->notes }}</p>
    </div>
@endif

<div class="section">
    <h6>{{ __('Documents') }}</h6>
    @if($expense->documents && $expense->documents->count())
        <ul class="list-group list-group-flush">
            @foreach($expense->documents as $doc)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <strong>{{ $doc->document_type }}</strong>
                        <small class="text-muted d-block">{{ $doc->posted_date }}</small>
                    </div>
                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">{{ __('View') }}</a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="text-center py-4 text-muted">{{ __('No documents uploaded for this expense.') }}</div>
    @endif
</div>
