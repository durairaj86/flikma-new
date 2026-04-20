<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $expense->row_no ?? 'New Expense' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Expense">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $expense->id }}">

        <!-- Expense Header -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Customer -->
                    <div class="col-md-4">
                        <label class="form-label">Customer</label>
                        <x-common.customers :value="$expense->customer_id" :required="false"></x-common.customers>
                    </div>

                    <!-- Supplier -->
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <x-common.suppliers :value="$expense->vendor_id" :required="false"></x-common.suppliers>
                    </div>

                    <!-- Reference Number -->
                    <div class="col-md-4">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" value="{{ $expense->reference_number }}">
                    </div>

                    <!-- Expense Date -->
                    <div class="col-md-4">
                        <label class="form-label">Expense Date *</label>
                        <input type="date" name="posted_at" class="form-control datepicker"
                               value="{{ $expense->posted_at }}" required>
                    </div>

                    <!-- Currency -->
                    <div class="col-md-4">
                        <label class="form-label">Currency *</label>
                        <x-common.currencies-exchange :value="$expense->currency" width="auto"
                                                      :exchangeRate="$expense->currency_rate"/>
                    </div>

                    <!-- Payment Status -->
                    {{--<div class="col-md-4">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="tom-select">
                            <option value="unpaid" @selected($expense->payment_status == 'unpaid')>Unpaid</option>
                            <option value="partial" @selected($expense->payment_status == 'partial')>Partial</option>
                            <option value="paid" @selected($expense->payment_status == 'paid')>Paid</option>
                        </select>
                    </div>--}}

                    <!-- Payment Mode -->
                    <div class="col-md-4">
                        <label class="form-label">Payment Mode</label>
                        <x-common.account-groups :parentAccount="$mainParents" name="main_account"
                                                 :subAccounts="$mainSubAccounts"
                                                 :value="$expense->payment_mode"></x-common.account-groups>
                    </div>

                    <!-- Paid Amount -->
                    {{--<div class="col-md-4">
                        <label class="form-label">Paid Amount</label>
                        <input type="text" name="paid_amount" class="form-control float" step="0.01" min="0"
                               value="{{ $expense->paid_amount ?? 0 }}">
                    </div>--}}

                    <!-- Is Billable -->
                    {{--<div class="col-md-4">
                        <label class="form-label">Is Billable</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_billable" value="1"
                                   @checked($expense->is_billable) id="is_billable">
                            <label class="form-check-label" for="is_billable">Charge to Customer</label>
                        </div>
                    </div>--}}
                    <div class="col-md-4">
                        <label class="form-label">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                        @if($expense->documents && count($expense->documents))
                            <small class="text-primary text-decoration-underline cursor-pointer"
                                   data-bs-toggle="offcanvas" data-bs-target="#attachmentsDrawer">
                                {{ $expense->documents->count() }}
                                {{ \Illuminate\Support\Str::plural('Document', $expense->documents->count()) }}
                            </small>
                        @endif
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Attachments -->

                    <!-- Offcanvas Drawer -->
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="attachmentsDrawer"
                         aria-labelledby="attachmentsDrawerLabel" style="width: 500px;">
                        <div class="offcanvas-header border-bottom">
                            <h5 id="attachmentsDrawerLabel" class="mb-0">Expense Documents</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body p-0">
                            @if($expense->documents && $expense->documents->count())
                                <ul class="list-group list-group-flush">
                                    @foreach($expense->documents as $doc)
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

        <!-- Expense Items Table -->
        <div class="border-0 mb-4">
            <div class="card-body p-0">
                <table class="table align-middle mb-0" id="expenseItemsTable">
                    <thead class="table-light">
                    <tr>
                        <th>Account</th>
                        <th>Employee</th>
                        <th>Comment</th>
                        <th class="text-end d-none">Qty</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">VAT (%)</th>
                        {{--<th class="text-end">Total</th>--}}
                        <th></th>
                    </tr>
                    </thead>

                    <tbody id="EXPENSE-tbody" class="error-tooltip-off">
                    @foreach($expense->expenseSubs as $subItem)
                        <tr class="align-middle main-row">
                            <!-- Account -->
                            <td class="col-md-2">
                                <x-common.account-groups :parentAccount="$parents"
                                                         :subAccounts="$subAccounts"
                                                         :value="$subItem->account_id"></x-common.account-groups>
                                {{--<x-common.accounts :accounts="$accounts" :value="$subItem->account_id"/>--}}
                            </td>

                            <td class="col-md-3">
                                {{--<x-common.items :value="$subItem->item_id" new="true"/>--}}
                                <x-common.employee :value="$subItem->employee_id"/>
                            </td>

                            <!-- Comment -->
                            <td class="col-md-3">
                                <textarea name="comment[]" class="form-control">{{ $subItem->comment }}</textarea>
                            </td>

                            <!-- Quantity -->
                            <td class="col-md-1 d-none">
                                <input type="text" name="quantity[]" class="form-control text-end float quantity"
                                       autocomplete="off"
                                       value="1" min="1" required>
                            </td>

                            <!-- Rate -->
                            <td class="col-md-1">
                                <input type="text" name="unit_price[]"
                                       class="form-control text-end float unit_price" autocomplete="off"
                                       value="{{ $subItem->unit_price }}" min="0" required>
                            </td>

                            <!-- VAT Rate -->
                            <td class="col-md-2">
                                <x-common.tax :value="$subItem->tax_code" width="220" dropdownWidth="250"/>
                            </td>

                            <!-- Total -->
                            <td class="col-md-1 d-none">
                                <input type="text" class="form-control text-end row-total"
                                       value="{{ $subItem->unit_price * $subItem->quantity }}"
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
                        <td colspan="5" class="text-end">Subtotal</td>
                        <td class="text-end"
                            id="subTotal">{{ number_format($expense->sub_total ?? 0, decimals()) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end">Total Tax</td>
                        <td class="text-end"
                            id="totalTax">{{ number_format($expense->tax_total ?? 0, decimals()) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end">Grand Total</td>
                        <td class="text-end fw-bold" id="grandNet">
                            {{ number_format($expense->grand_total ?? 0, decimals()) }}
                        </td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Terms -->
        {{--<div class="mt-3 px-4">
            <label class="form-label fw-semibold">Terms & Conditions</label>
            <textarea name="terms" class="form-control h-100" rows="4"
                      placeholder="Any additional notes...">{{ $expense->terms }}</textarea>
        </div>--}}
    </form>
</div>
