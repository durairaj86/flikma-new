@php
    $anStatus = [
        1 => ['label' => __('Draft'), 'class' => 'bg-warning-subtle text-warning'],
        2 => ['label' => __('Notified'), 'class' => 'bg-primary-subtle text-primary'],
        3 => ['label' => __('D.O Collected'), 'class' => 'bg-success-subtle text-success'],
        4 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger'],
    ];
    $anInfo = $anStatus[$notice->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
    $urgent = (int) $notice->status === 1 && $notice->eta && $notice->eta->lte(today()->addDays(3));
    $det = $notice->detentionTable();
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
    /* two-sided time frame: Enquiry / Job on the right, Quotation / Arrival Notice on the left */
    .an-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .an-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .an-timeline li { position: relative; width: 50%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .an-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .an-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .an-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .an-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .an-timeline .t-label { font-weight: 600; color: #0f172a; }
    .an-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#anDetailsTab" type="button" role="tab">
            <i class="bi bi-truck me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#anTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="anDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $notice->row_no }}
        <span class="badge {{ $anInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $anInfo['label'] }}</span>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                onclick="if (window.ARRIVAL_NOTICE) { ARRIVAL_NOTICE.printPreview('{{ $notice->id }}', 'notice'); }">
            <i class="bi bi-printer me-1"></i> {{ __('Print Notice') }}
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3"
                onclick="if (window.ARRIVAL_NOTICE) { ARRIVAL_NOTICE.printPreview('{{ $notice->id }}', 'notification'); }">
            <i class="bi bi-printer me-1"></i> {{ __('Print Notification') }}
        </button>
    </div>
</div>

<div class="section">
    <h6>{{ __('Notice Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Customer') }}:</strong><span>@if($notice->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $notice->customer_id }}" data-title="{{ $notice->customer->name_en ?? '' }}">{{ $notice->customer->name_en ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Job') }}:</strong><span>@if($notice->job_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="job" data-id="{{ $notice->job_id }}" data-title="{{ $notice->job->row_no ?? '' }}">{{ $notice->job->row_no ?? '-' }}</a>@else - @endif</span></div>
        <div><strong>{{ __('Notice Date') }}:</strong><span>{{ $notice->notice_date?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Notified At') }}:</strong><span>{{ $notice->notified_at?->format('d-m-Y H:i') ?? '-' }}</span></div>
        <div><strong>{{ __('Mobile') }}:</strong><span>{{ $notice->to_mobile ?: '-' }}</span></div>
        <div><strong>{{ __('Fax') }}:</strong><span>{{ $notice->to_fax ?: '-' }}</span></div>
        <div style="grid-column: 1 / -1;"><strong>{{ __('Subject') }}:</strong><span>{{ $notice->subject ?: '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Vessel & Route') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('B/L No') }}:</strong><span>{{ $notice->bl_no }}</span></div>
        <div><strong>{{ __('Vessel') }}:</strong><span>{{ $notice->vessel_name }}</span></div>
        <div><strong>{{ __('Voyage No') }}:</strong><span>{{ $notice->voyage_no ?: '-' }}</span></div>
        <div><strong>{{ __('ETA') }}:</strong><span class="{{ $urgent ? 'text-danger fw-semibold' : '' }}">{{ $notice->eta?->format('d-m-Y') ?? '-' }}@if($urgent) <i class="bi bi-exclamation-triangle-fill"></i> {{ __('not notified yet') }}@endif</span></div>
        <div><strong>{{ __('Load Port') }}:</strong><span>{{ $notice->pol }}</span></div>
        <div><strong>{{ __('Discharge Port') }}:</strong><span>{{ $notice->pod }}</span></div>
        <div><strong>{{ __('Final Destination') }}:</strong><span>{{ $notice->final_destination ?: '-' }}</span></div>
        <div><strong>{{ __('Carrier / Line') }}:</strong><span>{{ $notice->carrier_name ?: '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Parties') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Shipper') }}:</strong><span>{{ $notice->shipper ?: '-' }}</span></div>
        <div><strong>{{ __('Consignee') }}:</strong><span>{{ $notice->consignee ?: '-' }}</span></div>
        <div style="grid-column: 1 / -1;"><strong>{{ __('Consignee Address') }}:</strong><span>{{ $notice->consignee_address ?: '-' }}</span></div>
        <div style="grid-column: 1 / -1;"><strong>{{ __('Notify Party') }}:</strong><span>{{ $notice->notify_party ?: '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Cargo') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Containers') }}:</strong><span>{{ (int) $notice->containers_20 }} x 20' &middot; {{ (int) $notice->containers_40 }} x 40'</span></div>
        <div><strong>{{ __('Packages') }}:</strong><span>{{ $notice->packages ?? '-' }}</span></div>
        <div style="grid-column: 1 / -1;"><strong>{{ __('Container Numbers') }}:</strong><span>{{ $notice->container_nos ?: '-' }}</span></div>
        <div style="grid-column: 1 / -1;"><strong>{{ __('Commodity') }}:</strong><span>{{ $notice->commodity ?: '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Free Time & Line Detention') }}</h6>
    <div class="info-grid mb-2">
        <div><strong>{{ __('Empty Return Within') }}:</strong><span>{{ $notice->free_days }} {{ __('days') }}</span></div>
        <div><strong>{{ __('Return Depot') }}:</strong><span>{{ $notice->return_depot ?: '-' }}</span></div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle text-center mb-0">
            <thead><tr><th class="text-start">{{ __('Container') }}</th><th>{{ __('Free Time') }}</th><th>{{ __('Next 1') }}</th><th>{{ __('Next 2') }}</th><th>{{ __('Next 3') }}</th><th>{{ __('Thereafter') }}</th></tr></thead>
            <tbody>
            @foreach(['standard' => __('Standard'), 'special' => __('Special'), 'reefer' => __('Reefer')] as $k => $label)
                <tr>
                    <td class="text-start fw-semibold">{{ $label }}</td>
                    <td>{{ $det[$k]['free'] }} {{ __('days') }}</td>
                    @foreach($det[$k]['tiers'] as $t)
                        <td class="small">@if($t['days']){{ $t['days'] }} {{ __('days') }}<br>@endif{{ rtrim(rtrim(number_format($t['r20'], 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($t['r40'], 2), '0'), '.') }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-muted x-small mt-1">{{ __('Rates per day: 20\' / 40\' container') }}</div>
</div>

<div class="section">
    <h6>{{ __('Contact') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Contact Person') }}:</strong><span>{{ $notice->contact_person ?: '-' }}</span></div>
        <div><strong>{{ __('Tel') }}:</strong><span>{{ $notice->contact_tel ?: '-' }}</span></div>
        <div><strong>{{ __('Fax') }}:</strong><span>{{ $notice->contact_fax ?: '-' }}</span></div>
        <div><strong>{{ __('Email') }}:</strong><span>{{ $notice->contact_email ?: '-' }}</span></div>
    </div>
</div>

@if($notice->remarks)
    <div class="section"><h6>{{ __('Remarks') }}</h6><p class="mb-0">{{ $notice->remarks }}</p></div>
@endif

</div>

<div class="tab-pane fade" id="anTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <div class="text-muted small mb-3"><i class="bi bi-diagram-3 me-1"></i>{{ $origin }}</div>
        @php
            $sideRight = ['enquiry', 'job'];
            $modLabel = ['enquiry' => __('Enquiry'), 'quotation' => __('Quotation'), 'job' => __('Job'), 'arrival' => __('Arrival Notice')];
        @endphp
        <ul class="an-timeline">
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
