<div class="container-fluid px-4 py-3" id="modal-buttons" data-buttons="cancel,save" data-button-save="Save Changes">

    <form id="moduleForm" novalidate action="{{ url('sales/quotations-new/'.$quotation->id.'/update') }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $quotation->id }}">

        @php
            $statusLabels = [1 => 'Pending', 2 => 'Approved', 3 => 'Cancelled'];
        @endphp

        {{-- Row 1: Quotation No | Quote Date | Client --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Quotation No</label>
                <input type="text" class="form-control" value="{{ $quotation->row_no }}" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Quote Date</label>
                <input type="date" name="quotation_date" class="form-control datepicker"
                       value="{{ $quotation->quotation_date }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Client</label>
                <x-common.customers :value="$quotation->client_id"></x-common.customers>
            </div>
        </div>

        {{-- Row 2: Valid From | Valid To | Quote Status --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Valid From</label>
                <input type="date" name="valid_from" class="form-control datepicker"
                       value="{{ $quotation->valid_from }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Valid To</label>
                <input type="date" name="valid_to" class="form-control datepicker"
                       value="{{ $quotation->valid_to }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Quote Status</label>
                <input type="text" class="form-control"
                       value="{{ $statusLabels[$quotation->status] ?? '—' }}" readonly>
            </div>
        </div>

        {{-- Row 3: Freight | Place of Receipt | POR --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Freight</label>
                <select name="freight" class="tom-select">
                    <option value="">--Select--</option>
                    <option value="Prepaid" @selected($quotation->freight === 'Prepaid')>Prepaid</option>
                    <option value="Collect" @selected($quotation->freight === 'Collect')>Collect</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Place of Receipt</label>
                <input type="text" name="place_of_receipt" class="form-control"
                       value="{{ $quotation->place_of_receipt }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">POR</label>
                <select id="qn_por" name="por" class="tom-select-search"
                        data-placeholder="Select POR">
                    <option value="">--Select--</option>
                    @if($quotation->por)
                        <option value="{{ $quotation->por }}" selected>{{ $quotation->por }}</option>
                    @endif
                    @foreach($polPod as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Row 4: POL | POD | POF --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">POL</label>
                <select id="qn_pol" name="pol" class="tom-select-search"
                        data-placeholder="Select POL">
                    <option value="">--Select--</option>
                    @if($quotation->pol)
                        <option value="{{ $quotation->pol }}" selected>{{ $quotation->pol }}</option>
                    @endif
                    @foreach($polPod as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">POD</label>
                <select id="qn_pod" name="pod" class="tom-select-search"
                        data-placeholder="Select POD">
                    <option value="">--Select--</option>
                    @if($quotation->pod)
                        <option value="{{ $quotation->pod }}" selected>{{ $quotation->pod }}</option>
                    @endif
                    @foreach($polPod as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">POF</label>
                <select id="qn_pof" name="pof" class="tom-select-search"
                        data-placeholder="Select POF">
                    <option value="">--Select--</option>
                    @if($quotation->pof)
                        <option value="{{ $quotation->pof }}" selected>{{ $quotation->pof }}</option>
                    @endif
                    @foreach($polPod as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Row 5: Place of Delivery | Service Type | Carrier --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Place of Delivery</label>
                <input type="text" name="place_of_delivery" class="form-control"
                       value="{{ $quotation->place_of_delivery }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Service Type</label>
                <select name="service_type" class="tom-select">
                    <option value="">-Select-</option>
                    @foreach(['FCL','LCL','AIR','ROAD','COURIER'] as $st)
                        <option value="{{ $st }}" @selected($quotation->service_type === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Carrier</label>
                <select id="qn_edit_carrier" name="carrier" class="tom-select-search"
                        data-placeholder="-Select-">
                    <option value="">-Select-</option>
                    @if($quotation->carrier)
                        <option value="{{ $quotation->carrier }}" selected>{{ $quotation->carrier }}</option>
                    @endif
                </select>
            </div>
        </div>

        {{-- Row 6: Transit Time | Frequency | INCO Term --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Transit Time</label>
                <input type="text" name="transit_time" class="form-control"
                       value="{{ $quotation->transit_time }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Frequency</label>
                <input type="text" name="frequency" class="form-control"
                       value="{{ $quotation->frequency }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">INCO Term</label>
                <select name="inco_terms" class="tom-select">
                    <option value="">-Select-</option>
                    @foreach(incoterms() as $incoterm)
                        <option value="{{ $incoterm->code }}"
                                data-subtext="{{ $incoterm->description }}"
                            @selected($quotation->inco_terms === $incoterm->code)>
                            {{ $incoterm->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Row 7: Mark No | Internal Notes | Remarks --}}
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Mark No</label>
                <textarea name="mark_no" class="form-control" rows="3">{{ $quotation->mark_no }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Internal Notes</label>
                <textarea name="internal_notes" class="form-control" rows="3">{{ $quotation->internal_notes }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Remarks</label>
                <textarea name="remarks" class="form-control" rows="3">{{ $quotation->remarks }}</textarea>
            </div>
        </div>

    </form>
</div>
