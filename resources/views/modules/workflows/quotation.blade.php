@include('modules.workflows._styles')

<!-- Quotation Workflow Modal -->
<div class="modal fade" id="quotationWorkflowModal" tabindex="-1" aria-labelledby="quotationWorkflowModalLabel" aria-hidden="true">
    <div class="modal-dialog fc-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 py-2">
                <div>
                    <h5 class="modal-title fw-semibold fs-6" id="quotationWorkflowModalLabel">
                        <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Quotation Workflow') }}
                    </h5>
                    <p class="text-muted mb-0" style="font-size:.72rem;">{{ __('How a quotation becomes a job') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 pb-2">
                <div class="fc-wrap">
                    <div class="fc-info">
                        <h6>{{ __('Status Guide') }}</h6>
                        <div class="fc-stat" style="background:#fffbeb;border-color:#f59e0b;"><span class="badge bg-secondary">{{ __('Pending') }}</span><span>{{ __('Being prepared or awaiting the customer.') }}</span></div><div class="fc-stat" style="background:#f0fdf4;border-color:#16a34a;"><span class="badge bg-success">{{ __('Accepted') }}</span><span>{{ __('Customer said yes. Can move back to Pending.') }}</span></div><div class="fc-stat" style="background:#ecfeff;border-color:#0891b2;"><span class="badge bg-info">{{ __('Converted') }}</span><span>{{ __('A job has been created from it.') }}</span></div><div class="fc-stat" style="background:#fef2f2;border-color:#dc2626;"><span class="badge bg-danger">{{ __('Cancelled') }}</span><span>{{ __('Withdrawn.') }}</span></div><div class="fc-stat" style="background:#fffbeb;border-color:#f59e0b;"><span class="badge bg-warning text-dark">{{ __('Expired') }}</span><span>{{ __('Passed its valid-until date.') }}</span></div>
                        <h6 class="mt-3">{{ __('Key rules') }}</h6>
                        <ul><li><i class="bi bi-hash"></i><span>Numbered <b>QTN/YYYY/001</b>.</span></li><li><i class="bi bi-calendar-event"></i><span>Valid for 30 days by default.</span></li><li><i class="bi bi-boxes"></i><span>Containers, packages and charges are quoted here.</span></li><li><i class="bi bi-briefcase"></i><span>Converting creates the job and copies the details.</span></li><li><i class="bi bi-person-plus"></i><span>A prospect becomes a customer on conversion.</span></li><li><i class="bi bi-envelope"></i><span>Pending quotations can be emailed.</span></li></ul>
                    </div>
                    <div class="fc">
<div class="fc-node fc-draft fc-start"><span class="fc-step">{{ __('Start') }}</span><i class="bi bi-play-circle"></i> {{ __('Create quotation') }}<small>{{ __('From an enquiry or directly') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-draft"><span class="badge bg-secondary">{{ __('Pending') }}</span> {{ __('Add cargo and charges') }}<small>{{ __('Set the valid-until date') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-decision">{{ __('Customer reply?') }}</div>
<div class="fc-branches"><div class="fc-branch" style="flex: 0.8 1 0;"><span class="fc-tag no">{{ __('Cancel') }}</span><div class="fc-node fc-bad fc-end"><i class="bi bi-slash-circle"></i> {{ __('Cancelled') }}<small>{{ __('Withdrawn') }}</small></div></div><div class="fc-branch" style="flex: 2.2 1 0;"><span class="fc-tag yes">{{ __('Accept') }}</span><div class="fc-node fc-ok"><span class="badge bg-success">{{ __('Accepted') }}</span> {{ __('Accepted') }}<small>{{ __('Ready to convert') }}</small></div><div class="fc-line"></div><div class="fc-node fc-decision">{{ __('Convert to job?') }}</div><div class="fc-branches"><div class="fc-branch" style="flex: 1 1 0;"><span class="fc-tag no">{{ __('Not yet') }}</span><div class="fc-node fc-draft">{{ __('Stays accepted') }}<small>{{ __('Can go back to Pending') }}</small></div></div><div class="fc-branch" style="flex: 1.3 1 0;"><span class="fc-tag yes">{{ __('Yes') }}</span><div class="fc-node fc-system"><span class="fc-step">{{ __('System') }}</span><i class="bi bi-briefcase"></i> {{ __('Create job') }}<small>{{ __('JOB-YY-0001, data copied') }}</small></div><div class="fc-line"></div><div class="fc-node fc-ok fc-end"><i class="bi bi-check2-circle"></i> {{ __('Converted') }}<small>{{ __('Quotation linked to its job') }}</small></div></div></div></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 py-2">
                <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-dismiss="modal"
                        onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Create Quotation') }}
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
