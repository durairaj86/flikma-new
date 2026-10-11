<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $item->row_no ?? __('New Driver') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Driver') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $item->id }}">

        <div class="px-4 mt-3">
            <div class="row g-3">
                <div class="col-12"><h6 class="border-bottom pb-2 mb-0 fw-semibold">{{ __('Driver') }}</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Full Name') }} <sup class="text-danger">*</sup></label>
                    <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ $item->phone }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Iqama / National ID') }}</label>
                    <input type="text" name="iqama_no" class="form-control" value="{{ $item->iqama_no }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Licence No') }}</label>
                    <input type="text" name="license_no" class="form-control" value="{{ $item->license_no }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Licence Expiry') }}</label>
                    <input type="text" name="license_expiry" class="form-control datepicker" value="{{ $item->license_expiry?->format('d-m-Y') }}">
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
