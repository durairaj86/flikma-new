@php
    $colStatus = [1 => ['label' => __('Draft'), 'class' => 'bg-warning-subtle text-warning'], 2 => ['label' => __('Approved'), 'class' => 'bg-success-subtle text-success'], 3 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger']];
    $colInfo = $colStatus[$collection->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
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
    /* two-sided time frame: Enquiry / Job / Collection on the right, Quotation / Invoice on the left */
    .col-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .col-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .col-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .col-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .col-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .col-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .col-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .col-timeline .t-label { font-weight: 600; color: #0f172a; }
    .col-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#colDetailsTab" type="button" role="tab">
            <i class="bi bi-cash-coin me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#colTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="colDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $collection->row_no }}
        <span class="badge {{ $colInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $colInfo['label'] }}</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 linked-print"
            data-print-url="{{ url('transaction/collections/' . $collection->id . '/print') }}"
            onclick="if (window.COLLECTION && COLLECTION.printPreview) { COLLECTION.printPreview('{{ $collection->id }}'); }">
        <i class="bi bi-printer me-1"></i> {{ __('Print') }}
    </button>
</div>

@php
    $paidIntoAccount = $collection->account ? \App\Models\Finance\Account\Account::find($collection->account) : null;
@endphp

<div class="section">
    <h6>{{ __('Customer & Collection Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Customer') }}:</strong><span>@if($collection->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $collection->customer_id }}" data-title="{{ $collection->customer->name_en ?? '' }}">{{ $collection->customer->name_en ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Collection No') }}:</strong><span>#{{ $collection->row_no }}</span></div>
        <div><strong>{{ __('Phone') }}:</strong><span>{{ $collection->customer->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Collection Date') }}:</strong><span>{{ $collection->collection_date }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>@if($collection->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $collection->job_id }}" data-title="{{ $collection->job->row_no ?? $collection->job_no }}">{{ $collection->job->row_no ?? $collection->job_no ?? '-' }}</a>@else{{ $collection->job_no ?? '-' }}@endif</span></div>
        <div><strong>{{ __('Reference No') }}:</strong><span>{{ $collection->reference_no ?? '-' }}</span></div>
        <div><strong>{{ __('Paid Into') }}:</strong><span>{{ $paidIntoAccount->name ?? '-' }}</span></div>
        <div><strong>{{ __('Payment Method') }}:</strong><span>{{ $collection->payment_method ?? $collection->collection_method ?? '-' }}</span></div>
        <div><strong>{{ __('Currency') }}:</strong><span>{{ strtoupper($collection->currency) }} ({{ __('rate') }} {{ number_format($collection->currency_rate, decimals()) }})</span></div>
        <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\CollectionEnum::tryFrom($collection->status)?->label() ?? '-' }}</span></div>
        @if($collection->disapproval_reason)
            <div><strong>{{ __('Disapproval Reason') }}:</strong><span>{{ $collection->disapproval_reason }}</span></div>
        @endif
    </div>
</div>

<div class="section">
    <h6>{{ __('Invoices Collected') }}</h6>
    @if($collection->collectionInvoices && $collection->collectionInvoices->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Invoice No') }}</th>
                    <th>{{ __('Invoice Date') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th class="text-end">{{ __('Invoice Total') }}</th>
                    <th class="text-end">{{ __('Collection Amount') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($collection->collectionInvoices as $ci)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>@if($ci->customerInvoice)<a href="#" class="open-linked text-primary text-decoration-none" data-type="invoice" data-id="{{ $ci->customerInvoice->id }}" data-title="{{ $ci->customerInvoice->row_no }}">{{ $ci->customerInvoice->row_no }}</a>@else - @endif</td>
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
        <div class="text-center py-4 text-muted">{{ __('No invoices linked to this collection.') }}</div>
    @endif
</div>

<div class="section">
    <h6>{{ __('Totals') }}</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr>
            <td><strong>{{ __('Subtotal') }}</strong></td>
            <td class="text-end">{{ number_format($collection->sub_total, decimals()) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Tax') }}</strong></td>
            <td class="text-end">{{ number_format($collection->tax_total, decimals()) }}</td>
        </tr>
        @if($collection->bank_charges > 0)
            <tr>
                <td><strong>{{ __('Bank Charges') }}</strong></td>
                <td class="text-end">{{ number_format($collection->bank_charges, decimals()) }}</td>
            </tr>
        @endif
        @if($collection->other_charges > 0)
            <tr>
                <td><strong>{{ __('Other Charges') }}</strong></td>
                <td class="text-end">{{ number_format($collection->other_charges, decimals()) }}</td>
            </tr>
        @endif
        <tr class="table-secondary">
            <td><strong>{{ __('Grand Total') }}</strong></td>
            <td class="text-end fw-bold">{{ number_format($collection->grand_total, decimals()) }} {{ strtoupper($collection->currency) }}</td>
        </tr>
        @if(strtoupper($collection->currency) !== 'SAR')
            <tr>
                <td><strong>{{ __('Base Currency Total') }}</strong></td>
                <td class="text-end">{{ number_format($collection->base_grand_total, decimals()) }} SAR</td>
            </tr>
        @endif
    </table>
</div>

@if($collection->notes)
    <div class="section">
        <h6>{{ __('Notes') }}</h6>
        <p class="mb-0">{{ $collection->notes }}</p>
    </div>
@endif

<div class="section">
    <h6>{{ __('Documents') }}</h6>
    @if($collection->documents && $collection->documents->count())
        <ul class="list-group list-group-flush">
            @foreach($collection->documents as $doc)
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
        <div class="text-center py-4 text-muted">{{ __('No documents uploaded for this collection.') }}</div>
    @endif
</div>

<div class="section">
    <h6>{{ __('Audit Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Created By') }}:</strong><span>{{ $collection->createdBy->name ?? '-' }}</span></div>
        <div><strong>{{ __('Created At') }}:</strong><span>{{ $collection->created_at ? $collection->created_at->format('d-m-Y H:i:s') : '-' }}</span></div>
        @if($collection->status == 2)
            <div><strong>{{ __('Approved By') }}:</strong><span>{{ $collection->approvedBy->name ?? '-' }}</span></div>
            <div><strong>{{ __('Approved At') }}:</strong><span>{{ $collection->approved_at ?? '-' }}</span></div>
        @endif
    </div>
</div>

</div>

<div class="tab-pane fade" id="colTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job', 'collection'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'invoice' => __('Invoice'), 'collection' => __('Collection')];
        @endphp
        <ul class="col-timeline">
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
