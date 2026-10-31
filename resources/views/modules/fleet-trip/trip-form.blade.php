<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $item->row_no ?? __('New Trip') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Trip') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $item->id }}">

        <div class="px-4 mt-3">
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Trip') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Trip Date') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="trip_date" class="form-control datepicker" value="{{ $item->trip_date?->format('d-m-Y') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Vehicle') }} <sup class="text-danger">*</sup></label>
                    <select name="vehicle_id" class="tom-select" data-live-search="true" required>
                        <option value="">{{ __('Select') }}</option>
                        @foreach($vehicles as $r)
                            <option value="{{ $r->id }}" @selected($item->vehicle_id == $r->id) data-driver="{{ $r->driver_id }}">{{ $r->plate_no }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Driver') }}</label>
                    <select name="driver_id" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($drivers as $r)
                            <option value="{{ $r->id }}" @selected($item->driver_id == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Distance (km)') }}</label>
                    <input type="text" name="distance_km" class="form-control float" value="{{ $item->distance_km }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('From') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="origin" class="form-control" value="{{ $item->origin }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('To') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="destination" class="form-control" value="{{ $item->destination }}" required>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Linked To') }}</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Job') }}</label>
                    <x-common.jobs :jobs="$jobs" :value="$item->job_id"/>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Delivery Order') }}</label>
                    <select name="delivery_order_id" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($deliveryOrders as $r)
                            <option value="{{ $r->id }}" @selected($item->delivery_order_id == $r->id)>{{ $r->row_no }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Money') }}</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Freight Charged') }}</label>
                    <input type="text" name="freight_amount" class="form-control float" value="{{ $item->freight_amount }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Driver Allowance') }}</label>
                    <input type="text" name="driver_allowance" class="form-control float" value="{{ $item->driver_allowance }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Fuel') }}</label>
                    <input type="text" name="fuel_cost" class="form-control float" value="{{ $item->fuel_cost }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Other Costs') }}</label>
                    <input type="text" name="other_cost" class="form-control float" value="{{ $item->other_cost }}">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Notes') }}</h6></div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">{{ __('Remarks') }}</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ $item->remarks }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
