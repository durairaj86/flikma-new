@php [$flowTone, $flowLabel] = ['success', __('Money in · refund from supplier')]; @endphp
@include('modules.transaction._flow-header')
<div class="row g-3 align-items-center border-bottom py-3 px-4 mb-1 small flow-head flow-{{ $flowTone }}" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $payment->row_no ?? __('New Refund') }}</span>
            <span class="badge bg-success ms-2">{{ __('Refund') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0" id="modal-buttons"
     data-buttons="cancel,saveDraft,saveApprove"
     data-button-draft="Save as Draft"
     data-button-approve="Save and Approve"
     data-approve-url="{{ url('transaction/payments') }}/{id}/status/2"
     data-approve-id-key="payment_id">

    <form id="moduleForm" novalidate action="{{ url('transaction/payments') }}/{{ $payment->id ? $payment->id . '/' : '' }}refund">
        @csrf
        <input type="hidden" name="payment_id" id="payment_id" value="{{ $payment->id }}">
        <span id="refund-form" class="d-none"></span>
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="alert alert-info small py-2">
                    {{ __('A refund is money the supplier returns out of the advance we paid them (an excess paid earlier). It cannot be more than that advance.') }}
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="supplier" class="form-label required">{{ __('Supplier') }} <span class="text-danger">*</span></label>
                            <x-common.suppliers :value="$payment->supplier_id" :required="true"></x-common.suppliers>
                            <div class="small text-muted mt-1">{{ __('Advance paid, available to refund') }}: <b id="refund-available">{{ number_format($available, 2) }}</b></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_date" class="form-label required">{{ __('Refund Date') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker" id="payment_date" name="payment_date"
                                   value="{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') : date('d-m-Y') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Refund Through') }} <span class="text-danger">*</span></label>
                            <x-common.account-groups :parentAccount="$parents" :subAccounts="$paidThroughAccounts" :value="$payment->account"></x-common.account-groups>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="refund_amount" class="form-label required">{{ __('Refund Amount') }} <span class="text-danger">*</span></label>
                            <input type="text" step="0.01" data-decimal="2" class="form-control float text-end" id="refund_amount" name="amount" required
                                   value="{{ $payment->id ? number_format((float) $payment->grand_total, 2, '.', '') : '' }}" placeholder="0.00" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="reference_no" class="form-label">{{ __('Reference No') }}</label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no" value="{{ $payment->reference_no }}" maxlength="255">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2">{{ $payment->notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
