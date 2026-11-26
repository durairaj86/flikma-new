<div class="row g-3 align-items-center border-bottom py-3 px-4 mb-1 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $collection->row_no ?? __('New Refund') }}</span>
            <span class="badge bg-warning text-dark ms-2">{{ __('Refund') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0" id="modal-buttons"
     data-buttons="cancel,saveDraft,saveApprove"
     data-button-draft="Save as Draft"
     data-button-approve="Save and Approve"
     data-approve-url="{{ url('transaction/collections') }}/{id}/status/2"
     data-approve-id-key="collection_id">

    <form id="moduleForm" novalidate action="{{ url('transaction/collections') }}/{{ $collection->id ? $collection->id . '/' : '' }}refund">
        @csrf
        <input type="hidden" name="collection_id" id="collection_id" value="{{ $collection->id }}">
        <span id="refund-form" class="d-none"></span>
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="alert alert-info small py-2">
                    {{ __('A refund pays money back to the customer out of the advance they hold (an excess collected earlier). It cannot be more than that advance.') }}
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="customer" class="form-label required">{{ __('Customer') }} <span class="text-danger">*</span></label>
                            <x-common.customers :value="$collection->customer_id" :required="true"></x-common.customers>
                            <div class="small text-muted mt-1">{{ __('Advance available') }}: <b id="refund-available">{{ number_format($available, 2) }}</b></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="collection_date" class="form-label required">{{ __('Refund Date') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker" id="collection_date" name="collection_date"
                                   value="{{ $collection->collection_date ? \Carbon\Carbon::parse($collection->collection_date)->format('d-m-Y') : date('d-m-Y') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Paid Through') }} <span class="text-danger">*</span></label>
                            <x-common.account-groups :parentAccount="$parents" :subAccounts="$subAccounts" :value="$collection->account"></x-common.account-groups>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="refund_amount" class="form-label required">{{ __('Refund Amount') }} <span class="text-danger">*</span></label>
                            <input type="text" step="0.01" data-decimal="2" class="form-control float text-end" id="refund_amount" name="amount" required
                                   value="{{ $collection->id ? number_format((float) $collection->grand_total, 2, '.', '') : '' }}" placeholder="0.00" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="reference_no" class="form-label">{{ __('Reference No') }}</label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no" value="{{ $collection->reference_no }}" maxlength="255">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2">{{ $collection->notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
