@php
    $balanceAmount = (float) $invoice->balance_amount;
    $currency = strtoupper($invoice->currency ?? 'SAR');
@endphp
<div class="rp-form">

    {{-- Invoice summary strip --}}
    <div class="rp-summary px-4 py-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="rp-eyebrow">Recording Payment For</div>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <h4 class="text-white fw-bold mb-0">{{ $invoice->row_no }}</h4>
                    @if($invoice->job_no)
                        <span class="badge bg-white text-primary fw-semibold">{{ $invoice->job_no }}</span>
                    @endif
                </div>
                <div class="text-white-75 small mt-1">{{ $invoice->customer->name_en ?? '' }} <span class="opacity-75">&middot; {{ $invoice->customer->row_no ?? '' }}</span></div>
            </div>
            <div class="text-md-end">
                <div class="rp-eyebrow">Balance Due</div>
                <div class="text-white fw-bold rp-balance">{{ number_format($balanceAmount, 2) }} <span class="fs-6 fw-normal opacity-75">{{ $currency }}</span></div>
            </div>
        </div>
        <div class="row g-3 mt-1 pt-3 rp-summary-divider">
            <div class="col-4">
                <div class="rp-eyebrow">Invoice Total</div>
                <div class="text-white fw-semibold">{{ number_format($invoice->grand_total, 2) }}</div>
            </div>
            <div class="col-4">
                <div class="rp-eyebrow">Already Paid</div>
                <div class="text-white fw-semibold">{{ number_format($invoice->paid_amount, 2) }}</div>
            </div>
            <div class="col-4">
                <div class="rp-eyebrow">Due Date</div>
                <div class="text-white fw-semibold">{{ $invoice->due_at }}</div>
            </div>
        </div>
    </div>

    <div class="container-fluid align-items-center px-0" id="modal-buttons"
         data-buttons="cancel,saveDraft,saveApprove"
         data-button-draft="Save as Draft"
         data-button-approve="Save and Approve"
         data-approve-url="{{ url('transaction/collections') }}/{id}/status/2"
         data-approve-id-key="collection_id">

        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="collection_id" id="collection_id" value="{{ $collection->id }}">
            <input type="hidden" name="customer" value="{{ encodeId($invoice->customer_id) }}">
            <input type="hidden" name="customer_invoice_ids[]" value="{{ $invoice->id }}">

            <div class="px-4 pt-4 pb-2">

                {{-- Payment amount — the one field that matters most --}}
                <div class="rp-amount-card mb-4">
                    <label for="rp_amount" class="form-label fw-semibold text-uppercase small ls-1 text-muted mb-2">Payment Amount</label>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="input-group rp-amount-input">
                            <span class="input-group-text bg-white border-end-0 fw-semibold">{{ $currency }}</span>
                            <input type="text" step="0.01" class="form-control invoice-amount float border-start-0 fw-bold"
                                   id="rp_amount"
                                   data-id="{{ $invoice->id }}" name="invoice_amounts[{{ $invoice->id }}]"
                                   value="{{ number_format($balanceAmount, 2, '.', '') }}" min="0"
                                   max="{{ $balanceAmount }}" data-balance="{{ $balanceAmount }}" required>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary" id="rp_amount_full">Full Balance</button>
                            <button type="button" class="btn btn-outline-primary" id="rp_amount_half">50%</button>
                        </div>
                        <div class="ms-auto text-end">
                            <div class="small text-muted text-uppercase">Remaining After</div>
                            <div class="fw-bold" id="rp_remaining">0.00 {{ $currency }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="collection_date" class="form-label required">Collection Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control datepicker" id="collection_date" name="collection_date"
                               value="{{ $collection->collection_date ? \Carbon\Carbon::parse($collection->collection_date)->format('d-m-Y') : date('d-m-Y') }}"
                               required>
                    </div>
                    <div class="col-md-4">
                        <label for="payment_method" class="form-label required">Paid Through <span class="text-danger">*</span></label>
                        <x-common.account-groups :parentAccount="$parents"
                                                 :subAccounts="$subAccounts"
                                                 :value="$collection->account"></x-common.account-groups>
                    </div>
                    <div class="col-md-4">
                        <label for="currency" class="form-label required">Currency <span class="text-danger">*</span></label>
                        <x-common.currencies-exchange :value="$invoice->currency"
                                                      exchangeRate="{{ $collection->currency_rate ?? 1 }}"
                                                      width="auto"></x-common.currencies-exchange>
                    </div>
                    <div class="col-md-4">
                        <label for="reference_no" class="form-label required">Reference No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" required
                               value="{{ $collection->reference_no }}"
                               placeholder="Check/Transaction Reference">
                    </div>
                    <div class="col-md-4">
                        <label for="bank_charges" class="form-label">Bank Charges</label>
                        <input type="text" step="0.01" class="form-control float" id="bank_charges"
                               name="bank_charges" value="{{ old('bank_charges', $collection->bank_charges) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="other_charges" class="form-label">Other Charges</label>
                        <input type="text" step="0.01" class="form-control float" id="other_charges"
                               name="other_charges" value="{{ old('other_charges', $collection->other_charges) }}">
                    </div>
                    <div class="col-12">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ $collection->notes }}</textarea>
                    </div>
                </div>

                @if($collection->status == 3)
                    <div class="alert alert-danger mt-3">
                        <strong>Disapproval Reason:</strong> {{ $collection->disapproval_reason }}
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<style>
    .rp-summary {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    }
    .rp-eyebrow {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgba(255, 255, 255, 0.65);
    }
    .rp-balance {
        font-size: 1.75rem;
        line-height: 1.2;
    }
    .text-white-75 { color: rgba(255, 255, 255, 0.85); }
    .rp-summary-divider { border-top: 1px solid rgba(255, 255, 255, 0.2); }
    .rp-amount-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
    }
    .rp-amount-input { max-width: 260px; }
    .rp-amount-input .form-control {
        font-size: 1.15rem;
    }
    .ls-1 { letter-spacing: 0.05em; }
</style>

<script>
    (function () {
        var $amount = $('#rp_amount');
        var balance = parseFloat($amount.data('balance')) || 0;
        var currency = '{{ $currency }}';

        function formatNumber(num) {
            return parseFloat(num || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function updateRemaining() {
            var amount = parseFloat($amount.val()) || 0;
            var remaining = balance - amount;
            var $remaining = $('#rp_remaining');
            $remaining.text(formatNumber(remaining) + ' ' + currency);
            $remaining.toggleClass('text-success', remaining <= 0).toggleClass('text-danger', remaining > 0);
        }

        $amount.off('input.rp').on('input.rp', function () {
            var val = parseFloat($(this).val());
            if (!isNaN(val) && val > balance) {
                $(this).val(balance);
            }
            updateRemaining();
        });

        $('#rp_amount_full').off('click').on('click', function () {
            $amount.val(balance.toFixed(2)).trigger('input.rp');
        });
        $('#rp_amount_half').off('click').on('click', function () {
            $amount.val((balance / 2).toFixed(2)).trigger('input.rp');
        });

        updateRemaining();
    })();
</script>
