<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save HS Tariff') }}">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $hsTariff->hs_code ?? __('New HS Tariff') }}</span>
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
                        <label class="form-label required">{{ __('HS Code') }} <sup class="text-danger">*</sup></label>
                        <input type="text" name="hs_code" class="form-control" required
                               value="{{ $hsTariff->hs_code ?? '' }}">
                        <div class="field-hint-wrap" data-full="{{ __('The customs commodity code (6 to 10 digits) from the Harmonized System, for example 8471.30 for laptops.') }}"><div class="field-hint" tabindex="0">{{ __('The customs commodity code (6 to 10 digits) from the Harmonized System, for example 8471.30 for laptops.') }}</div></div>
                    </div>

                    <div class="col-6 form-group">
                        <label class="form-label required">{{ __('Duty Rate (%)') }} <sup class="text-danger">*</sup></label>
                        <input type="number" name="duty_rate" class="form-control" step="0.01" min="0" max="100"
                               required value="{{ $hsTariff->duty_rate ?? '' }}">
                        <div class="field-hint-wrap" data-full="{{ __('Customs duty as a percentage of the goods value, from 0 to 100. For example 5 means 5%.') }}"><div class="field-hint" tabindex="0">{{ __('Customs duty as a percentage of the goods value, from 0 to 100. For example 5 means 5%.') }}</div></div>
                    </div>

                    <div class="col-8 form-group">
                        <label class="form-label required">{{ __('Description') }} <sup class="text-danger">*</sup></label>
                        <input type="text" name="description" class="form-control" required
                               value="{{ $hsTariff->description ?? '' }}">
                        <div class="field-hint-wrap" data-full="{{ __('What the goods are, in plain words. It is shown with the code on quotations and clearance jobs.') }}"><div class="field-hint" tabindex="0">{{ __('What the goods are, in plain words. It is shown with the code on quotations and clearance jobs.') }}</div></div>
                    </div>

                    <div class="col-4 form-group">
                        <label class="form-label">{{ __('Unit') }}</label>
                        <input type="text" name="unit" class="form-control" placeholder="{{ __('e.g. KG, PCS') }}"
                               value="{{ $hsTariff->unit ?? '' }}">
                        <div class="field-hint-wrap" data-full="{{ __('The unit the duty is counted in, for example KG or PCS.') }}"><div class="field-hint" tabindex="0">{{ __('The unit the duty is counted in, for example KG or PCS.') }}</div></div>
                    </div>

                    <div class="col-12 form-group">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" role="switch" id="is_active"
                                   name="is_active" value="1"
                                   @checked($hsTariff->is_active ?? true)>
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                        </div>
                        <div class="field-hint-wrap" data-full="{{ __('Switch off to hide this code from new quotations and jobs. Existing records keep it.') }}"><div class="field-hint" tabindex="0">{{ __('Switch off to hide this code from new quotations and jobs. Existing records keep it.') }}</div></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
