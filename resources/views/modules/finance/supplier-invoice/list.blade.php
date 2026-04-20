@section('js','supplier_invoice')
@section('page-title','Supplier Invoice')
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div id="filterPanel" class="card shadow-sm border-0 d-none">

            <!-- Header -->
            <div class="card-header bg-light border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">Advanced Filters</h6>
                </div>
            </div>

            <div class="card-body">

                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <!-- Date Range Section -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-2">
                                <label class="form-label fw-medium">Date Range</label>
                                <select class="tom-select avoid-filter" id="presetDateRange">
                                    <option value="">Custom</option>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="thisMonth">This Month</option>
                                    <option value="lastMonth">Last Month</option>
                                    <option value="thisQuarter">This Quarter</option>
                                    <option value="lastQuarter">Last Quarter</option>
                                    <option value="thisYear">This Year</option>
                                    <option value="lastYear">Last Year</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-filter">
                                <label class="form-label fw-medium">Invoice Date</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                           value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                    <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                           value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                                </div>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Supplier</label>
                                <x-common.suppliers multiple></x-common.suppliers>
                            </div>

                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-start">
            @if(isset($job_no))
                <h3 class="fw-bold text-muted bg-info-subtle rounded p-3">
                    {{ $job_no }}
                </h3>
            @endif
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav status-tabs align-items-center border-bottom" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                <span><i class="bi bi-clock me-1"></i> Draft -</span>
                                <span class="status-count ms-2" id="draftCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                <span><i class="bi bi-check-circle me-1"></i> Approved -</span>
                                <span class="status-count ms-2" id="approvedCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle"></i> Cancelled -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between pt-3">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-primary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        Filter
                    </button>
                </div>
                {{--<label class="form-label">Attachments</label>
                <div class="d-flex gap-2">
                    <input type="file" name="attachments[]" class="form-control" multiple>
                    <button type="button" class="btn btn-primary" id="ocrButton" data-bs-target="#ocrModal">
                        <i class="bi bi-file-earmark-text"></i> OCR
                    </button>
                </div>--}}
                <button class="btn btn-primary rounded-pill px-4" id="new" data-loader-id="{{ $job_id ?? 'list' }}">New Supplier Invoice</button>
            </div>
        </div>
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
            </div>
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Job" data-model-size="lg">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>SI No</th>
                        <th>Invoice No</th>
                        <th>Job No</th>
                        <th>Supplier</th>
                        <th class="text-end">Base Amount</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance Due</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.email.send-email')
    @include('modules.finance.supplier-invoice.supplier-invoice-view')

    <!-- OCR Modal -->
    <div class="modal fade show" id="ocrModal1" tabindex="-1" aria-labelledby="ocrModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ocrModalLabel">OCR Document Processing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p>Upload an invoice document (PDF, JPG, PNG) to automatically extract information and fill the form.</p>
                        <form id="ocrUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <input type="file" name="file" id="ocrFile" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Process Document</button>
                        </form>
                    </div>
                    <div id="ocrProcessingStatus" class="d-none">
                        <div class="d-flex align-items-center mb-3">
                            <div class="spinner-border text-primary me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>Processing document, please wait...</span>
                        </div>
                    </div>
                    <div id="ocrResult" class="d-none">
                        <h6>Extracted Information:</h6>
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div id="ocrResultContent"></div>
                        </div>
                        <button type="button" id="fillFormButton" class="btn btn-success">Fill Form with Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

