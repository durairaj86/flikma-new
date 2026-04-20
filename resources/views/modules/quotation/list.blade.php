@section('page-title','Quotations')
@section('js','quotation')
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
                                <label class="form-label fw-medium">Quotation Date</label>
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
            <div class="d-flex justify-content-between pt-3">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-secondary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        Filter
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Quotation</button>
            </div>
        </div>
        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search quotations..." aria-label="Search quotations...">
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1 overflow-auto">
                <table class="table align-middle dataTable" id="dataTable">
                    <thead class="table-light sticky-top bg-white">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Services</th>
                        <th>Activity</th>
                        <th>POL -> POD</th>
                        <th>Quotation Date</th>
                        <th>Expiry Date</th>
                        <th>Salesman</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {{--<tr>
                        <td>
                            <strong>Quote Alpha-12345</strong><br>
                            Received at Mumbai Warehouse
                        </td>
                        <td><span class="status-badge status-ontime">On Time</span></td>
                        <td>A1BC23D45E</td>
                        <td>Mumbai, IN</td>
                        <td>Dubai, AE</td>
                        <td>Aug 15, 2025</td>
                        <td>Aug 30, 2025</td>
                        <td>No</td>
                        <td><i class="bi bi-three-dots"></i></td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Quote Beta-67890</strong><br>
                            Received at Chennai Warehouse
                        </td>
                        <td><span class="status-badge status-delayed">Delayed</span></td>
                        <td>XY9Z7AB56C</td>
                        <td>Chennai, IN</td>
                        <td>Singapore, SG</td>
                        <td>Sep 5, 2025</td>
                        <td>Sep 20, 2025</td>
                        <td>Yes</td>
                        <td><i class="bi bi-three-dots"></i></td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Quote Gamma-54321</strong><br>
                            Received at Delhi Warehouse
                        </td>
                        <td><span class="status-badge status-intransit">In Transit</span></td>
                        <td>MN10Z34PQ</td>
                        <td>Delhi, IN</td>
                        <td>Hamburg, DE</td>
                        <td>Oct 1, 2025</td>
                        <td>Oct 20, 2025</td>
                        <td>No</td>
                        <td><i class="bi bi-three-dots"></i></td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Quote Delta-96765</strong><br>
                            Received at Bangalore Warehouse
                        </td>
                        <td><span class="status-badge status-cancelled">Cancelled</span></td>
                        <td>T8U9OVWZ12</td>
                        <td>Bangalore, IN</td>
                        <td>London, UK</td>
                        <td>Nov 5, 2025</td>
                        <td>Nov 22, 2025</td>
                        <td>No</td>
                        <td><i class="bi bi-three-dots"></i></td>
                    </tr>--}}
                    </tbody>
                </table>
            </div>

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
    @include('modules.quotation.quotation-view')
</x-app-layout>
