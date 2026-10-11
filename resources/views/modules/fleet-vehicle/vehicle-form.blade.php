<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $item->row_no ?? __('New Vehicle') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Vehicle') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $item->id }}">

        <div class="px-4 mt-3">
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Vehicle') }}</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Plate No') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="plate_no" class="form-control" value="{{ $item->plate_no }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Type') }} <sup class="text-danger">*</sup></label>
                    <select name="type" class="tom-select" required>
                        <option value="">{{ __('Select') }}</option>
                        <option value="truck" @selected($item->type == 'truck')>{{ __('Truck') }}</option>
                        <option value="trailer" @selected($item->type == 'trailer')>{{ __('Trailer') }}</option>
                        <option value="container_truck" @selected($item->type == 'container_truck')>{{ __('Container truck') }}</option>
                        <option value="van" @selected($item->type == 'van')>{{ __('Van') }}</option>
                        <option value="pickup" @selected($item->type == 'pickup')>{{ __('Pickup') }}</option>
                        <option value="other" @selected($item->type == 'other')>{{ __('Other') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Make / Model') }}</label>
                    <input type="text" name="make_model" class="form-control" value="{{ $item->make_model }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Capacity (kg)') }}</label>
                    <input type="text" name="capacity_kg" class="form-control float" value="{{ $item->capacity_kg }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Usual Driver') }}</label>
                    <select name="driver_id" class="tom-select" data-live-search="true">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($drivers as $r)
                            <option value="{{ $r->id }}" @selected($item->driver_id == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Papers') }}</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Registration Expiry') }}</label>
                    <input type="text" name="registration_expiry" class="form-control datepicker" value="{{ $item->registration_expiry?->format('d-m-Y') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Insurance Expiry') }}</label>
                    <input type="text" name="insurance_expiry" class="form-control datepicker" value="{{ $item->insurance_expiry?->format('d-m-Y') }}">
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
