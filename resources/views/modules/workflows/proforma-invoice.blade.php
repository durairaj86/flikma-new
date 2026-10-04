@include('modules.workflows._styles')

<!-- Proforma Invoice Workflow Modal -->
<div class="modal fade" id="proformaInvoiceWorkflowModal" tabindex="-1" aria-labelledby="proformaInvoiceWorkflowModalLabel" aria-hidden="true">
    <div class="modal-dialog fc-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 py-2">
                <div>
                    <h5 class="modal-title fw-semibold fs-6" id="proformaInvoiceWorkflowModalLabel">
                        <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Proforma Invoice Workflow') }}
                    </h5>
                    <p class="text-muted mb-0" style="font-size:.72rem;">{{ __('How a proforma invoice is prepared and used') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 pb-2">
                <div class="fc-wrap">
                    <div class="fc-info">
                        <h6>{{ __('Status Guide') }}</h6>
                        <div class="fc-stat" style="background:#fffbeb;border-color:#f59e0b;"><span class="badge bg-secondary">{{ __('Draft') }}</span><span>{{ __('Being prepared. Can be edited or deleted.') }}</span></div><div class="fc-stat" style="background:#f0fdf4;border-color:#16a34a;"><span class="badge bg-success">{{ __('Approved') }}</span><span>{{ __('Final quote. Not posted to the ledger.') }}</span></div><div class="fc-stat" style="background:#ecfeff;border-color:#0891b2;"><span class="badge bg-info">{{ __('Converted') }}</span><span>{{ __('Billed through a customer invoice.') }}</span></div><div class="fc-stat" style="background:#fef2f2;border-color:#dc2626;"><span class="badge bg-danger">{{ __('Cancelled') }}</span><span>{{ __('Withdrawn.') }}</span></div>
                        <h6 class="mt-3">{{ __('Key rules') }}</h6>
                        <ul><li><i class="bi bi-info-circle"></i><span>A proforma is a quote of charges. It does <b>not</b> post ledger entries.</span></li><li><i class="bi bi-briefcase"></i><span>Can be raised from a job or directly.</span></li><li><i class="bi bi-arrow-counterclockwise"></i><span>Approved proformas can move back to Draft.</span></li><li><i class="bi bi-envelope"></i><span>Email it to the customer from the row menu.</span></li><li><i class="bi bi-printer"></i><span>Print and view work at any stage.</span></li></ul>
                    </div>
                    <div class="fc">
<div class="fc-node fc-draft fc-start"><span class="fc-step">{{ __('Start') }}</span><i class="bi bi-play-circle"></i> {{ __('Create proforma') }}<small>{{ __('From a job or directly') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-draft"><span class="badge bg-secondary">{{ __('Draft') }}</span> {{ __('Add charges and tax') }}<small>{{ __('Editable, deletable') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-decision">{{ __('Review outcome?') }}</div>
<div class="fc-branches"><div class="fc-branch" style="flex: 0.8 1 0;"><span class="fc-tag no">{{ __('Cancel') }}</span><div class="fc-node fc-bad fc-end"><i class="bi bi-slash-circle"></i> {{ __('Cancelled') }}<small>{{ __('Withdrawn') }}</small></div></div><div class="fc-branch" style="flex: 2.2 1 0;"><span class="fc-tag yes">{{ __('Approve') }}</span><div class="fc-node fc-ok"><span class="badge bg-success">{{ __('Approved') }}</span> {{ __('Approved') }}<small>{{ __('Share with the customer') }}</small></div><div class="fc-line"></div><div class="fc-node fc-decision">{{ __('Customer confirms?') }}</div><div class="fc-branches"><div class="fc-branch" style="flex: 1 1 0;"><span class="fc-tag no">{{ __('Revise') }}</span><div class="fc-node fc-draft">{{ __('Back to Draft') }}<small>{{ __('Edit and approve again') }}</small></div></div><div class="fc-branch" style="flex: 1.3 1 0;"><span class="fc-tag yes">{{ __('Yes') }}</span><div class="fc-node fc-system"><span class="fc-step">{{ __('System') }}</span><i class="bi bi-arrow-repeat"></i> {{ __('Convert') }}<small>{{ __('Mark as invoiced') }}</small></div><div class="fc-line"></div><div class="fc-node fc-ok fc-end"><i class="bi bi-check2-circle"></i> {{ __('Converted') }}<small>{{ __('Raise the customer invoice') }}</small></div></div></div></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 py-2">
                <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-dismiss="modal"
                        onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Create Proforma Invoice') }}
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
