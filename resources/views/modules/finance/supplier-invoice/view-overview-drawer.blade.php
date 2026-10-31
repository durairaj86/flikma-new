@php
    $siStatus = [
        1 => ['label' => __('Draft'), 'class' => 'bg-warning-subtle text-warning'],
        2 => ['label' => __('Sent'), 'class' => 'bg-info-subtle text-info'],
        3 => ['label' => __('Approved'), 'class' => 'bg-success-subtle text-success'],
        4 => ['label' => __('Rejected'), 'class' => 'bg-danger-subtle text-danger'],
        5 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger'],
        6 => ['label' => __('Converted'), 'class' => 'bg-primary-subtle text-primary'],
    ];
    $siInfo = $siStatus[(int) ($supplierInvoice->status instanceof \BackedEnum ? $supplierInvoice->status->value : $supplierInvoice->status)] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
@endphp
@php
    $siGrandTop = (float) ($supplierInvoice->grand_total ?? 0);
    $siPaidTop = (float) ($supplierInvoice->paid_amount ?? 0);
    if ((int) ($supplierInvoice->status instanceof \BackedEnum ? $supplierInvoice->status->value : $supplierInvoice->status) === 3) {
        $siInfo = ($siGrandTop > 0 && $siPaidTop >= $siGrandTop) ? ['label' => __('Fully Paid'), 'class' => 'bg-success-subtle text-success']
            : ($siPaidTop > 0 ? ['label' => __('Partially Paid'), 'class' => 'bg-warning-subtle text-warning'] : ['label' => __('Unpaid'), 'class' => 'bg-danger-subtle text-danger']);
    }
