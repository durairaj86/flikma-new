@php
    $statusMap = [
        1 => ['label' => __('Draft'),     'class' => 'bg-warning-subtle text-warning'],
        2 => ['label' => __('Approved'),  'class' => 'bg-success-subtle text-success'],
        3 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger'],
    ];
    $statusInfo = $statusMap[$debitNote->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
    $cnCurrency = strtoupper($debitNote->currency ?? 'SAR');
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
    /* two-sided time frame: Enquiry / Job / Debit Note on the right, Quotation / Invoice on the left */
    .dn-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .dn-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .dn-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .dn-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .dn-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .dn-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .dn-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .dn-timeline .t-label { font-weight: 600; color: #0f172a; }
    .dn-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#dnDetailsTab" type="button" role="tab">
            <i class="bi bi-receipt-cutoff me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#dnTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="dnDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $debitNote->row_no }}
        <span class="badge {{ $statusInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $statusInfo['label'] }}</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 linked-print"
            data-print-url="{{ url('adjustment/debit-note/' . $debitNote->id . '/print') }}"
            onclick="if (window.DEBIT_NOTE && DEBIT_NOTE.printPreview) { DEBIT_NOTE.printPreview('{{ $debitNote->id }}'); }">
        <i class="bi bi-printer me-1"></i> {{ __('Print') }}
    </button>
</div>

<div class="section">
    <h6>{{ __('Supplier & Debit Note Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Supplier') }}:</strong><span>@if($debitNote->supplier_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="supplier" data-id="{{ $debitNote->supplier_id }}" data-title="{{ $debitNote->supplier->name_en ?? '' }}">{{ $debitNote->supplier->name_en ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Email') }}:</strong><span>{{ $debitNote->supplier->email ?? '-' }}</span></div>
        <div><strong>{{ __('Debit Note Date') }}:</strong><span>{{ $debitNote->posted_at ? \Carbon\Carbon::parse($debitNote->posted_at)->format('d-m-Y') : '-' }}</span></div>
        <div><strong>{{ __('Phone') }}:</strong><span>{{ $debitNote->supplier->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Supplier Invoice') }}:</strong><span>@if($debitNote->invoice_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="supplier_invoice" data-id="{{ $debitNote->invoice_id }}" data-title="{{ $debitNote->invoice->row_no ?? '' }}">{{ $debitNote->invoice->row_no ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>@if($debitNote->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $debitNote->job_id }}" data-title="{{ $debitNote->job_no }}">{{ $debitNote->job_no }}</a>@else{{ $debitNote->job_no ?? '-' }}@endif</span></div>
        <div><strong>{{ __('Currency') }}:</strong><span>{{ $cnCurrency }} ({{ __('rate') }} {{ number_format($debitNote->currency_rate ?? 1, decimals()) }})</span></div>
        @if($debitNote->reason)
            <div style="grid-column: 1 / -1;"><strong>{{ __('Reason') }}:</strong><span>{{ $debitNote->reason }}</span></div>
        @endif
    </div>
</div>

<div class="section">
    <h6>{{ __('Line Items') }}</h6>
    @if($debitNote->debitNoteSubs && $debitNote->debitNoteSubs->count())
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
                @foreach($debitNote->debitNoteSubs as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $descriptions[$item->description_id] ?? $item->description ?? '-' }}</td>
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
        <div class="text-center py-4 text-muted">{{ __('No line items on this debit note.') }}</div>
    @endif
</div>

<div class="section">
    <h6>{{ __('Totals') }}</h6>
    <table class="table table-sm total-table w-auto ms-auto">
        <tr>
            <td><strong>{{ __('Subtotal') }}</strong></td>
            <td class="text-end">{{ amountFormat($debitNote->sub_total) }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Tax') }}</strong></td>
            <td class="text-end">{{ amountFormat($debitNote->tax_total) }}</td>
        </tr>
        <tr class="table-secondary">
            <td><strong>{{ __('Grand Total') }}</strong></td>
            <td class="text-end text-primary fw-bold">{{ amountFormat($debitNote->grand_total) }} {{ $cnCurrency }}</td>
        </tr>
    </table>
</div>

@if($debitNote->terms)
    <div class="section">
        <h6>{{ __('Terms & Conditions') }}</h6>
        <p class="mb-0">{{ $debitNote->terms }}</p>
    </div>
@endif

@if($debitNote->documents && $debitNote->documents->count())
    <div class="section">
        <h6>{{ __('Documents') }} ({{ $debitNote->documents->count() }})</h6>
        @foreach($debitNote->documents as $doc)
            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
               class="d-flex align-items-center gap-2 text-decoration-none p-2 rounded mb-2"
               style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <i class="bi bi-file-earmark-text text-primary"></i>
                <span class="small text-dark fw-medium">{{ $doc->file_name }}</span>
                <i class="bi bi-box-arrow-up-right ms-auto text-muted x-small"></i>
            </a>
        @endforeach
    </div>
@endif

</div>

<div class="tab-pane fade" id="dnTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job', 'debit_note'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'supplier_invoice' => __('Supplier Invoice'), 'debit_note' => __('Debit Note')];
        @endphp
        <ul class="dn-timeline">
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
