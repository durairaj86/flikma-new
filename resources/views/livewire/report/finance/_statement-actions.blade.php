{{--
    Statement actions under the balance card (customer and supplier statement): Send by email, Record collection / payment,
    Collections / Payments history. Needs: $kind ('customer' | 'supplier'), $partyId, $partyName, $partyEmail, $printArea (id of
    the print layout the PDF is made from), $balance, $periodFrom, $periodTo.
--}}
@php
    $isCustomer = $kind === 'customer';
    $docModule = $isCustomer ? 'transactions.collections' : 'transactions.payments';
    $docUrl = url($isCustomer ? 'transaction/collections' : 'transaction/payments') . '?' . $kind . '=' . encodeId($partyId);
    $attach = \App\Services\Finance\FinanceSettings::get('statement_attachment');
    $companyName = optional(authUserCompany())->name;
    $currency = optional(authUserCompany())->base_currency ?: 'SAR';
    $from = \Carbon\Carbon::parse($periodFrom)->format('d-m-Y');
    $to = \Carbon\Carbon::parse($periodTo)->format('d-m-Y');
@endphp
<div class="d-grid gap-2 mt-4 d-print-none">
    <button type="button" class="btn btn-primary btn-sm rounded-pill" id="stmtMailOpen"
            data-email="{{ $partyEmail }}" data-print-area="{{ $printArea }}" data-attach="{{ $attach }}"
            data-subject="{{ __('Statement of account') }} - {{ $partyName }} ({{ $from }} - {{ $to }})"
            data-message="{{ __('Dear :name,', ['name' => $partyName]) }}&#10;&#10;{{ __('Please find attached your statement of account for :from to :to.', ['from' => $from, 'to' => $to]) }}&#10;{{ __('Closing balance') }}: {{ $currency }} {{ number_format((float) $balance, 2) }}&#10;&#10;{{ __('Regards,') }}&#10;{{ $companyName }}"
            data-filename="{{ \Illuminate\Support\Str::slug($partyName) }}-statement.pdf">
        <i class="bi bi-envelope me-1"></i>{{ __('Send by email') }}
    </button>
    @if(userCan($docModule, 'create'))
        {{-- opens the new collection / payment form in the modal right here (the statement script); the link is the fallback --}}
        <a class="btn btn-outline-success btn-sm rounded-pill" href="{{ $docUrl }}&new=1" id="stmtRecordOpen"
           data-url="{{ url($isCustomer ? 'transaction/collections/create' : 'transaction/payments/create') }}?{{ $kind }}={{ encodeId($partyId) }}"
           data-title="{{ $isCustomer ? __('Create Collection') : __('Create Payment') }}">
            <i class="bi bi-plus-circle me-1"></i>{{ $isCustomer ? __('Record Collection') : __('Record Payment') }}
        </a>
    @endif
    @if(userCan($docModule, 'view'))
        <a class="btn btn-outline-secondary btn-sm rounded-pill" href="{{ $docUrl }}">
            <i class="bi bi-clock-history me-1"></i>{{ $isCustomer ? __('Collections History') : __('Payments History') }}
        </a>
    @endif
</div>

<div class="modal fade" id="stmtMailModal" tabindex="-1" aria-labelledby="stmtMailTitle" aria-hidden="true" wire:ignore>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stmtMailTitle"><i class="bi bi-envelope me-2"></i>{{ __('Send statement by email') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="stmtMailTo">{{ __('To') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="stmtMailTo" maxlength="500" placeholder="name@example.com">
                        <div class="form-text">{{ __('Several addresses: separate them with commas.') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="stmtMailCc">{{ __('CC') }}</label>
                        <input type="text" class="form-control" id="stmtMailCc" maxlength="500">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="stmtMailSubject">{{ __('Subject') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="stmtMailSubject" maxlength="191">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="stmtMailMessage">{{ __('Message') }}</label>
                        <textarea class="form-control" id="stmtMailMessage" rows="7" maxlength="5000" style="min-height: 160px;"></textarea>
                    </div>
                    <div class="col-12 small text-muted">
                        <i class="bi bi-paperclip me-1"></i>{{ __('Attached') }}: <span id="stmtMailAttach"></span>
                        · {{ __('Change this in Settings › Finance.') }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="stmtMailSend"><i class="bi bi-send me-1"></i>{{ __('Send') }}</button>
            </div>
        </div>
    </div>
</div>

@script
<script>
    (function () {
        const labels = {pdf: @json(__('PDF')), pdf_excel: @json(__('PDF and Excel')), excel: @json(__('Excel'))};
        const modalEl = document.getElementById('stmtMailModal');

        // the PDF is the print layout, made the same way as Export > PDF Document
        function statementPdf(btn) {
            const area = document.getElementById(btn.dataset.printArea);
            if (!area) return Promise.reject(new Error('no statement'));
            const clone = area.cloneNode(true);
            clone.classList.remove('d-none');
            clone.style.padding = '10px';
            return html2pdf().set({
                margin: 0.4, html2canvas: {scale: 2, useCORS: true},
                jsPDF: {unit: 'in', format: 'a4', orientation: 'portrait'}, pagebreak: {mode: ['avoid-all', 'css']}
            }).from(clone).outputPdf('blob').then(blob => new File([blob], btn.dataset.filename, {type: 'application/pdf'}));
        }

        $(document).off('click.stmtMail').on('click.stmtMail', '#stmtMailOpen', function () {
            $('#stmtMailTo').val(this.dataset.email || '');
            $('#stmtMailCc').val('');
            $('#stmtMailSubject').val(this.dataset.subject);
            $('#stmtMailMessage').val(this.dataset.message);
            $('#stmtMailAttach').text(labels[this.dataset.attach] || labels.pdf);
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            setTimeout(() => document.getElementById($('#stmtMailTo').val() ? 'stmtMailSubject' : 'stmtMailTo').focus(), 300);
        });

        $(document).off('click.stmtSend').on('click.stmtSend', '#stmtMailSend', function () {
            const btn = document.getElementById('stmtMailOpen'), $send = $(this);
            const to = $('#stmtMailTo').val().trim(), subject = $('#stmtMailSubject').val().trim();
            if (!to) { toastr.error(@json(__('Enter the email address.'))); $('#stmtMailTo').focus(); return; }
            if (!subject) { toastr.error(@json(__('Enter the subject.'))); $('#stmtMailSubject').focus(); return; }
            $send.prop('disabled', true);
            const send = () => $wire.sendStatementMail(to, $('#stmtMailCc').val(), subject, $('#stmtMailMessage').val()).then(function (r) {
                if (r && r.ok) {
                    toastr.success(r.message);
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                } else {
                    toastr.error((r && r.message) || @json(__('The email could not be queued.')));
                }
            });
            const done = () => $send.prop('disabled', false);
            if (btn.dataset.attach === 'excel') {
                send().finally(done);
            } else {
                statementPdf(btn)
                    .then(file => new Promise((resolve, reject) => $wire.upload('mailPdf', file, resolve, reject)))
                    .then(send)
                    .catch(() => toastr.error(@json(__('The statement PDF could not be made. Try again.'))))
                    .finally(done);
            }
        });
    })();
</script>
@endscript
