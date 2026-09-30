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
</style>

@php
    $party = $enquiry->customer_id ? $enquiry->customer : $enquiry->prospect;
@endphp

<div class="section">
    <h6>{{ __('Party & Enquiry Information') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Party:') }}</strong><span>{{ $party->name ?? '-' }}</span></div>
        <div><strong>{{ __('Enquiry No:') }}</strong><span>#{{ $enquiry->row_no }}</span></div>
        <div><strong>{{ __('Email:') }}</strong><span>{{ $party->email ?? '-' }}</span></div>
        <div><strong>{{ __('Enquiry Date:') }}</strong><span>{{ showDate($enquiry->created_at) }}</span></div>
        <div><strong>{{ __('Phone:') }}</strong><span>{{ $party->phone ?? '-' }}</span></div>
        <div><strong>{{ __('Expiry Date:') }}</strong><span>{{ showDate($enquiry->expiry_date) }}</span></div>
        <div><strong>{{ __('Activity:') }}</strong><span>{{ $enquiry->activity->name ?? '-' }}</span></div>
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