@endphp
<style>
    .invoice-no-heading { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin-bottom: 1rem; }
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
    /* two-sided time frame: Enquiry / Job / Customer Invoice on the right, Quotation / Supplier Invoice / Payment on the left */
    .si-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .si-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .si-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .si-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .si-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .si-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .si-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .si-timeline .t-label { font-weight: 600; color: #0f172a; }
    .si-timeline .t-meta { color: #64748b; font-size: 12.5px; }
</style>

<style>
    /* Drawer tabs: flat, with an accent underline on the active one (no boxed tab borders). */
    .ci-tabs { border-bottom: 1px solid #e5e7eb; gap: 0; flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; }
    .ci-tabs::-webkit-scrollbar { display: none; }
    .ci-tabs .nav-link { border: 0; border-bottom: 3px solid transparent; border-radius: 0; background: transparent; color: #64748b; padding: .65rem .8rem; margin-bottom: -1px; white-space: nowrap; font-size: .9rem; }
    .ci-tabs .nav-link:hover { color: #0d6efd; background: rgba(13, 110, 253, .05); }
    .ci-tabs .nav-link.active { color: #0d6efd; background: transparent; border-bottom-color: #0d6efd; }
    .ci-tabs .nav-link .badge { background: #eef2f7 !important; color: #475467 !important; font-weight: 600; }
    .ci-tabs .nav-link.active .badge { background: rgba(13, 110, 253, .12) !important; color: #0d6efd !important; }
</style>
<ul class="nav nav-tabs ci-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#siDetailsTab" type="button" role="tab">
            <i class="bi bi-receipt me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#siTransactionsTab" type="button" role="tab">
            <i class="bi bi-arrow-left-right me-1"></i> {{ __('Transactions') }}
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis ms-1">{{ count($transactions) }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#siEntriesTab" type="button" role="tab">
            <i class="bi bi-cash-stack me-1"></i> {{ __('Cash Flow') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#siTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="siDetailsTab" role="tabpanel">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $supplierInvoice->row_no }}
        <span class="badge {{ $siInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $siInfo['label'] }}</span>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary linked-print"
            data-print-url="{{ url('invoice/supplier/' . $supplierInvoice->id . '/print') }}"
            onclick="if (window.SUPPLIER_INVOICE && SUPPLIER_INVOICE.printPreview) { SUPPLIER_INVOICE.printPreview('{{ $supplierInvoice->id }}'); }">
        <i class="bi bi-printer me-1"></i>{{ __('Print') }}
    </button>
</div>
@php
    $siTotal = (float) ($supplierInvoice->grand_total ?? 0);
    $siPaid = (float) ($supplierInvoice->paid_amount ?? 0);
    $siDraftPaid = (float) collect($transactions ?? [])->where('kind', 'payment')->where('status_label', 'Draft')->sum('amount');
    $siRemaining = $siTotal - $siPaid;
@endphp
<div class="doc-tiles">
    <div class="doc-tile t-blue"><small>{{ __('Total') }}</small><b>{{ amountFormat($siTotal) }}</b><em>{{ $supplierInvoice->currency }}</em></div>
    <div class="doc-tile t-green"><small>{{ __('Total Paid') }}</small><b>{{ amountFormat($siPaid) }}</b><em>{{ $supplierInvoice->currency }}</em></div>
    @if($siDraftPaid > 0)
        <div class="doc-tile t-amber"><small>{{ __('Draft Payments') }}</small><b>{{ amountFormat($siDraftPaid) }}</b><em>{{ __('not approved yet') }}</em></div>
    @endif
    <div class="doc-tile {{ $siRemaining > 0 ? 't-red' : 't-green' }}"><small>{{ __('Remaining Balance') }}</small><b>{{ amountFormat($siRemaining) }}</b><em>{{ $supplierInvoice->currency }}</em></div>
</div>
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
                        <td>{{ vatLabel($item->tax_code ?? null) }}</td>
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

<div class="section section-totals">
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
        @if($siDraftPaid > 0)
        <tr>
            <td><strong>{{ __('Draft Payments') }}</strong><div style="font-size:12px;color:#666;">{{ __('not approved yet') }}</div></td>
            <td class="text-end">{{ amountFormat($siDraftPaid) }} {{ $supplierInvoice->currency }}</td>
        </tr>
        @endif
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
<div class="section">
    <h6>{{ __('Audit Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Created By') }}:</strong><span>{{ $audit['created_by'] ?? '-' }}</span></div>
        <div><strong>{{ __('Created At') }}:</strong><span>{{ $audit['created_at'] ?? '-' }}</span></div>
        <div><strong>{{ __('Approved By') }}:</strong><span>{{ $audit['approved_by'] ?? '-' }}</span></div>
        <div><strong>{{ __('Approved At') }}:</strong><span>{{ $audit['approved_at'] ?? '-' }}</span></div>
    </div>
</div>

</div>

<div class="tab-pane fade" id="siTransactionsTab" role="tabpanel">
<div class="section">
    <h6>{{ __('Transactions') }}</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
            <tr>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Reference') }}</th>
                <th class="text-end">{{ __('Amount') }}</th>
                <th>{{ __('Created by') }}</th>
                <th>{{ __('Approved by') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td><span class="badge bg-{{ $tx['type_color'] }}-subtle text-{{ $tx['type_color'] }}-emphasis">{{ $tx['type'] }}</span></td>
                    <td>
                        @if(!empty($tx['id']))
                            <a href="#" class="open-linked text-primary text-decoration-none" data-type="{{ $tx['kind'] }}" data-id="{{ $tx['id'] }}" data-title="{{ $tx['reference'] }}">{{ $tx['reference'] }}</a>
                        @elseif($tx['url'])
                            <a href="{{ $tx['url'] }}" target="_blank">{{ $tx['reference'] }}</a>
                        @else
                            {{ $tx['reference'] }}
                        @endif
                    </td>
                    <td class="text-end">
                        <div>{{ amountFormat($tx['amount']) }}</div>
                        @if(!empty($tx['note']))
                            <div class="small text-{{ $tx['note_color'] ?? 'muted' }}">{{ $tx['note'] }}</div>
                        @endif
                    </td>
                    <td>
                        <div>{{ $tx['by'] ?? '—' }}</div>
                        <div class="small text-muted">{{ $tx['created_on'] ?? $tx['date'] }}</div>
                    </td>
                    <td>
                        <div>{{ $tx['approved_by'] ?? '—' }}</div>
                        <div class="small {{ !empty($tx['approved_on']) ? 'text-success' : 'text-muted' }}">{{ $tx['approved_on'] ?? '—' }}</div>
                    </td>
                    <td><span class="badge bg-{{ $tx['status_color'] }}-subtle text-{{ $tx['status_color'] }}-emphasis">{{ $tx['status_label'] }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">{{ __('No payments or debit notes recorded against this invoice yet.') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

@include('modules.common.doc-entries', ['entrySource' => 'supplier_invoice', 'entrySourceId' => $supplierInvoice->id, 'entryPrefix' => 'si'])

<div class="tab-pane fade" id="siTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        <ul class="si-timeline">
            @php
                $sideRight = ['enquiry', 'job', 'customer_invoice'];
                $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'invoice' => __('Supplier Invoice'), 'customer_invoice' => __('Customer Invoice'), 'payment' => __('Payment')];
            @endphp
            @foreach($timeline as $step)
                <li class="{{ in_array($step['module'], $sideRight) ? 'side-r' : 'side-l' }}">
                    <span class="dot"><i class="bi {{ $step['icon'] }}"></i></span>
                    <div class="t-mod">{{ $modLabel[$step['module']] ?? '' }}</div>
                    <div class="t-label">{{ $step['label'] }}@if($step['meta']) <span class="text-muted fw-normal">· {{ $step['meta'] }}</span>@endif</div>
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
