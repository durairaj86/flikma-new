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
        border-left: 4px solid #0d6efd;
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
</style>

<!-- General Tab -->
<div class="tab-pane fade show active" id="quotationGeneralTab" role="tabpanel">

    <div class="section">
        <h6>Party &amp; Quotation Information</h6>
        <div class="info-grid">
            <div><strong>Party:</strong><span>{{ $quotation->party->name ?? '-' }}</span></div>
            <div><strong>Quote No:</strong><span>#{{ $quotation->row_no }}</span></div>
            <div><strong>Email:</strong><span>{{ $quotation->party->email ?? '-' }}</span></div>
            <div><strong>Quotation Date:</strong><span>{{ showDate($quotation->posted_at) }}</span></div>
            <div><strong>Phone:</strong><span>{{ $quotation->party->phone ?? '-' }}</span></div>
            <div><strong>Valid Until:</strong><span>{{ showDate($quotation->valid_until) }}</span></div>
            <div><strong>Prepared By:</strong><span>{{ $quotation->prepared_by ?? '-' }}</span></div>
            <div><strong>Shipment Mode:</strong><span>{{ shipmentMode()[$quotation->shipment_mode] ?? '-' }}</span></div>
            <div><strong>Activity:</strong><span>{{ $quotation->activity->name ?? '-' }}</span></div>
            <div><strong>Status:</strong><span>{{ \App\Enums\QuotationEnum::tryFrom($quotation->status)?->label() ?? '-' }}</span></div>
        </div>
    </div>

    <div class="section">
        <h6>Shipment Routing</h6>
        <div class="info-grid">
            <div><strong>Place of Receipt:</strong><span>{{ $quotation->place_of_receipt ?? '-' }}</span></div>
            <div><strong>POL:</strong><span>{{ $quotation->pol ?? '-' }}</span></div>
            <div><strong>POD:</strong><span>{{ $quotation->pod ?? '-' }}</span></div>
            <div><strong>Place of Delivery:</strong><span>{{ $quotation->place_of_delivery ?? '-' }}</span></div>
            <div><strong>Final Destination:</strong><span>{{ $quotation->final_destination ?? '-' }}</span></div>
            <div><strong>Incoterm:</strong><span>{{ $quotation->incoterm ?? '-' }}</span></div>
            <div><strong>Carrier:</strong><span>{{ $quotation->carrier ?? '-' }}</span></div>
        </div>
    </div>

    @if($quotation->terms || $quotation->notes)
        <div class="section">
            <h6>Additional Information</h6>
            @if($quotation->terms)
                <p class="mb-2"><strong>Terms &amp; Conditions:</strong> {{ $quotation->terms }}</p>
            @endif
            @if($quotation->notes)
                <p class="mb-0"><strong>Notes:</strong> {{ $quotation->notes }}</p>
            @endif
        </div>
    @endif

</div>

<!-- Container Tab -->
<div class="tab-pane fade" id="quotationContainerTab" role="tabpanel">
    @if($quotation->containers && $quotation->containers->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th><th>Size</th><th>Container No</th><th>Seal No</th>
                    <th>Gross Wt</th><th>Net Wt</th><th>CBM</th><th>Hazardous</th>
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
            No containers added to this quotation.
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
                    <th>#</th><th>Commodity</th><th>Description</th><th>HS Code</th>
                    <th>L</th><th>W</th><th>H</th><th>Weight</th>
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
            No packages added to this quotation.
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
                    <th>#</th><th>Charge Description</th><th>Unit</th><th class="text-end">Qty</th>
                    <th class="text-end">Rate</th><th>Currency</th><th class="text-end">FCY Amount</th><th class="text-end">Local Amount</th>
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
                    <td colspan="7" class="text-end">Total</td>
                    <td class="text-end">{{ number_format($quotation->charges->sum('local_amount'), 2) }}</td>
                </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-receipt fs-2 mb-2 d-block"></i>
            No charges added to this quotation.
        </div>
    @endif
</div>
