<div class="g-3 align-items-center <!--bg-white--> border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $creditNote->row_no ?? 'New Credit Note' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Credit Note">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $creditNote->id }}">

        <!-- HEADER -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">

                    <!-- CREDIT NOTE TYPE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Credit Note Type *</label>
                        <select class="tom-select" name="credit_note_type" id="creditType">
                            <option value="invoice" @selected($creditNote->credit_note_type == 'invoice')>Against Invoice</option>
                            {{--<option value="standalone" @selected($creditNote->credit_note_type == 'standalone')>Standalone</option>--}}
                            {{--<option value="price_adjustment" @selected($creditNote->credit_note_type == 'price_adjustment')>Price Adjustment</option>
                            <option value="cancellation" @selected($creditNote->credit_note_type == 'cancellation')>Cancellation</option>
                            <option value="tax_correction" @selected($creditNote->credit_note_type == 'tax_correction')>Tax Correction</option>--}}
                        </select>
                    </div>

                    <!-- CUSTOMER -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Customer *</label>
                        <x-common.customers :value="$creditNote->customer_id" :new="false" :required="true"></x-common.customers>
                    </div>

                    <!-- REFERENCE INVOICE (Only If Linked) -->
                    <div class="col-md-4" id="invoice-select-box">
                        <label class="form-label fw-semibold">Invoice</label>
                        <select name="invoice_id" class="tom-select" required data-live-search="true">
                            <option value="">Select Invoice</option>
                            @foreach($customerInvoices as $inv)
                                <option value="{{ $inv->id }}" @selected($creditNote->invoice_id == $inv->id)>
                                    {{ $inv->row_no }} – {{ $inv->grand_total }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- CREDIT NOTE DATE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Credit Note Date *</label>
                        <input type="text" class="form-control datepicker" name="credit_note_date"
                               value="{{ $creditNote->posted_at }}">
                    </div>

                    <!-- JOB -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Job / File No</label>
                        <select name="job_id" class="tom-select">
                            <option value="">Select Job</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" @selected($creditNote->job_id == $job->id)>
                                    {{ $job->row_no }} - {{ $job->customer->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- LOGISTICS REFERENCES -->
                    {{--<div class="col-md-4">
                        <label class="form-label fw-semibold">BL / AWB No</label>
                        <input type="text" class="form-control" name="ref_no"
                               value="{{ $creditNote->ref_no }}">
                    </div>--}}

                    <!-- REASON CATEGORY -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Reason *</label>
                        <select name="reason" class="tom-select">
                            <option value="">Select Reason</option>
                            <option value="rate_correction" @selected($creditNote->reason == 'rate_correction')>Rate Correction</option>
                            <option value="service_cancellation" @selected($creditNote->reason == 'service_cancellation')>Service Cancellation</option>
                            <option value="double_entry" @selected($creditNote->reason == 'double_entry')>Duplicate Billing</option>
                            <option value="job_change" @selected($creditNote->reason == 'job_change')>Job Change Adjustment</option>
                            <option value="manual_adjustment" @selected($creditNote->reason == 'manual_adjustment')>Manual Adjustment</option>
                        </select>
                    </div>

                    <!-- CURRENCY -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Currency *</label>
                        <x-common.currencies-exchange
                            :value="$creditNote->currency" width="auto"
                            :exchangeRate="$creditNote->currency_rate"/>
                    </div>

                    <!-- ATTACHMENTS -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Attachments</label>
                        <input type="file" class="form-control" multiple name="attachments[]">

                        @if($creditNote->documents->count())
                            <small class="text-primary text-decoration-underline cursor-pointer"
                                   data-bs-toggle="offcanvas" data-bs-target="#attachmentsDrawer">
                                {{ $creditNote->documents->count() }} Document(s)
                            </small>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- ITEM TABLE -->
        <div class="border-0 mb-4">
            <div class="card-body p-0">
                <table class="table align-middle mb-0" id="creditItemsTable">
                    <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Account</th>
                        <th>Comment</th>
                        <th>Unit</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Tax (%)</th>
                        <th class="text-end d-none">Amount</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody id="CREDIT_NOTE-tbody" class="error-tooltip-off">
                    @foreach($creditNote->creditNoteSubs as $subItem)
                        <tr class="align-middle main-row">

                            <td class="col-md-3"><x-common.description :value="$subItem->description_id" required="required"/></td>
                            <td class="col-md-2"><x-common.account-groups :parentAccount="$parents"
                                                                          :subAccounts="$subAccounts"
                                                                          :value="$subItem->account_id"></x-common.account-groups></td>
                            <td class="col-md-2"><textarea name="comment[]" class="form-control">{{ $subItem->comment }}</textarea></td>
                            <td class="col-md-1"><x-common.unit :value="$subItem->unit_id"/></td>

                            <td class="col-md-1">
                                <input type="text" name="quantity[]" class="form-control text-end float quantity"
                                       value="{{ $subItem->quantity }}">
                            </td>

                            <td class="col-md-2">
                                <input type="text" name="unit_price[]" class="form-control text-end float unit_price"
                                       value="{{ $subItem->unit_price }}">
                            </td>

                            <td class="col-md-1"><x-common.tax :value="$subItem->tax_code" width="200" dropdown-width="275"/></td>

                            <td class="d-none">
                                <input type="text" class="form-control text-end row-total" readonly
                                       value="{{ number_format($subItem->unit_price * $subItem->quantity, decimals()) }}">
                            </td>

                            <td class="align-content-center">
                                <div class="d-flex justify-content-between gap-3 action-icons">
                                    <div class="add-row"><i class="bi bi-plus-circle text-muted"></i></div>
                                    <div class="remove-row"><i class="bi bi-trash text-danger"></i></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>

                    <tfoot class="fw-semibold">
                    <tr>
                        <td colspan="6" class="text-end">Subtotal</td>
                        <td class="text-end" id="subTotal">
                            {{ number_format($creditNote->sub_total, decimals()) }}
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">Total Tax</td>
                        <td class="text-end" id="totalTax">
                            {{ number_format($creditNote->tax_total, decimals()) }}
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">Grand Total</td>
                        <td class="text-end fw-bold" id="grandNet">
                            {{ number_format($creditNote->grand_total, decimals()) }}
                        </td>
                        <td></td>
                    </tr>
                    </tfoot>

                </table>

            </div>
        </div>

        <!-- REMARKS -->
        <div class="mt-3 px-4">
            <label class="form-label fw-semibold">Terms & Conditions</label>
            <textarea name="terms" class="form-control h-100" rows="4">{{ $creditNote->terms }}</textarea>
        </div>

    </form>
</div>
