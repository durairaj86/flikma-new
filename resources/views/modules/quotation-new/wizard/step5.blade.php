@section('page-title','New Quotation – Summary')
@section('js','quotation-new')
<x-app-layout>
    <main class="bg-white px-3 py-3">

        @include('modules.quotation-new.wizard._wizard-header', ['currentStep' => 5])

        <form id="wizardForm" novalidate>
            @csrf
            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

            <div class="mx-auto" style="max-width:1100px;">

                {{-- ─── Client / Branch Header ──────────────────────────── --}}
                <div class="d-flex justify-content-between align-items-start mb-3 px-1">
                    <div>
                        <div class="fw-bold fs-6">{{ $quotation->client?->name_en ?? '—' }}</div>
                        @if($quotation->client_address)
                            <div class="text-muted small">{{ $quotation->client_address }}</div>
                        @endif
                        <div class="mt-1 small">
                            <span class="text-muted">Department</span>&nbsp;
                            <strong>{{ $quotation->department ?? '—' }}</strong>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small">
                            <span class="text-muted">Branch</span>&nbsp;
                            <strong>{{ $quotation->branch ?? '—' }}</strong>
                        </div>
                        @php
                            $statusLabels = [1 => ['Pending','warning'], 2 => ['Approved','success'], 3 => ['Cancelled','danger']];
                            [$sLabel, $sColor] = $statusLabels[$quotation->status] ?? ['—','secondary'];
                        @endphp
                        <span class="badge bg-{{ $sColor }} mt-1">{{ $sLabel }}</span>
                    </div>
                </div>

                {{-- ─── Quotation Info (green) ───────────────────────────── --}}
                <div class="fw-semibold text-white px-3 py-2"
                     style="background-color:#5b9a39;">Quotation Info</div>
                <div class="border border-top-0 px-3 py-3 mb-3">
                    @php
                        $lbl = 'col-md-2 text-end text-muted small fw-medium py-1';
                        $val = 'col-md-4 fw-bold small py-1';
                    @endphp
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Origin :</div>
                        <div class="{{ $val }}">{{ $quotation->origin ?? '—' }}</div>
                        <div class="{{ $lbl }}">Destination :</div>
                        <div class="{{ $val }}">{{ $quotation->destination ?? '—' }}</div>
                    </div>
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Quote Date :</div>
                        <div class="{{ $val }}">{{ $quotation->quotation_date ?? '—' }}</div>
                        <div class="{{ $lbl }}">INCO Terms :</div>
                        <div class="{{ $val }}">{{ $quotation->inco_terms ?? '—' }}</div>
                    </div>
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Valid From :</div>
                        <div class="{{ $val }}">{{ $quotation->valid_from ?? '—' }}</div>
                        <div class="{{ $lbl }}">Valid To :</div>
                        <div class="{{ $val }}">{{ $quotation->valid_to ?? '—' }}</div>
                    </div>
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Transit Time :</div>
                        <div class="{{ $val }}">{{ $quotation->transit_time ?? '—' }}</div>
                        <div class="{{ $lbl }}">Frequency :</div>
                        <div class="{{ $val }}">{{ $quotation->frequency ?? '—' }}</div>
                    </div>
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Service Type :</div>
                        <div class="{{ $val }}">{{ $quotation->service_type ?? '—' }}</div>
                        <div class="{{ $lbl }}">PP / CC :</div>
                        <div class="{{ $val }}">{{ $quotation->pp_cc ?? '—' }}</div>
                    </div>
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">ETD :</div>
                        <div class="{{ $val }}">
                            {{ $quotation->etd ? \Carbon\Carbon::parse($quotation->etd)->format('d-M-y g:iA') : '—' }}
                        </div>
                        <div class="{{ $lbl }}">ETA :</div>
                        <div class="{{ $val }}">
                            {{ $quotation->eta ? \Carbon\Carbon::parse($quotation->eta)->format('d-M-y g:iA') : '—' }}
                        </div>
                    </div>
                    @if($quotation->destination_free_days)
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Dest. Free Days :</div>
                        <div class="{{ $val }}">{{ $quotation->destination_free_days }}</div>
                        <div class="col-md-6"></div>
                    </div>
                    @endif
                </div>

                {{-- ─── Container Details (purple) ──────────────────────── --}}
                <div class="fw-semibold text-white px-3 py-2"
                     style="background-color:#6f42c1;">Container Details</div>
                <div class="border border-top-0 px-3 py-3 mb-3">
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Airline :</div>
                        <div class="{{ $val }}">{{ $quotation->carrier ?? '—' }}</div>
                        <div class="{{ $lbl }}">Flight Name / No. :</div>
                        <div class="{{ $val }}">
                            {{ trim(($quotation->vessel_name ?? '') . ' / ' . ($quotation->voyage_no ?? ''), ' /') ?: '—' }}
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">No of Pcs :</div>
                        <div class="{{ $val }}">{{ $quotation->no_of_pcs ?? '—' }}</div>
                        <div class="{{ $lbl }}">Volume :</div>
                        <div class="{{ $val }}">{{ $quotation->volume ?? '—' }} {{ $quotation->volume_unit }}</div>
                    </div>
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Volume Weight :</div>
                        <div class="{{ $val }}">{{ $quotation->volume_weight ?? '—' }}</div>
                        <div class="{{ $lbl }}">Gross Weight :</div>
                        <div class="{{ $val }}">{{ $quotation->gross_weight ?? '—' }} {{ $quotation->weight_unit }}</div>
                    </div>
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">HS Code :</div>
                        <div class="{{ $val }}">{{ $quotation->hs_code ?? '—' }}</div>
                        <div class="{{ $lbl }}">Description :</div>
                        <div class="{{ $val }}">{{ $quotation->description ?? '—' }}</div>
                    </div>
                    @if($quotation->consignment_remarks)
                    <div class="row mb-1">
                        <div class="{{ $lbl }}">Remarks :</div>
                        <div class="col-md-10 fw-bold small py-1">{{ $quotation->consignment_remarks }}</div>
                    </div>
                    @endif
                </div>

                {{-- ─── Charges (blue) ──────────────────────────────────── --}}
                <div class="fw-semibold text-white px-3 py-2"
                     style="background-color:#0d6efd;">Charges</div>
                <div class="border border-top-0 mb-4">
                    @if($quotation->charges->count())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 align-middle" style="font-size:13px;">
                                <thead class="table-light">
                                <tr>
                                    <th>Charge Description</th>
                                    <th>OFD Type</th>
                                    <th>Unit</th>
                                    <th>Qty</th>
                                    <th>Freight</th>
                                    <th>Dr/Cr</th>
                                    <th class="text-end">Qty/Amount</th>
                                    <th class="text-end">Amount (INR)</th>
                                    <th class="text-end">Tax (INR)</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $grandTotal = 0; $totalTax = 0; @endphp
                                @foreach($quotation->charges as $charge)
                                    @php
                                        $grandTotal += $charge->amount_inr ?? 0;
                                        $totalTax   += $charge->tax_amount_inr ?? 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $charge->charge_description }}</td>
                                        <td>{{ $charge->ofd_type }}</td>
                                        <td>{{ $charge->unit }}</td>
                                        <td>{{ $charge->qty }}</td>
                                        <td>{{ $charge->freight }}</td>
                                        <td>{{ $charge->dr_cr }}</td>
                                        <td class="text-end">{{ number_format($charge->qty_amount, 2) }}</td>
                                        <td class="text-end">{{ number_format($charge->amount_inr, 2) }}</td>
                                        <td class="text-end">{{ number_format($charge->tax_amount_inr, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="7" class="text-end">Grand Total (INR)</td>
                                    <td class="text-end text-primary">{{ number_format($grandTotal, 2) }}</td>
                                    <td class="text-end">{{ number_format($totalTax, 2) }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted small px-3 py-2 mb-0">No charges added.</p>
                    @endif
                </div>

            </div>

            @include('modules.quotation-new.wizard._wizard-footer', ['step' => 5])
        </form>
    </main>
</x-app-layout>
