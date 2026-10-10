@section('js','supplier_invoice')
@section('page-title', __('Supplier Invoice'))
@section('hide-topbar', true)
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#supplierInvoiceWorkflowModal"
            title="{{ __('How supplier invoices work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .si-title { display: none; }
            body:not(.has-top-header) .si-title { display: block; }
            /* same light-grey header row as the Quotations table */
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
            #listTabs .status-btn:not(.active) { background: #f1f3f5; color: #495057; }
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            #listTabs .status-btn.active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 si-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new" data-loader-id="{{ $job_id ?? 'list' }}">{{ __('New Supplier Invoice') }}</button>
            </div>
        </div>

        <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card">

            <!-- Header -->
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="filter-panel-icon"><i class="bi bi-funnel-fill"></i></span>
                    <h6 class="mb-0 fw-semibold">{{ __('Filters') }}</h6>
                </div>
            </div>

            <div class="card-body pt-3">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="row g-4">

                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Invoice Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                       value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-3 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Supplier') }}</label>
                            <x-common.suppliers multiple></x-common.suppliers>
                        </div>

                        <div class="col-md-6 col-xl-2 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Payment Status') }}</label>
                            <select class="tom-select avoid-filter" name="filter-payment-status" id="filter-payment-status" size="3" placeholder="{{ __('All Payments') }}">
                                    <option value="all" selected>{{ __('All Payments') }}</option>
                                    <option value="paid">{{ __('Paid') }}</option>
                                    <option value="partial">{{ __('Partially Paid') }}</option>
                                    <option value="unpaid">{{ __('Unpaid') }}</option>
                                </select>
                        </div>

                        <div class="col-md-6 col-xl-3 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Invoice Type') }}</label>
                            <select class="tom-select avoid-filter" name="filter-overdue" id="filter-overdue">
                                    <option value="all" selected>{{ __('All Invoices') }}</option>
                                    <option value="overdue">{{ __('Overdue Invoices') }}</option>
                                    <option value="non_due">{{ __('Non-Due Invoices') }}</option>
                                </select>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4 pt-2 border-top filter-panel-actions">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            @if(isset($job_no))
                <h3 class="fw-bold text-muted bg-info-subtle rounded p-3">
                    {{ $job_no }}
                </h3>
            @endif
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                <span><i class="bi bi-clock me-1"></i> {{ __('Draft') }} -</span>
                                <span class="status-count ms-2" id="draftCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="approved">
                                <span><i class="bi bi-check-circle me-1"></i> {{ __('Approved') }} -</span>
                                <span class="status-count ms-2" id="approvedCount">0</span>
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle"></i> {{ __('Cancelled') }} -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                </div>
                <button class="btn btn-icon-search rounded-circle" id="toggle-summary" type="button"
                        title="{{ __('Hide Summary') }}" aria-label="{{ __('Hide Summary') }}"><i class="bi bi-eye-slash"></i></button>
                <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button"
                        title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
            </div>
        </div>

        <div class="container-fluid pb-3" id="summary-cards">
            <div class="row g-3">

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-body-tertiary px-4 py-3 h-100">
                        <h6 class="text-uppercase text-muted fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Summary') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="cardAllCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Total Invoices') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="cardApprovedCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Approved') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="cardDraftCount" class="fs-4 fw-bold mb-0">0</div>
                                <small class="text-muted">{{ __('Draft') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-si-approved px-4 py-3 h-100">
                        <h6 class="text-uppercase text-si-approved fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Approved Invoices') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="total_approved_sub" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Total Amount') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="total_approved_tax" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Total Tax') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="total_approved_grand" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Net Total') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="rounded-3 bg-si-draft px-4 py-3 h-100">
                        <h6 class="text-uppercase text-si-draft fw-semibold small mb-3" style="letter-spacing:.03em;">{{ __('Draft Invoices') }}</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div id="total_draft_sub" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Total Amount') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="total_draft_tax" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Total Tax') }}</small>
                            </div>
                            <div class="col-4">
                                <div id="total_draft_grand" class="fs-4 fw-bold mb-0">0.00</div>
                                <small class="text-muted">{{ __('Net Total') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                <div id="filtered-data"></div>
            </div>
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Job" data-model-size="lg">
                    <thead>
                    <tr class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.03em;">
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('FCY Amount') }}</th>
                        <th class="text-end">{{ __('Excl. VAT') }}</th>
                        <th class="text-end">{{ __('Tax') }}</th>
                        <th class="text-end">{{ __('Balance Due') }}</th>
                        <th>{{ __('Dates') }}</th>
                        <th class="text-end">{{ __('Aging') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        (function () {
            const KEY = 'supplier_invoice_hide_summary';
            const box = document.getElementById('summary-cards');
            const btn = document.getElementById('toggle-summary');
            if (!box || !btn) return;
            const apply = (hide) => {
                box.classList.toggle('d-none', hide);
                btn.querySelector('i').className = 'bi ' + (hide ? 'bi-eye' : 'bi-eye-slash');
                const t = hide ? @json(__('Show Summary')) : @json(__('Hide Summary'));
                btn.title = t; btn.setAttribute('aria-label', t);
            };
            let hidden = false;
            try { hidden = localStorage.getItem(KEY) === '1'; } catch (e) {}
            apply(hidden);
            btn.addEventListener('click', () => {
                hidden = !hidden;
                try { localStorage.setItem(KEY, hidden ? '1' : '0'); } catch (e) {}
                apply(hidden);
            });
        })();
    </script>
    @include('modules.email.send-email')
    @include('modules.finance.supplier-invoice.supplier-invoice-view')

    <!-- OCR Modal -->
    <div class="modal fade show" id="ocrModal1" tabindex="-1" aria-labelledby="ocrModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ocrModalLabel">{{ __('OCR Document Processing') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p>{{ __('Upload an invoice document (PDF, JPG, PNG) to automatically extract information and fill the form.') }}</p>
                        <form id="ocrUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <input type="file" name="file" id="ocrFile" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('Process Document') }}</button>
                        </form>
                    </div>
                    <div id="ocrProcessingStatus" class="d-none">
                        <div class="d-flex align-items-center mb-3">
                            <div class="spinner-border text-primary me-2" role="status">
                                <span class="visually-hidden">{{ __('Loading...') }}</span>
                            </div>
                            <span>{{ __('Processing document, please wait...') }}</span>
                        </div>
                    </div>
                    <div id="ocrResult" class="d-none">
                        <h6>{{ __('Extracted Information:') }}</h6>
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div id="ocrResultContent"></div>
                        </div>
                        <button type="button" id="fillFormButton" class="btn btn-success">{{ __('Fill Form with Data') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


<style>
    /* Supplier Invoice = money going out — amber tones instead of the
       green/yellow used on Customer Invoice's summary cards. */
    .bg-si-approved { background-color: #fef3c7; }
    .text-si-approved { color: #92400e; }
    .bg-si-draft { background-color: #ffedd5; }
    .text-si-draft { color: #9a3412; }

    /* Clean flat table — no floating row cards, just a thin divider
       between rows, matching a standard finance-app list. */
    #dataTable {
        border-collapse: collapse;
    }

    #dataTable thead th {
        background-color: #fff;
        color: #6c757d;
        font-weight: 600;
        border-bottom: 1px solid #e9ecef;
        padding: 0.65rem 1rem;
        white-space: nowrap;
    }

    #dataTable tbody td {
        padding: 0.65rem 1rem;
        border-bottom: 1px solid #f1f3f5;
        vertical-align: middle;
    }

    #dataTable tbody tr:hover td {
        background-color: #fafbfc;
    }

    #dataTable tbody tr:last-child td {
        border-bottom: none;
    }

    /* Two-line cell convention: bold primary line, muted small caption */
    .cell-primary {
        font-weight: 600;
        color: #212529;
        line-height: 1.3;
    }

    .cell-secondary {
        font-size: 0.75rem;
        color: #868e96;
        line-height: 1.3;
    }

    .x-small {
        font-size: 0.65rem;
    }

    /* Workflow flowchart */
    .wf-box {
        border: 2px solid;
        border-radius: 10px;
        padding: 10px 20px;
        text-align: center;
        min-width: 170px;
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.4;
    }
    .wf-box.wf-neutral  { border-color: #6c757d; background: #f8f9fa;  color: #495057; }
    .wf-box.wf-pending  { border-color: #ffc107; background: #fffbf0;  color: #856404; }
    .wf-box.wf-action   { border-color: #0d6efd; background: #f0f7ff;  color: #084298; }
    .wf-box.wf-success  { border-color: #198754; background: #f0fff4;  color: #0f5132; }
    .wf-box.wf-danger   { border-color: #dc3545; background: #fff5f5;  color: #842029; }
    .wf-box.wf-job      { border-color: #0dcaf0; background: #f0fdff;  color: #055160; }
    .wf-box.wf-decision { border-color: #6f42c1; background: #f8f0ff;  color: #432874; }
    .wf-arrow { color: #adb5bd; font-size: 1.3rem; line-height: 1.3; text-align: center; }
    .wf-badge { font-size: 0.7rem; border-radius: 20px; padding: 2px 8px; display: inline-block; margin-top: 4px; }
</style>

    @include('modules.workflows.supplier-invoice')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var wfBtn = document.querySelector('[data-bs-target="#supplierInvoiceWorkflowModal"]');
        var wfModalEl = document.getElementById('supplierInvoiceWorkflowModal');
        if (wfBtn && wfModalEl && window.bootstrap) {
            wfBtn.addEventListener('click', function (e) {
                e.preventDefault();
                try {
                    bootstrap.Modal.getOrCreateInstance(wfModalEl).show();
                } catch (err) {
                    console.error('Failed to open Supplier Invoice workflow modal:', err);
                }
            });
        } else {
            console.warn('Supplier Invoice workflow modal wiring skipped', {
                buttonFound: !!wfBtn,
                modalFound: !!wfModalEl,
                bootstrapLoaded: !!window.bootstrap
            });
        }
    });
</script>
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
            fetch('{{ route("ocr.read") }}', {
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
                        ocrResultContent.innerHTML = `<div class="alert alert-danger">{{ __('Error') }}: ${data.message || '{{ __('Failed to process document') }}'}</div>`;
                    }
                })
                .catch(error => {
                    // Hide processing status and show error
                    ocrProcessingStatus.classList.add('d-none');
                    ocrResult.classList.remove('d-none');
                    ocrResultContent.innerHTML = `<div class="alert alert-danger">{{ __('Error') }}: ${error.message}</div>`;
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
