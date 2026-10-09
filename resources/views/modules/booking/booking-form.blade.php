<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $booking->row_no ?? __('New Booking') }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Booking') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $booking->id }}">

        <div class="px-4 mt-3">
            {{-- 1. Who and how --}}
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Booking') }}</h6></div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Shipment Mode') }} <sup class="text-danger">*</sup></label>
                    <select name="shipment_mode" id="bk-mode" class="tom-select" required>
                        <option value="sea" @selected($booking->shipment_mode == 'sea')>{{ __('Sea') }}</option>
                        <option value="air" @selected($booking->shipment_mode == 'air')>{{ __('Air') }}</option>
                        <option value="road" @selected($booking->shipment_mode == 'road')>{{ __('Road') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Job') }} <small class="text-muted fw-normal">({{ __('optional') }})</small></label>
                    <select name="job_id" id="bk-job" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Select Job') }}</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" @selected($booking->job_id == $job->id)
                                    data-customer-id="{{ $job->customer_id ? encodeId($job->customer_id) : '' }}"
                                    data-mode="{{ strtolower($job->shipment_mode ?? '') }}" data-pol="{{ $job->pol }}" data-pod="{{ $job->pod }}"
                                    data-etd="{{ $job->etd ? \Carbon\Carbon::parse($job->etd)->format('d-m-Y') : '' }}"
                                    data-eta="{{ $job->eta ? \Carbon\Carbon::parse($job->eta)->format('d-m-Y') : '' }}">
                                {{ $job->row_no }} - {{ $job->customer->name_en ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('Choosing a job fills the customer, route and dates.') }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Customer') }} <sup class="text-danger">*</sup></label>
                    <x-common.customers :value="$booking->customer_id" :new="false" :required="true"></x-common.customers>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Carrier') }}</label>
                    <select name="carrier_id" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Select Carrier') }}</option>
                        @foreach($carriers->groupBy('mode') as $mode => $group)
                            <optgroup label="{{ $mode }}">
                                @foreach($group as $carrier)
                                    <option value="{{ $carrier->id }}" @selected($booking->carrier_id == $carrier->id)>{{ $carrier->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Carrier Booking No') }}</label>
                    <input type="text" name="booking_ref" class="form-control" value="{{ $booking->booking_ref }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Booking Date') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="booking_date" class="form-control datepicker" value="{{ $booking->booking_date?->format('d-m-Y') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Vessel / Flight') }}</label>
                    <input type="text" name="vessel_flight" class="form-control" value="{{ $booking->vessel_flight }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Voyage / Flight No') }}</label>
                    <input type="text" name="voyage_no" class="form-control" value="{{ $booking->voyage_no }}">
                </div>
            </div>

            {{-- 2. Route and dates --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Route & Schedule') }}</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Port / Airport of Loading') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="pol" id="bk-pol" class="form-control" value="{{ $booking->pol }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Port / Airport of Discharge') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="pod" id="bk-pod" class="form-control" value="{{ $booking->pod }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('ETD') }}</label>
                    <input type="text" name="etd" id="bk-etd" class="form-control datepicker" value="{{ $booking->etd?->format('d-m-Y') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('ETA') }}</label>
                    <input type="text" name="eta" id="bk-eta" class="form-control datepicker" value="{{ $booking->eta?->format('d-m-Y') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Cargo Cut-off') }}</label>
                    <input type="datetime-local" name="cargo_cutoff" class="form-control" value="{{ $booking->cargo_cutoff?->format('Y-m-d\TH:i') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Document Cut-off') }}</label>
                    <input type="datetime-local" name="doc_cutoff" class="form-control" value="{{ $booking->doc_cutoff?->format('Y-m-d\TH:i') }}">
                </div>
            </div>

            {{-- 3. Cargo --}}
            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Cargo') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Container Type') }}</label>
                    <input type="text" name="container_type" class="form-control" list="bk-container-types" value="{{ $booking->container_type }}">
                    <datalist id="bk-container-types">
                        @foreach($containerTypes as $type)<option value="{{ $type }}">@endforeach
                    </datalist>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Containers / Pieces') }}</label>
                    <input type="number" min="0" name="container_qty" class="form-control" value="{{ $booking->container_qty }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Gross Weight (kg)') }}</label>
                    <input type="text" name="gross_weight" class="form-control float" value="{{ $booking->gross_weight }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Volume (CBM)') }}</label>
                    <input type="text" name="volume" class="form-control float" value="{{ $booking->volume }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Cargo Description') }}</label>
                    <textarea name="cargo_description" class="form-control" rows="2">{{ $booking->cargo_description }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Remarks') }}</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ $booking->remarks }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
