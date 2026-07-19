<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save HS Tariff">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $hsTariff->hs_code ?? 'New HS Tariff' }}</span>
                </div>
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="hsTariffForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $hsTariff->id ?? '' }}">

            <div class="model-form-tab-div">
                <div class="row g-3">
                    <div class="col-6 form-group">
                        <label class="form-label required">HS Code <sup class="text-danger">*</sup></label>
                        <input type="text" name="hs_code" class="form-control" required
                               value="{{ $hsTariff->hs_code ?? '' }}">
                    </div>

                    <div class="col-6 form-group">
                        <label class="form-label required">Duty Rate (%) <sup class="text-danger">*</sup></label>
                        <input type="number" name="duty_rate" class="form-control" step="0.01" min="0" max="100"
                               required value="{{ $hsTariff->duty_rate ?? '' }}">
                    </div>

                    <div class="col-8 form-group">
                        <label class="form-label required">Description <sup class="text-danger">*</sup></label>
                        <input type="text" name="description" class="form-control" required
                               value="{{ $hsTariff->description ?? '' }}">
                    </div>

                    <div class="col-4 form-group">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" class="form-control" placeholder="e.g. KG, PCS"
                               value="{{ $hsTariff->unit ?? '' }}">
                    </div>

                    <div class="col-12 form-group">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" role="switch" id="is_active"
                                   name="is_active" value="1"
                                   @checked($hsTariff->is_active ?? true)>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
