<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Customer') }}">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $packageCode->name ?? __('New Package Code') }}</span>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="packageForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $packageCode->id ?? '' }}">

            <div class="model-form-tab-div">
                <div class="row">
                    {{--<div class="col-4 form-group">
                        <label class="form-label">Package Code</label>
                        <input type="text" name="package_code" class="form-control" required value="{{ $packageCode->package_code ?? '' }}">
                    </div>--}}
                    <div class="col-12 form-group">
                        <label class="form-label">{{ __('Package Name') }}</label>
                        <input type="text" name="package_name" class="form-control" required
                               value="{{ $packageCode->name ?? '' }}">
                    </div>
                    <div class="col-12 form-group mt-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <input type="text" name="description" class="form-control"
                               value="{{ $packageCode->description ?? '' }}">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
