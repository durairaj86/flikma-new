<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $rate->row_no ?? __('New Rate') }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Rate') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $rate->id }}">

        <div class="px-4 mt-3">
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Lane') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Mode') }} <sup class="text-danger">*</sup></label>
                    <select name="shipment_mode" class="tom-select" required>
                        <option value="sea" @selected($rate->shipment_mode == 'sea')>{{ __('Sea') }}</option>
                        <option value="air" @selected($rate->shipment_mode == 'air')>{{ __('Air') }}</option>
                        <option value="road" @selected($rate->shipment_mode == 'road')>{{ __('Road') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Origin') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="origin" class="form-control" value="{{ $rate->origin }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Destination') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="destination" class="form-control" value="{{ $rate->destination }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Carrier') }} <small class="text-muted fw-normal">({{ __('optional') }})</small></label>
                    <select name="carrier_id" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Any carrier') }}</option>
                        @foreach($carriers->groupBy('mode') as $mode => $group)
                            <optgroup label="{{ $mode }}">
                                @foreach($group as $carrier)
                                    <option value="{{ $carrier->id }}" @selected($rate->carrier_id == $carrier->id)>{{ $carrier->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Rate') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Container / Equipment') }}</label>
                    <input type="text" name="container_type" class="form-control" list="rt-container-types" value="{{ $rate->container_type }}" placeholder="{{ __('e.g. 40HC, LCL, General cargo') }}">
                    <datalist id="rt-container-types">
                        @foreach($containerTypes as $type)<option value="{{ $type }}">@endforeach
                    </datalist>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Charged') }} <sup class="text-danger">*</sup></label>
                    <select name="basis" class="tom-select" required>
                        @foreach($basisList as $key => $label)
                            <option value="{{ $key }}" @selected($rate->basis == $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('Currency') }} <sup class="text-danger">*</sup></label>
                    <select name="currency" class="tom-select" required>
                        @foreach($currencies as $code)
                            <option value="{{ $code }}" @selected($rate->currency == $code)>{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('Buy Rate') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="buy_rate" id="rt-buy" class="form-control float text-end" value="{{ $rate->buy_rate }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('Sell Rate') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="sell_rate" id="rt-sell" class="form-control float text-end" value="{{ $rate->sell_rate }}" required>
                </div>
                <div class="col-12">
                    <div class="small text-muted" id="rt-margin-hint"></div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Minimum Charge') }}</label>
                    <input type="text" name="min_charge" class="form-control float text-end" value="{{ $rate->min_charge }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Transit Days') }}</label>
                    <input type="number" min="0" name="transit_days" class="form-control" value="{{ $rate->transit_days }}">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Validity') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Valid From') }}</label>
                    <input type="text" name="valid_from" class="form-control datepicker" value="{{ $rate->valid_from?->format('d-m-Y') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Valid To') }}</label>
                    <input type="text" name="valid_to" class="form-control datepicker" value="{{ $rate->valid_to?->format('d-m-Y') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Remarks') }}</label>
                    <input type="text" name="remarks" class="form-control" value="{{ $rate->remarks }}" placeholder="{{ __('Surcharges, free days, conditions …') }}">
                </div>
            </div>
        </div>
    </form>
</div>