<!-- OCR JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ocrUploadForm = document.getElementById('ocrUploadForm');
        const ocrProcessingStatus = document.getElementById('ocrProcessingStatus');
        const ocrResult = document.getElementById('ocrResult');
        const ocrResultContent = document.getElementById('ocrResultContent');
        const fillFormButton = document.getElementById('fillFormButton');
        const ocrButton = document.querySelector('[data-bs-target="#ocrModal"]');

        let extractedData = null;

        // Manually initialize the modal to prevent issues
        const ocrModalEl = document.getElementById('ocrModal');
        let ocrModal;

        if (ocrModalEl) {
            ocrModal = new bootstrap.Modal(ocrModalEl, {
                backdrop: 'static',
                keyboard: false
            });

            // Add event listener to the OCR button
            ocrButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                ocrModal.show();
            });
        }

        ocrUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show processing status
            ocrProcessingStatus.classList.remove('d-none');
            ocrResult.classList.add('d-none');

            // Send the file for OCR processing
            fetch('{{ route("test-ocr.upload") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    // Hide processing status
                    ocrProcessingStatus.classList.add('d-none');

                    if (data.status === 'success') {
                        // Store the extracted data
                        extractedData = data.structured_data;

                        // Display the extracted data
                        // The second and third arguments (null, 2) add indentation
                        const formattedJson = JSON.stringify(extractedData, null, 2);

// Use a <pre> tag in your HTML for best results
                        ocrResultContent.innerHTML = `<pre>${formattedJson}</pre>`;
                        /*ocrResultContent.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Invoice Number:</strong> ${extractedData.header.invoice_no || 'Not found'}</p>
                                <p><strong>Date:</strong> ${extractedData.header.date || 'Not found'}</p>
                                <p><strong>Due Date:</strong> ${extractedData.header.due_date || 'Not found'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> ${extractedData.header.customer_name || 'Not found'}</p>
                                <p><strong>VAT Number:</strong> ${extractedData.header.vat_no || 'Not found'}</p>
                                <p><strong>Total Amount:</strong> ${extractedData.summary.grand_total || 'Not found'}</p>
                            </div>
                        </div>
                    `;*/

                        // Show the result
                        ocrResult.classList.remove('d-none');
                    } else {
                        // Show error
                        ocrResult.classList.remove('d-none');
                        ocrResultContent.innerHTML = `<div class="alert alert-danger">Error: ${data.message || 'Failed to process document'}</div>`;
                    }
                })
                .catch(error => {
                    // Hide processing status and show error
                    ocrProcessingStatus.classList.add('d-none');
                    ocrResult.classList.remove('d-none');
                    ocrResultContent.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                });
        });

        // Fill form with extracted data
        fillFormButton.addEventListener('click', function() {
            if (!extractedData) return;

            // Fill invoice details
            if (extractedData.header) {
                // Invoice Number
                const invoiceNumberInput = document.querySelector('input[name="invoice_number"]');
                if (invoiceNumberInput && extractedData.header.invoice_no) {
                    invoiceNumberInput.value = extractedData.header.invoice_no;
                }

                // Invoice Date
                const invoiceDateInput = document.querySelector('input[name="invoice_date"]');
                if (invoiceDateInput && extractedData.header.date) {
                    // Convert date format if needed
                    const dateStr = extractedData.header.date;
                    try {
                        const dateParts = dateStr.split(/[\/\-\.]/);
                        if (dateParts.length === 3) {
                            // Assuming day/month/year format
                            let day = dateParts[0].padStart(2, '0');
                            let month = dateParts[1].padStart(2, '0');
                            let year = dateParts[2];

                            // If year is 2 digits, convert to 4 digits
                            if (year.length === 2) {
                                year = '20' + year;
                            }

                            invoiceDateInput.value = `${year}-${month}-${day}`;
                        }
                    } catch (e) {
                        console.error('Error parsing date:', e);
                    }
                }

                // Due Date
                const dueDateInput = document.querySelector('input[name="due_date"]');
                if (dueDateInput && extractedData.header.due_date) {
                    // Convert date format if needed
                    const dateStr = extractedData.header.due_date;
                    try {
                        const dateParts = dateStr.split(/[\/\-\.]/);
                        if (dateParts.length === 3) {
                            // Assuming day/month/year format
                            let day = dateParts[0].padStart(2, '0');
                            let month = dateParts[1].padStart(2, '0');
                            let year = dateParts[2];

                            // If year is 2 digits, convert to 4 digits
                            if (year.length === 2) {
                                year = '20' + year;
                            }

                            dueDateInput.value = `${year}-${month}-${day}`;
                        }
                    } catch (e) {
                        console.error('Error parsing date:', e);
                    }
                }
            }

            // Fill line items
            if (extractedData.items && extractedData.items.length > 0) {
                // Clear existing items except the first row
                const tbody = document.getElementById('SUPPLIER_INVOICE-tbody');
                while (tbody.children.length > 1) {
                    tbody.removeChild(tbody.lastChild);
                }

                // Get the first row as a template
                const firstRow = tbody.children[0];

                // Clear the first row's values
                const descriptionSelect = firstRow.querySelector('select[name="description_id[]"]');
                if (descriptionSelect) {
                    descriptionSelect.value = '';
                }

                const commentTextarea = firstRow.querySelector('textarea[name="comment[]"]');
                if (commentTextarea) {
                    commentTextarea.value = '';
                }

                const quantityInput = firstRow.querySelector('input[name="quantity[]"]');
                if (quantityInput) {
                    quantityInput.value = '';
                }

                const unitPriceInput = firstRow.querySelector('input[name="unit_price[]"]');
                if (unitPriceInput) {
                    unitPriceInput.value = '';
                }

                // Fill the first row with the first item
                if (commentTextarea && extractedData.items[0].description) {
                    commentTextarea.value = extractedData.items[0].description;
                }

                if (quantityInput) {
                    quantityInput.value = '1';
                }

                if (unitPriceInput && extractedData.items[0].total_excl_vat) {
                    unitPriceInput.value = extractedData.items[0].total_excl_vat;
                }

                // Add additional rows for remaining items
                for (let i = 1; i < extractedData.items.length; i++) {
                    const item = extractedData.items[i];

                    // Clone the first row
                    const newRow = firstRow.cloneNode(true);

                    // Update the values
                    const newCommentTextarea = newRow.querySelector('textarea[name="comment[]"]');
                    if (newCommentTextarea && item.description) {
                        newCommentTextarea.value = item.description;
                    }

                    const newQuantityInput = newRow.querySelector('input[name="quantity[]"]');
                    if (newQuantityInput) {
                        newQuantityInput.value = '1';
                    }

                    const newUnitPriceInput = newRow.querySelector('input[name="unit_price[]"]');
                    if (newUnitPriceInput && item.total_excl_vat) {
                        newUnitPriceInput.value = item.total_excl_vat;
                    }

                    // Add the new row to the table
                    tbody.appendChild(newRow);
                }

                // Trigger change events to update totals
                const event = new Event('change', { bubbles: true });
                document.querySelectorAll('input.unit_price').forEach(input => {
                    input.dispatchEvent(event);
                });
            }

            // Close the modal
            if (ocrModal) {
                ocrModal.hide();
            }
        });
    });
</script>
