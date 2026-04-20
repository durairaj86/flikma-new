<div class="modal-header justify-content-between border-bottom py-3" data-close-title="enquiry">
    <div class="row align-items-center bg-white  small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $salesperson->name ?? 'New Salesperson' }}</span></div>
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
                <input type="hidden" name="data-id" value="{{ $salesperson->id ?? '' }}">
                <div class="model-form-tab-div">
                    <div class="row mt-2">
                        <!-- Account Holder -->
                        <div class="col-12 form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control" required
                                   value="{{ $salesperson->name ?? '' }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
