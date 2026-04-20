<div class="row g-3 align-items-center <!--bg-white--> border-bottom py-3 px-4 mb-1 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $payment->row_no ?? 'New Payment' }}</span>
            </div>

        </div>

        <!-- Save & Next Button -->
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Payment">

    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="payment_id" id="payment_id" value="{{ $payment->id }}">
        <div class="mb-4 mt-3 border-0 px-4 <!--error-border-off-->">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="supplier" class="form-label required">Supplier <span
                                    class="text-danger">*</span></label>
                            <x-common.suppliers :value="$payment->supplier_id" :required="true"></x-common.suppliers>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_date" class="form-label required">Payment Date <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker" id="payment_date" name="payment_date"
                                   value="{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') : date('d-m-Y') }}"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label required">Paid Through <span
                                    class="text-danger">*</span></label>
                            <x-common.account-groups :parentAccount="$parents"
                                                     :subAccounts="$paidThroughAccounts"
                                                     :value="$payment->account"></x-common.account-groups>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="currency" class="form-label required">Currency <span
                                    class="text-danger">*</span></label>
                            <x-common.currencies-exchange :value="$payment->currency"
                                                          exchangeRate="{{ $payment->currency_rate }}"
                                                          width="auto"></x-common.currencies-exchange>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="reference_no" class="form-label required">Reference No <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no"
                                   value="{{ $payment->reference_no }}"
                                   placeholder="Check/Transaction Reference">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes"
                                      rows="1">{{ $payment->notes }}</textarea>
                        </div>
                    </div>
                </div>

                @if($payment->status == 3)
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <strong>Disapproval Reason:</strong> {{ $payment->disapproval_reason }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="border-0 mb-4 px-4">
            <div class="card-body p-0">
                <div class="row mt-4">
                    <div class="col-12">
                        <h4>Supplier Invoices</h4>
                        <div class="alert alert-info" id="no-invoices-message"
                             style="{{ count($supplierInvoices) > 0 ? 'display: none;' : '' }}">
                            No invoices available for this supplier.
                        </div>
                        <div class="table-responsive" id="invoices-table-container"
                             style="{{ count($supplierInvoices) > 0 ? '' : 'display: none;' }}">
                            <table class="table table-bordered table-striped" id="invoices-table">
                                <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox"
                                                          id="select-all-invoices" {{--{{ $payment->status != 1 ? 'disabled' : '' }}--}}>
                                    </th>
                                    <th>Invoice No</th>
                                    <th>Job No</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-end">Payment Amount</th>
                                    <th class="text-end">Balance Amount</th>
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
                                        <td>{{ $invoice->row_no }}</td>
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h4>Additional Transactions</h4>
                        <p class="text-muted">Add additional transactions apart from supplier invoice payments.</p>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="additional-transactions-table">
                                <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th width="5%">Action</th>
                                </tr>
                                </thead>
                                <tbody id="additional-transactions-body">
                                @if(isset($payment->additionalTransactions) && $payment->additionalTransactions->count() > 0)
                                    @foreach($payment->additionalTransactions as $index => $transaction)
                                        <tr class="additional-transaction-row">
                                            <td>
                                                <select class="form-select account-select" name="additional_transaction_accounts[]" required>
                                                    <option value="">Select Account</option>
                                                    @foreach($subAccounts as $account)
                                                        <option value="{{ $account->id }}" @selected($transaction->account_id == $account->id)>{{ $account->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="additional_transaction_descriptions[]" value="{{ $transaction->description }}" placeholder="Description">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control float additional-transaction-amount" name="additional_transaction_amounts[]" value="{{ $transaction->amount }}" placeholder="0.00" required>
                                            </td>
                                            <td>
                                                <select class="form-select" name="additional_transaction_types[]" required>
                                                    <option value="debit" @selected($transaction->is_debit)>Debit</option>
                                                    <option value="credit" @selected(!$transaction->is_debit)>Credit</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-transaction"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="5">
                                        <button type="button" class="btn btn-primary btn-sm" id="add-transaction">
                                            <i class="fas fa-plus"></i> Add Transaction
                                        </button>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Total Payment Amount</label>
                            <h3 id="total-payment-amount">{{ number_format($payment->grand_total, 2) }}</h3>
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
