{{-- Settings › Finance › Cost sheet is on: a customer invoice is made from the job's cost sheet only (shown instead of the invoice form) --}}
<div class="g-3 align-items-center border-bottom py-3 px-4 small cin-modal-head">
    <div class="d-flex justify-content-between align-items-center">
        <span class="fw-semibold fs-5">{{ __('New Customer Invoice') }}</span>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btn-cancel"><i class="bi bi-x-circle me-1"></i> {{ __('Close') }}</button>
    </div>
</div>
<div class="p-5 text-center">
    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-3" style="width:56px;height:56px;font-size:1.5rem;"><i class="bi bi-calculator"></i></span>
    <h6 class="fw-bold">{{ __('Invoices are created from the cost sheet') }}</h6>
    <p class="text-muted mx-auto mb-0" style="max-width:520px;">{{ __('Cost sheet is on for your company. Open the job, choose Invoice › Cost Sheet, fill in the charges and click Create Final Invoice.') }}</p>
</div>
