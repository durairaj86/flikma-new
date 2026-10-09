<div class="g-3 align-items-center <!--bg-white--> border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $debitNote->row_no ?? __('New Debit Note') }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Debit Note') }}">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $debitNote->id }}">

        <!-- HEADER -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">

<!-- SUPPLIER -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Supplier') }} *</label>
                        <x-common.suppliers :value="$debitNote->supplier_id" :required="true"></x-common.suppliers>
                    </div>

                    <!-- SUPPLIER INVOICE BEING DEBITED -->
                    <div class="col-md-4" id="invoice-select-box">
                        <label class="form-label fw-semibold">{{ __('Supplier Invoice') }} *</label>
                        <select name="invoice_id" id="dn-invoice" class="tom-select" required data-live-search="true">
                            <option value="">{{ __('Select Invoice') }}</option>
                            @foreach($supplierInvoices as $inv)
                                <option value="{{ $inv->id }}" @selected($debitNote->invoice_id == $inv->id)
                                        data-supplier-id="{{ encodeId($inv->supplier_id) }}" data-job-id="{{ $inv->job_id }}">
                                    {{ $inv->row_no }} – {{ $inv->grand_total }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- DEBIT NOTE DATE -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Debit Note Date') }} *</label>
                        <input type="text" class="form-control datepicker" name="debit_note_date"
                               value="{{ $debitNote->posted_at }}">
                    </div>

                    <!-- JOB -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Job / File No') }}</label>
                        <select name="job_id" class="tom-select">
                            <option value="">{{ __('Select Job') }}</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" @selected($debitNote->job_id == $job->id)>
                                    {{ $job->row_no }} - {{ $job->customer->name_en ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- LOGISTICS REFERENCES -->
                    {{--<div class="col-md-4">
                        <label class="form-label fw-semibold">BL / AWB No</label>
                        <input type="text" class="form-control" name="ref_no"
                               value="{{ $debitNote->ref_no }}">
                    </div>--}}

                    <!-- REASON CATEGORY -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Reason') }} *</label>
                        <select name="reason" class="tom-select">
                            <option value="">{{ __('Select Reason') }}</option>
                            <option value="overcharge" @selected($debitNote->reason == 'overcharge')>{{ __('Overcharge / Rate Difference') }}</option>
                            <option value="goods_returned" @selected($debitNote->reason == 'goods_returned')>{{ __('Goods / Service Returned') }}</option>
                            <option value="service_not_provided" @selected($debitNote->reason == 'service_not_provided')>{{ __('Service Not Provided') }}</option>
                            <option value="duplicate_billing" @selected($debitNote->reason == 'duplicate_billing')>{{ __('Duplicate Billing') }}</option>
                            <option value="manual_adjustment" @selected($debitNote->reason == 'manual_adjustment')>{{ __('Manual Adjustment') }}</option>
                        </select>
                    </div>

                    <!-- CURRENCY -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Currency') }} *</label>
                        <x-common.currencies-exchange
                            :value="$debitNote->currency" width="auto"
                            :exchangeRate="$debitNote->currency_rate"/>
                    </div>

                    <!-- ATTACHMENTS -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('Attachments') }}</label>
                        <input type="file" class="form-control" multiple name="attachments[]">

                        @if($debitNote->documents->count())
                            <small class="text-primary text-decoration-underline cursor-pointer"
                                   data-bs-toggle="offcanvas" data-bs-target="#attachmentsDrawer">
                                {{ $debitNote->documents->count() }} {{ __('Document(s)') }}
                            </small>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- ITEM TABLE -->
        <div class="border-0 mb-4">
            <div class="card-body p-0">
                <table class="table align-middle mb-0" id="debitItemsTable">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Comment') }}</th>
                        <th>{{ __('Unit') }}</th>
                        <th class="text-end">{{ __('Qty') }}</th>
                        <th class="text-end">{{ __('Price') }}</th>
                        <th class="text-end">{{ __('Tax (%)') }}</th>
                        <th class="text-end d-none">{{ __('Amount') }}</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody id="DEBIT_NOTE-tbody" class="error-tooltip-off">
                    @foreach($debitNote->debitNoteSubs as $subItem)
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
                        <td colspan="6" class="text-end">{{ __('Subtotal') }}</td>
                        <td class="text-end" id="subTotal">
                            {{ number_format($debitNote->sub_total, decimals()) }}
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">{{ __('Total Tax') }}</td>
                        <td class="text-end" id="totalTax">
                            {{ number_format($debitNote->tax_total, decimals()) }}
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">{{ __('Grand Total') }}</td>
                        <td class="text-end fw-bold" id="grandNet">
                            {{ number_format($debitNote->grand_total, decimals()) }}
                        </td>
                        <td></td>
                    </tr>
                    </tfoot>

                </table>

            </div>
        </div>

        <!-- REMARKS -->
        <div class="mt-3 px-4">
            <label class="form-label fw-semibold">{{ __('Terms & Conditions') }}</label>
            <textarea name="terms" class="form-control h-100" rows="4">{{ $debitNote->terms }}</textarea>
        </div>

    </form>
</div>
