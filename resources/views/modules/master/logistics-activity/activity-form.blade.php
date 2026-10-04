<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Department') }}">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $logisticActivity->name ?? __('New Department') }}</span>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $logisticActivity->id ?? '' }}">

            <div class="model-form-tab-div">
                <!-- Mode & Category in styled box -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="">
                            <div class="row g-3">
                                <!-- Department name (Freza style: FCL Import, FCL Export, Air Export ...) -->
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ __('Department Name') }} <sup class="text-danger">*</sup></label>
                                    <input type="text" name="name" class="form-control" required maxlength="60"
                                           value="{{ $logisticActivity->name ?? '' }}"
                                           placeholder="{{ __('FCL Export') }}">
                                </div>

                                <!-- Mode -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Mode') }} <sup class="text-danger">*</sup></label>
                                    <select name="mode" class="form-control tom-select" required>
                                        <option value="">{{ __('Select Mode') }}</option>
                                        <option value="sea" @selected($logisticActivity->mode == 'sea')>{{ __('Sea') }}</option>
                                        <option value="air" @selected($logisticActivity->mode == 'air')>{{ __('Air') }}</option>
                                        <option value="land" @selected($logisticActivity->mode == 'land')>{{ __('Land') }}</option>
                                        <option value="vas" @selected($logisticActivity->mode == 'vas')>{{ __('Value Added Service') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Type') }} <sup class="text-danger">*</sup></label>
                                    <select name="type" class="form-control tom-select" required>
                                        <option value="">{{ __('Select Type') }}</option>
                                        <option value="import" @selected($logisticActivity->type == 'import')>{{ __('Import') }}</option>
                                        <option value="export" @selected($logisticActivity->type == 'export')>{{ __('Export') }}</option>
                                        <option value="land" @selected($logisticActivity->type == 'land')>{{ __('Land') }}</option>
                                        <option value="value-added" @selected($logisticActivity->type == 'value-added')>{{ __('Value Added') }}</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .model-form-tab-div .form-label {
        font-size: 0.9rem;
        color: #555;
    }

    .model-form-tab-div input,
    .model-form-tab-div select {
        font-size: 0.9rem;
    }

    .model-form-tab-div .border {
        border-color: #dee2e6 !important;
    }
</style>
