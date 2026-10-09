@php
    $ccStatus = [
        'pending' => ['label' => __('Pending'), 'class' => 'bg-warning-subtle text-warning'],
        'under-process' => ['label' => __('Under Process'), 'class' => 'bg-info-subtle text-info'],
        'cleared' => ['label' => __('Cleared'), 'class' => 'bg-success-subtle text-success'],
        'on-hold' => ['label' => __('On Hold'), 'class' => 'bg-danger-subtle text-danger'],
    ];
    $key = $clearance->clearance_status ?: 'pending';
    $ccInfo = $ccStatus[$key] ?? ['label' => ucfirst($key), 'class' => 'bg-secondary-subtle text-secondary'];
    $typeLabel = ['import' => __('Import'), 'export' => __('Export'), 'transit' => __('Transit')][$clearance->type_of_clearance] ?? ($clearance->type_of_clearance ?: '-');
    $yn = fn($v) => $v ? __('Yes') : __('No');
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
    /* two-sided time frame: Enquiry / Job on the right, Quotation / Customs on the left */
    .cc-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .cc-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .cc-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .cc-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .cc-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .cc-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .cc-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .cc-timeline .t-label { font-weight: 600; color: #0f172a; }
    .cc-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#ccDetailsTab" type="button" role="tab">
            <i class="bi bi-shield-check me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#ccTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="ccDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">{{ $job->row_no ?? '-' }}
        <span class="badge {{ $ccInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $ccInfo['label'] }}</span>
    </div>
</div>

<div class="section">
    <h6>{{ __('Clearance Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Job') }}:</strong><span>@if($job)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $job->id }}" data-title="{{ $job->row_no }}">{{ $job->row_no }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Customer') }}:</strong><span>@if($job && $job->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $job->customer_id }}" data-title="{{ $job->customer->name_en ?? '' }}">{{ $job->customer->name_en ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Type of Clearance') }}:</strong><span>{{ $typeLabel }}</span></div>
        <div><strong>{{ __('Customs Broker') }}:</strong><span>{{ $clearance->customs_broker ?: '-' }}</span></div>
        <div><strong>{{ __('Port of Clearance') }}:</strong><span>{{ $clearance->port_clearance ?: '-' }}</span></div>
        <div><strong>{{ __('HS Code') }}:</strong><span>{{ $clearance->hs_code ?: '-' }}</span></div>
        <div><strong>{{ __('Declaration No') }}:</strong><span>{{ $clearance->declaration_no ?: '-' }}</span></div>
        <div><strong>{{ __('Bayan No') }}:</strong><span>{{ $clearance->bayan_no ?: '-' }}</span></div>
        <div><strong>{{ __('D.O No') }}:</strong><span>{{ $clearance->do_no ?: '-' }}</span></div>
        <div><strong>{{ __('Lab Clearance') }}:</strong><span>{{ $yn($clearance->lab_clearance) }}</span></div>
        <div><strong>{{ __('Inspection') }}:</strong><span>{{ $yn($clearance->inspection) }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Documents & Dates') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Docs Copy Received') }}:</strong><span>{{ $clearance->doc_received ?? '-' }}</span></div>
        <div><strong>{{ __('BL Received') }}:</strong><span>{{ $clearance->bl_receive_date ?? '-' }}</span></div>
        <div><strong>{{ __('Original Docs Received') }}:</strong><span>{{ $clearance->original_doc_received ?? '-' }}</span></div>
        <div><strong>{{ __('Saber Certificate') }}:</strong><span>{{ $clearance->saber_certificate_date ?? '-' }}</span></div>
        <div><strong>{{ __('Bayan Date') }}:</strong><span>{{ $clearance->bayan_date ?? '-' }}</span></div>
        <div><strong>{{ __('D.O Date') }}:</strong><span>{{ $clearance->do_date ?? '-' }}</span></div>
        <div><strong>{{ __('Clearance Date') }}:</strong><span>{{ $clearance->clearance_date ?? '-' }}</span></div>
        <div><strong>{{ __('Demurrage Starts') }}:</strong><span>{{ $clearance->demurrage_date ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Duty') }}</h6>
    <table class="total-table ms-auto" style="min-width:320px;">
        <tr><td><strong>{{ __('Duty Amount (Our Cost)') }}</strong></td><td class="text-end">{{ number_format((float) $clearance->duty_amount, decimals()) }}</td></tr>
        <tr class="table-secondary"><td><strong>{{ __('Duty Amount (Client)') }}</strong></td><td class="text-end fw-bold">{{ number_format((float) $clearance->duty_amount_client, decimals()) }}</td></tr>
    </table>
</div>

@if($clearance->clearance_remarks || $clearance->do_remarks)
    <div class="section">
        <h6>{{ __('Remarks') }}</h6>
        @if($clearance->clearance_remarks)<p class="mb-1"><strong>{{ __('Clearance') }}:</strong> {{ $clearance->clearance_remarks }}</p>@endif
        @if($clearance->do_remarks)<p class="mb-0"><strong>{{ __('D.O') }}:</strong> {{ $clearance->do_remarks }}</p>@endif
    </div>
@endif

</div>

<div class="tab-pane fade" id="ccTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'customs' => __('Customs')];
        @endphp
        <ul class="cc-timeline">
            @foreach($timeline as $step)
                <li class="{{ in_array($step['module'], $sideRight) ? 'side-r' : 'side-l' }}">
                    <span class="dot"><i class="bi {{ $step['icon'] }}"></i></span>
                    <div class="t-mod">{{ $modLabel[$step['module']] ?? '' }}</div>
                    <div class="t-label">@if(!empty($step['link']))<a href="#" class="open-linked text-primary text-decoration-none" data-type="{{ $step['link'][0] }}" data-id="{{ $step['link'][1] }}" data-title="{{ $step['link'][2] }}">{{ $step['label'] }}</a>@else{{ $step['label'] }}@endif @if($step['meta'])<span class="text-muted fw-normal">· {{ $step['meta'] }}</span>@endif</div>
                    <div class="t-meta">{{ \Carbon\Carbon::parse($step['at'])->format('d-m-Y') }}</div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
</div>
