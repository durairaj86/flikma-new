<div class="g-3 align-items-center <!--bg-white--> border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $proforma->row_no ?? 'New Proforma Invoice' }}</span>
            </div>

        </div>

        <!-- Save & Next Button -->
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Invoice">
    <!-- Meta Info -->

    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $proforma->id }}">
        <!-- Invoice Header -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Job Reference -->
                    <div class="col-md-3">
                        <label class="form-label">Job</label>
                        <select name="job_id" class="tom-select" data-live-search="true" placeholder="--Select Job--" required  @disabled($job_id)>
                            <option value="">Select Job</option>
                            @foreach($jobs as $job)
                                <option value="{{ $job->id }}" data-subtext="{{ $job->customer->name_en }}" @selected($proforma->job_id == $job->id || $job->id == $job_id)>
                                    {{ $job->row_no }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Invoice Date -->
                    <div class="col-md-3">
                        <label class="form-label">Invoice Date *</label>
                        <input type="date" name="invoice_date" class="form-control datepicker"
                               value="{{ $proforma->posted_at }}" required>
                    </div>

                    <!-- Currency -->
                    <div class="col-md-3">
                        <label class="form-label">Currency *</label>
                        <x-common.currencies-exchange :value="$proforma->currency" width="auto"
                                                      :exchangeRate="$proforma->currency_rate"/>
                    </div>

                    <!-- Invoice Date -->
                    <div class="col-md-3">
                        <label class="form-label">Reference</label>
                        <input type="text" name="reference_no" class="form-control" value="{{ $proforma->reference_no }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="border-0 mb-4">
            <div class="card-body p-0">
                <table class="table align-middle mb-0" id="proformaItemsTable">
                    <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Comment</th>
                        <th>Unit</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Tax (%)</th>
                        {{--<th class="text-end">Amount</th>--}}
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="PROFORMA_INVOICE-tbody" class="error-tooltip-off">
                    @foreach($proforma->proformaInvoiceSubs as $subItem)
                        <tr>
                            <td class="col-md-3">
                                <x-common.description :value="$subItem->description_id" required="required"/>
                            </td>
                            <td class="col-md-3">
                                <textarea name="comment[]" class="form-control">{{ $subItem->comment }}</textarea>
                            </td>
                            <td class="col-md-1">
                                <x-common.unit :value="$subItem->unit_id"/>
                            </td>
                            <td class="col-md-1">
                                <input type="text" name="quantity[]" class="form-control text-end quantity float" autocomplete="off"
                                       value="{{ $subItem->quantity }}" min="0" step="0.01" required>
                            </td>
                            <td class="col-md-2">
                                <input type="text" name="unit_price[]" class="form-control text-end unit_price float"  autocomplete="off"
                                       value="{{ $subItem->unit_price }}" min="0" step="0.01" required>
                            </td>
                            <td class="col-md-2">
                                <x-common.tax :value="$subItem->tax_code" width="220" dropdownWidth="250"></x-common.tax>
                            </td>
                            <td class="col-md-1 d-none">
                                <input type="text" class="form-control row-total"
                                       value="{{ number_format($subItem->unit_price * $subItem->quantity,decimals()) }}"
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
                            <div class="d-flex justify-content-start">
                                <button type="button" id="addProformaRow" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-circle"></i> Add Item
                                </button>
                            </div>
                        </td>--}}
                        <td colspan="5" class="text-end align-content-center">Subtotal</td>
                        <td class="align-content-center">
                            <div id="subTotal"
                                 class="text-end">{{ number_format($proforma->sub_total, decimals()) }}</div>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end align-content-center">Total Tax</td>
                        <td>
                            <div id="totalTax"
                                 class="text-end">{{ number_format($proforma->tax_total, decimals()) }}</div>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end">Grand Total
                            <div class="@if ($proforma->currency === 'SAR' || !$proforma->currency) d-none @endif">
                                <small class="text-muted mt-2">
                                    ≈ <span
                                        id="sarRate">{{ number_format($proforma->cyrrency_rate, decimals()) }}</span>
                                    SAR/{{ $proforma->currency }}
                                </small>
                            </div>
                        </td>
                        <td class="text-end align-middle">
                            <div class="d-flex flex-column align-items-end">
                                <div id="grandNet" class="fw-bold">
                                    {{ number_format($proforma->grand_total, decimals()) }}
                                </div>

                                <small class="text-muted @if ($proforma->currency === 'SAR' || !$proforma->currency) d-none @endif">
            <span id="sarEquivalent">
                {{ number_format($proforma->grand_total * $proforma->cyrrency_rate, decimals()) }}
            </span> SAR
                                </small>
                            </div>
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
                      placeholder="Any additional notes...">{{ $proforma->terms }}</textarea>
        </div>
    </form>
</div>
