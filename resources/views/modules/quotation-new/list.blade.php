@section('page-title','Quotations New')
@section('js','quotation-new')
<x-app-layout>
    <main class="gmail-content bg-white px-3">

        <style>
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
            #dataTable tbody tr:hover td:first-child { background-color: rgba(0,0,0,.04); }

            /* Sticky last column (Edit) */
            #dataTable thead tr th:last-child,
            #dataTable tbody tr td:last-child {
                position: sticky;
                right: 0;
                z-index: 2;
                box-shadow: -3px 0 6px -3px rgba(0, 0, 0, .15);
            }
            #dataTable thead tr th:last-child { background-color: #f8f9fa; z-index: 3; }
            #dataTable tbody tr td:last-child  { background-color: #fff; }
            #dataTable tbody tr:hover td:last-child { background-color: rgba(0,0,0,.04); }
        </style>

        <!-- Filter Panel -->
        <div id="filterPanel" class="card shadow-sm border-0 d-none">
            <div class="card-header bg-light border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">Advanced Filters</h6>
                </div>
            </div>
            <div class="card-body">
                <form id="list-filter" method="post" novalidate>
                    @csrf
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
                                    <option value="thisYear">This Year</option>
                                </select>
                            </div>--}}
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
                                <label class="form-label fw-medium">Client</label>
                                <x-common.customers multiple></x-common.customers>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                                <i class="bi bi-search me-1"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs + New Button -->
        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0">
                <ul class="nav align-items-center" id="listTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center gap-1 active status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button"
                                id="{{ \App\Models\QuotationNew\QuotationNew::STATUS_PENDING }}">
                            <i class="bi bi-clock me-1"></i> Pending -
                            <span class="status-count ms-1" id="pendingCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link py-2 d-flex align-items-center gap-1 status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button"
                                id="{{ \App\Models\QuotationNew\QuotationNew::STATUS_APPROVED }}">
                            <i class="bi bi-check-circle me-1"></i> Approved -
                            <span class="status-count ms-1" id="approvedCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link py-2 d-flex align-items-center gap-1 status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button"
                                id="{{ \App\Models\QuotationNew\QuotationNew::STATUS_CANCELLED }}">
                            <i class="bi bi-x-circle me-1"></i> Cancelled -
                            <span class="status-count ms-1" id="cancelledCount">0</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="d-flex justify-content-between gap-2">
                <button class="btn btn-outline-secondary btn-round" id="filter-box">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ url('sales/quotations-new/create') }}" class="btn btn-primary rounded-pill px-4" id="new">
                    New Quotation
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
                <div>
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search quotations...">
                    </div>
                </div>
            </div>
            <div class="flex-grow-1 overflow-auto" style="min-height:320px;">
                <table class="table align-middle dataTable" id="dataTable" data-model-size="lg">
                    <thead class="table-light sticky-top">
                    <tr>
                        <th style="min-width:140px;">Quote No</th>
                        <th style="min-width:200px;">Client</th>
                        <th style="min-width:85px;">Branch</th>
                        <th style="min-width:100px;">Date</th>
                        <th style="min-width:90px;">Status</th>
                        <th style="min-width:120px;">Latest Comments</th>
                        <th style="min-width:150px;">Operational Activity</th>
                        <th style="min-width:130px;">Origin</th>
                        <th style="min-width:200px;">Destination</th>
                        <th style="min-width:100px;">Valid From</th>
                        <th style="min-width:90px;">Valid To</th>
                        <th style="min-width:100px;">User Name</th>
                        <th style="min-width:100px;">Sales Person</th>
                        <th style="min-width:90px;">INCO Term</th>
                        <th style="min-width:140px;">Carrier</th>
                        <th style="min-width:120px;">Remarks</th>
                        <th style="min-width:110px;">Shipment No.</th>
                        <th style="min-width:80px;">Job No.</th>
                        <th style="min-width:80px;">No.of Pcs</th>
                        <th style="min-width:90px;">G.Weight</th>
                        <th style="min-width:80px;">Volume</th>
                        <th style="min-width:80px;">P.Sale</th>
                        <th style="min-width:75px;">P.Cost</th>
                        <th style="min-width:70px;">GP</th>
                        <th style="min-width:65px;">GP%</th>
                        <th style="min-width:120px;">Shipper Name</th>
                        <th style="min-width:130px;">Consignee Name</th>
                        <th style="min-width:110px;">Shipment Status</th>
                        <th style="min-width:100px;">ETD</th>
                        <th style="min-width:100px;">ETA</th>
                        <th style="min-width:110px;">Origin Agent</th>
                        <th style="min-width:140px;">Destination Agent</th>
                        <th style="min-width:100px;">Enquiry No</th>
                        <th style="min-width:80px;">No Of Teu</th>
                        <th style="min-width:120px;">Container Type</th>
                        <th style="min-width:160px;">Vessel/Flight Name</th>
                        <th style="min-width:140px;">Voyage/Flight No</th>
                        <th style="min-width:130px;">Place Of Delivery</th>
                        <th style="min-width:50px;"></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
</x-app-layout>
