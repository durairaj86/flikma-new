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
    .info-grid .col-span-2 {
        grid-column: span 2;
    }
    .qtn-refs { display: flex; flex-wrap: wrap; gap: .5rem 1.25rem; align-items: center; padding: .75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1rem; font-size: 13.5px; }
    .qtn-refs .ref-label { color: #64748b; margin-right: .35rem; }
    .qtn-refs .ref-value { font-weight: 700; color: #0f172a; }
    .qtn-timeline { list-style: none; margin: 0; padding: 0 0 0 .25rem; }
    .qtn-timeline li { position: relative; padding: 0 0 .85rem 2rem; font-size: 13.5px; }
    .qtn-timeline li:last-child { padding-bottom: 0; }
    .qtn-timeline li::before { content: ''; position: absolute; left: .7rem; top: 1.5rem; bottom: 0; width: 2px; background: #e2e8f0; }
    .qtn-timeline li:last-child::before { display: none; }
    .qtn-timeline .dot { position: absolute; left: 0; top: 0; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; }
    .qtn-timeline .t-label { font-weight: 600; color: #0f172a; }
    .qtn-timeline .t-meta { color: #64748b; font-size: 12.5px; }
</style>

@php
    $party = $enquiry->customer_id ? $enquiry->customer : $enquiry->prospect;
@endphp

<!-- General Tab -->
<div class="tab-pane fade show active" id="enquiryGeneralTab" role="tabpanel">

<div class="qtn-refs">
    <span><span class="ref-label">{{ __('Enquiry No') }}:</span><span class="ref-value">{{ $enquiry->row_no }}</span></span>
    @if($quotationNo)
        <span><span class="ref-label">{{ __('Quotation') }}:</span><span class="ref-value">{{ $quotationNo }}</span></span>
    @endif
    @if($jobNo)
        <span><span class="ref-label">{{ __('Job') }}:</span><span class="ref-value">{{ $jobNo }}</span></span>
    @endif
</div>

<div class="section">
    <h6>{{ __('Party & Enquiry Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Party:') }}</strong><span>{{ $party->name ?? '-' }}</span></div>
        <div><strong>{{ __('Enquiry No:') }}</strong><span>#{{ $enquiry->row_no }}</span></div>
        <div><strong>{{ __('Email:') }}</strong><span>{{ $party->email ?? '-' }}</span></div>
        <div><strong>{{ __('Enquiry Date:') }}</strong><span>{{ showDate($enquiry->created_at) }}</span></div>
        <div><strong>{{ __('Phone:') }}</strong><span>{{ $party->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Expiry Date:') }}</strong><span>{{ showDate($enquiry->expiry_date) }}</span></div>
        <div><strong>{{ __('Department:') }}</strong><span>{{ $enquiry->activity->name ?? '-' }}</span></div>
        <div><strong>{{ __('Status:') }}</strong><span>{{ \App\Enums\EnquiryEnum::tryFrom($enquiry->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Shipment Details') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Shipment Mode:') }}</strong><span>{{ ucfirst($enquiry->shipment_mode ?? '-') }}</span></div>
        <div><strong>{{ __('Category:') }}</strong><span>{{ ucfirst($enquiry->shipment_category ?? '-') }}</span></div>
        <div><strong>{{ __('Weight:') }}</strong><span>{{ $enquiry->weight ?? '-' }} {{ __('kg') }}</span></div>
        <div><strong>{{ __('Volume:') }}</strong><span>{{ $enquiry->volume ?? '-' }} m&sup3;</span></div>
        <div><strong>{{ __('Pickup Date:') }}</strong><span>{{ showDate($enquiry->pickup_date) }}</span></div>
        <div><strong>{{ __('Shipper:') }}</strong><span>{{ $enquiry->shipper ?? '-' }}</span></div>
        <div><strong>{{ __('Incoterm:') }}</strong><span>{{ $enquiry->incoterm ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('POL & POD') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Place of Receipt:') }}</strong><span>{{ $enquiry->place_of_receipt ?? '-' }}</span></div>
        <div><strong>{{ __('Origin City:') }}</strong><span>{{ $enquiry->origin_city ?? '-' }}</span></div>
        <div><strong>{{ __('POL:') }}</strong><span>{{ $enquiry->pol ?? '-' }}</span></div>
        <div><strong>{{ __('POD:') }}</strong><span>{{ $enquiry->pod ?? '-' }}</span></div>
    </div>
</div>

@if($enquiry->remark)
    <div class="section">
        <h6>{{ __('Notes') }}</h6>
        <p class="mb-0">{{ $enquiry->remark }}</p>
    </div>
@endif

</div>

<!-- Time Frame Tab -->
<div class="tab-pane fade" id="enquiryTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <ul class="qtn-timeline">
            @foreach($timeline as $step)
                <li>
                    <span class="dot"><i class="bi {{ $step['icon'] }}"></i></span>
                    <div class="t-label">{{ $step['label'] }}</div>
                    <div class="t-meta">
                        {{ $step['at'] ? \Carbon\Carbon::parse($step['at'])->format('d-m-Y H:i') : '-' }}
                        @if($step['by']) &middot; {{ __('by') }} {{ $step['by'] }} @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
