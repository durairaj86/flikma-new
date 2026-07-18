@section('page-title','Enquiries')
@section('js','enquiry')
@section('extra-js','customer,prospect')
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#enquiryWorkflowModal"
            title="How enquiries work">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">How it works</span>
    </button>
@endpush
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
                                <label class="form-label fw-medium">Enquiry Date</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                           value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                    <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                           value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                                </div>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Customer</label>
                                <x-common.customers multiple></x-common.customers>
                            </div>

                            <div class="col-md-3 form-filter pol-pod-select">
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

                            <div class="col-md-3 pol-pod-select">
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
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle me-1"></i> Confirmed -</span>
                                <span class="status-count ms-2" id="confirmedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="completed">
                                <span><i class="bi bi-arrow-repeat me-1"></i> Converted to Quotation -</span>
                                <span class="status-count ms-2" id="completedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle me-1"></i> Cancelled / Expired -</span>
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
                <button class="btn btn-primary rounded-pill px-4" id="new">New Enquiry</button>
            </div>
        </div>
        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1" style="overflow: hidden;">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search enquiries..." aria-label="Search enquiries...">
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable">
                    <thead class="table-light sticky-top bg-white" style="z-index: 10;">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Activity</th>
                        <th>POL</th>
                        <th>POD</th>
                        <th>Pickup Date</th>
                        {{--<th>Weight(kg)/Volume (m³)</th>--}}
                        <th>Expiry Date</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.enquiry.enquiry-view')
    @include('modules.email.send-email')

    <style>
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

    <!-- Enquiry Workflow Modal -->
    <div class="modal fade" id="enquiryWorkflowModal" tabindex="-1" aria-labelledby="enquiryWorkflowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="enquiryWorkflowModalLabel">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>Enquiry Workflow
                        </h5>
                        <p class="text-muted small mb-0">How enquiries move through your system</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3 pb-4">

                    <!-- Flowchart -->
                    <div class="d-flex flex-column align-items-center gap-0">

                        <div class="wf-box wf-pending">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 1</div>
                            <i class="bi bi-clipboard-plus me-1"></i> Create Enquiry
                            <div class="wf-badge bg-warning text-dark">Pending</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-action">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 2</div>
                            <i class="bi bi-check2-square me-1"></i> Confirm Requirements
                            <div class="wf-badge bg-info text-white">Confirmed</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-job">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 3</div>
                            <i class="bi bi-file-earmark-plus me-1"></i> Convert to Quotation
                            <div class="wf-badge text-white" style="background:#0dcaf0;">Quotation</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <!-- Decision -->
                        <div class="wf-box wf-decision">
                            <i class="bi bi-question-circle me-1"></i> Enquiry Outcome
                        </div>

                        <div class="d-flex justify-content-center gap-5 w-100 mt-0">
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-success">
                                    <i class="bi bi-check-circle me-1"></i> Completed
                                    <div class="wf-badge bg-success text-white">Completed</div>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-danger">
                                    <i class="bi bi-x-circle me-1"></i> Cancelled
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
                                <small class="text-muted">Newly created, awaiting review</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0f7ff;border:1px solid #0d6efd;">
                                <span class="badge bg-info text-white">Confirmed</span>
                                <small class="text-muted">Requirements verified</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fff4;border:1px solid #198754;">
                                <span class="badge bg-success">Completed</span>
                                <small class="text-muted">Enquiry fulfilled</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fff5f5;border:1px solid #dc3545;">
                                <span class="badge bg-danger">Cancelled</span>
                                <small class="text-muted">Rejected or dropped</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> Create Enquiry
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
