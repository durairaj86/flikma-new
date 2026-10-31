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
    /* two-sided time frame: Enquiry / Job / Credit Note on the right, Quotation / Invoice / Collection on the left */
    .ci-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .ci-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .ci-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .ci-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .ci-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .ci-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .ci-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .ci-timeline .t-label { font-weight: 600; color: #0f172a; }
    .ci-timeline .t-meta { color: #64748b; font-size: 12.5px; }
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
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#ciDetailsTab" type="button" role="tab">
            <i class="bi bi-receipt me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#ciTransactionsTab" type="button" role="tab">
            <i class="bi bi-arrow-left-right me-1"></i> {{ __('Transactions') }}
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis ms-1">{{ count($transactions) }}</span>
        </button>
    </li>
    @if($customerInvoice->terms)
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#ciTermsTab" type="button" role="tab">
            <i class="bi bi-file-earmark-text me-1"></i> {{ __('Terms & Conditions') }}
        </button>
    </li>
    @endif
    <li class="nav-item">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#ciEntriesTab" type="button" role="tab">
            <i class="bi bi-cash-stack me-1"></i> {{ __('Cash Flow') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#ciTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="ciDetailsTab" role="tabpanel">
@php
    $ciBalance = (float) $customerInvoice->grand_total - (float) ($customerInvoice->paid_amount ?? 0);
    $ciApproved = (int) $customerInvoice->status === 3;
    $ciGrandTop = (float) $customerInvoice->grand_total;
    $ciPaidTop = (float) ($customerInvoice->paid_amount ?? 0);
    if ($ciApproved) {
        $ciChip = ($ciGrandTop > 0 && $ciPaidTop >= $ciGrandTop) ? [__('Fully Paid'), 'bg-success-subtle text-success']
            : ($ciPaidTop > 0 ? [__('Partially Paid'), 'bg-warning-subtle text-warning'] : [__('Unpaid'), 'bg-danger-subtle text-danger']);
    } else {
        $ciChip = [\App\Enums\CustomerInvoiceEnum::tryFrom((int) $customerInvoice->status)?->label() ?? '-', (int) $customerInvoice->status === 1 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger'];
    }
@endphp
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="invoice-no-heading" data-no="{{ $customerInvoice->row_no }}">{{ docNumber($customerInvoice, '#') }}<span class="badge {{ $ciChip[1] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $ciChip[0] }}</span></div>
    {{-- Print (any status); Collection opens the Collection form for this invoice; Credit Note opens the New Credit Note form for it --}}
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary linked-print"
                data-print-url="{{ url('invoice/customer/' . $customerInvoice->id . '/print') }}"
                onclick="if (window.CUSTOMER_INVOICE && CUSTOMER_INVOICE.printPreview) { CUSTOMER_INVOICE.printPreview('{{ $customerInvoice->id }}'); }">
            <i class="bi bi-printer me-1"></i>{{ __('Print') }}
        </button>
        @if((int) $customerInvoice->status === 1)
            @if(userCan('finances', 'edit'))
            <button type="button" class="btn btn-sm btn-success" onclick="CUSTOMER_INVOICE.drawerApprove('{{ $customerInvoice->id }}')">
                <i class="bi bi-check-circle me-1"></i>{{ __('Mark as Approved') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="CUSTOMER_INVOICE.drawerEdit('{{ $customerInvoice->id }}')">
                <i class="bi bi-pencil-square me-1"></i>{{ __('Edit') }}
            </button>
            @endif
            @if(userCan('finances', 'delete'))
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="CUSTOMER_INVOICE.drawerDelete('{{ $customerInvoice->id }}', @js($customerInvoice->row_no))">
                <i class="bi bi-trash3 me-1"></i>{{ __('Delete') }}
            </button>
            @endif
        @endif
        @if($ciApproved)
            @if($ciBalance > 0)
                <button type="button" class="btn btn-sm btn-success" onclick="CUSTOMER_INVOICE.recordPayment('{{ $customerInvoice->id }}')">
                    <i class="bi bi-cash-coin me-1"></i>{{ __('Collection') }}
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="CUSTOMER_INVOICE.newCreditNote('{{ $customerInvoice->id }}')">
                <i class="bi bi-receipt-cutoff me-1"></i>{{ __('Credit Note') }}
            </button>
        @endif
    </div>
</div>


<div class="section">
    <h6>{{ __('Customer & Invoice Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Customer') }}:</strong><span>@if($customerInvoice->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $customerInvoice->customer_id }}" data-title="{{ $customerInvoice->customer->name ?? '' }}">{{ $customerInvoice->customer->name ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Email') }}:</strong><span>{{ $customerInvoice->customer->email ?? '-' }}</span></div>
        <div><strong>{{ __('Invoice Date') }}:</strong><span>{{ $customerInvoice->invoice_date }}</span></div>
        <div><strong>{{ __('Phone') }}:</strong><span>{{ $customerInvoice->customer->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Due Date') }}:</strong><span>{{ $customerInvoice->due_at }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>@if($customerInvoice->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $customerInvoice->job_id }}" data-title="{{ $customerInvoice->job_no }}">{{ $customerInvoice->job_no }}</a>@else{{ $customerInvoice->job_no }}@endif</span></div>
        <div><strong>{{ __('Currency') }}:</strong><span>{{ $customerInvoice->currency }} ({{ __('rate') }} {{ number_format($customerInvoice->currency_rate, decimals()) }})</span></div>
        <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\CustomerInvoiceEnum::tryFrom($customerInvoice->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

@php
    $ciCur = strtoupper((string) $customerInvoice->currency);
    $ciBase = strtoupper((string) (authUserCompany()?->base_currency ?: authUserCompany()?->currency ?: 'SAR'));
    $ciUnits = \Illuminate\Support\Facades\DB::table('units')->pluck('unit_symbol', 'id');
    $ciUnitNames = \Illuminate\Support\Facades\DB::table('units')->pluck('unit_name', 'id');
    $ciSubs = $customerInvoice->customerInvoiceSubs ?? collect();
    // cost tax comes from the supplier invoice line the cost was taken from
    $ciSrcSubs = \App\Models\Finance\SupplierInvoice\SupplierInvoiceSub::withoutGlobalScopes()
        ->whereIn('id', $ciSubs->where('source_type', 'supplier_invoice')->pluck('source_sub_id')->filter()->all())->get()->keyBy('id');
    $ciSale = (float) $ciSubs->sum('base_total');
    $ciCostLines = $ciSubs->filter(fn ($i) => $i->cost_base_amount !== null);
    $ciCost = (float) $ciCostLines->sum('cost_base_amount');
    $ciGp = (float) $ciCostLines->sum(fn ($i) => (float) $i->base_total - (float) $i->cost_base_amount);
    $ciGpSale = (float) $ciCostLines->sum('base_total');
    $ciGpPct = $ciGpSale > 0 ? $ciGp / $ciGpSale * 100 : 0;
    $ciMarkup = $ciCost > 0 ? $ciGp / $ciCost * 100 : 0;
@endphp
@if($ciCostLines->count())
    <div class="doc-tiles">
        <div class="doc-tile t-slate"><small>{{ __('Total Cost') }}</small><b>{{ amountFormat($ciCost) }}</b><em>{{ $ciBase }}</em></div>
        <div class="doc-tile t-blue"><small>{{ __('Total Sale') }}</small><b>{{ amountFormat($ciSale) }}</b><em>{{ $ciBase }}</em></div>
        <div class="doc-tile {{ $ciGp >= 0 ? 't-green' : 't-red' }}"><small>{{ __('Gross Profit') }}</small><b>{{ amountFormat($ciGp) }}</b><em>{{ $ciBase }}</em></div>
        <div class="doc-tile {{ $ciGp >= 0 ? 't-green' : 't-red' }}"><small>{{ __('Sale GP%') }}</small><b>{{ number_format($ciGpPct, 2) }}%</b><em>{{ __('Cost GP%') }} {{ number_format($ciMarkup, 2) }}%</em></div>
    </div>
@endif

<div class="section">
    <h6>{{ __('Line Items') }}</h6>
    @if($ciSubs->count())
        <div class="table-responsive">
            <table class="table table-sm align-middle ci-lines">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Unit') }}</th>
                    <th class="text-end">{{ __('Qty') }}</th>
                    <th class="text-end">{{ __('Unit Price') }} ({{ $ciCur }})</th>
                    <th class="text-end col-cost">{{ __('Cost') }} ({{ $ciBase }})</th>
                    <th class="text-end col-sale">{{ __('Sale') }} ({{ $ciBase }})</th>
                    <th class="text-end">{{ __('GP') }} ({{ $ciBase }})</th>
                    <th class="text-end">{{ __('Total (Incl. Tax)') }} ({{ $ciBase }})</th>
                </tr>
                </thead>
                <tbody>
                @foreach($ciSubs as $item)
                    @php
                        $hasCost = $item->cost_base_amount !== null;
                        $lineGp = $hasCost ? (float) $item->base_total - (float) $item->cost_base_amount : null;
                        $costTaxPct = $hasCost ? (float) ($ciSrcSubs[$item->source_sub_id]->tax_percent ?? 0) : null;
                        $costTax = $hasCost ? round((float) $item->cost_base_amount * $costTaxPct / 100, 2) : null;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $descriptions[$item->description_id] ?? $item->description }}</div>
                            @if($item->comment)<div class="text-muted small">{{ $item->comment }}</div>@endif
                            @if($item->source_type)
                                <span class="ci-src {{ $item->source_type === 'quotation' ? 'src-q' : 'src-s' }}"><i class="bi {{ $item->source_type === 'quotation' ? 'bi-file-earmark-text' : 'bi-receipt' }}"></i>{{ $item->source_no ?: ($item->source_type === 'quotation' ? __('Quotation') : __('Supplier Invoice')) }}</span>
                            @endif
                        </td>
                        <td>{{ $item->unit ?: ($ciUnits[$item->unit_id] ?? $ciUnitNames[$item->unit_id] ?? '-') }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, decimals()) }}</td>
                        <td class="text-end col-cost">
                            @if($hasCost)
                                <div>{{ number_format($item->cost_base_amount, decimals()) }}</div>
                                @if(isset($ciSrcSubs[$item->source_sub_id]))   {{-- the cost tax is only known for a cost taken from a supplier invoice line --}}
                                    <div class="ci-tax">{{ rtrim(rtrim(number_format($costTaxPct, 2), '0'), '.') }}% · {{ number_format($costTax, decimals()) }}</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end col-sale">
                            <div>{{ number_format($item->base_total, decimals()) }}</div>
                            <div class="ci-tax">{{ rtrim(rtrim(number_format((float) $item->tax_percent, 2), '0'), '.') }}% · {{ number_format($item->base_tax_amount, decimals()) }}</div>
                            @php
                                $ciEx = vatExemption($item->tax_code);
                                // what was saved on the line wins over the current tax list
                                if ($ciEx && $item->exemption_code) { $ciEx['code'] = $item->exemption_code; $ciEx['text'] = $item->exemption_code_text ?: $ciEx['text']; }
                            @endphp
                            @if($ciEx)
                                <div><span class="ci-vatex" title="{{ $ciEx['text'] }}">{{ $ciEx['category'] }}@if($ciEx['code']) · {{ $ciEx['code'] }}@endif</span></div>
                            @endif
                        </td>
                        <td class="text-end fw-bold {{ $lineGp === null ? 'text-muted' : ($lineGp < 0 ? 'text-danger' : 'text-success') }}">{{ $lineGp === null ? '-' : number_format($lineGp, decimals()) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($item->base_total_with_tax, decimals()) }}</td>
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
    @php
        $grand = (float) $customerInvoice->grand_total;
        $paid = (float) ($customerInvoice->paid_amount ?? 0);
        $isFullyPaid = $grand > 0 && $paid >= $grand;
        $isPartiallyPaid = $paid > 0 && $paid < $grand;
    @endphp
    <div class="row g-3 text-center mb-3">
        <div class="col-6 col-md-3">
            <div class="small text-muted text-uppercase">{{ __('Grand Total') }}</div>
            <div class="fw-bold fs-5 text-primary">{{ amountFormat($grand) }}</div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small text-muted text-uppercase">{{ __('Collection') }}</div>
            <div class="fw-bold fs-5 text-success">{{ amountFormat($collected ?? 0) }}</div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small text-muted text-uppercase">{{ __('Credit Note') }}</div>
            <div class="fw-bold fs-5" style="color:#0891b2;">{{ amountFormat($credited ?? 0) }}</div>
        </div>
        <div class="col-6 col-md-3">
            @if($balance < 0)
                <div class="small text-muted text-uppercase">{{ __('Customer Credit') }}</div>
                <div class="fw-bold fs-5 text-info">{{ amountFormat(abs($balance)) }}</div>
            @else
                <div class="small text-muted text-uppercase">{{ __('Balance') }}</div>
                <div class="fw-bold fs-5 {{ $balance > 0 ? 'text-danger' : 'text-success' }}">{{ amountFormat($balance) }}</div>
            @endif
        </div>
    </div>
    @if($balance < 0)
        <div class="alert alert-info py-2 small mb-3">
            {{ __('Collections and credit notes together are more than this invoice. The extra :amount :cur is owed back to the customer (refund it, or keep it as a credit for their next invoice).', ['amount' => amountFormat(abs($balance)), 'cur' => $customerInvoice->currency]) }}
        </div>
    @endif
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
            <td><strong>{{ __('Collection Amount') }}</strong></td>
            <td class="text-end text-success fw-bold">{{ amountFormat($collected ?? 0) }} {{ $customerInvoice->currency }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Credit Note Amount') }}</strong></td>
            <td class="text-end fw-bold" style="color:#0891b2;">{{ amountFormat($credited ?? 0) }} {{ $customerInvoice->currency }}</td>
        </tr>
        @if(abs($creditMoved ?? 0) > 0.004)
            <tr>
                <td><strong>{{ ($creditMoved ?? 0) > 0 ? __('Customer Credit Applied') : __('Customer Credit Moved Out') }}</strong></td>
                <td class="text-end fw-bold" style="color:#0891b2;">{{ ($creditMoved ?? 0) > 0 ? '' : '-' }}{{ amountFormat(abs($creditMoved)) }} {{ $customerInvoice->currency }}</td>
            </tr>
        @endif
        <tr class="table-secondary">
            <td><strong>{{ $balance < 0 ? __('Customer Credit') : __('Balance') }}</strong></td>
            <td class="text-end fw-bold {{ $balance < 0 ? 'text-info' : ($balance > 0 ? 'text-danger' : 'text-success') }}">
                {{ amountFormat(abs($balance)) }} {{ $customerInvoice->currency }}
            </td>
        </tr>
    </table>
</div>



</div>

<div class="tab-pane fade" id="ciTransactionsTab" role="tabpanel">
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
                    <td colspan="6" class="text-center text-muted py-4">{{ __('No collections or credit notes recorded against this invoice yet.') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

@if($customerInvoice->terms)
<div class="tab-pane fade" id="ciTermsTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Terms & Conditions') }}</h6>
        <p class="mb-0" style="white-space: pre-line;">{{ $customerInvoice->terms }}</p>
    </div>
</div>
@endif

@include('modules.common.doc-entries', ['entrySource' => 'customer_invoice', 'entrySourceId' => $customerInvoice->id, 'entryPrefix' => 'ci'])

<div class="tab-pane fade" id="ciTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        <ul class="ci-timeline">
            @php
                $sideRight = ['enquiry', 'job', 'credit_note'];
                $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'invoice' => __('Invoice'), 'credit_note' => __('Credit Note'), 'collection' => __('Collection')];
            @endphp
            @foreach($timeline as $step)
                <li class="{{ in_array($step['module'], $sideRight) ? 'side-r' : 'side-l' }}">
                    <span class="dot"><i class="bi {{ $step['icon'] }}"></i></span>
                    <div class="t-mod">{{ $modLabel[$step['module']] ?? '' }}</div>
                    <div class="t-label">@if(!empty($step['link']))<a href="#" class="open-linked text-primary text-decoration-none" data-type="{{ $step['link'][0] }}" data-id="{{ $step['link'][1] }}" data-title="{{ $step['link'][2] }}">{{ $step['label'] }}</a>@else{{ $step['label'] }}@endif@if($step['meta']) <span class="text-muted fw-normal">· {{ $step['meta'] }}</span>@endif</div>
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
