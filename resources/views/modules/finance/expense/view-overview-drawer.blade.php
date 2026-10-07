@php
    $exStatus = [1 => ['label' => __('Pending'), 'class' => 'bg-warning-subtle text-warning'], 2 => ['label' => __('Approved'), 'class' => 'bg-success-subtle text-success'], 3 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger']];
    $exInfo = $exStatus[$expense->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
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
    /* two-sided time frame: Enquiry / Job on the right, Quotation / Expense on the left */
    .ex-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .ex-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .ex-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .ex-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .ex-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .ex-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .ex-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .ex-timeline .t-label { font-weight: 600; color: #0f172a; }
    .ex-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#exDetailsTab" type="button" role="tab">
            <i class="bi bi-wallet2 me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#exTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="exDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $expense->row_no }}
        <span class="badge {{ $exInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $exInfo['label'] }}</span>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 linked-print"
            data-print-url="{{ url('finance/expense/' . $expense->id . '/print') }}"
            onclick="if (window.EXPENSE && EXPENSE.printPreview) { EXPENSE.printPreview('{{ $expense->id }}'); }">
        <i class="bi bi-printer me-1"></i> {{ __('Print') }}
    </button>
</div>

<div class="section">
    <h6>{{ __('Vendor/Customer & Expense Information') }}</h6>
    <div class="info-grid">
        @if($expense->vendor)
            <div><strong>{{ __('Vendor') }}:</strong><span><a href="#" class="open-linked text-primary text-decoration-none" data-type="supplier" data-id="{{ $expense->vendor_id }}" data-title="{{ $expense->vendor->name_en ?? '' }}">{{ $expense->vendor->name_en ?? '-' }}</a></span></div>
        @elseif($expense->customer)
            <div><strong>{{ __('Customer') }}:</strong><span><a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $expense->customer_id }}" data-title="{{ $expense->customer->name_en ?? '' }}">{{ $expense->customer->name_en ?? '-' }}</a></span></div>
        @else
            <div><strong>{{ __('Party') }}:</strong><span>-</span></div>
        @endif
        <div><strong>{{ __('Expense No') }}:</strong><span>#{{ $expense->row_no }}</span></div>
        <div><strong>{{ __('Reference No') }}:</strong><span>{{ $expense->reference_number ?? '-' }}</span></div>
        <div><strong>{{ __('Expense Date') }}:</strong><span>{{ showDate($expense->posted_at) }}</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>@if($expense->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $expense->job_id }}" data-title="{{ $expense->job->row_no ?? $expense->job->job_no ?? '' }}">{{ $expense->job->row_no ?? $expense->job->job_no ?? '-' }}</a>@else - @endif</span></div>
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

</div>

<div class="tab-pane fade" id="exTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'expense' => __('Expense')];
        @endphp
        <ul class="ex-timeline">
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
