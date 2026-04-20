<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="importModalLabel">Import Customers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="bg-light p-3 rounded mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-primary fw-bold">Step 1: Upload</small>
                        <small class="text-muted" id="import-step-2">Step 2: Map Fields</small>
                        <small class="text-muted" id="import-step-3">Step 3: Finish</small>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div id="importProgressBar" class="progress-bar" role="progressbar" style="width: 33%;"></div>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>How it works:</strong> Upload your Excel file, match your spreadsheet columns to our system fields, and click import to sync your data.
                    </p>
                </div>

                <div id="step1" class="import-step">
                    <div class="text-center mb-4">
                        <h6 class="mb-3">Upload Excel File</h6>
                        <p class="text-muted">Select a <strong>.xlsx</strong> or <strong>.xls</strong> file containing your customer list.</p>
                        <form id="uploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                            </div>
                            <div class="mb-4">
                                <small class="text-muted">Don't have a file? <a href="#" id="downloadSample">Download a sample template</a></small>
                            </div>
                            <button type="submit" class="btn btn-primary px-5">Upload & Continue</button>
                        </form>
                    </div>
                </div>

                <div id="step2" class="import-step d-none">
                    <h6 class="mb-1">Map Columns</h6>
                    <p class="text-muted mb-4 small">Ensure your Excel columns match the fields below. If a column isn't needed, select "Skip".</p>
                    <form id="mappingForm">
                        <div class="row" id="columnMappingContainer" style="max-height: 300px; overflow-y: auto;">
                            @php
                                $customerImport = new \App\Import\CustomerImport();
                                $fields = $customerImport->getAvailableFields();
                            @endphp

                            @foreach($fields as $index => $field)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label mb-1 {{ isset($field['required']) ? 'required' : '' }}"><strong>{{ $field['label'] }}</strong></label>
                                    <select class="form-select form-select-sm excel-column-select" {{ $field['required'] ?? '' }} name="{{ $field['key'] }}">
                                        <option value="">-- Skip this field --</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between mt-4 border-top pt-3">
                            <button type="button" class="btn btn-outline-secondary px-4" id="backToUpload">
                                <i class="bi bi-arrow-left"></i> Back
                            </button>
                            <button type="submit" class="btn btn-primary px-4">Start Import</button>
                        </div>
                    </form>
                </div>

                <div id="step3" class="import-step d-none">
                    <div class="text-center mb-4">
                        <div id="importSuccess" class="d-none">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                            <h5 class="mt-3">Import Complete!</h5>
                            <p class="text-muted" id="successMessage"></p>
                        </div>
                        <div id="importError" class="d-none">
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3.5rem;"></i>
                            <h5 class="mt-3">Import Encountered Issues</h5>
                            <div class="alert alert-danger text-start" id="errorContainer">
                                <ul id="errorList" class="mb-0 small"></ul>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-4 border-top pt-3">
                            <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Close Window</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
