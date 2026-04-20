<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Customer">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $logisticActivity->name ?? 'New Activity' }}</span>
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
                                <!-- Mode -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mode</label>
                                    <select name="mode" class="form-control tom-select" required>
                                        <option value="">Select Mode</option>
                                        <option value="sea" @selected($logisticActivity->mode == 'sea')>Sea</option>
                                        <option value="air" @selected($logisticActivity->mode == 'air')>Air</option>
                                        <option value="land" @selected($logisticActivity->mode == 'land')>Land</option>
                                        <option value="vas" @selected($logisticActivity->mode == 'vas')>Value Added
                                            Service
                                        </option>
                                    </select>
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Type</label>
                                    <select name="type" class="form-control tom-select" required>
                                        <option value="">Select Category</option>
                                        <option value="import" @selected($logisticActivity->type == 'import')>Import
                                        </option>
                                        <option value="export" @selected($logisticActivity->type == 'export')>Export
                                        </option>
                                        <option value="land" @selected($logisticActivity->type == 'land')>Land</option>
                                        <option value="value-added" @selected($logisticActivity->type == 'value-added')>
                                            Value Added
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Service</label>
                                    <input type="text" name="service_en" class="form-control"
                                           value="{{ $logisticActivity->service ?? '' }}"
                                           placeholder="Clearance">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Activity Name</label>
                                    <input type="text" name="name" class="form-control disabled" disabled
                                           value="{{ $logisticActivity->name ?? '' }}"
                                           placeholder="Sea Import Clearance">
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
