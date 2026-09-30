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

@php
    $paidThroughAccount = \App\Models\Finance\Account\Account::find($payment->account);
@endphp

<div class="section">
    <h6>{{ __('Supplier & Payment Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Supplier') }}:</strong><span>{{ $payment->supplier->name_en ?? $payment->supplier->name ?? '-' }}</span></div>
        <div><strong>{{ __('Payment No') }}:</strong><span>#{{ $payment->row_no }}</span></div>
        <div><strong>{{ __('Phone') }}:</strong><span>{{ $payment->supplier->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Payment Date') }}:</strong><span>{{ $payment->payment_date }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>{{ $payment->job_no ?? ($payment->job->job_no ?? '-') }}</span></div>
        <div><strong>{{ __('Reference No') }}:</strong><span>{{ $payment->reference_no ?? '-' }}</span></div>
        <div><strong>{{ __('Paid Through') }}:</strong><span>{{ $paidThroughAccount->name ?? '-' }}</span></div>
        <div><strong>{{ __('Payment Method') }}:</strong><span>{{ $payment->payment_method ?? '-' }}</span></div>
        <div><strong>{{ __('Currency') }}:</strong><span>{{ strtoupper($payment->currency) }} ({{ __('rate') }} {{ number_format($payment->currency_rate, decimals()) }})</span></div>
        <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\PaymentEnum::tryFrom($payment->status)?->label() ?? '-' }}</span></div>
        @if($payment->status == \App\Enums\PaymentEnum::CANCELLED->value && $payment->disapproval_reason)
            <div><strong>{{ __('Disapproval Reason') }}:</strong><span>{{ $payment->disapproval_reason }}</span></div>
        @endif
    </div>
</div>

<div class="section">
    <h6>{{ __('Invoices Paid') }}</h6>
    @if($payment->paymentInvoices && $payment->paymentInvoices->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Invoice No') }}</th>
                    <th>{{ __('Invoice Date') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th class="text-end">{{ __('Invoice Total') }}</th>
                    <th class="text-end">{{ __('Payment Amount') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payment->paymentInvoices as $pi)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $pi->supplierInvoice->row_no ?? '-' }}</td>
                        <td>{{ $pi->supplierInvoice->invoice_date ?? '-' }}</td>
                        <td>{{ $pi->supplierInvoice->due_at ?? '-' }}</td>
                        <td class="text-end">{{ number_format($pi->supplierInvoice->grand_total ?? 0, decimals()) }}</td>
                        <td class="text-end">{{ number_format($pi->amount, decimals()) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted">{{ __('No invoices linked to this payment.') }}</div>
    @endif
</div>

@if($payment->additionalTransactions && $payment->additionalTransactions->count())
    <div class="section">
        <h6>{{ __('Additional Transactions') }}</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Account') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payment->additionalTransactions as $txn)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $txn->account->name ?? '-' }}</td>
                        <td>{{ $txn->description ?? '-' }}</td>
                        <td>{{ $txn->is_debit ? __('Debit') : __('Credit') }}</td>
                        <td class="text-end">{{ number_format($txn->amount, decimals()) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="section">
    <h6>{{ __('Totals') }}</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr>
            <td><strong>{{ __('Subtotal') }}</strong></td>
            <td class="text-end">{{ number_format($payment->sub_total, decimals()) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Tax') }}</strong></td>
            <td class="text-end">{{ number_format($payment->tax_total, decimals()) }}</td>
        </tr>
        @if($payment->bank_charges > 0)
            <tr>
                <td><strong>{{ __('Bank Charges') }}</strong></td>
                <td class="text-end">{{ number_format($payment->bank_charges, decimals()) }}</td>
            </tr>
        @endif
        @if($payment->other_charges > 0)
            <tr>
                <td><strong>{{ __('Other Charges') }}</strong></td>
                <td class="text-end">{{ number_format($payment->other_charges, decimals()) }}</td>
            </tr>
        @endif
        <tr class="table-secondary">
            <td><strong>{{ __('Grand Total') }}</strong></td>
            <td class="text-end fw-bold">{{ number_format($payment->grand_total, decimals()) }} {{ strtoupper($payment->currency) }}</td>
        </tr>
        @if(strtoupper($payment->currency) !== 'SAR')
            <tr>
                <td><strong>{{ __('Base Currency Total') }}</strong></td>
                <td class="text-end">{{ number_format($payment->base_grand_total, decimals()) }} SAR</td>
            </tr>
        @endif
    </table>
</div>

@if($payment->notes)
    <div class="section">
        <h6>{{ __('Notes') }}</h6>
        <p class="mb-0">{{ $payment->notes }}</p>
    </div>
@endif

<div class="section">
    <h6>{{ __('Documents') }}</h6>
    @if($payment->documents && $payment->documents->count())
        <ul class="list-group list-group-flush">
            @foreach($payment->documents as $doc)
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
        <div class="text-center py-4 text-muted">{{ __('No documents uploaded for this payment.') }}</div>
    @endif
</div>

<div class="section">
    <h6>{{ __('Audit Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Created By') }}:</strong><span>{{ $payment->createdBy->name ?? '-' }}</span></div>
        <div><strong>{{ __('Created At') }}:</strong><span>{{ $payment->created_at ? $payment->created_at->format('d-m-Y H:i:s') : '-' }}</span></div>
        @if($payment->status == \App\Enums\PaymentEnum::APPROVED->value)
            <div><strong>{{ __('Approved By') }}:</strong><span>{{ $payment->approvedBy->name ?? '-' }}</span></div>
            <div><strong>{{ __('Approved At') }}:</strong><span>{{ $payment->approved_at ?? '-' }}</span></div>
        @endif
    </div>
</div>
