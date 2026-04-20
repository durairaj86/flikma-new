<div class="modal-header justify-content-between border-bottom py-3" data-close-title="enquiry">
    <div class="row align-items-center bg-white  small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $unit->name ?? 'New Unit' }}</span></div>
        </div>
    </div>
    <div id="show-buttons"></div>
</div>
<div class="modal-body p-0">
    <div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
         data-button-save="Save Salesperson">
        <div class="row">
            <form id="moduleForm" novalidate action="{{ request()->url() }}">
                @csrf
                <input type="hidden" name="data-id" value="{{ $unit->id ?? '' }}">
                <div class="model-form-tab-div">
                    <div class="row mt-2">
                        <!-- Account Holder -->
                        <div class="col-6 form-group">
                            <label class="form-label">Unit Name</label>
                            <input type="text" name="unit_name" id="unit_name" class="form-control" required
                                   value="{{ $unit->unit_name ?? '' }}">
                        </div>
                        <div class="col-6 form-group">
                            <label class="form-label">Unit Symbol</label>
                            <input type="text" name="unit_symbol" id="unit_symbol" class="form-control" required
                                   value="{{ $unit->unit_symbol ?? '' }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
