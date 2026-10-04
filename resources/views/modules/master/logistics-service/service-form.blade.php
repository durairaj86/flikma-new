<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Service') }}">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $logisticService->name ?? __('New Service') }}</span>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $logisticService->id ?? '' }}">

            <div class="model-form-tab-div">
                <!-- Mode & Category in styled box -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="">
                            <div class="row g-3">
                                <!-- Category -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Category') }}</label>
                                    <select name="category" class="form-control tom-select" required>
                                        <option value="">{{ __('Select Category') }}</option>
                                        @foreach(services() as $logisticServiceId => $logisticServiceName)
                                            <option
                                                value="{{ $logisticServiceId }}" @selected($logisticServiceId == $logisticService->category_id)>{{ $logisticServiceName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6"></div>
                                <!-- Mode -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Mode') }}</label>
                                    <select name="mode" class="form-control tom-select" required>
                                        <option value="">{{ __('Select Mode') }}</option>
                                        <option value="sea" @selected($logisticService->mode == 'sea')>{{ __('Sea') }}</option>
                                        <option value="air" @selected($logisticService->mode == 'air')>{{ __('Air') }}</option>
                                        <option value="land" @selected($logisticService->mode == 'land')>{{ __('Land') }}</option>
                                        <option value="vas" @selected($logisticService->mode == 'vas')>{{ __('Value Added Service') }}
                                        </option>
                                    </select>
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Type') }}</label>
                                    <select name="type" class="form-control tom-select" required>
                                        <option value="">{{ __('Select Type') }}</option>
                                        <option value="import" @selected($logisticService->type == 'import')>{{ __('Import') }}
                                        </option>
                                        <option value="export" @selected($logisticService->type == 'export')>{{ __('Export') }}
                                        </option>
                                        <option value="land" @selected($logisticService->type == 'land')>{{ __('Land') }}</option>
                                        <option value="value-added" @selected($logisticService->type == 'value-added')>
                                            {{ __('Value Added') }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Description English -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Service (English)') }}</label>
                                    <input type="text" name="service_en" class="form-control"
                                           value="{{ $logisticService->service_en ?? '' }}"
                                           placeholder="{{ __('Service name') }}">
                                </div>

                                <!-- Description Arabic -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ __('Service (Arabic)') }}</label>
                                    <input type="text" name="service_ar" class="form-control text-end"
                                           value="{{ $logisticService->service_ar ?? '' }}"
                                           placeholder="{{ __('Service in arabic') }}">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">{{ __('Description (about service)') }}</label>
                                    <textarea name="description"
                                              class="form-control h-100">{{ $logisticService->description ?? '' }}</textarea>
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
