<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $master->row_no ?? __('New Master B/L') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Master B/L') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $master->id }}">
        <input type="hidden" id="mbl-master-id" value="{{ $master->id }}">

        <div class="px-4 mt-3">
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Master B/L') }}</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Shipment Mode') }} <sup class="text-danger">*</sup></label>
                    <select name="shipment_mode" id="mbl-mode" class="tom-select" required>
                        <option value="sea" @selected($master->shipment_mode == 'sea')>{{ __('Sea') }}</option>
                        <option value="air" @selected($master->shipment_mode == 'air')>{{ __('Air') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Master B/L / AWB No') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="mbl_no" class="form-control" value="{{ $master->mbl_no }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Issue Date') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="issue_date" class="form-control datepicker" value="{{ $master->issue_date?->format('d-m-Y') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Carrier') }}</label>
                    <select name="carrier_id" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Select Carrier') }}</option>
                        @foreach($carriers->groupBy('mode') as $mode => $group)
                            <optgroup label="{{ $mode }}">
                                @foreach($group as $carrier)
                                    <option value="{{ $carrier->id }}" @selected($master->carrier_id == $carrier->id)>{{ $carrier->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Vessel / Flight') }}</label>
                    <input type="text" name="vessel_flight" class="form-control" value="{{ $master->vessel_flight }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Voyage / Flight No') }}</label>
                    <input type="text" name="voyage_no" class="form-control" value="{{ $master->voyage_no }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Loading') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="pol" class="form-control" value="{{ $master->pol }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Discharge') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="pod" class="form-control" value="{{ $master->pod }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('ETD') }}</label>
                    <input type="text" name="etd" class="form-control datepicker" value="{{ $master->etd?->format('d-m-Y') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('ETA') }}</label>
                    <input type="text" name="eta" class="form-control datepicker" value="{{ $master->eta?->format('d-m-Y') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Shipper') }}</label>
                    <input type="text" name="shipper" class="form-control" value="{{ $master->shipper }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Consignee') }}</label>
                    <input type="text" name="consignee" class="form-control" value="{{ $master->consignee }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Freight Terms') }} <sup class="text-danger">*</sup></label>
                    <select name="freight_terms" class="tom-select" required>
                        <option value="prepaid" @selected($master->freight_terms == 'prepaid')>{{ __('Prepaid') }}</option>
                        <option value="collect" @selected($master->freight_terms == 'collect')>{{ __('Collect') }}</option>
                    </select>
                </div>

                <div class="col-12 mt-4"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('House Bills') }}</h6></div>
                <div class="col-12">
                    <div class="form-text mb-2">{{ __('Tick the house bills that travel under this master. Bills already under another master are not listed.') }}</div>
                    <div id="mbl-houses" class="border rounded p-2" style="max-height:240px;overflow:auto;"></div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Remarks') }}</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ $master->remarks }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
