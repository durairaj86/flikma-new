@section('page-title', __('New Quotation – Port Details'))
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
                            <label class="form-label fw-medium">{{ __('Branch') }}</label>
                            <select name="branch" class="tom-select">
                                <option value="">{{ __('--Select--') }}</option>
                                @foreach(['CHENNAI','MUMBAI','DELHI','BANGALORE','HYDERABAD','KOLKATA'] as $b)
                                    <option value="{{ $b }}" @selected($quotation->branch === $b)>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('Department') }}</label>
                            <select name="department" class="tom-select">
                                <option value="">{{ __('--Select--') }}</option>
                                @foreach(['FCL EXPORT','FCL IMPORT','LCL EXPORT','LCL IMPORT','AIR EXPORT','AIR IMPORT'] as $d)
                                    <option value="{{ $d }}" @selected($quotation->department === $d)>{{ __($d) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                {{ __('Quotation Date') }} <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="quotation_date" class="form-control datepicker"
                                   value="{{ $quotation->quotation_date }}" required>
                        </div>
                    </div>

                    {{-- Row 2: *Client | Origin | Destination --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                {{ __('Client') }} <span class="text-danger">*</span>
                            </label>
                            <x-common.customers :value="$quotation->client_id" :required="true"></x-common.customers>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('Origin') }}</label>
                            <select id="qn_origin" name="origin" class="tom-select-search"
                                    data-placeholder="{{ __('Select Origin') }}">
                                <option value="">{{ __('--Select--') }}</option>
                                @if($quotation->origin)
                                    <option value="{{ $quotation->origin }}" selected>{{ $quotation->origin }}</option>
                                @endif
                                @foreach($polPod as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('Destination') }}</label>
                            <select id="qn_destination" name="destination" class="tom-select-search"
                                    data-placeholder="{{ __('Select Destination') }}">
                                <option value="">{{ __('--Select--') }}</option>
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
                            <label class="form-label fw-medium">{{ __('Address') }}</label>
                            <input type="text" name="client_address" id="qn_client_address"
                                   class="form-control" value="{{ $quotation->client_address }}"
                                   placeholder="{{ __('Auto-populated from client') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('Place Of Receipt') }}</label>
                            <input type="text" name="place_of_receipt" class="form-control"
                                   value="{{ $quotation->place_of_receipt }}" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('Place of Delivery') }}</label>
                            <input type="text" name="place_of_delivery" class="form-control"
                                   value="{{ $quotation->place_of_delivery }}" maxlength="100">
                        </div>
                    </div>

                    {{-- Row 4: INCO Terms | *Valid From | *Valid To --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('INCO Terms') }}</label>
                            <select name="inco_terms" class="tom-select">
                                <option value="">{{ __('--Select--') }}</option>
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
                                {{ __('Valid From') }} <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="valid_from" class="form-control datepicker"
                                   value="{{ $quotation->valid_from }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">
                                {{ __('Valid To') }} <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="valid_to" class="form-control datepicker"
                                   value="{{ $quotation->valid_to }}" required>
                        </div>
                    </div>

                    {{-- Row 5: Service Type | PP / CC | Transit Time | Frequency --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-medium">{{ __('Service Type') }}</label>
                            <select name="service_type" class="tom-select">
                                <option value="">{{ __('--Select--') }}</option>
                                @foreach(['FCL','LCL','AIR','ROAD','COURIER'] as $st)
                                    <option value="{{ $st }}" @selected($quotation->service_type === $st)>{{ __($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">{{ __('PP / CC') }}</label>
                            <select name="pp_cc" class="tom-select">
                                <option value="">{{ __('--Select--') }}</option>
                                <option value="Prepaid" @selected($quotation->pp_cc === 'Prepaid')>{{ __('Prepaid') }}</option>
                                <option value="Collect" @selected($quotation->pp_cc === 'Collect')>{{ __('Collect') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">{{ __('Transit Time') }}</label>
                            <input type="text" name="transit_time" class="form-control"
                                   value="{{ $quotation->transit_time }}" placeholder="{{ __('e.g. 14 days') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">{{ __('Frequency') }}</label>
                            <input type="text" name="frequency" class="form-control"
                                   value="{{ $quotation->frequency }}" placeholder="{{ __('e.g. Weekly') }}">
                        </div>
                    </div>

                    {{-- Row 6: ETD | ETA | Destination Free Days --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('ETD') }}</label>
                            <input type="datetime-local" name="etd" class="form-control"
                                   value="{{ $quotation->etd ? \Carbon\Carbon::parse($quotation->etd)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('ETA') }}</label>
                            <input type="datetime-local" name="eta" class="form-control"
                                   value="{{ $quotation->eta ? \Carbon\Carbon::parse($quotation->eta)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('Destination Free Days') }}</label>
                            <input type="number" name="destination_free_days" class="form-control"
                                   value="{{ $quotation->destination_free_days }}" min="0">
                        </div>
                    </div>

                    {{-- Row 7: Remarks --}}
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">{{ __('Remarks') }}</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ $quotation->remarks }}</textarea>
                        </div>
                    </div>

                </div>
            </div>

            @include('modules.quotation-new.wizard._wizard-footer', ['step' => 2])
        </form>
    </main>
</x-app-layout>
