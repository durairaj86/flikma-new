{{--<div class="g-3 align-items-center <!--bg-white--> border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $supplier->row_no ?? 'New Supplier Invoice' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>--}}
<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info">
            <span class="fw-semibold fs-5">{{ $supplier->row_no ?? 'New Supplier Invoice' }}</span>
            @if(!$supplier->row_no)
                <button type="button" id="btn-show-scan" class="btn btn-sm btn-primary">
                    <i class="bi bi-camera-fill me-1"></i> Scan Invoice (AI OCR)
                </button>
                <button type="button" id="btn-show-form" class="btn btn-sm btn-outline-secondary d-none">
                    <i class="bi bi-arrow-left me-1"></i> Back to Form
                </button>
            @endif
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Invoice">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $supplier->id }}">

        <!-- Invoice Header -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Job Reference -->
                    <div class="col-md-4">
                        <label class="form-label required">Job <sup class="text-danger">*</sup></label>
                        <select name="job_id" class="tom-select" data-live-search="true" required
                                placeholder="Search Job" @disabled($job_id)>
                            <option value="">Select Job</option>
                            @foreach($jobs as $job)
                                <option
                                    value="{{ $job->id }}" @selected($supplier->job_id == $job->id || $job->id == $job_id)>
                                    {{ $job->row_no }} - {{ $job->customer->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Supplier -->
                    <div class="col-md-4">
                        <label class="form-label required">Supplier <sup class="text-danger">*</sup></label>
                        <x-common.suppliers :value="$supplier->supplier_id" required="required"></x-common.suppliers>
                    </div>

                    <!-- Posting Date -->
                    {{--<div class="col-md-4">
                        <label class="form-label">Posting Date *</label>
                        <input type="date" name="posting_date" class="form-control datepicker"
                               value="{{ $supplier->posted_at }}" required>
                    </div>--}}

                    <!-- Invoice Number -->
                    <div class="col-md-4">
                        <label class="form-label required">Invoice Number <sup class="text-danger">*</sup></label>
                        <input type="text" name="invoice_number" class="form-control"
                               value="{{ $supplier->invoice_number ?? '' }}" required
                               placeholder="Received Invoice Number">
                    </div>

                    <!-- Invoice Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Invoice Date <sup class="text-danger">*</sup></label>
                        <input type="date" name="invoice_date" class="form-control datepicker"
                               value="{{ $supplier->invoice_date }}" required>
                    </div>

                    <!-- Due Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Due Date <sup class="text-danger">*</sup></label>
                        <input type="date" name="due_date" class="form-control datepicker"
                               value="{{ $supplier->due_at }}" required>
                    </div>

                    <!-- Currency -->
                    <div class="col-md-4">
                        <label class="form-label required">Currency <sup class="text-danger">*</sup></label>
                        <x-common.currencies-exchange :value="$supplier->currency"
                                                      :exchangeRate="$supplier->currency_rate" width="auto"/>
                    </div>

                </div>

                <div class="row g-3 mt-2">

                    <!-- Attachments -->
                    <div class="col-md-4">
                        @if($supplier->documents && count($supplier->documents))
                            <small class="text-primary text-decoration-underline cursor-pointer"
                                   data-bs-toggle="offcanvas" data-bs-target="#attachmentsDrawer">
                                {{ $supplier->documents->count() }}
                                {{ \Illuminate\Support\Str::plural('Document', $supplier->documents->count()) }}
                            </small>
                        @endif
                    </div>
                    <!-- Offcanvas Drawer -->
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="attachmentsDrawer"
                         aria-labelledby="attachmentsDrawerLabel" style="width: 500px;">
                        <div class="offcanvas-header border-bottom">
                            <h5 id="attachmentsDrawerLabel" class="mb-0">Supplier Documents</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body p-0">
                            @if($supplier->documents->count())
                                <ul class="list-group list-group-flush">
                                    @foreach($supplier->documents as $doc)
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
        <div class="position-relative">
            <div id="scanning-overlay">
                <div class="scanner-line"></div>
                <div class="scanning-content">
                    <div class="spinner-border text-info mb-3" role="status"></div>
                    <h4 class="fw-bold">Scanning Document...</h4>
                    <p>Please sit back and relax. We are extracting and importing your data.</p>
                </div>
            </div>
            <div class="border-0 mb-4 ">
                <div class="card-body p-0">
                    {{--<pre id="output" class="bg-dark text-white p-3 rounded" style="height: 400px; overflow:auto;"></pre>--}}
                    <table class="table align-middle mb-0" id="supplierItemsTable">
                        <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Comment</th>
                            <th>Unit</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax (%)</th>
                            <th class="text-end d-none">Amount</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="SUPPLIER_INVOICE-tbody" class="error-tooltip-off">
                        @foreach($supplier->supplierInvoiceSubs as $subItem)
                            <tr>
                                <!-- Description -->
                                <td class="col-md-2">
                                    <x-common.description :value="$subItem->description_id" required="required"
                                                          width="200"
                                                          dropdownWidth="250"/>
                                </td>

                                <!-- Account -->
                                <td class="col-md-2">
                                    {{--<x-common.accounts :accounts="$accounts" :value="$subItem->account_id"/>--}}
                                    <x-common.account-groups :parentAccount="$parents"
                                                             :subAccounts="$subAccounts"
                                                             :value="$subItem->account_id"></x-common.account-groups>
                                </td>

                                <!-- Comment -->
                                <td class="col-md-3">
                                    <textarea name="comment[]" class="form-control">{{ $subItem->comment }}</textarea>
                                </td>

                                <td class="col-md-1">
                                    <x-common.unit :value="$subItem->unit_id"/>
                                </td>

                                <!-- Quantity -->
                                <td class="col-md-1">
                                    <input type="text" name="quantity[]" class="form-control text-end quantity float"
                                           value="{{ $subItem->quantity }}" min="1" required>
                                </td>

                                <!-- Unit Price -->
                                <td class="col-md-1">
                                    <input type="text" name="unit_price[]"
                                           class="form-control text-end float unit_price"
                                           value="{{ $subItem->unit_price }}" min="0" required>
                                </td>

                                <!-- Tax -->
                                <td class="col-md-2">
                                    <x-common.tax :value="$subItem->tax_code" width="220" dropdownWidth="250"/>
                                </td>

                                <!-- Amount -->
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
                            {{--<td>
                                <button type="button" id="addProformaRow" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-circle"></i> Add Item
                                </button>
                            </td>--}}
                            <td colspan="6" class="text-end">Subtotal</td>
                            <td class="text-end"
                                id="subTotal">{{ number_format($supplier->sub_total, decimals()) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end">Total Tax</td>
                            <td class="text-end"
                                id="totalTax">{{ number_format($supplier->tax_total, decimals()) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end">Grand Total</td>
                            <td class="text-end fw-bold" id="grandNet">
                                {{ number_format($supplier->grand_total, decimals()) }}
                            </td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="mt-3 px-4">
                <label class="form-label fw-semibold">Terms & Conditions</label>
                <textarea name="terms" class="form-control h-100" rows="4"
                          placeholder="Any additional notes...">{{ $supplier->terms }}</textarea>
            </div>
        </div>
        <!-- Remarks -->

    </form>
</div>
<div id="ocr-view" class="container-fluid py-5 d-none text-center" style="background: #f8f9fa;">
    <div class="mx-auto" style="max-width: 500px;">
        <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
        <h4 class="mt-3">Upload Invoice for OCR</h4>
        <p class="text-muted">Upload an image or PDF to automatically fill the form.</p>
        <form id="ocrForm" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="file" name="file" class="form-control" required accept="image/*,.pdf">
            </div>
        </form>
        <div class="d-flex gap-2 justify-content-center">
            <button type="button" id="start-ocr-scan" class="btn btn-primary">Start Scanning</button>
            <button type="button" id="btn-cancel-scan" class="btn btn-light">Cancel</button>
        </div>
        <div id="ocr-loader" class="mt-3 d-none">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            Processing with AI...
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        //manualFn();
        const $formView = $('#modal-buttons');
        const $ocrView = $('#ocr-view');
        const $btnShowScan = $('#btn-show-scan');
        const $btnShowForm = $('#btn-show-form');

        $btnShowScan.on('click', function () {
            $formView.addClass('d-none');
            $ocrView.removeClass('d-none');
            $(this).addClass('d-none');
            $btnShowForm.removeClass('d-none');
        });

        // 2. Switch View: Scan -> Form
        $btnShowForm.on('click', function () {
            $ocrView.addClass('d-none');
            $formView.removeClass('d-none');
            $(this).addClass('d-none');
            $btnShowScan.removeClass('d-none');
        });
    })

    $('#start-ocr-scan').on('click', function (e) {
        e.preventDefault();

        $('#ocr-view').addClass('d-none');
        $('#modal-buttons').removeClass('d-none');

        const $overlay = $('#scanning-overlay');
        const $content = $('#main-content-area');

        // 1. Show overlay and dim content
        $overlay.css('display', 'flex');
        $content.css('opacity', '0.3');

        let ocrForm = $('#ocrForm')[0];
        let formData = new FormData(ocrForm);
        //$('#output').text('Processing...');
        $.ajax({
            url: '{{ route('test-google-ocr.upload') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                /*jsonData = JSON.stringify(res, null, 4);
                $('#output').text(jsonData);*/

                const data = res.structured_data;

                $('input[name="invoice_number"]').val(data.invoice_details.invoice_no);
                let supplierSelect = $('select[name="supplier"]')[0]; // Get raw DOM element
                supplierSelect.tomselect.setValue(data.supplier.supplier_id);

                let currencySelect = $('select[name="currency"]')[0]; // Get raw DOM element
                currencySelect.tomselect.setValue(data.invoice_details.currency);

                $('textarea[name="terms"]').val(data.notes);

                // Convert DD-MM-YYYY to YYYY-MM-DD for the date input
                function formatToDMY(dateString) {
                    if (!dateString) return '';

                    // Create a date object (handles "21-JAN-26", "2026-01-21", etc.)
                    const dateObj = new Date(dateString);

                    // If the date is invalid, return the original string so you don't lose data
                    if (isNaN(dateObj.getTime())) {
                        console.warn("Could not parse date:", dateString);
                        return dateString;
                    }

                    const day = ("0" + dateObj.getDate()).slice(-2);
                    const month = ("0" + (dateObj.getMonth() + 1)).slice(-2);
                    const year = dateObj.getFullYear();

                    return `${day}-${month}-${year}`;
                }

                function updateFlatpickr(selector, dateStr) {
                    const formatted = formatToDMY(dateStr);
                    const el = $(selector)[0];
                    if (el && el._flatpickr) {
                        el._flatpickr.setDate(formatted, true, "d-m-Y");
                    } else {
                        $(selector).val(formatted);
                    }
                }

// Inside your AJAX Success:
                updateFlatpickr('input[name="invoice_date"]', data.invoice_details.invoice_date);
                updateFlatpickr('input[name="due_date"]', data.invoice_details.due_date);

                const items = data.items;
                const $tbody = $('#SUPPLIER_INVOICE-tbody');

// Optional: Clear existing rows if you want a fresh start
                // $tbody.empty();
                let $newRow
                items.forEach((item, index) => {
                    if (index === 0) {
                        $newRow = $tbody.find('tr:first');
                    } else {
                        $newRow = $tbody.find('tr:first').clone();
                        $newRow.find('input, select, textarea').val('');
                        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                        $newRow.find('div.ts-wrapper').remove();
                    }
                    // Clear values in cloned row


                    $newRow.find('select[name="description_id[]"]').val(item.description_id);
                    $newRow.find('textarea[name="comment[]"]').val(item.description);
                    $newRow.find('input[name="quantity[]"]').val(item.quantity);
                    $newRow.find('select[name="unit_id[]"]').val(item.unit);
                    $newRow.find('input[name="unit_price[]"]').val(item.total_excl_vat);

                    // const taxPercent = item.vat_percentage; // e.g., 15 or 18
                    const ocrVat = item.vat_category.toUpperCase(); // e.g., "STANDARD"
                    $newRow.find('select[name="tax[]"]').val(ocrVat);

                    if (index === 0) {
                        let descriptionSelect = $newRow.find('select[name="description_id[]"]')[0];
                        descriptionSelect.tomselect.setValue(item.description_id);

                        let unitSelect = $newRow.find('select[name="unit_id[]"]')[0];
                        unitSelect.tomselect.setValue(item.unit);

                        let taxSelect = $newRow.find('select[name="tax[]"]')[0];
                        taxSelect.tomselect.setValue(item.vat_category);
                    } else {
                        initTomSelectForm($newRow);
                        $tbody.append($newRow);
                    }
                });
                setTimeout(function () {
                    CALCULATION.finalTotals();
                    $('#moduleForm').valid();
                }, 1000);
            },
            error: function (err) {
                toastr.error(err.responseText);
                //$('#output').text('Error: ' + err.responseText);
            },
            complete: function () {
                $overlay.hide();
                $content.css('opacity', '1');
            }
        });
    });

</script>
