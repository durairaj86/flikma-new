<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save" data-modal-size="md" data-modal-height="300"
     data-button-save="Save Prospect">
    <!-- Meta Info -->
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="module-info">
                    <span class="fw-semibold fs-5">{{ $prospect->name ?? 'New Prospect' }}</span> <small
                        class="text-secondary">{{ $prospect->row_no ? ' - ' . $prospect->row_no : '' }}</small>
                </div>

            </div>

            <!-- Save & Next Button -->
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prospect Name <span class="text-danger">*</span></label>
                    <input type="text" id="quick-prospect-name" name="quick_prospect_name" class="form-control"
                           value="{{ $prospect->name_en }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Salesperson <span class="text-danger">*</span></label>
                    <x-common.salesperson
                        :value="$prospect->salesperson_id"></x-common.salesperson>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="quick_prospect_email" class="form-control" value="{{ $prospect->email }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="quick_prospect_phone" class="form-control" value="{{ $prospect->phone }}"
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="quick_prospect_address" class="form-control h-100"
                          rows="2">{{ nl2br($prospect->address) }}</textarea>
            </div>

        </form>
    </div>
</div>
