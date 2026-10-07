@php
    $payStatus = [1 => ['label' => __('Draft'), 'class' => 'bg-warning-subtle text-warning'], 2 => ['label' => __('Approved'), 'class' => 'bg-success-subtle text-success'], 3 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger']];
    $payInfo = $payStatus[$payment->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
@endphp
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
    /* two-sided time frame: Enquiry / Job / Payment on the right, Quotation / Supplier Invoice on the left */
    .pay-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .pay-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .pay-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .pay-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .pay-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .pay-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .pay-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .pay-timeline .t-label { font-weight: 600; color: #0f172a; }
    .pay-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#payDetailsTab" type="button" role="tab">
            <i class="bi bi-cash-coin me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#payTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="payDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $payment->row_no }}
        <span class="badge {{ $payInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $payInfo['label'] }}</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 linked-print"
            data-print-url="{{ url('transaction/payments/' . $payment->id . '/print') }}"
            onclick="if (window.PAYMENT && PAYMENT.printPreview) { PAYMENT.printPreview('{{ $payment->id }}'); }">
        <i class="bi bi-printer me-1"></i> {{ __('Print') }}
    </button>
</div>

@php
    $paidThroughAccount = \App\Models\Finance\Account\Account::find($payment->account);
@endphp

<div class="section">
    <h6>{{ __('Supplier & Payment Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Supplier') }}:</strong><span>@if($payment->supplier_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="supplier" data-id="{{ $payment->supplier_id }}" data-title="{{ $payment->supplier->name_en ?? '' }}">{{ $payment->supplier->name_en ?? $payment->supplier->name ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Payment No') }}:</strong><span>#{{ $payment->row_no }}</span></div>
        <div><strong>{{ __('Phone') }}:</strong><span>{{ $payment->supplier->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Payment Date') }}:</strong><span>{{ $payment->payment_date }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>@if($payment->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $payment->job_id }}" data-title="{{ $payment->job->row_no ?? $payment->job_no }}">{{ $payment->job->row_no ?? $payment->job_no ?? '-' }}</a>@else{{ $payment->job_no ?? '-' }}@endif</span></div>
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
                        <td>@if($pi->supplierInvoice)<a href="#" class="open-linked text-primary text-decoration-none" data-type="supplier_invoice" data-id="{{ $pi->supplierInvoice->id }}" data-title="{{ $pi->supplierInvoice->row_no }}">{{ $pi->supplierInvoice->row_no }}</a>@else - @endif</td>
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

</div>

<div class="tab-pane fade" id="payTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job', 'payment'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'supplier_invoice' => __('Supplier Invoice'), 'payment' => __('Payment')];
        @endphp
        <ul class="pay-timeline">
            @foreach($timeline as $step)
                <li class="{{ in_array($step['module'], $sideRight) ? 'side-r' : 'side-l' }}">
                    <span class="dot"><i class="bi {{ $step['icon'] }}"></i></span>
                    <div class="t-mod">{{ $modLabel[$step['module']] ?? '' }}</div>
                    <div class="t-label">@if(!empty($step['link']))<a href="#" class="open-linked text-primary text-decoration-none" data-type="{{ $step['link'][0] }}" data-id="{{ $step['link'][1] }}" data-title="{{ $step['link'][2] }}">{{ $step['label'] }}</a>@else{{ $step['label'] }}@endif @if($step['meta'])<span class="text-muted fw-normal">· {{ $step['meta'] }}</span>@endif</div>
                    <div class="t-meta">
                        {{ \Carbon\Carbon::parse($step['at'])->format('d-m-Y H:i') }}
                        @if($step['by']) &middot; {{ __('by') }} {{ $step['by'] }} @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
</div>
