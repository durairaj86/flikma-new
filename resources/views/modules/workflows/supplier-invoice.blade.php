@include('modules.workflows._styles')

<!-- Supplier Invoice Workflow Modal -->
<div class="modal fade" id="supplierInvoiceWorkflowModal" tabindex="-1" aria-labelledby="supplierInvoiceWorkflowModalLabel" aria-hidden="true">
    <div class="modal-dialog fc-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 py-2">
                <div>
                    <h5 class="modal-title fw-semibold fs-6" id="supplierInvoiceWorkflowModalLabel">
                        <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Supplier Invoice Workflow') }}
                    </h5>
                    <p class="text-muted mb-0" style="font-size:.72rem;">{{ __('How a supplier bill moves from draft to payment') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 pb-2">
                <div class="fc-wrap">
                    <div class="fc-info">
                        <h6>{{ __('Status Guide') }}</h6>
                        <div class="fc-stat" style="background:#fffbeb;border-color:#f59e0b;"><span class="badge bg-secondary">{{ __('Draft') }}</span><span>{{ __('Being prepared. Can be edited or deleted.') }}</span></div><div class="fc-stat" style="background:#f0fdf4;border-color:#16a34a;"><span class="badge bg-success">{{ __('Approved') }}</span><span>{{ __('Posted to the ledger, ready to pay.') }}</span></div><div class="fc-stat" style="background:#fef2f2;border-color:#dc2626;"><span class="badge bg-danger">{{ __('Cancelled') }}</span><span>{{ __('Withdrawn before approval.') }}</span></div>
                        <h6 class="mt-3">{{ __('Key rules') }}</h6>
                        <ul><li><i class="bi bi-journal-check"></i><span>Approval posts your cost and VAT entries against the supplier payable.</span></li><li><i class="bi bi-arrow-counterclockwise"></i><span>Moving an approved bill back removes those entries.</span></li><li><i class="bi bi-briefcase"></i><span>Can be raised from a job to track job cost.</span></li><li><i class="bi bi-cash-coin"></i><span>Pay it from the Payments module.</span></li><li><i class="bi bi-printer"></i><span>Print and email work at any stage.</span></li></ul>
                    </div>
                    <div class="fc">
<div class="fc-node fc-draft fc-start"><span class="fc-step">{{ __('Start') }}</span><i class="bi bi-play-circle"></i> {{ __('Create supplier invoice') }}<small>{{ __('From a job or directly') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-draft"><span class="badge bg-secondary">{{ __('Draft') }}</span> {{ __('Add cost lines and tax') }}<small>{{ __('Editable, deletable') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-decision">{{ __('Review outcome?') }}</div>
<div class="fc-branches"><div class="fc-branch" style="flex: 0.8 1 0;"><span class="fc-tag no">{{ __('Cancel') }}</span><div class="fc-node fc-bad fc-end"><i class="bi bi-slash-circle"></i> {{ __('Cancelled') }}<small>{{ __('No ledger entries') }}</small></div></div><div class="fc-branch" style="flex: 1.6 1 0;"><span class="fc-tag yes">{{ __('Approve') }}</span><div class="fc-node fc-system"><span class="fc-step">{{ __('System') }}</span><i class="bi bi-journal-check"></i> {{ __('Post ledger entries') }}<small>{{ __('Cost and input VAT to payable') }}</small></div><div class="fc-line"></div><div class="fc-node fc-ok"><span class="badge bg-success">{{ __('Approved') }}</span> {{ __('Approved') }}<small>{{ __('Ready for payment') }}</small></div><div class="fc-line"></div><div class="fc-node fc-money"><i class="bi bi-cash-coin"></i> {{ __('Pay supplier') }}<small>{{ __('Payments module') }}</small></div><div class="fc-line"></div><div class="fc-node fc-ok fc-end"><i class="bi bi-patch-check"></i> {{ __('Fully paid') }}<small>{{ __('Part payments leave a balance') }}</small></div></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 py-2">
                <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-dismiss="modal"
                        onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Create Supplier Invoice') }}
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
