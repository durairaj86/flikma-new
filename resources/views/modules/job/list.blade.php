@section('page-title','Jobs')
@section('js','job')
@section('extra-js','customer')
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
                                <label class="form-label fw-medium">Job Date</label>
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

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Carrier</label>
                                <input type="text" class="form-control" id="filter-carrier" name="filter_carrier" placeholder="Search carrier">
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
                            {{--<button class="btn btn-outline-secondary me-2 px-4">Reset</button>--}}
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
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                                <span><i class="bi bi-clock me-1"></i> Pending -</span>
                                <span class="status-count ms-2" id="pendingCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="completed">
                                <span><i class="bi bi-check-circle me-1"></i> Completed -</span>
                                <span class="status-count ms-2" id="completedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle me-1"></i> Cancelled -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="trashed">
                                <span><i class="bi bi bi-trash me-1"></i> Trashed -</span>
                                <span class="status-count ms-2" id="trashedCount">0</span>
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

                    <!-- Filter panel (dropdown style) -->

                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Job</button>
            </div>
        </div>
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data">
                {{--<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small" style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button" class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close">&times;</button>
                </div>
                <div class="d-inline-flex bg-light border rounded-pill px-2 py-1 me-2 mb-2 small" style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button" class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close">&times;</button>
                </div>--}}
                </div>


                <!-- Example static label -->
                <!-- Example tag that will be dynamically created
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
                -->
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search jobs..." aria-label="Search jobs...">
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            {{--<div class="">
                <table class="table align-middle dataTable" id="dataTable" data-title="Job" data-model-size="lg"
                       data-min-height="min-height:75vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>Job No</th>
                        <th>Customer</th>
                        <th>Services</th>
                        <th>POL</th>
                        <th>POD</th>
                        <th>Carrier / Lines</th>
                        <th class="text-end">Cus Inv.</th>
                        <th class="text-end">Job Date</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>--}}
                <table class="table align-middle" id="dataTable" style="border-collapse: separate; border-spacing: 0 12px;">
                    <thead>
                    <tr class="text-secondary small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                        <th class="ps-4 border-0">Job / Service</th>
                        <th class="border-0">Routing & Vessel</th>
                        <th class="border-0">Payload Details</th>
                        <th class="border-0">Tracking / Ref</th>
                        <th class="border-0">Consignor & Consignee</th>
                        {{--<th class="border-0">Ops Owner</th>--}}
                        {{--<th class="text-end border-0">Financial Status</th>--}}

                        <th class="text-end border-0">Milestone</th>
                        <th class="text-end border-0">Job Date</th>
                        <th class="border-0"></th>
                    </tr>
                    </thead>
                    <tbody>


                    </tbody>
                </table>

            <style>
                .x-small { font-size: 0.7rem; }
                /* Tooltip for priority icon */
                [title]:hover::after {
                    content: attr(title);
                    position: absolute;
                    background: #333;
                    color: #fff;
                    padding: 4px 8px;
                    font-size: 10px;
                    border-radius: 4px;
                    margin-top: -25px;
                }
                #dataTable {
                    /* Force separation */
                    border-collapse: separate !important;
                    /* 0 horizontal space, 12px vertical space */
                    border-spacing: 0 12px !important;
                }

                #dataTable tbody tr {
                    /* Optional: shadow makes the "floating" effect look better */
                    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                }

                #dataTable td {
                    /* Ensure borders don't double up */
                   /* border-bottom: 1px solid #dee2e6;*/
                }

                /* Round the corners of the floating rows */

            </style>

            <!-- Rejected Quotes -->
            {{--<div class="rejected-box mt-4">
                <h6 class="fw-bold">Rejected Quotes List</h6>
                <p class="mb-1 text-muted">You rejected this quote & asked for edit.</p>
                <small class="text-muted">Date: 23 July, 2023 | ID: #241041080</small>
                <hr>
                <div class="d-flex align-items-center">
                    <div
                        class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                        style="width:40px; height:40px;">
                        H
                    </div>
                    <div>
                        <p class="mb-0 fw-bold">Himanshu Shrivastav</p>
                        <small class="text-muted">himanshu12@gmail.com</small>
                    </div>
                    <div class="ms-auto text-end">
                        <p class="mb-0">Quotation No<br><b>#131341</b></p>
                        <small class="text-muted">From DB Schenker</small>
                    </div>
                </div>
            </div>--}}
        </div>
    </main>
    @include('modules.email.send-email')
    @include('modules.job.job-view')

</x-app-layout>
