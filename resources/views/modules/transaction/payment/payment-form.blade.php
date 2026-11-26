@php [$flowTone, $flowLabel] = ($advanceMode ?? false) ? ['warning', __('Money out · advance to supplier')] : ['info', __('Settles invoices')]; @endphp
@include('modules.transaction._flow-header')
<div class="row g-3 align-items-center border-bottom py-3 px-4 mb-1 small flow-head flow-{{ $flowTone }}" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $payment->row_no ?? (($advanceMode ?? false) ? __('New Advance') : __('New Payment')) }}</span>
                @if(($advanceMode ?? false) && $payment->id)<span class="badge bg-warning text-dark ms-2">{{ __('Advance') }}</span>@endif
            </div>

        </div>

        <!-- Save & Next Button -->
        <div id="show-buttons"></div>
    </div>
</div>

<script>
    // Account options for dynamically-added "Additional Transactions" rows.
    // This modal's HTML is injected via jQuery.html(), which does not go
    // through @stack('scripts'), so payment.js reads this global directly
    // instead of cloning options from a server-rendered row (which doesn't
    // exist yet on a brand-new payment).
    window.PAYMENT_SUB_ACCOUNTS = @json($subAccounts->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->values());
</script>

<div class="container-fluid align-items-center px-0" id="modal-buttons" data-buttons="cancel,saveDraft,saveApprove"
     data-button-draft="Save as Draft"
     data-button-approve="Save and Approve"
     data-approve-url="{{ url('transaction/payments') }}/{id}/status/2"
     data-approve-id-key="payment_id">

    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="payment_id" id="payment_id" value="{{ $payment->id }}">
        @if($advanceMode ?? false)<span id="advance-mode" class="d-none"></span><input type="hidden" name="advance_mode" value="1">@endif
        <div class="mb-2 mt-2 border-0 px-4 <!--error-border-off-->">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6 col-lg-3">
                        <div>
                            <label for="supplier" class="form-label required">{{ __('Supplier') }} <span
                                    class="text-danger">*</span></label>
                            <x-common.suppliers :value="$payment->supplier_id" :required="true"></x-common.suppliers>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-2">
                        <div>
                            <label for="payment_date" class="form-label required">{{ __('Payment Date') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker" id="payment_date" name="payment_date"
                                   value="{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') : date('d-m-Y') }}"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div>
                            <label for="payment_method" class="form-label required">{{ __('Paid Through') }} <span
                                    class="text-danger">*</span></label>
                            <x-common.account-groups :parentAccount="$parents"
                                                     :subAccounts="$paidThroughAccounts"
                                                     :value="$payment->account"></x-common.account-groups>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div>
                            <label for="currency" class="form-label required">{{ __('Currency') }} <span
                                    class="text-danger">*</span></label>
                            <x-common.currencies-exchange :value="$payment->currency"
                                                          exchangeRate="{{ $payment->currency_rate }}"
                                                          width="auto"></x-common.currencies-exchange>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div>
                            <label for="reference_no" class="form-label">{{ __('Reference No') }}</label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no"
                                   value="{{ $payment->reference_no }}"
                                   placeholder="{{ __('Check/Transaction Reference') }}">
                        </div>
                    </div>
                </div>

                @if($payment->status == 3)
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <strong>{{ __('Disapproval Reason') }}:</strong> {{ $payment->disapproval_reason }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="border-0 mb-2 px-4">
            <div class="card-body p-0">
                @unless($advanceMode ?? false)
                <div class="row mt-2">
                    <div class="col-12">
                        <h6 class="fw-semibold mb-2">{{ __('Supplier Invoices') }}</h6>
                        <div class="alert alert-info small py-2 mb-0" id="no-invoices-message"
                             style="{{ count($supplierInvoices) > 0 || ($opening['balance'] ?? 0) > 0 || ($opening['chosen'] ?? 0) > 0 ? 'display: none;' : '' }}">
                            {{ __('No invoices available for this supplier.') }}
                        </div>
                        <div class="table-responsive" id="invoices-table-container"
                             style="{{ count($supplierInvoices) > 0 || ($opening['balance'] ?? 0) > 0 || ($opening['chosen'] ?? 0) > 0 ? '' : 'display: none;' }}">
                            <table class="table table-sm table-bordered table-striped align-middle mb-0" id="invoices-table">
                                <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox"
                                                          id="select-all-invoices" {{--{{ $payment->status != 1 ? 'disabled' : '' }}--}}>
                                    </th>
                                    <th>{{ __('Invoice No') }}</th>
                                    <th>{{ __('Job No') }}</th>
                                    <th>{{ __('Invoice Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th class="text-end">{{ __('Total Amount') }}</th>
                                    <th class="text-end">{{ __('Payment Amount') }}</th>
                                    <th class="text-end">{{ __('Balance Amount') }}</th>
                                </tr>
                                </thead>
                                <tbody id="invoices-body">
                                @foreach($supplierInvoices as $invoice)
                                    @php
                                        //$paymentInvoice = $payment->paymentInvoices->firstWhere('supplier_invoice_id', $invoice->id);
                                        $isSelected = isset($selectedInvoice) && array_key_exists($invoice->id, $selectedInvoice);
                                        $balanceAmount = isset($invoice->balance_amount) ? $invoice->balance_amount : ($invoice->grand_total - ($invoice->paid_amount ?? 0));
                                        $paymentAmount = $isSelected ? $selectedInvoice[$invoice->id] : $balanceAmount;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="invoice-checkbox"
                                                   name="supplier_invoice_ids[]"
                                                   data-id="{{ $invoice->id }}" value="{{ $invoice->id }}"
                                                   data-amount="{{ $invoice->grand_total }}" @checked($isSelected) {{--{{ $payment->status != 1 ? 'disabled' : '' }}--}}>
                                        </td>
                                        @php
                                            // other draft payments already hold part of what this invoice owes: the balance is after them
                                            $draftHeld = \Illuminate\Support\Facades\DB::table('payment_invoices as pi')
                                                ->join('payments as p', 'p.id', '=', 'pi.payment_id')
                                                ->where('pi.supplier_invoice_id', $invoice->id)->whereNull('p.deleted_at')
                                                ->where('p.status', \App\Enums\PaymentEnum::DRAFT->value)
                                                ->when(!empty($payment->id), fn ($q) => $q->where('p.id', '!=', $payment->id))
                                                ->get(['p.row_no', 'pi.amount']);
                                        @endphp
                                        <td>
                                            {{ $invoice->row_no }}
                                            @if($draftHeld->count())
                                                <div class="small text-warning-emphasis" title="{{ __('Draft payments are taken off in the Payment form') }}">
                                                    <i class="bi bi-hourglass-split"></i>
                                                    {{ number_format($draftHeld->sum('amount'), 2) }} {{ __('in draft payment') }} ({{ $draftHeld->pluck('row_no')->implode(', ') }})
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->job_no }}</td>
                                        <td>{{ $invoice->invoice_date }}</td>
                                        <td>{{ $invoice->due_at }}</td>
                                        <td class="text-end">{{ number_format($invoice->grand_total, 2) }}</td>
                                        <td class="text-end">
                                            <input type="text" step="0.01" class="form-control invoice-amount float text-end @if($isSelected) bg-white @endif"
                                                   data-id="{{ $invoice->id }}"
                                                   name="invoice_amounts[{{ $invoice->id }}]"
                                                   value="{{ $paymentAmount }}" min="0"
                                                   max="{{ $balanceAmount }}"
                                                   data-balance="{{ $balanceAmount }}"
                                                   {{ !$isSelected /*|| $payment->status != 1*/ ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($balanceAmount - ($isSelected ? $paymentAmount : 0), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                @php($obChosen = (float) ($opening['chosen'] ?? 0))
                                @php($obBalance = (float) ($opening['balance'] ?? 0))
                                @if($obBalance > 0.004 || $obChosen > 0)
                                    <tr class="ob-row">
                                        <td><input type="checkbox" class="invoice-checkbox" name="opening_check" value="1" data-id="ob" data-amount="{{ $obBalance }}" @checked($obChosen > 0)></td>
                                        <td>{{ __('Opening Balance') }} <span class="badge bg-light text-dark border">{{ $opening['currency'] ?? '' }}</span></td>
                                        <td></td><td></td><td></td>
                                        <td class="text-end">{{ number_format($obBalance, 2) }}</td>
                                        <td class="text-end">
                                            <input type="text" step="0.01" data-decimal="2" class="form-control invoice-amount float text-end @if($obChosen > 0) bg-white @endif" data-id="ob" name="opening_amount"
                                                   value="{{ number_format($obChosen > 0 ? $obChosen : $obBalance, 2, '.', '') }}" min="0" max="{{ number_format($obBalance, 2, '.', '') }}" data-balance="{{ number_format($obBalance, 2, '.', '') }}" {{ $obChosen > 0 ? '' : 'disabled' }}>
                                        </td>
                                        <td class="text-end">{{ number_format($obBalance - $obChosen, 2) }}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        @php($hasTransactions = isset($payment->additionalTransactions) && $payment->additionalTransactions->count() > 0)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h6 class="fw-semibold mb-0">{{ __('Additional Transactions') }} <span class="small fw-normal text-muted">&middot; {{ __('Add additional transactions apart from supplier invoice payments.') }}</span></h6>
                            <button type="button" class="btn btn-outline-primary btn-sm ms-auto" id="add-transaction">
                                <i class="fas fa-plus"></i> {{ __('Add Transaction') }}
                            </button>
                        </div>

                        <div class="table-responsive" id="additional-transactions-wrap" style="{{ $hasTransactions ? '' : 'display: none;' }}">
                            <table class="table table-sm table-bordered align-middle mb-0" id="additional-transactions-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Account') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th width="5%">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody id="additional-transactions-body">
                                @if($hasTransactions)
                                    @foreach($payment->additionalTransactions as $index => $transaction)
                                        <tr class="additional-transaction-row">
                                            <td>
                                                <select class="form-select account-select" name="additional_transaction_accounts[]" required>
                                                    <option value="">{{ __('Select Account') }}</option>
                                                    @foreach($subAccounts as $account)
                                                        <option value="{{ $account->id }}" @selected($transaction->account_id == $account->id)>{{ $account->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="additional_transaction_descriptions[]" value="{{ $transaction->description }}" placeholder="{{ __('Description') }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control float additional-transaction-amount" name="additional_transaction_amounts[]" value="{{ $transaction->amount }}" placeholder="0.00" required>
                                            </td>
                                            <td>
                                                <select class="form-select" name="additional_transaction_types[]" required>
                                                    <option value="debit" @selected($transaction->is_debit)>{{ __('Debit') }}</option>
                                                    <option value="credit" @selected(!$transaction->is_debit)>{{ __('Credit') }}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-transaction"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @endunless
                <div class="row g-2 mt-2 align-items-start border-top pt-2">
                    <div class="col-md-6 col-lg-2">
                        <div>
                            <label class="form-label">{{ __('Total Payment Amount') }}</label>
                            <div class="fs-4 fw-semibold" id="total-payment-amount">{{ number_format($payment->grand_total, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div>
                            <div class="d-flex justify-content-between align-items-baseline gap-2">
                                <label for="total_paid" class="form-label">{{ ($advanceMode ?? false) ? __('Advance Amount') : __('Total Paid Amount') }}</label>
                                <span class="small text-muted text-nowrap">{{ __('Advance available') }}: <b id="advance-available" data-available="{{ (float) ($advanceAvailable ?? 0) }}">{{ number_format((float) ($advanceAvailable ?? 0), 2) }}</b></span>
                            </div>
                            <input type="text" step="0.01" data-decimal="2" class="form-control float text-end fw-bold" id="total_paid" name="total_paid"
                                   value="{{ $payment->id && $payment->total_amount !== null ? number_format((float) $payment->total_amount, 2, '.', '') : '' }}"
                                   data-touched="{{ ($advanceMode ?? false) || ($payment->id && $payment->total_amount !== null && (float) $payment->advance_amount + (float) $payment->advance_used > 0) ? 1 : 0 }}" placeholder="0.00" autocomplete="off">
                            <div class="form-text">{{ __('Must match the chosen invoices. Anything more is kept as the supplier\'s advance.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="pt-lg-4">
                            <div id="advance-note" class="small" style="display:none;"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="use-advance" style="display:none;">{{ __('Use advance') }}</button>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div>
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes"
                                      rows="1">{{ $payment->notes }}</textarea>
                        </div>
                    </div>
                    {{--<div class="col-md-6 text-end">
                        <button type="button" class="btn btn-secondary"
                                onclick="window.location.href='{{ route('transaction.payments.index') }}'">Cancel
                        </button>
                        @if($payment->status == 1)
                            <button type="submit" class="btn btn-primary" id="save-payment">Update Payment</button>
                        @endif
                    </div>--}}
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script src="{{ asset('js/page-all-js/payment.js') }}"></script>
@endpush
