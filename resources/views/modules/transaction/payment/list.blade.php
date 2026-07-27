@section('page-title','Payments')
@section('js','payment')
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#paymentWorkflowModal"
            title="How payments work">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">How it works</span>
    </button>
@endpush
<x-app-layout>
    <!-- Main Content -->
    <main class="gmail-content bg-white px-3">
        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
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
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle me-1"></i> Cancelled -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-secondary me-2" onclick="toggleFilter()"><i class="bi bi-funnel"></i>
                        Filter
                    </button>

                    <!-- Filter panel (dropdown style) -->
                    <div id="filterPanel" class="card p-3 d-none"
                         style="position: absolute; top: 100%; right: 0; width: 20rem; z-index: 1000; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);">
                        <!-- Date range -->
                        <div class="mb-3">
                            {{--<div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-medium">Date range</span>
                                <button class="btn btn-link btn-sm p-0" onclick="resetField('date')">Reset</button>
                            </div>

                            <!-- Predefined date ranges -->
                            <select class="form-control selectpicker mb-2" id="presetDateRange"
                                    onchange="setPresetDateRange()">
                                <option value="">Custom</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="thisMonth">This Month</option>
                                <option value="lastMonth">Last Month</option>
                                <option value="thisQuarter">This Quarter</option>
                                <option value="lastQuarter">Last Quarter</option>
                                <option value="thisYear">This Year</option>
                                <option value="lastYear">Last Year</option>
                            </select>--}}

                            <div class="d-flex gap-2">
                                <input type="date" class="form-control datepicker" id="fromDate">
                                <input type="date" class="form-control datepicker" id="toDate">
                            </div>
                        </div>

                        <!-- Activity type -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-medium">Activity type</span>
                                <button class="btn btn-link btn-sm p-0" onclick="resetField('activity')">Reset</button>
                            </div>
                            <select class="form-select" id="activityType">
                                <option>All warehouses</option>
                                <option>Warehouse 1</option>
                                <option>Warehouse 2</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-medium">Status</span>
                                <button class="btn btn-link btn-sm p-0" onclick="resetField('status')">Reset</button>
                            </div>
                            <select class="form-select" id="status">
                                <option>Active</option>
                                <option>Inactive</option>
                            </select>
                        </div>

                        <!-- Keyword search -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-medium">Keyword search</span>
                                <button class="btn btn-link btn-sm p-0" onclick="resetField('keyword')">Reset</button>
                            </div>
                            <input type="text" class="form-control" placeholder="Search..." id="keyword">
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-outline-secondary" onclick="resetAll()">Reset all</button>
                            <button class="btn btn-success">Apply now</button>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Payment</button>
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small"
                     style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button"
                            class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close"
                            onclick="clearDateLabel()">
                        &times;
                    </button>
                </div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search jobs..." aria-label="Search jobs...">
                    </div>
                </div>
            </div>
            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-model-size="md">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>#</th>
                        <th>Payment No</th>
                        <th>Supplier</th>
                        <th>Payment Date</th>
                        <th>Account</th>
                        <th>Reference No</th>
                        <th>Currency</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Disapproval Reason Modal -->
        <div class="modal fade" id="disapprovalReasonModal" tabindex="-1" aria-labelledby="disapprovalReasonModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="disapprovalReasonModalLabel">Disapproval Reason</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="disapprovalReasonForm">
                            <input type="hidden" id="payment_id" name="payment_id">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Disapproval</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="submitDisapprovalReason">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        @include('modules.transaction.payment.payment-view')
    </main>

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
        .wf-box.wf-decision { border-color: #6f42c1; background: #f8f0ff;  color: #432874; }
        .wf-arrow { color: #adb5bd; font-size: 1.3rem; line-height: 1.3; text-align: center; }
        .wf-badge { font-size: 0.7rem; border-radius: 20px; padding: 2px 8px; display: inline-block; margin-top: 4px; }
    </style>

    <!-- Payment Workflow Modal -->
    <div class="modal fade" id="paymentWorkflowModal" tabindex="-1" aria-labelledby="paymentWorkflowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="paymentWorkflowModalLabel">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>Payment Workflow
                        </h5>
                        <p class="text-muted small mb-0">How supplier payments move through your system</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3 pb-4">

                    <!-- Flowchart -->
                    <div class="d-flex flex-column align-items-center gap-0">

                        <div class="wf-box wf-neutral">
                            <i class="bi bi-receipt me-1"></i> Select Supplier &amp; Invoices
                            <div class="wf-badge bg-secondary text-white">Approved Supplier Invoices</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-pending">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 1</div>
                            <i class="bi bi-cash-coin me-1"></i> Create Payment
                            <div class="wf-badge bg-warning text-dark">Draft</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <div class="wf-box wf-action">
                            <div class="text-muted" style="font-size:0.7rem;font-weight:400;">STEP 2</div>
                            <i class="bi bi-send-check me-1"></i> Approve Payment
                            <div class="text-muted mt-1" style="font-size:0.75rem;">Deducts invoice balance &amp; posts to GL</div>
                        </div>
                        <div class="wf-arrow">↓</div>

                        <!-- Decision -->
                        <div class="wf-box wf-decision">
                            <i class="bi bi-question-circle me-1"></i> Payment Outcome
                        </div>

                        <div class="d-flex justify-content-center gap-5 w-100 mt-0">
                            <div class="d-flex flex-column align-items-center">
                                <div class="wf-arrow">↓</div>
                                <div class="wf-box wf-success">
                                    <i class="bi bi-check-circle me-1"></i> Approved
                                    <div class="wf-badge bg-success text-white">Approved</div>
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
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fffbf0;border:1px solid #ffc107;">
                                <span class="badge bg-warning text-dark">Draft</span>
                                <small class="text-muted">Awaiting approval</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f0fff4;border:1px solid #198754;">
                                <span class="badge bg-success">Approved</span>
                                <small class="text-muted">Posted &amp; invoice balance reduced</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#fff5f5;border:1px solid #dc3545;">
                                <span class="badge bg-danger">Cancelled</span>
                                <small class="text-muted">Payment called off</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal"
                            onclick="setTimeout(()=>document.getElementById('new').click(),300)">
                        <i class="bi bi-plus-lg me-1"></i> Create Payment
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
