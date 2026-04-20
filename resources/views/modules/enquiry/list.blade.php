@section('page-title','Enquiries')
@section('js','enquiry')
@section('extra-js','customer,prospect')
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
</x-app-layout>
