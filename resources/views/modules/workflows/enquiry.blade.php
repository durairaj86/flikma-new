@include('modules.workflows._styles')

<!-- Enquiry Workflow Modal -->
<div class="modal fade" id="enquiryWorkflowModal" tabindex="-1" aria-labelledby="enquiryWorkflowModalLabel" aria-hidden="true">
    <div class="modal-dialog fc-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 py-2">
                <div>
                    <h5 class="modal-title fw-semibold fs-6" id="enquiryWorkflowModalLabel">
                        <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Enquiry Workflow') }}
                    </h5>
                    <p class="text-muted mb-0" style="font-size:.72rem;">{{ __('How an enquiry becomes a quotation') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 pb-2">
                <div class="fc-wrap">
                    <div class="fc-info">
                        <h6>{{ __('Status Guide') }}</h6>
                        <div class="fc-stat" style="background:#fffbeb;border-color:#f59e0b;"><span class="badge bg-secondary">{{ __('Pending') }}</span><span>{{ __('New enquiry. Can be edited or emailed.') }}</span></div><div class="fc-stat" style="background:#f0fdf4;border-color:#16a34a;"><span class="badge bg-success">{{ __('Confirmed') }}</span><span>{{ __('Customer agreed. Ready to be quoted.') }}</span></div><div class="fc-stat" style="background:#ecfeff;border-color:#0891b2;"><span class="badge bg-info">{{ __('Quotation') }}</span><span>{{ __('A quotation has been created from it.') }}</span></div><div class="fc-stat" style="background:#fef2f2;border-color:#dc2626;"><span class="badge bg-danger">{{ __('Cancelled') }}</span><span>{{ __('Customer declined or went away.') }}</span></div>
                        <h6 class="mt-3">{{ __('Key rules') }}</h6>
                        <ul><li><i class="bi bi-hash"></i><span>{{ __('Numbered') }} <b>ENQ001</b>, <b>ENQ002</b> ...</span></li><li><i class="bi bi-person-check"></i><span>{{ __('Needs a customer or a prospect.') }}</span></li><li><i class="bi bi-signpost-split"></i><span>{{ __('Route (POL / POD), cargo and services are captured here.') }}</span></li><li><i class="bi bi-arrow-right-circle"></i><span>{{ __('Converting pre-fills the quotation.') }}</span></li><li><i class="bi bi-printer"></i><span>{{ __('Print and view work at any stage.') }}</span></li></ul>
                    </div>
                    <div class="fc">
<div class="fc-node fc-draft fc-start"><span class="fc-step">{{ __('Start') }}</span><i class="bi bi-play-circle"></i> {{ __('Create enquiry') }}<small>{{ __('Customer or prospect, ENQ001') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-draft"><span class="badge bg-secondary">{{ __('Pending') }}</span> {{ __('Add route and cargo') }}<small>{{ __('POL, POD, containers or packages') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-decision">{{ __('Customer decision?') }}</div>
<div class="fc-branches"><div class="fc-branch" style="flex: 0.8 1 0;"><span class="fc-tag no">{{ __('Cancel') }}</span><div class="fc-node fc-bad fc-end"><i class="bi bi-slash-circle"></i> {{ __('Cancelled') }}<small>{{ __('Remark: customer cancelled') }}</small></div></div><div class="fc-branch" style="flex: 1.6 1 0;"><span class="fc-tag yes">{{ __('Confirm') }}</span><div class="fc-node fc-ok"><span class="badge bg-success">{{ __('Confirmed') }}</span> {{ __('Confirmed') }}<small>{{ __('Ready to quote') }}</small></div><div class="fc-line"></div><div class="fc-node fc-system"><span class="fc-step">{{ __('System') }}</span><i class="bi bi-arrow-repeat"></i> {{ __('Convert to quotation') }}<small>{{ __('Details copied across') }}</small></div><div class="fc-line"></div><div class="fc-node fc-ok fc-end"><i class="bi bi-check2-circle"></i> {{ __('Quotation created') }}<small>{{ __('Enquiry marked as Quotation') }}</small></div></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 py-2">
                <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-dismiss="modal"
                        onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Create Enquiry') }}
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
