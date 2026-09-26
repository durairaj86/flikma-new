@section('page-title','New Quotation – Port Details')
@section('js','quotation-new')
<x-app-layout>
    <main class="bg-white px-3 py-3">

        @include('modules.quotation-new.wizard._wizard-header', ['currentStep' => 2])

        <form id="wizardForm" novalidate>
            @csrf
            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

            <div class="card shadow-sm border-0 mx-auto" style="max-width:1100px;">
                <div class="card-body p-4">

                    {{-- Row 1: Branch | Department | *Quotation Date --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Branch</label>
                            <select name="branch" class="tom-select">
                                <option value="">--Select--</option>
                                @foreach(['CHENNAI','MUMBAI','DELHI','BANGALORE','HYDERABAD','KOLKATA'] as $b)
                                    <option value="{{ $b }}" @selected($quotation->branch === $b)>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Department</label>
                            <select name="department" class="tom-select">
                                <option value="">--Select--</option>
                                @foreach(['FCL EXPORT','FCL IMPORT','LCL EXPORT','LCL IMPORT','AIR EXPORT','AIR IMPORT'] as $d)
                                    <option value="{{ $d }}" @selected($quotation->department === $d)>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                Quotation Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="quotation_date" class="form-control datepicker"
                                   value="{{ $quotation->quotation_date }}" required>
                        </div>
                    </div>

                    {{-- Row 2: *Client | Origin | Destination --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                Client <span class="text-danger">*</span>
                            </label>
                            <x-common.customers :value="$quotation->client_id" :required="true"></x-common.customers>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Origin</label>
                            <select id="qn_origin" name="origin" class="tom-select-search"
                                    data-placeholder="Select Origin">
                                <option value="">--Select--</option>
                                @if($quotation->origin)
                                    <option value="{{ $quotation->origin }}" selected>{{ $quotation->origin }}</option>
                                @endif
                                @foreach($polPod as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Destination</label>
                            <select id="qn_destination" name="destination" class="tom-select-search"
                                    data-placeholder="Select Destination">
                                <option value="">--Select--</option>
                                @if($quotation->destination)
                                    <option value="{{ $quotation->destination }}" selected>{{ $quotation->destination }}</option>
                                @endif
                                @foreach($polPod as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Row 3: Address | Place Of Receipt | Place of Delivery --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Address</label>
                            <input type="text" name="client_address" id="qn_client_address"
                                   class="form-control" value="{{ $quotation->client_address }}"
                                   placeholder="Auto-populated from client">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Place Of Receipt</label>
                            <input type="text" name="place_of_receipt" class="form-control"
                                   value="{{ $quotation->place_of_receipt }}" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Place of Delivery</label>
                            <input type="text" name="place_of_delivery" class="form-control"
                                   value="{{ $quotation->place_of_delivery }}" maxlength="100">
                        </div>
                    </div>

                    {{-- Row 4: INCO Terms | *Valid From | *Valid To --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">INCO Terms</label>
                            <select name="inco_terms" class="tom-select">
                                <option value="">--Select--</option>
                                @foreach(incoterms() as $incoterm)
                                    <option value="{{ $incoterm->code }}"
                                            data-subtext="{{ $incoterm->description }}"
                                        @selected($quotation->inco_terms === $incoterm->code)>
                                        {{ $incoterm->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                Valid From <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="valid_from" class="form-control datepicker"
                                   value="{{ $quotation->valid_from }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                Valid To <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="valid_to" class="form-control datepicker"
                                   value="{{ $quotation->valid_to }}" required>
                        </div>
                    </div>

                    {{-- Row 5: Service Type | PP / CC | Transit Time | Frequency --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Service Type</label>
                            <select name="service_type" class="tom-select">
                                <option value="">--Select--</option>
                                @foreach(['FCL','LCL','AIR','ROAD','COURIER'] as $st)
                                    <option value="{{ $st }}" @selected($quotation->service_type === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">PP / CC</label>
                            <select name="pp_cc" class="tom-select">
                                <option value="">--Select--</option>
                                <option value="Prepaid" @selected($quotation->pp_cc === 'Prepaid')>Prepaid</option>
                                <option value="Collect" @selected($quotation->pp_cc === 'Collect')>Collect</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Transit Time</label>
                            <input type="text" name="transit_time" class="form-control"
                                   value="{{ $quotation->transit_time }}" placeholder="e.g. 14 days">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Frequency</label>
                            <input type="text" name="frequency" class="form-control"
                                   value="{{ $quotation->frequency }}" placeholder="e.g. Weekly">
                        </div>
                    </div>

                    {{-- Row 6: ETD | ETA | Destination Free Days --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">ETD</label>
                            <input type="datetime-local" name="etd" class="form-control"
                                   value="{{ $quotation->etd ? \Carbon\Carbon::parse($quotation->etd)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">ETA</label>
                            <input type="datetime-local" name="eta" class="form-control"
                                   value="{{ $quotation->eta ? \Carbon\Carbon::parse($quotation->eta)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Destination Free Days</label>
                            <input type="number" name="destination_free_days" class="form-control"
                                   value="{{ $quotation->destination_free_days }}" min="0">
                        </div>
                    </div>

                    {{-- Row 7: Remarks --}}
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ $quotation->remarks }}</textarea>
                        </div>
                    </div>

                </div>
            </div>

            @include('modules.quotation-new.wizard._wizard-footer', ['step' => 2])
        </form>
    </main>
</x-app-layout>
