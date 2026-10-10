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
    .list-group-item {
        font-size: 14px;
        padding: 10px 0;
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
<div class="tab-pane fade show active" id="jobGeneralTab" role="tabpanel">
    <?php
        $v = fn($x) => filled($x) ? $x : '-';
        $d = function ($x) { try { return filled($x) ? \Illuminate\Support\Carbon::parse($x)->format('d-M-Y') : '-'; } catch (\Throwable) { return $x ?: '-'; } };
        $c = $job->clearance;
        $people = \App\Models\User::withoutGlobalScopes()->whereIn('id', array_filter([$job->salesperson_id, $job->prepared_by, $job->created_by]))->pluck('name', 'id');
        $jobStatus = ['1' => 'Pending', '2' => 'Completed', '3' => 'Cancelled', '4' => 'Trashed'][(string) (int) $job->status] ?? 'Pending';
    ?>

    <div class="section">
        <h6>{{ __('Customer & Job Information') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Customer:') }}</strong><span>{!! e($v($job->customer->name_en ?? null)) !!}</span></div>
            <div><strong>{{ __('Job No:') }}</strong><span>{!! e($job->row_no ? '#'.$job->row_no : '-') !!}</span></div>
            <div><strong>{{ __('Email:') }}</strong><span>{!! e($v($job->customer->email ?? null)) !!}</span></div>
            <div><strong>{{ __('Phone:') }}</strong><span>{!! e($v($job->customer->phone ?? null)) !!}</span></div>
            <div><strong>{{ __('Posting Date:') }}</strong><span>{!! e($d($job->posted_at)) !!}</span></div>
            <div><strong>{{ __('Status:') }}</strong><span>{!! e(__($jobStatus)) !!}</span></div>
            <div><strong>{{ __('Department:') }}</strong><span>{!! e($v($job->activity->name ?? null)) !!}</span></div>
            <div><strong>{{ __('Shipment Mode:') }}</strong><span>{!! e($v(ucfirst((string) $job->shipment_mode))) !!}</span></div>
            <div><strong>{{ __('Shipment Category:') }}</strong><span>{!! e($v(ucfirst((string) $job->shipment_category))) !!}</span></div>
            <div><strong>{{ __('Cargo Type:') }}</strong><span>{!! e($v($job->cargo_type)) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('Services:') }}</strong><span>{!! e($job->services ? services($job->services) : "-") !!}</span></div>
            <div><strong>{{ __('Salesperson:') }}</strong><span>{!! e($v($people[$job->salesperson_id] ?? null)) !!}</span></div>
            <div><strong>{{ __('Prepared By:') }}</strong><span>{!! e($v($people[$job->prepared_by] ?? $people[$job->created_by] ?? null)) !!}</span></div>
            <div><strong>{{ __('Customer Reference:') }}</strong><span>{!! e($v($job->client_ref ?: $job->client_reference_no)) !!}</span></div>
            <div><strong>{{ __('Quotation:') }}</strong><span>{!! e($v($job->quotation?->row_no ?? null)) !!}</span></div>
            <div><strong>{{ __('Tracking No:') }}</strong><span>{!! e($v($job->tracking_number)) !!}</span></div>
        </div>
    </div>

    <div class="section">
        <h6>{{ __('Shipment Parties') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Shipper:') }}</strong><span>{!! e($v($job->shipper)) !!}</span></div>
            <div><strong>{{ __('Consignee:') }}</strong><span>{!! e($v($job->consignee)) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('Shipper Address:') }}</strong><span>{!! e($v($job->shipper_address)) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('Consignee Address:') }}</strong><span>{!! e($v($job->consignee_address)) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('Pickup Address:') }}</strong><span>{!! e($v($job->pickup_address)) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('Delivery Address:') }}</strong><span>{!! e($v($job->delivery_address)) !!}</span></div>
        </div>
    </div>

    <div class="section">
        <h6>{{ __('Cargo & Transport') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Commodity:') }}</strong><span>{!! e($v($job->commodity)) !!}</span></div>
            <div><strong>{{ __('Incoterm:') }}</strong><span>{!! e($v($job->incoterm ?: $job->incoterms)) !!}</span></div>
            <div><strong>{{ __('Weight:') }}</strong><span>{!! e(filled($job->weight) && (float) $job->weight ? number_format((float) $job->weight, 2).' kg' : '-') !!}</span></div>
            <div><strong>{{ __('Volume:') }}</strong><span>{!! e(filled($job->volume) && (float) $job->volume ? number_format((float) $job->volume, 3).' CBM' : '-') !!}</span></div>
            <div><strong>{{ __('No. of Pieces:') }}</strong><span>{!! e($v($job->no_of_pieces)) !!}</span></div>
            <div><strong>{{ __('Carrier:') }}</strong><span>{!! e($v($job->carrier)) !!}</span></div>
            <div><strong>{{ __('Vessel / Flight No:') }}</strong><span>{!! e($v($job->voyage_flight_no)) !!}</span></div>
            <div><strong>{{ __('B/L No:') }}</strong><span>{!! e($v($job->hbl_number)) !!}</span></div>
            <div><strong>{{ __('AWB No:') }}</strong><span>{!! e($v($job->awb_number)) !!}</span></div>
            <div><strong>{{ __('Shipping Ref:') }}</strong><span>{!! e($v($job->shipping_ref)) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('Requirements:') }}</strong><span>{!! e(is_array($job->cargo_requirements) && $job->cargo_requirements ? implode(", ", $job->cargo_requirements) : $v($job->requirements)) !!}</span></div>
        </div>
    </div>

    <div class="section">
        <h6>{{ __('Routing & Schedule') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Place of Receipt:') }}</strong><span>{!! e($v($job->place_of_receipt)) !!}</span></div>
            <div><strong>{{ __('POL:') }}</strong><span>{!! e($v($job->pol_name ?: $job->pol)) !!}</span></div>
            <div><strong>{{ __('POD:') }}</strong><span>{!! e($v($job->pod_name ?: $job->pod)) !!}</span></div>
            <div><strong>{{ __('Place of Delivery:') }}</strong><span>{!! e($v($job->place_of_delivery)) !!}</span></div>
            <div><strong>{{ __('Final Destination:') }}</strong><span>{!! e($v($job->final_destination)) !!}</span></div>
            <div><strong>{{ __('Transshipment Port:') }}</strong><span>{!! e($v($job->transshipment_port)) !!}</span></div>
            <div><strong>{{ __('ETD:') }}</strong><span>{!! e($d($job->etd)) !!}</span></div>
            <div><strong>{{ __('ETA:') }}</strong><span>{!! e($d($job->eta)) !!}</span></div>
            <div><strong>{{ __('ATD (Actual):') }}</strong><span>{!! e($d($job->atd)) !!}</span></div>
            <div><strong>{{ __('ATA (Actual):') }}</strong><span>{!! e($d($job->ata)) !!}</span></div>
            <div><strong>{{ __('Pickup Date:') }}</strong><span>{!! e($d($job->pickup_date)) !!}</span></div>
            <div><strong>{{ __('Delivery Date:') }}</strong><span>{!! e($d($job->delivery_date)) !!}</span></div>
        </div>
    </div>

    <div class="section">
        <h6>{{ __('Remarks') }}</h6>
        <div class="info-grid">
            <div class="col-span-2"><strong>{{ __('Remarks:') }}</strong><span>{!! e($v($job->remarks)) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('Terms:') }}</strong><span>{!! e($v($job->terms)) !!}</span></div>
        </div>
    </div>

</div>

<!-- Clearance Tab -->
<div class="tab-pane fade" id="jobClearanceTab" role="tabpanel">
    <?php $cst = strtolower((string) ($job->clearance->clearance_status ?? '')); ?>
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="fw-semibold">{{ __('Clearance Status') }}</span>
        <span class="badge bg-{{ $cst === 'cleared' ? 'success' : ($cst === 'on-hold' ? 'danger' : ($cst ? 'info' : 'secondary')) }}-subtle text-{{ $cst === 'cleared' ? 'success' : ($cst === 'on-hold' ? 'danger' : ($cst ? 'info' : 'secondary')) }}-emphasis">{{ $cst ? \Illuminate\Support\Str::headline($cst) : __('Not started') }}</span>
        <a href="/operation/customs" class="ms-auto small">{{ __('Open Customs Clearance') }}</a>
    </div>
    <div class="section">
        <h6>{{ __('Customs & Clearance') }}</h6>
        <div class="info-grid">
            <div><strong>{{ __('Clearance Status:') }}</strong><span>{!! e(\Illuminate\Support\Str::headline((string) ($c->clearance_status ?? '')) ?: '-') !!}</span></div>
            <div><strong>{{ __('Clearance Date:') }}</strong><span>{!! e($d($c->clearance_date ?? null)) !!}</span></div>
            <div><strong>{{ __('Type of Clearance:') }}</strong><span>{!! e($v($c->type_of_clearance ?? $job->type_of_clearance)) !!}</span></div>
            <div><strong>{{ __('Customs Broker:') }}</strong><span>{!! e($v($c->customs_broker ?? $job->customs_broker)) !!}</span></div>
            <div><strong>{{ __('Port Clearance:') }}</strong><span>{!! e($v($c->port_clearance ?? $job->port_clearance)) !!}</span></div>
            <div><strong>{{ __('HS Code:') }}</strong><span>{!! e($v($c->hs_code ?? $job->hs_code)) !!}</span></div>
            <div><strong>{{ __('Declaration No:') }}</strong><span>{!! e($v($c->declaration_no ?? $job->declaration_no)) !!}</span></div>
            <div><strong>{{ __('Documents Received:') }}</strong><span>{!! e($d($c->doc_received ?? $job->doc_received)) !!}</span></div>
            <div><strong>{{ __('B/L Received:') }}</strong><span>{!! e($d($c->bl_receive_date ?? $job->bl_receive_date)) !!}</span></div>
            <div><strong>{{ __('Original Docs Received:') }}</strong><span>{!! e($d($c->original_doc_received ?? $job->original_doc_received)) !!}</span></div>
            <div><strong>{{ __('SABER Certificate:') }}</strong><span>{!! e($d($c->saber_certificate_date ?? $job->saber_certificate_date)) !!}</span></div>
            <div><strong>{{ __('Bayan Date:') }}</strong><span>{!! e($d($c->bayan_date ?? $job->bayan_date)) !!}</span></div>
            <div><strong>{{ __('Bayan No:') }}</strong><span>{!! e($v($c->bayan_no ?? $job->bayan_no)) !!}</span></div>
            <div><strong>{{ __('D/O Date:') }}</strong><span>{!! e($d($c->do_date ?? $job->do_date)) !!}</span></div>
            <div><strong>{{ __('D/O No:') }}</strong><span>{!! e($v($c->do_no ?? $job->do_no)) !!}</span></div>
            <div><strong>{{ __('Demurrage Date:') }}</strong><span>{!! e($d($c->demurrage_date ?? $job->demurrage_date)) !!}</span></div>
            <div><strong>{{ __('Duty Amount:') }}</strong><span>{!! e(number_format((float) ($c->duty_amount ?? $job->duty_amount ?? 0), 2)) !!}</span></div>
            <div><strong>{{ __('Duty (Client):') }}</strong><span>{!! e(number_format((float) ($c->duty_amount_client ?? $job->duty_amount_client ?? 0), 2)) !!}</span></div>
            <div><strong>{{ __('Lab Clearance:') }}</strong><span>{!! e(($c->lab_clearance ?? $job->lab_clearance) ? __('Yes') : __('No')) !!}</span></div>
            <div><strong>{{ __('Inspection:') }}</strong><span>{!! e(($c->inspection ?? $job->inspection) ? __('Yes') : __('No')) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('D/O Remarks:') }}</strong><span>{!! e($v($c->do_remarks ?? $job->do_remarks)) !!}</span></div>
            <div class="col-span-2"><strong>{{ __('Clearance Remarks:') }}</strong><span>{!! e($v($c->clearance_remarks ?? null)) !!}</span></div>
        </div>
    </div>

</div>

<!-- Container Tab -->
<div class="tab-pane fade" id="jobContainerTab" role="tabpanel">
    @if($job->containers->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th><th>{{ __('Size') }}</th><th>{{ __('Type') }}</th><th>{{ __('Container No') }}</th><th>{{ __('Seal No') }}</th>
                    <th>{{ __('Carrier') }}</th><th>{{ __('Vessel / Voyage') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Pcs') }}</th><th>{{ __('HS Code') }}</th><th>{{ __('Gross') }}</th><th>{{ __('Net') }}</th><th>{{ __('Volume') }}</th><th>{{ __('Hazardous') }}</th><th>{{ __('Temp Ctrl') }}</th><th>{{ __('Discharged') }}</th><th>{{ __('Gate Out') }}</th><th>{{ __('Returned') }}</th><th>{{ __('Free Days') }}</th><th>{{ __('Return Depot') }}</th><th>{{ __('Remarks') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($job->containers as $i => $c)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $c->container_size }}</td>
                        <td>{{ ucfirst($c->container_type ?? '') }}</td>
                        <td>{{ $c->container_number ?? '-' }}</td>
                        <td>{{ $c->seal_number ?? '-' }}</td>
                        <td>{{ $c->carrier ?: '-' }}</td>
                        <td>{{ trim(($c->vessel_name ?? '') . ' ' . ($c->voyage_no ?? '')) ?: '-' }}</td>
                        <td>{{ $c->qty ?? $c->quantity ?? '-' }} {{ $c->uom }}</td>
                        <td>{{ $c->no_of_pcs ?: '-' }}</td>
                        <td>{{ $c->hs_code ?: '-' }}</td>
                        <td>{{ $c->gross_weight }} {{ $c->weight_unit }}</td>
                        <td>{{ $c->net_weight }}</td>
                        <td>{{ $c->volume }}</td>
                        <td>{{ $c->hazardous == 'Yes' ? __('Yes') : __('No') }}</td>
                        <td>{{ $c->temp_controlled == 'Yes' ? __('Yes') : __('No') }}</td>
                        <td>{{ $c->discharged_at ? \Illuminate\Support\Carbon::parse($c->discharged_at)->format('d-M-Y') : '-' }}</td>
                        <td>{{ $c->gate_out_at ? \Illuminate\Support\Carbon::parse($c->gate_out_at)->format('d-M-Y') : '-' }}</td>
                        <td>{{ $c->returned_at ? \Illuminate\Support\Carbon::parse($c->returned_at)->format('d-M-Y') : '-' }}</td>
                        <td>{{ $c->free_days ?: '-' }}</td>
                        <td>{{ $c->return_depot ?: '-' }}</td>
                        <td>{{ $c->remarks ?? $c->consignment_remarks ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-box-seam fs-2 mb-2 d-block"></i>
            {{ __('No containers added to this job.') }}
        </div>
    @endif
</div>

<!-- Package Tab -->
<div class="tab-pane fade" id="jobPackageTab" role="tabpanel">
    @if($job->packages->count())
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                <tr>
                    <th>#</th><th>{{ __('Commodity') }}</th><th>{{ __('Type') }}</th><th>{{ __('Description') }}</th><th>{{ __('HS Code') }}</th>
                    <th>{{ __('Qty') }}</th><th>{{ __('Dimensions') }}</th><th>{{ __('Weight') }}</th><th>{{ __('Total Weight') }}</th><th>{{ __('Chargeable') }}</th><th>{{ __('Volume') }}</th><th>{{ __('Unit Price') }}</th><th>{{ __('Line Total') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($job->packages as $i => $p)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $p->commodity_type }}</td>
                        <td>{{ $p->package_type }}</td>
                        <td>{{ $p->description_goods }}</td>
                        <td>{{ $p->hs_code }}</td>
                        <td>{{ $p->quantity }}</td>
                        <td>{{ $p->length }} &times; {{ $p->width }} &times; {{ $p->height }}</td>
                        <td>{{ $p->package_weight }}</td>
                        <td>{{ $p->total_weight }}</td>
                        <td>{{ $p->chargeable_weight }}</td>
                        <td>{{ $p->volume }}</td>
                        <td>{{ $p->unit_price ? number_format($p->unit_price, 2) : '-' }}</td>
                        <td>{{ $p->line_total ? number_format($p->line_total, 2) : '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-boxes fs-2 mb-2 d-block"></i>
            {{ __('No packages added to this job.') }}
        </div>
    @endif
</div>

<!-- Documents Tab -->
<div class="tab-pane fade" id="jobFinanceTab" role="tabpanel">
    @php($sm = $fin['summary'])
    <div class="row g-2 mb-3">
        @foreach([['Invoiced', $sm['invoiced'], 'primary'], ['Credit Notes', $sm['credits'], 'secondary'], ['Collected', $sm['collected'], 'success'], ['Outstanding', $sm['outstanding'], $sm['outstanding'] > 0 ? 'warning' : 'success'],
                  ['Cost', $sm['cost'], 'secondary'], ['Paid Out', $sm['paid_out'], 'info'], ['Still to Pay', $sm['payable'], $sm['payable'] > 0 ? 'warning' : 'success'], ['Profit', $sm['profit'], $sm['profit'] >= 0 ? 'success' : 'danger']] as [$l, $v, $c])
            <div class="col-6 col-md-3"><div class="rounded-3 bg-{{ $c }}-subtle px-3 py-2 h-100">
                <div class="small text-uppercase text-{{ $c }}-emphasis fw-semibold" style="font-size:.68rem">{{ __($l) }}</div>
                <div class="fw-bold">{{ number_format($v, 2) }}</div></div></div>
        @endforeach
    </div>
    <div class="text-muted small mb-3">{{ __('Amounts are in the company currency. Cancelled documents and drafts are not counted.') }}</div>
    @foreach($fin['groups'] as $g)
        <div class="mb-3">
            <h6 class="fw-bold d-flex align-items-center gap-2">{{ __($g['label']) }} <span class="badge bg-light text-dark border">{{ count($g['rows']) }}</span></h6>
            @if(count($g['rows']))
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>{{ __('No') }}</th><th>{{ __('Date') }}</th><th>{{ __('Party') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Amount') }}</th><th class="text-end">{{ __('Paid') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach($g['rows'] as $r)
                            <tr class="{{ $r['cancelled'] ? 'text-muted text-decoration-line-through' : '' }}">
                                <td>@if($r['type'])<a href="#" class="open-linked fw-semibold" data-type="{{ $r['type'] }}" data-id="{{ $r['id'] }}" data-title="{{ $r['no'] }}">{{ $r['no'] }}</a>@else<span class="fw-semibold">{{ $r['no'] }}</span>@endif</td>
                                <td>{{ $r['date']?->format('d-M-Y') }}</td>
                                <td>{{ $r['party'] }}</td>
                                <td><span class="badge bg-{{ $r['approved'] ? 'success' : ($r['cancelled'] ? 'danger' : 'secondary') }}-subtle text-{{ $r['approved'] ? 'success' : ($r['cancelled'] ? 'danger' : 'secondary') }}-emphasis">{{ __($r['status']) }}</span></td>
                                <td class="text-end">{{ number_format($r['amount'], 2) }}</td>
                                <td class="text-end">{{ $r['paid'] !== null ? number_format($r['paid'], 2) : '' }}</td>
                                <td class="text-end">@if($r['files'])<i class="bi bi-paperclip" title="{{ $r['files'] }} {{ __('attachments') }}"></i> {{ $r['files'] }}@endif</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted small">{{ __('None for this job.') }}</div>
            @endif
        </div>
    @endforeach
</div>

<div class="tab-pane fade" id="jobTimelineTab" role="tabpanel">
    <style>
        .tl { position: relative; padding: .5rem 0; }
        .tl::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; background: #e5e7eb; transform: translateX(-1px); }
        .tl-item { position: relative; width: 50%; padding: 0 1.6rem .9rem 0; text-align: right; }
        .tl-item.right { margin-left: 50%; padding: 0 0 .9rem 1.6rem; text-align: left; }
        .tl-dot { position: absolute; top: 0; right: -13px; width: 26px; height: 26px; border-radius: 50%; background: #fff; border: 2px solid var(--c); color: var(--c); display: flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
        .tl-item.right .tl-dot { right: auto; left: -13px; }
        .tl-card { display: inline-block; max-width: 100%; border: 1px solid #e5e7eb; border-top: 3px solid var(--c); border-radius: 10px; padding: .45rem .75rem; background: #fff; text-align: left; }
        .tl-legend { display: flex; justify-content: space-between; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; margin-bottom: .5rem; }
        @media (max-width: 700px) { .tl::before { left: 12px; } .tl-item, .tl-item.right { width: 100%; margin-left: 0; padding: 0 0 .9rem 2.4rem; text-align: left; } .tl-dot, .tl-item.right .tl-dot { left: 0; right: auto; } }
    </style>
    <?php
        // Customer billing goes on the right, everything else (job life, tracking, customs, documents, costs) on the left.
        $rightKinds = ['customer_invoice', 'proforma', 'credit_note', 'collection'];
        $colors = ['job' => '#2563eb', 'tracking' => '#0891b2', 'customs' => '#7c3aed', 'document' => '#6b7280', 'supplier_invoice' => '#d97706', 'payment' => '#ea580c', 'expense' => '#b45309',
                   'customer_invoice' => '#16a34a', 'proforma' => '#65a30d', 'credit_note' => '#dc2626', 'collection' => '#059669'];
    ?>
    @if($fin['timeline']->count())
        <div class="tl-legend"><span>{{ __('Operations & costs') }}</span><span>{{ __('Customer billing') }}</span></div>
        <div class="tl">
            @foreach($fin['timeline'] as $t)
                @php($right = in_array($t['kind'], $rightKinds))
                <div class="tl-item {{ $right ? 'right' : '' }}" style="--c: {{ $colors[$t['kind']] ?? '#6b7280' }}">
                    <span class="tl-dot"><i class="bi {{ $t['icon'] }}"></i></span>
                    <div class="tl-card">
                        <div class="fw-semibold small">{{ $t['title'] }}</div>
                        @if(!empty($t['text']))<div class="text-muted small">{{ $t['text'] }}</div>@endif
                        <div class="text-muted" style="font-size:.72rem">{{ $t['at']->format('d-M-Y H:i') }} · {{ $t['at']->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5 text-muted">{{ __('Nothing recorded yet.') }}</div>
    @endif
</div>

<div class="tab-pane fade" id="jobDocumentsTab" role="tabpanel">
    @if($fin['docs']->count())
        <ul class="list-group list-group-flush">
            @foreach($fin['docs'] as $doc)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <strong><i class="bi bi-paperclip me-1"></i>{{ $doc['title'] }}</strong>
                        <small class="text-muted d-block">{{ $doc['source'] }} · {{ $doc['date']?->format('d-M-Y') }}@if(!empty($doc['expiry'])) · {{ __('Expires') }} {{ $doc['expiry']->format('d-M-Y') }}@endif</small>
                    </div>
                    @if($doc['path'])<a href="{{ Storage::url($doc['path']) }}" target="_blank" class="btn btn-outline-primary btn-sm">{{ __('View') }}</a>@endif
                </li>
            @endforeach
        </ul>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-paperclip fs-2 mb-2 d-block"></i>
            {{ __('No documents uploaded for this job or its invoices.') }}
        </div>
    @endif
</div>
