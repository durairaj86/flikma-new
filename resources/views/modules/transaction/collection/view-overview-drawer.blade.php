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
    $paidIntoAccount = $collection->account ? \App\Models\Finance\Account\Account::find($collection->account) : null;
@endphp

<div class="section">
    <h6>Customer &amp; Collection Information</h6>
    <div class="info-grid">
        <div><strong>Customer:</strong><span>{{ $collection->customer->name_en ?? '-' }}</span></div>
        <div><strong>Collection No:</strong><span>#{{ $collection->row_no }}</span></div>
        <div><strong>Phone:</strong><span>{{ $collection->customer->phone ?? '-' }}</span></div>
        <div><strong>Collection Date:</strong><span>{{ $collection->collection_date }}</span></div>
        <div><strong>Job:</strong><span>{{ $collection->job_no ?? ($collection->job->job_no ?? '-') }}</span></div>
        <div><strong>Reference No:</strong><span>{{ $collection->reference_no ?? '-' }}</span></div>
        <div><strong>Paid Into:</strong><span>{{ $paidIntoAccount->name ?? '-' }}</span></div>
        <div><strong>Payment Method:</strong><span>{{ $collection->payment_method ?? $collection->collection_method ?? '-' }}</span></div>
        <div><strong>Currency:</strong><span>{{ strtoupper($collection->currency) }} (rate {{ number_format($collection->currency_rate, decimals()) }})</span></div>
        <div><strong>Status:</strong><span>{{ \App\Enums\CollectionEnum::tryFrom($collection->status)?->label() ?? '-' }}</span></div>
        @if($collection->disapproval_reason)
            <div><strong>Disapproval Reason:</strong><span>{{ $collection->disapproval_reason }}</span></div>
        @endif
    </div>
</div>

<div class="section">
    <h6>Invoices Collected</h6>
    @if($collection->collectionInvoices && $collection->collectionInvoices->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th class="text-end">Invoice Total</th>
                    <th class="text-end">Collection Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach($collection->collectionInvoices as $ci)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $ci->customerInvoice->row_no ?? '-' }}</td>
                        <td>{{ $ci->customerInvoice->invoice_date ?? '-' }}</td>
                        <td>{{ $ci->customerInvoice->due_at ?? '-' }}</td>
                        <td class="text-end">{{ number_format($ci->customerInvoice->grand_total ?? 0, decimals()) }}</td>
                        <td class="text-end">{{ number_format($ci->amount, decimals()) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4 text-muted">No invoices linked to this collection.</div>
    @endif
</div>

<div class="section">
    <h6>Totals</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr>
            <td><strong>Subtotal</strong></td>
            <td class="text-end">{{ number_format($collection->sub_total, decimals()) }}</td>
        </tr>
        <tr>
            <td><strong>Tax</strong></td>
            <td class="text-end">{{ number_format($collection->tax_total, decimals()) }}</td>
        </tr>
        @if($collection->bank_charges > 0)
            <tr>
                <td><strong>Bank Charges</strong></td>
                <td class="text-end">{{ number_format($collection->bank_charges, decimals()) }}</td>
            </tr>
        @endif
        @if($collection->other_charges > 0)
            <tr>
                <td><strong>Other Charges</strong></td>
                <td class="text-end">{{ number_format($collection->other_charges, decimals()) }}</td>
            </tr>
        @endif
        <tr class="table-secondary">
            <td><strong>Grand Total</strong></td>
            <td class="text-end fw-bold">{{ number_format($collection->grand_total, decimals()) }} {{ strtoupper($collection->currency) }}</td>
        </tr>
        @if(strtoupper($collection->currency) !== 'SAR')
            <tr>
                <td><strong>Base Currency Total</strong></td>
                <td class="text-end">{{ number_format($collection->base_grand_total, decimals()) }} SAR</td>
            </tr>
        @endif
    </table>
</div>

@if($collection->notes)
    <div class="section">
        <h6>Notes</h6>
        <p class="mb-0">{{ $collection->notes }}</p>
    </div>
@endif

<div class="section">
    <h6>Documents</h6>
    @if($collection->documents && $collection->documents->count())
        <ul class="list-group list-group-flush">
            @foreach($collection->documents as $doc)
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
        <div class="text-center py-4 text-muted">No documents uploaded for this collection.</div>
    @endif
</div>

<div class="section">
    <h6>Audit Information</h6>
    <div class="info-grid">
        <div><strong>Created By:</strong><span>{{ $collection->createdBy->name ?? '-' }}</span></div>
        <div><strong>Created At:</strong><span>{{ $collection->created_at ? $collection->created_at->format('d-m-Y H:i:s') : '-' }}</span></div>
        @if($collection->status == 2)
            <div><strong>Approved By:</strong><span>{{ $collection->approvedBy->name ?? '-' }}</span></div>
            <div><strong>Approved At:</strong><span>{{ $collection->approved_at ?? '-' }}</span></div>
        @endif
    </div>
</div>
