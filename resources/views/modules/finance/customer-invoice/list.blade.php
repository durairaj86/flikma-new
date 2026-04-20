@section('js','customer_invoice')
@section('page-title','Customer Invoice')
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
                                <label class="form-label fw-medium">Customer</label>
                                <x-common.customers multiple></x-common.customers>
                            </div>

                        </div>
                    </div>

                    <!-- Action buttons -->
                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0">
                @if(isset($job_no))
                    <h3 class="fw-bold text-muted bg-info-subtle rounded p-3">
                        {{ $job_no }}
                    </h3>
                @endif

                {{--<div class="gap-4">
                    <ul class="nav status-tabs align-items-center border-bottom" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="draft">
                                <span><i class="bi bi-clock text-warning me-1"></i> Draft -</span>
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
                </div>--}}
            </div>
            <div class="d-flex justify-content-between">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-primary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        Filter
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new" data-loader-id="{{ $job_id ?? 'list' }}">New
                    Customer Invoice
                </button>
            </div>
        </div>


        <div class="container-fluid pb-4">
            <div class="row g-4">

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="text-uppercase text-muted fw-bold small mb-1">Total Net Sales</h6>
                                    <h4 class="fw-black mb-0 text-primary"><span id="overall_sales">0.00</span> <small class="fs-6 fw-medium">SAR</small></h4>
                                </div>
                                <div class="bg-primary-subtle p-3 rounded-3">
                                    <i class="bi bi-file-earmark-bar-graph fs-4 text-primary"></i>
                                </div>
                            </div>

                            <div class="row g-0 text-center border-top pt-3">
                                <div class="col-3 border-end">
                                    <div id="allCount" class="h5 fw-bold mb-0">0</div>
                                    <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Total</small>
                                </div>
                                <div class="col-3 border-end">
                                    <div id="approvedCount" class="h5 fw-bold text-success mb-0">0</div>
                                    <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Paid</small>
                                </div>
                                <div class="col-3 border-end">
                                    <div id="draftCount" class="h5 fw-bold text-warning mb-0">0</div>
                                    <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Draft</small>
                                </div>
                                <div class="col-3">
                                    <div id="cancelledCount" class="h5 fw-bold text-danger mb-0">0</div>
                                    <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Void</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="text-uppercase text-muted fw-bold small mb-1">Outstanding Receivable</h6>
                                    <h4 class="fw-black mb-0 text-danger"><span id="total_approved_grand">0.00</span> <small class="fs-6 fw-medium text-dark">SAR</small></h4>
                                </div>
                                <div class="bg-success-subtle p-3 rounded-3">
                                    <i class="bi bi-check-circle fs-4 text-success"></i>
                                </div>
                            </div>

                            <div class="d-flex gap-4 border-top pt-3">
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Excl. Tax</small>
                                    <span id="total_approved_sub" class="fw-bold">0.00</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">VAT Amount</small>
                                    <span id="total_approved_tax" class="fw-bold">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="text-uppercase text-muted fw-bold small mb-1">Net Draft Value</h6>
                                    <h4 class="fw-black mb-0 text-dark"><span id="total_draft_grand">0.00</span> <small class="fs-6 fw-medium">SAR</small></h4>
                                </div>
                                <div class="bg-secondary-subtle p-3 rounded-3">
                                    <i class="bi bi-pencil-square fs-4 text-secondary"></i>
                                </div>
                            </div>

                            <div class="d-flex gap-4 border-top pt-3">
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Excl. Tax</small>
                                    <span id="total_draft_sub" class="fw-bold">0.00</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">VAT Amount</small>
                                    <span id="total_draft_tax" class="fw-bold">0.00</span>
                                </div>
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
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
            </div>

            <div class="">
                <table class="table align-middle dataTable" style="border-collapse: separate; border-spacing: 0 10px;" id="dataTable" data-min-height="min-height:75vh;" data-title="Job" data-model-size="lg">
                    <thead class="table-light bg-white">
                    <tr class="text-muted" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                        <th class="border-0">Invoice / Job Ref</th>
                        <th class="border-0">Customer</th>
                        <th class="border-0">Route & Carrier</th>
                        <th class="text-end border-0">Total Excl. VAT</th>
                        <th class="text-end border-0">Tax Amount</th>
                        <th class="text-end border-0">Balance Due</th>
                        <th class="border-0">Dates</th>
                        <th class="text-end border-0">Aging / Status</th>
                        <th class="border-0"></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.email.send-email')
    @include('modules.finance.customer-invoice.customer-invoice-view')
</x-app-layout>
<style>
    /* Typography and Alignment */
    .main-text {
        font-weight: 600;
        color: #212529;
    }

    .sub-text {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .amount-text {
        font-weight: 700;
        color: #0d6efd; /* Primary blue for main amounts */
    }

    .amount-text.paid {
        color: #198754; /* Green for paid amounts */
    }

    /* Status Pills */
    .status-pill {
        padding: 3px 8px;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.65rem;
        margin-bottom: 2px;
    }

    .status-paid {
        background-color: #d1e7dd; /* Light green */
        color: #0f5132; /* Dark green */
    }

    .status-overdue {
        background-color: #f8d7da; /* Light red */
        color: #842029; /* Dark red */
    }

    .status-approved {
        background-color: #cfe2ff; /* Light blue */
        color: #084298; /* Dark blue */
    }

    .status-draft {
        background-color: #fff3cd; /* Light yellow */
        color: #664d03; /* Dark yellow */
    }

    /* Action Button Styling */
    .btn-action {
        width: 30px;
        height: 30px;
        padding: 0;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .x-small {
        font-size: 0.65rem;
    }
</style>
