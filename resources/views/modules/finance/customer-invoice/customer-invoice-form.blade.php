<div class="g-3 align-items-center <!--bg-white--> border-bottom py-3 px-4 small">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $customer->row_no ?? 'New Customer Invoice' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Invoice">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $customer->id }}">

        <!-- Invoice Header -->
        <div class="mb-4 mt-3 border-0 px-4 <!--error-border-off-->">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Job Reference -->
                    <div class="col-md-4">
                        <label class="form-label required">Job <sup class="text-danger">*</sup></label>
                        <select name="job_id" class="tom-select" data-live-search="true"
                                {{--@disabled($job_id)--}} required data-call-back="customerList">
                            <option value="">Select Job</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}"
                                        @selected($customer->job_id == $job->id || $job->id == $job_id) data-subtext="{{ $job->customer->name_en }}"
                                        data-call-value="{{ encodeId($job->customer_id) }}">
                                    {{ $job->row_no }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Customer -->
                    <div class="col-md-4">
                        <label class="form-label required">Customer <sup class="text-danger">*</sup></label>
                        <x-common.customers :value="$customer->customer_id ?? $job_customer_id" disabled="true"
                                            :required="true" {{--:disabled="(bool)$job_customer_id" :new="false"--}}></x-common.customers>
                    </div>

                    <!-- Invoice Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Invoice Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="invoice_date" name="invoice_date" class="form-control datepicker"
                               value="{{ $customer->invoice_date ?? \Carbon\Carbon::today()->format('d-m-Y') }}"
                               required>
                    </div>

                    <!-- Due Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Due Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="due_date" name="due_date" class="form-control datepicker"
                               value="{{ $customer->due_at }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                        @if($customer->documents && count($customer->documents))
                            <small class="text-primary text-decoration-underline cursor-pointer"
                                   data-bs-toggle="offcanvas" data-bs-target="#attachmentsDrawer">
                                {{ $customer->documents->count() }}
                                {{ \Illuminate\Support\Str::plural('Document', $customer->documents->count()) }}
                            </small>
                        @endif
                    </div>
                    <div class="col-md-4 mt-3">
                        <label class="form-label required">Freza Invoice No <sup class="text-danger">*</sup></label>
                        <input name="row_no" class="form-control" required value="{{ $customer->row_no }}">
                    </div>

                    <!-- Currency -->
                    <div class="col-md-4">
                        <label class="form-label">Currency *</label>
                        <x-common.currencies-exchange :value="$customer->currency" width="auto" :disabled="true"
                                                      :exchangeRate="$customer->currency_rate"/>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Attachments -->


                    <!-- Offcanvas Drawer -->
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="attachmentsDrawer"
                         aria-labelledby="attachmentsDrawerLabel" style="width: 500px;">
                        <div class="offcanvas-header border-bottom">
                            <h5 id="attachmentsDrawerLabel" class="mb-0">Customer Documents</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body p-0">
                            @if($customer->documents->count())
                                <ul class="list-group list-group-flush">
                                    @foreach($customer->documents as $doc)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-earmark-text text-primary fs-4 me-2"></i>
                                                <div>
                                                    <div class="fw-semibold">{{ $doc->file_name }}</div>
                                                    <small
                                                        class="text-muted">{{ \Carbon\Carbon::parse($doc->posted_date)->format('d-m-Y, h:i A') }}</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <!-- View -->
                                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                                   class="text-success" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <!-- Download -->
                                                <a href="{{ asset('storage/' . $doc->file_path) }}"
                                                   download="{{ $doc->file_name }}"
                                                   class="text-primary" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-folder2-open fs-2 d-block mb-2"></i>
                                    No documents uploaded.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="border-0 mb-4">
            <div class="card-body p-0">
                <table class="table align-middle mb-0" id="customerItemsTable">
                    <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Comment</th>
                        <th>Account</th>
                        <th>Unit</th>
                        <th class="text-end">Qty</th>
                        {{--<th class="text-end">Cost</th>--}}
                        <th class="text-end">Price</th>
                        <th class="text-end">Tax (%)</th>
                        <th class="text-end d-none">Amount</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody id="CUSTOMER_INVOICE-tbody" {{--class="error-tooltip-off"--}}>
                    @foreach($customer->customerInvoiceSubs as $subItem)
                        <tr class="align-middle main-row">
                            <!-- Description -->
                            <td class="col-md-3">
                                <x-common.description :value="$subItem->description_id" required="required"/>
                            </td>

                            <!-- Comment -->
                            <td class="col-md-2">
                                <textarea name="comment[]" class="form-control">{{ $subItem->comment }}</textarea>
                            </td>

                            <!-- Account -->
                            <td class="col-md-2">
                                {{--<x-common.accounts :accounts="$accounts" :value="$subItem->account_id"/>--}}
                                <x-common.account-groups :parentAccount="$parents"
                                                         :subAccounts="$subAccounts"
                                                         :value="$subItem->account_id"></x-common.account-groups>
                            </td>

                            <td class="col-md-1">
                                <x-common.unit :value="$subItem->unit_id"/>
                            </td>

                            <!-- Quantity -->
                            <td class="col-md-1">
                                <input type="text" name="quantity[]" class="form-control text-end float quantity"
                                       autocomplete="off"
                                       value="{{ $subItem->quantity }}" min="1" required>
                            </td>

                            <!-- Cost -->
                            {{--<td class="col-md-1">
                                <input type="text" name="cost[]" class="form-control text-end float cost" autocomplete="off"
                                       value="{{ number_format($subItem->cost ?? 0, decimals()) }}" min="0">
                            </td>--}}

                            <!-- Revenue Price -->
                            <td class="col-md-2">
                                <input type="text" name="unit_price[]" class="form-control text-end float unit_price"
                                       autocomplete="off"
                                       value="{{ $subItem->unit_price }}" min="1" required>
                            </td>

                            <!-- Tax -->
                            <td class="col-md-1">
                                <x-common.tax :value="$subItem->tax_code" width="200" dropdown-width="275"/>
                            </td>

                            <!-- Amount -->
                            <td class="col-md-1 d-none">
                                <input type="text" class="form-control text-end row-total"
                                       value="{{ number_format($subItem->unit_price * $subItem->quantity, decimals()) }}"
                                       readonly>
                            </td>

                            <td class="col-md-1 align-content-center">
                                <div class="d-flex justify-content-between gap-3 action-icons">
                                    <div class="add-row">
                                        <i class="bi bi-plus-circle text-muted"></i>
                                    </div>
                                    <div class="remove-row">
                                        <i class="bi bi-trash text-danger"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>

                    <tfoot class="fw-semibold">
                    <tr>
                        {{--<td>
                            <button type="button" id="addInvoiceRow" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Add Item
                            </button>
                        </td>--}}
                        <td colspan="6" class="text-end">Subtotal</td>
                        <td class="text-end" id="subTotal">{{ number_format($customer->sub_total, decimals()) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">Total Tax</td>
                        <td class="text-end" id="totalTax">{{ number_format($customer->tax_total, decimals()) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">Grand Total</td>
                        <td class="text-end fw-bold" id="grandNet">
                            {{ number_format($customer->grand_total, decimals()) }}
                        </td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Remarks -->
        <div class="mt-3 px-4">
            <label class="form-label fw-semibold">Terms & Conditions</label>
            <textarea name="terms" class="form-control h-100" rows="4"
                      placeholder="Any additional notes...">{{ $customer->terms }}</textarea>
        </div>
    </form>
</div>
