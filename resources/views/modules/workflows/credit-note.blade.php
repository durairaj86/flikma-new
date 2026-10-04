@include('modules.workflows._styles')

    <!-- Credit Note Workflow Modal -->
    <div class="modal fade" id="creditNoteWorkflowModal" tabindex="-1" aria-labelledby="creditNoteWorkflowModalLabel" aria-hidden="true">
        <div class="modal-dialog fc-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0 py-2">
                    <div>
                        <h5 class="modal-title fw-semibold fs-6" id="creditNoteWorkflowModalLabel">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>{{ __('Credit Note Workflow') }}
                        </h5>
                        <p class="text-muted mb-0" style="font-size:.72rem;">{{ __('How a credit note reduces what a customer owes') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-2 pb-2">
                    <div class="fc-wrap">

                        <!-- Left: status guide and key rules -->
                        <div class="fc-info">
                            <h6>{{ __('Status Guide') }}</h6>
                            <div class="fc-stat" style="background:#fffbeb;border-color:#f59e0b;"><span class="badge bg-secondary">{{ __('Draft') }}</span><span>{{ __('Being prepared. Can be edited or deleted.') }}</span></div>
                            <div class="fc-stat" style="background:#f0fdf4;border-color:#16a34a;"><span class="badge bg-success">{{ __('Approved') }}</span><span>{{ __('Numbered and posted to the ledger. Customer balance goes down.') }}</span></div>
                            <div class="fc-stat" style="background:#fef2f2;border-color:#dc2626;"><span class="badge bg-danger">{{ __('Cancelled') }}</span><span>{{ __('Withdrawn. No ledger effect.') }}</span></div>

                            <h6 class="mt-3">{{ __('Key rules') }}</h6>
                            <ul>
                                <li><i class="bi bi-link-45deg"></i><span>{{ __('Always raised against an approved customer invoice.') }}</span></li>
                                <li><i class="bi bi-currency-exchange"></i><span>{{ __('Currency and rate are copied from that invoice.') }}</span></li>
                                <li><i class="bi bi-hash"></i><span>{{ __('Draft number') }} <b>DRC…</b>, {{ __('final number') }} <b>CR-YY-0001</b> {{ __('on approval.') }}</span></li>
                                <li><i class="bi bi-journal-check"></i><span>{{ __('Approval reverses revenue and VAT against receivable.') }}</span></li>
                                <li><i class="bi bi-arrow-counterclockwise"></i><span>{{ __('Moving an approved note back removes those entries.') }}</span></li>
                                <li><i class="bi bi-shield-check"></i><span>{{ __('With ZATCA, the date becomes today and the note is reported.') }}</span></li>
                            </ul>
                        </div>

                        <!-- Right: flowchart -->
                        <div class="fc">
                            <div class="fc-node fc-ok fc-start">
                                <span class="fc-step">{{ __('Before you start') }}</span>
                                <i class="bi bi-receipt"></i> {{ __('Approved customer invoice') }}
                                <small>{{ __('Wrong rate, cancelled service, duplicate billing') }}</small>
                            </div>
                            <div class="fc-line"></div>
                            <div class="fc-node fc-draft">
                                <span class="fc-step">{{ __('Create') }}</span>
                                <i class="bi bi-file-earmark-minus"></i> {{ __('New credit note') }}
                                <small>{{ __('Pick invoice, reason and the lines to credit') }}</small>
                            </div>
                            <div class="fc-line"></div>
                            <div class="fc-node fc-draft">
                                <span class="badge bg-secondary">{{ __('Draft') }}</span>
                                {{ __('Review amounts and tax') }}
                            </div>
                            <div class="fc-line"></div>
                            <div class="fc-node fc-decision">{{ __('Review outcome?') }}</div>

                            <div class="fc-branches">
                                <div class="fc-branch" style="flex: 0.8 1 0;">
                                    <span class="fc-tag no">{{ __('Cancel') }}</span>
                                    <div class="fc-node fc-bad fc-end">
                                        <i class="bi bi-slash-circle"></i> {{ __('Cancelled') }}
                                        <small>{{ __('No ledger entries') }}</small>
                                    </div>
                                </div>

                                <div class="fc-branch" style="flex: 2.2 1 0;">
                                    <span class="fc-tag yes">{{ __('Approve') }}</span>
                                    <div class="fc-node fc-system"><span class="fc-step">{{ __('System') }}</span>{{ __('Assign number') }}<small>CR-YY-0001</small></div>
                                    <div class="fc-line"></div>
                                    <div class="fc-node fc-system"><span class="fc-step">{{ __('System') }}</span><i class="bi bi-journal-check"></i> {{ __('Post ledger entries') }}<small>{{ __('Dr Revenue + Output VAT / Cr Receivable') }}</small></div>
                                    <div class="fc-line"></div>
                                    <div class="fc-node fc-decision">{{ __('ZATCA result?') }}</div>
                                    <div class="fc-note">{{ __('Skipped when ZATCA is not registered') }}</div>

                                    <div class="fc-branches">
                                        <div class="fc-branch" style="flex: 0.8 1 0;">
                                            <span class="fc-tag no">{{ __('Error') }}</span>
                                            <div class="fc-node fc-bad"><i class="bi bi-arrow-counterclockwise"></i> {{ __('Rolled back') }}<small>{{ __('Stays draft; retry') }}</small></div>
                                        </div>
                                        <div class="fc-branch" style="flex: 1.6 1 0;">
                                            <span class="fc-tag yes">{{ __('OK / skipped') }}</span>
                                            <div class="fc-node fc-ok"><span class="badge bg-success">{{ __('Approved') }}</span> {{ __('Credit note is final') }}<small>{{ __('Ready to print or email') }}</small></div>
                                            <div class="fc-line"></div>
                                            <div class="fc-node fc-money fc-end"><i class="bi bi-cash-coin"></i> {{ __('Customer owes less') }}<small>{{ __('Balance reduced by the credit') }}</small></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 py-2">
                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('Create Credit Note') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
