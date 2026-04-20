<div class="modal-header">
    <h5 class="modal-title">Add New Prospect</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="quickModuleForm" novalidate action="{{ request()->url() }}">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Prospect Name <span class="text-danger">*</span></label>
                <input type="text" id="quick-prospect-name" name="quick_prospect_name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Salesperson <span class="text-danger">*</span></label>
                <x-common.salesperson
                    :value="$prospect->salesperson_id"></x-common.salesperson>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="quick_prospect_email" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="quick_prospect_phone" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="quick_prospect_address" class="form-control" rows="2"></textarea>
        </div>
    </form>
</div>
