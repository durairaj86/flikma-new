<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Reason') }}">
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $lostReason->name ?? __('New Lost Reason') }}</span>
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $lostReason->id ?? '' }}">
            <div class="model-form-tab-div">
                <div class="row g-3">
                    <div class="col-6 form-group">
                        <label class="form-label required">{{ __('Reason') }} <sup class="text-danger">*</sup></label>
                        <input type="text" name="name" class="form-control" required maxlength="100"
                               value="{{ $lostReason->name ?? '' }}">
                    </div>
                    <div class="col-3 form-group">
                        <label class="form-label">{{ __('Sort order') }}</label>
                        <input type="text" name="sort_order" class="form-control integer" data-max-int="3"
                               value="{{ $lostReason->sort_order ?? 0 }}">
                    </div>
                    <div class="col-3 form-group">
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="is_active" class="tom-select" required>
                            <option value="1" @selected($lostReason->is_active ?? true)>{{ __('Active') }}</option>
                            <option value="0" @selected(!($lostReason->is_active ?? true))>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
