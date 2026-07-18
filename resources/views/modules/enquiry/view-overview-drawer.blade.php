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
    <h6>Party &amp; Enquiry Information</h6>
    <div class="info-grid">
        <div><strong>Party:</strong><span>{{ $party->name ?? '-' }}</span></div>
        <div><strong>Enquiry No:</strong><span>#{{ $enquiry->row_no }}</span></div>
        <div><strong>Email:</strong><span>{{ $party->email ?? '-' }}</span></div>
        <div><strong>Enquiry Date:</strong><span>{{ showDate($enquiry->created_at) }}</span></div>
        <div><strong>Phone:</strong><span>{{ $party->phone ?? '-' }}</span></div>
        <div><strong>Expiry Date:</strong><span>{{ showDate($enquiry->expiry_date) }}</span></div>
        <div><strong>Activity:</strong><span>{{ $enquiry->activity->name ?? '-' }}</span></div>
        <div><strong>Status:</strong><span>{{ \App\Enums\EnquiryEnum::tryFrom($enquiry->status)?->label() ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>Shipment Details</h6>
    <div class="info-grid">
        <div><strong>Shipment Mode:</strong><span>{{ ucfirst($enquiry->shipment_mode ?? '-') }}</span></div>
        <div><strong>Category:</strong><span>{{ ucfirst($enquiry->shipment_category ?? '-') }}</span></div>
        <div><strong>Weight:</strong><span>{{ $enquiry->weight ?? '-' }} kg</span></div>
        <div><strong>Volume:</strong><span>{{ $enquiry->volume ?? '-' }} m&sup3;</span></div>
        <div><strong>Pickup Date:</strong><span>{{ showDate($enquiry->pickup_date) }}</span></div>
        <div><strong>Shipper:</strong><span>{{ $enquiry->shipper ?? '-' }}</span></div>
        <div><strong>Incoterm:</strong><span>{{ $enquiry->incoterm ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>POL &amp; POD</h6>
    <div class="info-grid">
        <div><strong>Place of Receipt:</strong><span>{{ $enquiry->place_of_receipt ?? '-' }}</span></div>
        <div><strong>Origin City:</strong><span>{{ $enquiry->origin_city ?? '-' }}</span></div>
        <div><strong>POL:</strong><span>{{ $enquiry->pol ?? '-' }}</span></div>
        <div><strong>POD:</strong><span>{{ $enquiry->pod ?? '-' }}</span></div>
    </div>
</div>

@if($enquiry->remark)
    <div class="section">
        <h6>Notes</h6>
        <p class="mb-0">{{ $enquiry->remark }}</p>
    </div>
@endif
