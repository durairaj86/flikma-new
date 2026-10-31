<style>
    .tab-pane[id^="quotation"] .section { margin-bottom: 1.25rem; background: #fff; border: 1px solid #e8eaf3; border-radius: 12px; box-shadow: 0 1px 3px rgba(45, 58, 79, .06); overflow: hidden; }
    .tab-pane[id^="quotation"] .section > p { padding: 8px 14px; font-size: 13.5px; }
    .tab-pane[id^="quotation"] .section h6 {
        display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; color: #2d3a4f; margin: 0;
        background: linear-gradient(90deg, #eef0ff, #f8f9ff 70%); padding: 10px 14px; border-bottom: 1px solid #e8eaf3; border-inline-start: 4px solid #6366f1;
    }
    .tab-pane[id^="quotation"] .section h6 .sec-ico {
        width: 26px; height: 26px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;
        background: #6366f1; color: #fff; font-size: 13px; box-shadow: 0 3px 8px rgba(99, 102, 241, .35);
    }
    .tab-pane[id^="quotation"] .q-tbl { border: 1px solid #e8eaf3; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(45, 58, 79, .06); }
    .tab-pane[id^="quotation"] table.table { font-size: 13.5px; margin: 0; }
    .tab-pane[id^="quotation"] .q-tbl table.table { border-collapse: separate; border-spacing: 0; }
    .tab-pane[id^="quotation"] table.table th { background: #2d3a4f; border-color: #2d3a4f; color: #fff; font-weight: 600; white-space: nowrap; font-size: .75rem; letter-spacing: .03em; }
    .tab-pane[id^="quotation"] table.table td { border-color: #f0f1f7; }
    .tab-pane[id^="quotation"] table.table tfoot td { background: #eef0ff; color: #4338ca; font-size: 1rem; border-top: 2px solid #6366f1; }
    .tab-pane[id^="quotation"] .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0 1.5rem; font-size: 13.5px; line-height: 1.6; padding: 6px 14px 10px; }
    .tab-pane[id^="quotation"] .info-grid div { display: flex; justify-content: space-between; border-bottom: 1px solid #f0f1f7; padding: 7px 4px; border-radius: 6px; transition: background .15s; }
    .tab-pane[id^="quotation"] .info-grid div:hover { background: #f7f8ff; }
    .tab-pane[id^="quotation"] .info-grid strong { color: #7a8196; min-width: 140px; font-weight: 600; font-size: 12.5px; }
    .tab-pane[id^="quotation"] .info-grid span { color: #1f2937; font-weight: 600; flex: 1; text-align: left; margin-left: 8px; }
    .tab-pane[id^="quotation"] .info-grid span.is-empty { color: #c3c7d4; font-weight: 400; }
    .tab-pane[id^="quotation"] .info-grid .col-span-2 { grid-column: span 2; }
    .tab-pane[id^="quotation"] .qh {
        position: relative; overflow: hidden; margin-bottom: 1.25rem; padding: 18px 20px 16px; border-radius: 14px; color: #fff;
        background: linear-gradient(135deg, #2d3a4f 0%, #4f46e5 70%, #8b5cf6 100%); box-shadow: 0 8px 22px rgba(79, 70, 229, .28);
    }
    .tab-pane[id^="quotation"] .qh::after { content: ''; position: absolute; right: -60px; top: -60px; width: 220px; height: 220px; border-radius: 50%; background: rgba(255, 255, 255, .08); }
    .tab-pane[id^="quotation"] .qh-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; position: relative; z-index: 1; }
    .tab-pane[id^="quotation"] .qh-no { font-size: 1.35rem; font-weight: 800; }
    .tab-pane[id^="quotation"] .qh-party { opacity: .85; font-size: .9rem; }
    .tab-pane[id^="quotation"] .qh-chip { display: inline-flex; align-items: center; gap: 6px; padding: 3px 12px; border-radius: 999px; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; background: rgba(255, 255, 255, .18); }
    .tab-pane[id^="quotation"] .qh-chip i { font-size: .5rem; color: #fbbf24; }
    .tab-pane[id^="quotation"] .qh-refs { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: 8px; position: relative; z-index: 1; }
    .tab-pane[id^="quotation"] .qh-refs a, .tab-pane[id^="quotation"] .qh-refs span.qh-ref { background: rgba(255, 255, 255, .16); color: #fff; border-radius: 999px; padding: 2px 10px; font-size: .74rem; font-weight: 600; text-decoration: none; }
    .tab-pane[id^="quotation"] .qh-refs a:hover { background: rgba(255, 255, 255, .3); }
    .tab-pane[id^="quotation"] .qh-route { display: flex; align-items: center; gap: 12px; margin: 16px 0 14px; position: relative; z-index: 1; }
    .tab-pane[id^="quotation"] .qh-port { font-weight: 700; font-size: .95rem; }
    .tab-pane[id^="quotation"] .qh-port small { display: block; font-weight: 500; opacity: .7; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; }
    .tab-pane[id^="quotation"] .qh-line { flex: 1; position: relative; border-top: 2px dashed rgba(255, 255, 255, .45); min-width: 40px; }
    .tab-pane[id^="quotation"] .qh-line i { position: absolute; left: 50%; top: -15px; transform: translateX(-50%); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #fff; color: #4f46e5; font-size: .9rem; box-shadow: 0 3px 10px rgba(0, 0, 0, .25); }
    .tab-pane[id^="quotation"] .qh-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 10px; position: relative; z-index: 1; }
    .tab-pane[id^="quotation"] .qh-stat { background: rgba(255, 255, 255, .13); border-radius: 10px; padding: 8px 12px; }
    .tab-pane[id^="quotation"] .qh-stat small { display: block; font-size: .66rem; text-transform: uppercase; letter-spacing: .06em; opacity: .75; font-weight: 600; }
    .tab-pane[id^="quotation"] .qh-stat b { font-size: .98rem; font-weight: 700; }
    .tab-pane[id^="quotation"] .qh-stat.total { background: #fff; color: #4338ca; }
    .tab-pane[id^="quotation"] .qtn-timeline { list-style: none; margin: 0; padding: 12px 14px 12px .75rem; }
    .tab-pane[id^="quotation"] .qtn-timeline li { position: relative; padding: 0 0 .85rem 2rem; font-size: 13.5px; }
    .tab-pane[id^="quotation"] .qtn-timeline li:last-child { padding-bottom: 0; }
    .tab-pane[id^="quotation"] .qtn-timeline li::before { content: ''; position: absolute; left: .7rem; top: 1.5rem; bottom: 0; width: 2px; background: #e2e8f0; }
    .tab-pane[id^="quotation"] .qtn-timeline li:last-child::before { display: none; }
    .tab-pane[id^="quotation"] .qtn-timeline .dot { position: absolute; left: 0; top: 0; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #eef0ff; color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; }
    .tab-pane[id^="quotation"] .qtn-timeline .t-label { font-weight: 600; color: #0f172a; }
    .tab-pane[id^="quotation"] .qtn-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    @media (max-width: 700px) { .tab-pane[id^="quotation"] .info-grid { grid-template-columns: 1fr; } .tab-pane[id^="quotation"] .info-grid .col-span-2 { grid-column: span 1; } }
</style>

<!-- General Tab -->
<div class="tab-pane fade show active" id="quotationGeneralTab" role="tabpanel">

    <?php
        $mode = strtolower((string) $quotation->shipment_mode);
        $modeIcon = ['sea' => 'bi-water', 'air' => 'bi-airplane-fill', 'land' => 'bi-truck', 'road' => 'bi-truck'][$mode] ?? 'bi-box-seam';
        $qStatus = \App\Enums\QuotationEnum::tryFrom($quotation->status)?->label() ?? '-';
        $pc = $quotation->customer; $pp = $quotation->prospect;
        $partyName = $pc->name_en ?? $pp->name ?? '-';
        $partyEmail = $pc->email ?? $pp->email ?? '-';
        $partyPhone = $pc->phone ?? $pp->phone ?? '-';
        $qTotal = $quotation->charges ? $quotation->charges->sum('local_amount') : 0;
    ?>
    <div class="qh">
        <div class="qh-top">
            <div>
                <div class="qh-no">{{ $quotation->row_no }}</div>
                <div class="qh-party">@if($quotation->customer_id)<a href="#" class="open-linked text-white" data-type="customer" data-id="{{ $quotation->customer_id }}" data-title="{{ $partyName }}">{{ $partyName }} <i class="bi bi-box-arrow-up-right small"></i></a>@else{{ $partyName }}@endif</div>
            </div>
            <span class="qh-chip"><i class="bi bi-circle-fill"></i>{{ $qStatus }}</span>
        </div>
        @if($enquiryNo || $jobNo)
            <div class="qh-refs">
                @if($enquiryNo)
                    @if($quotation->enquiry_id)<a href="#" class="open-linked" data-type="enquiry" data-id="{{ $quotation->enquiry_id }}" data-title="{{ $enquiryNo }}" title="{{ __('Open enquiry') }}">{{ __('Enquiry') }}: {{ $enquiryNo }} <i class="bi bi-box-arrow-up-right small"></i></a>@else<span class="qh-ref">{{ __('Enquiry') }}: {{ $enquiryNo }}</span>@endif
                @endif
                @if($jobNo)
                    @if($quotation->job_id)<a href="#" class="open-linked" data-type="job" data-id="{{ $quotation->job_id }}" data-title="{{ $jobNo }}" title="{{ __('Open job') }}">{{ __('Job') }}: {{ $jobNo }} <i class="bi bi-box-arrow-up-right small"></i></a>@else<span class="qh-ref">{{ __('Job') }}: {{ $jobNo }}</span>@endif
                @endif
            </div>
        @endif
        <div class="qh-route">
            <div class="qh-port"><small>{{ __('POL') }}</small>{{ $quotation->pol ?: '-' }}</div>
            <div class="qh-line"><i class="bi {{ $modeIcon }}"></i></div>
            <div class="qh-port text-end"><small>{{ __('POD') }}</small>{{ $quotation->pod ?: '-' }}</div>
        </div>
        <div class="qh-stats">
            <div class="qh-stat"><small>{{ __('Quotation Date') }}</small><b>{{ showDate($quotation->posted_at) }}</b></div>
            <div class="qh-stat"><small>{{ __('Valid Until') }}</small><b>{{ showDate($quotation->valid_until) }}</b></div>
            <div class="qh-stat"><small>{{ __('Incoterm') }}</small><b>{{ $quotation->incoterm ?: '-' }}</b></div>
            <div class="qh-stat"><small>{{ __('Carrier') }}</small><b>{{ $quotation->carrier ?: '-' }}</b></div>
            <div class="qh-stat total"><small>{{ __('Total') }}</small><b>{{ number_format($qTotal, 2) }}</b></div>
        </div>
    </div>

    <div class="section">
        <h6><span class="sec-ico"><i class="bi bi-person-vcard"></i></span>{{ __('Party & Quotation Information') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Party') }}:</strong><span>@if($quotation->customer_id)<a href="#" class="open-linked text-primary text-decoration-none" data-type="customer" data-id="{{ $quotation->customer_id }}" data-title="{{ $partyName }}">{{ $partyName }}</a>@else{{ $partyName }}@endif</span></div>
            <div><strong>{{ __('Quote No') }}:</strong><span>#{{ $quotation->row_no }}</span></div>
            <div><strong>{{ __('Email') }}:</strong><span>{{ $partyEmail }}</span></div>
            <div><strong>{{ __('Quotation Date') }}:</strong><span>{{ showDate($quotation->posted_at) }}</span></div>
            <div><strong>{{ __('Phone') }}:</strong><span>{{ $partyPhone }}</span></div>
            <div><strong>{{ __('Valid Until') }}:</strong><span>{{ showDate($quotation->valid_until) }}</span></div>
            <div><strong>{{ __('Prepared By') }}:</strong><span>{{ $quotation->prepared_by ?? '-' }}</span></div>
            <div><strong>{{ __('Shipment Mode') }}:</strong><span>{{ shipmentMode()[$quotation->shipment_mode] ?? '-' }}</span></div>
            <div><strong>{{ __('Department') }}:</strong><span>{{ $quotation->activity->name ?? '-' }}</span></div>
            <div><strong>{{ __('Status') }}:</strong><span>{{ \App\Enums\QuotationEnum::tryFrom($quotation->status)?->label() ?? '-' }}</span></div>
        </div>
    </div>

    <div class="section">
        <h6><span class="sec-ico"><i class="bi bi-signpost-split"></i></span>{{ __('Shipment Routing') }}</h6>
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
            <h6><span class="sec-ico"><i class="bi bi-chat-left-text"></i></span>{{ __('Additional Information') }}</h6>
            @if($quotation->terms)
                <p class="mb-2"><strong>{{ __('Terms & Conditions') }}:</strong> {{ $quotation->terms }}</p>
            @endif
            @if($quotation->notes)
                <p class="mb-0"><strong>{{ __('Notes') }}:</strong> {{ $quotation->notes }}</p>
            @endif
        </div>
    @endif

</div>

<!-- Container Tab -->
<div class="tab-pane fade" id="quotationContainerTab" role="tabpanel">
    @if($quotation->containers && $quotation->containers->count())
        <div class="table-responsive q-tbl">
            <table class="table table-sm align-middle">
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
        <div class="table-responsive q-tbl">
            <table class="table table-sm align-middle">
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
        <div class="table-responsive q-tbl">
            <table class="table table-sm align-middle">
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

<!-- Time Frame Tab -->
<div class="tab-pane fade" id="quotationTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6><span class="sec-ico"><i class="bi bi-clock-history"></i></span>{{ __('Time Frame') }}</h6>
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

<script>
    (function () {
        document.querySelectorAll('.tab-pane[id^="quotation"] .info-grid span').forEach(function (el) {
            if (el.textContent.trim() === '-') el.classList.add('is-empty');
        });
    })();
</script>
