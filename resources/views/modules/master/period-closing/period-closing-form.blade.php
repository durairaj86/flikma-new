<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Period') }}">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $periodClosing->year ?? __('New Period') }}</span>
                </div>
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="periodClosingForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $periodClosing->id ?? '' }}">

            <div class="model-form-tab-div">
                <div class="row g-3">
                    <div class="col-12 form-group">
                        <label class="form-label required">{{ __('Closing Date') }} <sup class="text-danger">*</sup></label>
                        <input type="date" name="closing_date" class="form-control datepicker" required
                               data-min-date="01-01-2000" data-max-date="31-12-2100"
                               value="{{ $periodClosing->closing_date ? $periodClosing->closing_date->format('d-m-Y') : '' }}">
                        <small class="text-muted">{{ __('Transactions on or before this date get locked once the period is closed.') }} {{ __('The year is taken from this date.') }}</small>
                    </div>

                    @if(userCan('masters.period-closing', 'approve'))
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" role="switch" id="close_period" name="close_period" value="1">
                                <label class="form-check-label fw-medium" for="close_period">{{ __('Close this period now') }}</label>
                            </div>
                            <small class="text-muted">{{ __('Locks every transaction on or before the closing date as soon as you save. You can reopen it later.') }}</small>
                        </div>
                    @endif

                    <div class="col-12 form-group">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $periodClosing->notes ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
