<div class="modal-header">
    <h5 class="modal-title">{{ __('Add New Customer') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="quickModuleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">{{ __('Customer Name') }} <span class="text-danger">*</span></label>
            <input type="text" id="quick-customer-name" name="quick_customer_name" class="form-control" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="quick_customer_email" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('Phone') }}</label>
                <input type="text" name="quick_customer_phone" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Address') }}</label>
            <textarea name="quick_customer_address" class="form-control" rows="2"></textarea>
        </div>

    </form>
</div>
