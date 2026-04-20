<div class="row g-3 align-items-center <!--bg-white--> border-bottom py-3 px-4 mb-1 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $collection->row_no ?? 'New Collection' }}</span>
            </div>

        </div>

        <!-- Save & Next Button -->
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Collection">

    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="collection_id" id="collection_id" value="{{ $collection->id }}">
        <div class="mb-4 mt-3 border-0 px-4 <!--error-border-off-->">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="customer" class="form-label required">Customer <span
                                    class="text-danger">*</span></label>
                            <x-common.customers :value="$collection->customer_id" :required="true"></x-common.customers>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="collection_date" class="form-label required">Collection Date <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker" id="collection_date" name="collection_date"
                                   value="{{ $collection->collection_date ? \Carbon\Carbon::parse($collection->collection_date)->format('d-m-Y') : date('d-m-Y') }}"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label required">Paid Through <span
                                    class="text-danger">*</span></label>
                            <x-common.account-groups :parentAccount="$parents"
                                                     :subAccounts="$subAccounts"
                                                     :value="$collection->account"></x-common.account-groups>
                        </div>
                    </div>
                    {{--<div class="col-md-4">
                        <div class="mb-3">
                            <label for="collection_method" class="form-label required">Collection Method <span
                                    class="text-danger">*</span></label>
                            <select class="tom-select" id="collection_method" name="collection_method" required>
                                <option value="">Select Method</option>
                                <option
                                    value="Bank Transfer" {{ old('collection_method', $collection->collection_method) == 'Bank Transfer' ? 'selected' : '' }}>
                                    Bank Transfer
                                </option>
                                <option
                                    value="Check" {{ old('collection_method', $collection->collection_method) == 'Check' ? 'selected' : '' }}>
                                    Check
                                </option>
                                <option
                                    value="Cash" {{ old('collection_method', $collection->collection_method) == 'Cash' ? 'selected' : '' }}>
                                    Cash
                                </option>
                                <option
                                    value="Credit Card" {{ old('collection_method', $collection->collection_method) == 'Credit Card' ? 'selected' : '' }}>
                                    Credit Card
                                </option>
                            </select>
                        </div>
                    </div>--}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="currency" class="form-label required">Currency <span
                                    class="text-danger">*</span></label>
                            <x-common.currencies-exchange :value="$collection->currency"
                                                          exchangeRate="{{ $collection->currency_rate }}"
                                                          width="auto"></x-common.currencies-exchange>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="reference_no" class="form-label required">Reference No <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no" required
                                   value="{{ $collection->reference_no }}"
                                   placeholder="Check/Transaction Reference">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bank_charges" class="form-label">Bank Charges</label>
                            <input type="text" step="0.01" class="form-control float" id="bank_charges"
                                   name="bank_charges"
                                   value="{{ old('bank_charges', $collection->bank_charges) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="other_charges" class="form-label">Other Charges</label>
                            <input type="text" step="0.01" class="form-control float" id="other_charges"
                                   name="other_charges"
                                   value="{{ old('other_charges', $collection->other_charges) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes"
                                      rows="1">{{ $collection->notes }}</textarea>
                        </div>
                    </div>
                </div>

                @if($collection->status == 3)
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <strong>Disapproval Reason:</strong> {{ $collection->disapproval_reason }}
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
                        <h4>Customer Invoices</h4>
                        <div class="alert alert-info" id="no-invoices-message"
                             style="{{ count($customerInvoices) > 0 ? 'display: none;' : '' }}">
                            No invoices available for this customer.
                        </div>
                        <div class="table-responsive" id="invoices-table-container"
                             style="{{ count($customerInvoices) > 0 ? '' : 'display: none;' }}">
                            <table class="table table-bordered table-striped" id="invoices-table">
                                <thead>
                                <tr>
                                    <th width="5%"><input type="checkbox"
                                                          id="select-all-invoices">
                                    </th>
                                    <th>Invoice No</th>
                                    <th>Job No</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-end">Collection Amount</th>
                                    <th class="text-end">Balance Amount</th>
                                </tr>
                                </thead>
                                <tbody id="invoices-body">
                                @foreach($customerInvoices as $invoice)
                                    @php
                                        $isSelected = isset($selectedInvoice) && array_key_exists($invoice->id, $selectedInvoice);
                                        $balanceAmount = isset($invoice->balance_amount) ? $invoice->balance_amount : ($invoice->grand_total - ($invoice->paid_amount ?? 0));
                                        $collectionAmount = $isSelected ? $selectedInvoice[$invoice->id] : $balanceAmount;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="invoice-checkbox" name="customer_invoice_ids[]"
                                                   data-id="{{ $invoice->id }}" value="{{ $invoice->id }}"
                                                   data-amount="{{ $invoice->grand_total }}" data-paid="{{ $invoice->paid_amount ?? 0 }}" @checked($isSelected)>
                                        </td>
                                        <td>{{ $invoice->row_no }}</td>
                                        <td>{{ $invoice->job_no }}</td>
                                        <td>{{ $invoice->invoice_date }}</td>
                                        <td>{{ $invoice->due_at }}</td>
                                        <td class="text-end">{{ number_format($invoice->grand_total, 2) }}</td>
                                        <td class="text-end">
                                            <input type="text" step="0.01" class="form-control invoice-amount float text-end @if($isSelected) bg-white @endif"
                                                   data-id="{{ $invoice->id }}" name="invoice_amounts[{{ $invoice->id }}]" value="{{ $collectionAmount }}" min="0"
                                                   max="{{ $balanceAmount }}" data-balance="{{ $balanceAmount }}" {{ !$isSelected ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($balanceAmount - ($isSelected ? $collectionAmount : 0), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Total Collection Amount</label>
                            <h3 id="total-collection-amount">{{ number_format($collection->grand_total, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
