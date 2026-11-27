@php
    $dec = decimals();
    $num = fn ($n) => $n === null ? '' : number_format((float) $n, $dec, '.', '');
@endphp
<div class="row g-3 align-items-center border-bottom py-3 px-4 mb-1 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $voucher->row_no ?? __('New Transfer') }}</span>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Transfer') }}">

    <form id="moduleForm" novalidate action="{{ route('finance.transfer_vouchers.store') }}">
        @csrf
        <input type="hidden" name="journal_voucher_id" value="{{ $voucher->id }}">
        <div class="mt-3 px-4 pb-3 error-border-off">
            @if((int) $voucher->status === 3 && $voucher->disapproval_reason)
                <div class="alert alert-danger"><strong>{{ __('Disapproval Reason') }}:</strong> {{ $voucher->disapproval_reason }}</div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="tv_from" class="form-label">{{ __('From Account') }} <span class="text-danger">*</span></label>
                    <select class="tom-select" data-live-search="true" id="tv_from" name="from_account_id" data-dropdown-parent="body" required>
                        <option value="">{{ __('Select account') }}</option>
                        @foreach($bankAccounts as $a)
                            <option value="{{ $a->id }}" @selected($fromAccountId == $a->id)>{{ $a->code }} - {{ __($a->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tv_to" class="form-label">{{ __('To Account') }} <span class="text-danger">*</span></label>
                    <select class="tom-select" data-live-search="true" id="tv_to" name="to_account_id" data-dropdown-parent="body" required>
                        <option value="">{{ __('Select account') }}</option>
                        @foreach($bankAccounts as $a)
                            <option value="{{ $a->id }}" @selected($toAccountId == $a->id)>{{ $a->code }} - {{ __($a->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tv_date" class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control datepicker" id="tv_date" name="voucher_date" required
                           value="{{ $voucher->voucher_date ? \Carbon\Carbon::parse($voucher->getRawOriginal('voucher_date'))->format('Y-m-d') : date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="tv_amount" class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control decimal" id="tv_amount" name="amount" required value="{{ $num($amount) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Currency') }} <span class="text-danger">*</span></label>
                    <x-common.currencies-exchange :value="$voucher->currency" exchangeRate="{{ $voucher->currency_rate }}" width="auto"></x-common.currencies-exchange>
                </div>

                <div class="col-md-4">
                    <label for="tv_charge" class="form-label">{{ __('Bank Charge') }}</label>
                    <input type="text" class="form-control decimal" id="tv_charge" name="charge_amount" value="{{ $num($chargeAmount) }}">
                </div>
                <div class="col-md-8">
                    <label for="tv_charge_account" class="form-label">{{ __('Charge Account') }}</label>
                    <select class="tom-select" data-live-search="true" id="tv_charge_account" name="charge_account_id" data-dropdown-parent="body">
                        <option value="">{{ __('Select account') }}</option>
                        @foreach($chargeAccounts as $a)
                            <option value="{{ $a->id }}" @selected($chargeAccountId == $a->id)>{{ $a->code }} - {{ __($a->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tv_reference" class="form-label">{{ __('Reference No') }}</label>
                    <input type="text" class="form-control" id="tv_reference" name="reference_no" maxlength="191" value="{{ $voucher->reference_no }}"
                           placeholder="{{ __('Cheque / bank reference') }}">
                </div>
                <div class="col-md-8">
                    <label for="tv_notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea class="form-control" id="tv_notes" name="notes" rows="1" maxlength="1000">{{ $voucher->notes }}</textarea>
                </div>
            </div>

            {{-- what the voucher will post, so the user sees both sides before saving --}}
            <div class="border rounded bg-light mt-4 p-3 small" id="tv_summary">
                <div class="fw-semibold mb-2">{{ __('Entries on approval') }}</div>
                <table class="table table-sm mb-0 bg-white">
                    <thead><tr><th>{{ __('Account') }}</th><th class="text-end">{{ __('Debit') }}</th><th class="text-end">{{ __('Credit') }}</th></tr></thead>
                    <tbody>
                    <tr><td data-tv="to">—</td><td class="text-end" data-tv="to-amt"></td><td></td></tr>
                    <tr data-tv="charge-row"><td data-tv="charge">—</td><td class="text-end" data-tv="charge-amt"></td><td></td></tr>
                    <tr><td data-tv="from">—</td><td></td><td class="text-end" data-tv="from-amt"></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
    (function () {
        const $form = $('#moduleForm');

        // the account lists open on <body>; Bootstrap's modal focus trap would pull focus back out of their search box and close
        // them, so the trap is off while this form is open (as on the Journal Voucher form)
        const modalEl = document.getElementById('globalModal');
        const releaseFocusTrap = () => bootstrap.Modal.getInstance(modalEl)?._focustrap?.deactivate();
        releaseFocusTrap();
        $(modalEl).one('shown.bs.modal', releaseFocusTrap);
        setTimeout(releaseFocusTrap, 400);
        const num = (sel) => parseFloat(String($(sel).val() || '0').replace(/,/g, '')) || 0;
        const fmt = (n) => n.toFixed({{ $dec }}).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        const text = (sel) => { const o = $(sel).find('option:selected'); return o.val() ? o.text().trim() : '—'; };

        function summary() {
            const amount = num('#tv_amount'), charge = num('#tv_charge');
            $form.find('[data-tv=to]').text(text('#tv_to'));
            $form.find('[data-tv=to-amt]').text(amount ? fmt(amount) : '');
            $form.find('[data-tv=charge-row]').toggle(charge > 0);
            $form.find('[data-tv=charge]').text(text('#tv_charge_account'));
            $form.find('[data-tv=charge-amt]').text(charge ? fmt(charge) : '');
            $form.find('[data-tv=from]').text(text('#tv_from'));
            $form.find('[data-tv=from-amt]').text(amount + charge ? fmt(amount + charge) : '');
        }

        $form.on('input change', '#tv_amount, #tv_charge, #tv_from, #tv_to, #tv_charge_account', summary);
        setTimeout(summary, 50);
        setTimeout(() => document.getElementById('tv_from')?.tomselect?.focus(), 400);
    })();
</script>
