@section('page-title','Quotations')
@section('js','quotation')
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#quotationWorkflowModal"
            title="How quotations work">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">How it works</span>
    </button>
@endpush
@section('extra-js','customer,prospect')
<x-app-layout>
    <main class="gmail-content bg-white px-3">

        <style>
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

            /* Sticky first column (Quote No) */
            #dataTable thead tr th:first-child,
            #dataTable tbody tr td:first-child {
                position: sticky;
                left: 0;
                z-index: 2;
                box-shadow: 3px 0 6px -3px rgba(0, 0, 0, .15);
            }
            #dataTable thead tr th:first-child { background-color: #f8f9fa; z-index: 3; }
            #dataTable tbody tr td:first-child  { background-color: #fff; }
            #dataTable tbody tr:hover td:first-child { background-color: #f5f5f5; }

            /* Column Settings drag-over highlight */
            .cs-drag-over {
                border-color: #0d6efd !important;
                background-color: #f0f7ff !important;
            }

            /* Sticky last column (Edit) */
            /*#dataTable thead tr th:last-child,
            #dataTable tbody tr td:last-child {
                position: sticky;
                right: 0;
                z-index: 2;
                box-shadow: -3px 0 6px -3px rgba(0, 0, 0, .15);
            }*/
            #dataTable thead tr th:last-child { background-color: #f8f9fa; z-index: 3; }
            #dataTable tbody tr td:last-child  { background-color: #fff; }
            #dataTable tbody tr:hover td:last-child { background-color: #f5f5f5; }
        </style>

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

                            {{--<div class="col-md-2">
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
                            </div>--}}

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Quotation Date</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                           value="{{ \Carbon\Carbon::today()->startOfYear()->format('d-m-Y') }}">
                                    <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                           value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                                </div>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Customer</label>
                                <x-common.customers multiple></x-common.customers>
                            </div>

                            <div class="col-md-2 form-filter pol-pod-select">
                                <label class="form-label fw-medium">
                                    POL <small class="text-muted">(Port of Loading)</small>
                                </label>

                                <div class="position-relative">

                                    <!-- Sea / Air toggle -->
                                    <div class="shipment-toggle">
                                        <input type="radio" class="btn-check sync-sea avoid-filter" name="shipment_mode" id="polSea"
                                               value="sea" checked>
                                        <label for="polSea">Sea</label>

                                        <input type="radio" class="btn-check sync-air avoid-filter" name="shipment_mode" id="polAir"
                                               value="air">
                                        <label for="polAir">Air</label>
                                    </div>

                                    <!-- POL -->
                                    <select id="filter-pol" name="filter-pol"
                                            class="tom-select-search"
                                            data-placeholder="Select Port of Loading">
                                        <option value=""></option>
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-2 pol-pod-select">
                                <label class="form-label fw-medium">
                                    POD <small class="text-muted">(Port of Discharge)</small>
                                </label>

                                <div class="position-relative">

                                    <!-- Sea / Air toggle -->
                                    <div class="shipment-toggle">
                                        <input type="radio" class="btn-check sync-sea avoid-filter" name="shipment_mode_2" id="polSea2"
                                               checked
                                               value="sea">
                                        <label for="polSea2">Sea</label>

                                        <input type="radio" class="btn-check sync-air avoid-filter" name="shipment_mode_2" id="polAir2"
                                               value="air">
                                        <label for="polAir2">Air</label>
                                    </div>

                                    <!-- POD -->
                                    <select id="filter-pod" name="filter-pod"
                                            class="tom-select-search"
                                            data-placeholder="Select Port of Discharge">
                                        <option value=""></option>
                                    </select>

                                </div>
                            </div>

                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mt-4">
                            <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-start">
            <div class="align-items-center flex-shrink-0 pb-3">
                <div class="gap-4">
                    <ul class="nav status-tabs align-items-center border-bottom mb-0" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between active status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                                <span><i class="bi bi-clock me-1"></i> Pending -</span>
                                <span class="status-count ms-2" id="pendingCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="accepted">
                                <span><i class="bi bi-check-circle me-1"></i> Accepted -</span>
                                <span class="status-count ms-2" id="acceptedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="converted">
                                <span><i class="bi bi-arrow-repeat me-1"></i> Converted To Job -</span>
                                <span class="status-count ms-2" id="convertedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle me-1"></i> Cancelled / Expired -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between pt-3 align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-round" id="filter-box">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <button class="btn btn-outline-secondary btn-round" id="columnSettingsBtn" title="Column Settings">
                    <i class="bi bi-columns-gap"></i> Columns
                </button>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Quotation</button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search quotations..." aria-label="Search quotations...">
                    </div>
                </div>
            </div>

            <!-- Empty / zero-records state (shown instead of table) -->
            <div id="quotationEmptyState" class="d-none text-center py-5 px-4">
                <div id="emptyStateNoData">
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-text-fill" style="font-size:3.5rem;color:#dde3ed;"></i>
                    </div>
                    <h5 class="fw-semibold text-muted mb-2">No Quotations Yet</h5>
                    <p class="text-muted small mb-4 mx-auto" style="max-width:400px;">
                        Manage your customer price requests here. Create a quotation, send it to your
                        customer, and convert accepted quotes into jobs.
                    </p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <button class="btn btn-primary rounded-pill px-4" id="new-first">
                            <i class="bi bi-plus-lg me-1"></i> Create First Quotation
                        </button>
                        <button class="btn btn-outline-secondary rounded-pill px-4"
                                data-bs-toggle="modal" data-bs-target="#quotationWorkflowModal">
                            <i class="bi bi-diagram-3 me-1"></i> How It Works
                        </button>
                    </div>
                </div>
                <div id="emptyStateNoResults" class="d-none">
                    <div class="mb-3">
                        <i class="bi bi-search" style="font-size:3rem;color:#dde3ed;"></i>
                    </div>
                    <h5 class="fw-semibold text-muted mb-2">No Results Found</h5>
                    <p class="text-muted small mb-0">Try adjusting your search or filter criteria.</p>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1 overflow-auto" id="tableWrapper">
                <table class="table align-middle dataTable" id="dataTable" data-modal-size="md">
                    <thead class="table-light sticky-top">
                    <tr>
                        {{-- Built dynamically by quotation.js from column settings --}}
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>{{-- #tableWrapper --}}

            {{-- Pagination / info — outside the scrollable wrapper so it doesn't move on horizontal scroll --}}
            <div id="dtFooter" class="d-none d-flex justify-content-between align-items-center px-3 py-2 border-top small text-muted flex-shrink-0"></div>
        </div>
    </main>
    @include('modules.email.send-email')
    @include('modules.quotation.quotation-view')

    <!-- Quotation Workflow Modal -->
    <div class="modal fade" id="quotationWorkflowModal" tabindex="-1" aria-labelledby="workflowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="workflowModalLabel">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>Quotation Workflow
                        </h5>
                        <p class="text-muted small mb-0">How quotations move through your system</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3 pb-4">

                    <!-- Flowchart -->
                    <div class="d-flex flex-column align-items-center gap-0">

                        <!-- Two starting points -->
                        <div class="d-flex justify-content-center gap-4 w-100">
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-box wf-neutral">
                                    <i class="bi bi-clipboard-check me-1"></i>
                                    From Enquiry
                                    <div class="wf-badge bg-secondary text-white">Via Enquiry Module</div>
                                </div>
                                <div class="wf-arrow">↓</div>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-box wf-neutral">
                                    <i class="bi bi-person-raised-hand me-1"></i>
                                    Direct Quotation
                                    <div class="wf-badge bg-secondary text-white">New Quotation Button</div>
                                </div>
                                <div class="wf-arrow">↓</div>
                            </div>
                        </div>

                        <!-- Step 2: Create Quotation -->
                        <div class="wf-box wf-pending">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 1</div>
                            <i class="bi bi-file-earmark-plus me-1"></i> Create Quotation
                            <div class="wf-badge bg-warning text-dark">Pending</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <!-- Step 3: Send to Customer -->
                        <div class="wf-box wf-action">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 2</div>
                            <i class="bi bi-send me-1"></i> Send to Customer
                            <div class="text-muted mt-1" style="font-size:0.75rem;">via Email or Print PDF</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <!-- Decision -->
                        <div class="wf-box wf-decision">
                            <i class="bi bi-question-circle me-1"></i> Customer Decision
                        </div>

                        <!-- Split paths -->
                        <div class="d-flex justify-content-center gap-5 w-100 mt-0">

                            <!-- Left: Accepted path -->
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-success">
                                    <i class="bi bi-check-circle me-1"></i> Accepted
                                    <div class="wf-badge bg-success text-white">Accepted</div>
                                </div>
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-action">
                                    <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 3</div>
                                    <i class="bi bi-arrow-repeat me-1"></i> Convert to Job
                                </div>
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-job">
                                    <i class="bi bi-briefcase-fill me-1"></i> Job Created
                                    <div class="wf-badge text-white" style="background:#0dcaf0;">Operations</div>
                                </div>
                            </div>

                            <!-- Right: Rejected path -->
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-danger">
                                    <i class="bi bi-x-circle me-1"></i> Rejected / Expired
                                </div>
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-danger">
                                    <i class="bi bi-slash-circle me-1"></i> Cancelled
                                    <div class="wf-badge bg-danger text-white">Cancelled</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Status legend -->
                    <hr class="mt-4">
                    <h6 class="fw-semibold text-muted mb-3 small text-uppercase">Status Guide</h6>
                    <div class="row g-2">
                        <div class="col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fffbf0;border:1px solid #ffc107;">
                                <span class="badge bg-warning text-dark">Pending</span>
                                <small class="text-muted">Awaiting customer response</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fff4;border:1px solid #198754;">
                                <span class="badge bg-success">Accepted</span>
                                <small class="text-muted">Customer approved quote</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fdff;border:1px solid #0dcaf0;">
                                <span class="badge" style="background:#0dcaf0;">Converted</span>
                                <small class="text-muted">Turned into a job</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fff5f5;border:1px solid #dc3545;">
                                <span class="badge bg-danger">Cancelled</span>
                                <small class="text-muted">Rejected or expired</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> Create Quotation
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Column Settings Modal -->
    <div class="modal fade" id="columnSettingsModal" tabindex="-1" aria-labelledby="columnSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="columnSettingsModalLabel">
                            <i class="bi bi-columns-gap text-primary me-2"></i>Column Settings
                        </h5>
                        <p class="text-muted small mb-0">Choose and arrange the columns shown in the quotation list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0" style="min-height:500px;">
                    <div class="row g-0 h-100">

                        <!-- Left: Available Fields -->
                        <div class="col-md-4 border-end d-flex flex-column" style="max-height:520px;">
                            <div class="p-3 border-bottom bg-light">
                                <h6 class="fw-semibold mb-2 small text-uppercase text-muted">Available Fields</h6>
                                <input type="text" id="csFieldSearch" class="form-control form-control-sm rounded-pill"
                                       placeholder="Search fields…">
                            </div>
                            <div id="csFieldList" class="flex-grow-1 overflow-auto p-2"></div>
                        </div>

                        <!-- Right: Column Order -->
                        <div class="col-md-8 d-flex flex-column" style="max-height:520px;">
                            <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between">
                                <h6 class="fw-semibold mb-0 small text-uppercase text-muted">Column Order</h6>
                                <span class="text-muted" style="font-size:0.75rem;">
                                    <i class="bi bi-grip-vertical"></i> Drag to reorder &nbsp;·&nbsp;
                                    <i class="bi bi-chevron-down"></i> Add sub-column
                                </span>
                            </div>
                            <div id="csColumnList" class="flex-grow-1 overflow-auto p-3"></div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="border-top p-3 bg-light">
                        <h6 class="small fw-semibold text-muted text-uppercase mb-2">
                            <i class="bi bi-eye me-1"></i>Preview
                        </h6>
                        <div class="table-responsive" style="max-height:100px;overflow:auto;">
                            <table class="table table-sm table-bordered mb-0 text-nowrap" id="csPreviewTable">
                                <thead class="table-secondary">
                                    <tr id="csPreviewRow"></tr>
                                </thead>
                                <tbody>
                                    <tr id="csPreviewDataRow"></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill me-auto" id="csResetBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to Default
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="csSaveBtn">
                        <i class="bi bi-check2 me-1"></i>Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
