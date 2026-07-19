<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Period">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $periodClosing->year ?? 'New Period' }}</span>
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
                    <div class="col-6 form-group">
                        <label class="form-label required">Year <sup class="text-danger">*</sup></label>
                        <input type="number" name="year" class="form-control" required min="2000" max="2100"
                               value="{{ $periodClosing->year ?? now()->year }}">
                    </div>

                    <div class="col-6 form-group">
                        <label class="form-label required">Closing Date <sup class="text-danger">*</sup></label>
                        <input type="date" name="closing_date" class="form-control" required
                               value="{{ $periodClosing->closing_date ? $periodClosing->closing_date->format('Y-m-d') : '' }}">
                        <small class="text-muted">Transactions on or before this date get locked once the period is closed.</small>
                    </div>

                    <div class="col-12 form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $periodClosing->notes ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
