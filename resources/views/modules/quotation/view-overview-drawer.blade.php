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
        font-size: 13.5px;
    }
    table.table th {
        background: #f8f9fa;
        font-weight: 600;
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

<!-- General Tab -->
<div class="tab-pane fade show active" id="quotationGeneralTab" role="tabpanel">

    <div class="qtn-refs">
        <span><span class="ref-label">{{ __('Quote No') }}:</span><span class="ref-value">{{ $quotation->row_no }}</span></span>
        @if($enquiryNo)
            <span><span class="ref-label">{{ __('Enquiry') }}:</span><span class="ref-value">{{ $enquiryNo }}</span></span>
        @endif
        @if($jobNo)
            <span><span class="ref-label">{{ __('Job') }}:</span><span class="ref-value">{{ $jobNo }}</span></span>
        @endif
    </div>

    <div class="section">
        <h6>{{ __('Party & Quotation Information') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Party') }}:</strong><span>{{ $quotation->party->name ?? '-' }}</span></div>
            <div><strong>{{ __('Quote No') }}:</strong><span>#{{ $quotation->row_no }}</span></div>
            <div><strong>{{ __('Email') }}:</strong><span>{{ $quotation->party->email ?? '-' }}</span></div>
            <div><strong>{{ __('Quotation Date') }}:</strong><span>{{ showDate($quotation->posted_at) }}</span></div>
            <div><strong>{{ __('Phone') }}:</strong><span>{{ $quotation->party->phone ?? '-' }}</span></div>
            <div><strong>{{ __('Valid Until') }}:</strong><span>{{ showDate($quotation->valid_until) }}</span></div>
            <div><strong>{{ __('Prepared By') }}:</strong><span>{{ $quotation->prepared_by ?? '-' }}</span></div>
            <div><strong>{{ __('Shipment Mode') }}:</strong><span>{{ shipmentMode()[$quotation->shipment_mode] ?? '-' }}</span></div>
            <div><strong>{{ __('Department') }}:</strong><span>{{ $quotation->activity->name ?? '-' }}</span></div>
            <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\QuotationEnum::tryFrom($quotation->status)?->label() ?? '-' }}</span></div>
        </div>
    </div>

    <div class="section">
        <h6>{{ __('Shipment Routing') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Place of Receipt') }}:</strong><span>{{ $quotation->place_of_receipt ?? '-' }}</span></div>
            <div><strong>{{ __('POL') }}:</strong><span>{{ $quotation->pol ?? '-' }}</span></div>
            <div><strong>{{ __('POD') }}:</strong><span>{{ $quotation->pod ?? '-' }}</span></div>
            <div><strong>{{ __('Place of Delivery') }}:</strong><span>{{ $quotation->place_of_delivery ?? '-' }}</span></div>
            <div><strong>{{ __('Final Destination') }}:</strong><span>{{ $quotation->final_destination ?? '-' }}</span></div>
            <div><strong>{{ __('Incoterm') }}:</strong><span>{{ $quotation->incoterm ?? '-' }}</span></div>
            <div><strong>{{ __('Carrier') }}:</strong><span>{{ $quotation->carrier ?? '-' }}</span></div>
        </div>
    </div>

    @if($quotation->terms || $quotation->notes)
        <div class="section">
            <h6>{{ __('Additional Information') }}</h6>
            @if($quotation->terms)
                <p class="mb-2"><strong>{{ __('Terms & Conditions') }}:</strong> {{ $quotation->terms }}</p>
            @endif
            @if($quotation->notes)
                <p class="mb-0"><strong>{{ __('Notes') }}:</strong> {{ $quotation->notes }}</p>
            @endif
        </div>
    @endif

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

<!-- Container Tab -->
<div class="tab-pane fade" id="quotationContainerTab" role="tabpanel">
    @if($quotation->containers && $quotation->containers->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th><th>{{ __('Size') }}</th><th>{{ __('Container No') }}</th><th>{{ __('Seal No') }}</th>
                    <th>{{ __('Gross Wt') }}</th><th>{{ __('Net Wt') }}</th><th>CBM</th><th>{{ __('Hazardous') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($quotation->containers as $i => $c)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $c->container_size }}</td>
                        <td>{{ $c->container_number ?? '-' }}</td>
                        <td>{{ $c->seal_number ?? '-' }}</td>
                        <td>{{ $c->gross_weight }}</td>
                        <td>{{ $c->net_weight }}</td>
                        <td>{{ $c->volume }}</td>
                        <td>{{ $c->hazardous }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-box-seam fs-2 mb-2 d-block"></i>
            {{ __('No containers added to this quotation.') }}
        </div>
    @endif
</div>

<!-- Package Tab -->
<div class="tab-pane fade" id="quotationPackageTab" role="tabpanel">
    @if($quotation->packages && $quotation->packages->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th><th>{{ __('Commodity') }}</th><th>{{ __('Description') }}</th><th>{{ __('HS Code') }}</th>
                    <th>L</th><th>W</th><th>H</th><th>{{ __('Weight') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($quotation->packages as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ commodityType()[$p->commodity_type] ?? $p->commodity_type }}</td>
                        <td>{{ $p->description_goods }}</td>
                        <td>{{ $p->hs_code }}</td>
                        <td>{{ $p->length }}</td>
                        <td>{{ $p->width }}</td>
                        <td>{{ $p->height }}</td>
                        <td>{{ $p->package_weight }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-boxes fs-2 mb-2 d-block"></i>
            {{ __('No packages added to this quotation.') }}
        </div>
    @endif
</div>

<!-- Charges Tab -->
<div class="tab-pane fade" id="quotationChargesTab" role="tabpanel">
    @if($quotation->charges && $quotation->charges->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th><th>{{ __('Charge Description') }}</th><th>{{ __('Unit') }}</th><th class="text-end">{{ __('Qty') }}</th>
                    <th class="text-end">{{ __('Rate') }}</th><th>{{ __('Currency') }}</th><th class="text-end">{{ __('FCY Amount') }}</th><th class="text-end">{{ __('Local Amount') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($quotation->charges as $i => $charge)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $charge->charge_description }}</td>
                        <td>{{ $charge->unit ?? '-' }}</td>
                        <td class="text-end">{{ $charge->qty }}</td>
                        <td class="text-end">{{ number_format($charge->amount_per_qty ?? 0, 2) }}</td>
                        <td>{{ $charge->currency ?? '-' }}</td>
                        <td class="text-end">{{ number_format($charge->fcy_amount ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($charge->local_amount ?? 0, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr class="fw-bold">
                    <td colspan="7" class="text-end">{{ __('Total') }}</td>
                    <td class="text-end">{{ number_format($quotation->charges->sum('local_amount'), 2) }}</td>
                </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-receipt fs-2 mb-2 d-block"></i>
            {{ __('No charges added to this quotation.') }}
        </div>
    @endif
</div>
