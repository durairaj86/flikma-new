@include('modules.workflows._styles')

<!-- Job Workflow Modal -->
<div class="modal fade" id="jobWorkflowModal" tabindex="-1" aria-labelledby="jobWorkflowModalLabel" aria-hidden="true">
    <div class="modal-dialog fc-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 py-2">
                <div>
                    <h5 class="modal-title fw-semibold fs-6" id="jobWorkflowModalLabel">
                        <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Job Workflow') }}
                    </h5>
                    <p class="text-muted mb-0" style="font-size:.72rem;">{{ __('How a job runs from start to completion') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 pb-2">
                <div class="fc-wrap">
                    <div class="fc-info">
                        <h6>{{ __('Status Guide') }}</h6>
                        <div class="fc-stat" style="background:#fffbeb;border-color:#f59e0b;"><span class="badge bg-secondary">{{ __('Pending') }}</span><span>{{ __('Active job. Shipment work in progress.') }}</span></div><div class="fc-stat" style="background:#f0fdf4;border-color:#16a34a;"><span class="badge bg-success">{{ __('Completed') }}</span><span>{{ __('Job finished. Invoices can still be raised.') }}</span></div><div class="fc-stat" style="background:#fef2f2;border-color:#dc2626;"><span class="badge bg-danger">{{ __('Cancelled') }}</span><span>{{ __('Job stopped.') }}</span></div>
                        <h6 class="mt-3">{{ __('Key rules') }}</h6>
                        <ul><li><i class="bi bi-hash"></i><span>Numbered <b>JOB-YY-0001</b>.</span></li><li><i class="bi bi-signpost-2"></i><span>Shipment details, ETD, ETA, containers and packages are kept on the job.</span></li><li><i class="bi bi-receipt"></i><span>Proforma, customer and supplier invoices are raised from the job.</span></li><li><i class="bi bi-pencil"></i><span>Pending jobs can be edited or deleted.</span></li><li><i class="bi bi-file-earmark-text"></i><span>Airway, seaway and waybill documents link to the job.</span></li></ul>
                    </div>
                    <div class="fc">
<div class="fc-node fc-draft fc-start"><span class="fc-step">{{ __('Start') }}</span><i class="bi bi-play-circle"></i> {{ __('Create job') }}<small>{{ __('From a quotation or directly') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-draft"><span class="badge bg-secondary">{{ __('Pending') }}</span> {{ __('Run the shipment') }}<small>{{ __('Routing, containers, packages') }}</small></div>
<div class="fc-line"></div>
<div class="fc-node fc-money"><i class="bi bi-receipt"></i> {{ __('Raise invoices') }}<small>{{ __('From the job menu') }}</small></div>
<div class="fc-branches"><div class="fc-branch" style="flex: 1 1 0;"><span class="fc-tag yes">{{ __('') }}</span><div class="fc-node fc-money">{{ __('Proforma') }}<small>{{ __('Quote the charges') }}</small></div></div><div class="fc-branch" style="flex: 1 1 0;"><span class="fc-tag yes">{{ __('') }}</span><div class="fc-node fc-money">{{ __('Customer invoice') }}<small>{{ __('Bill the customer') }}</small></div></div><div class="fc-branch" style="flex: 1 1 0;"><span class="fc-tag yes">{{ __('') }}</span><div class="fc-node fc-money">{{ __('Supplier invoice') }}<small>{{ __('Record costs') }}</small></div></div></div>
<div class="fc-line"></div>
<div class="fc-node fc-decision">{{ __('Job outcome?') }}</div>
<div class="fc-branches"><div class="fc-branch" style="flex: 0.8 1 0;"><span class="fc-tag no">{{ __('Cancel') }}</span><div class="fc-node fc-bad fc-end"><i class="bi bi-slash-circle"></i> {{ __('Cancelled') }}<small>{{ __('Job stopped') }}</small></div></div><div class="fc-branch" style="flex: 1.4 1 0;"><span class="fc-tag yes">{{ __('Complete') }}</span><div class="fc-node fc-ok"><span class="badge bg-success">{{ __('Completed') }}</span> {{ __('Completed') }}<small>{{ __('Invoices still allowed') }}</small></div><div class="fc-line"></div><div class="fc-node fc-ok fc-end"><i class="bi bi-patch-check"></i> {{ __('Closed out') }}<small>{{ __('Edit if corrections are needed') }}</small></div></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 py-2">
                <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-dismiss="modal"
                        onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Create Job') }}
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
