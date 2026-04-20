@section('page-title','Credit Note')
@section('js','credit_note')
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
                                <label class="form-label fw-medium">Credit Note Date</label>
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
                                <label class="form-label fw-medium">Invoice</label>
                                <select class="tom-select" id="filter-invoice" name="invoice">
                                    <option value="">All Invoices</option>
                                    @foreach(\App\Models\Finance\CustomerInvoice\CustomerInvoice::where('status', 3)->get() as $invoice)
                                        <option value="{{ encodeId($invoice->id) }}">{{ $invoice->row_no }}</option>
                                    @endforeach
                                </select>
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

        <div class="d-flex justify-content-between align-items-start">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
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
                </div>
            </div>
            <div class="d-flex justify-content-between pt-3">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-primary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        Filter
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Credit Note</button>
            </div>
        </div>
        <div class="row g-4 mb-3 align-items-stretch">
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 bg-light shadow-sm h-100">
                    <div class="d-flex flex-column border-bottom border-primary-subtle pb-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                            <div class="fw-bold text-primary"><i
                                    class="bi bi-file-earmark-bar-graph fs-5 text-primary-emphasis ms-2"></i> Overall
                                Summary
                            </div>
                            <div class="text-end">
                                <div class="fs-3 fw-bolder text-primary-emphasis">
                                    <span id="overall_sales">0.00</span> SAR
                                </div>
                                <span class="text-secondary-emphasis text-xsmall fw-medium d-block text-uppercase">Total Net Sales</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex flex-column align-items-center">
                            <div id="allCount" class="fs-4 fw-bold text-body-emphasis">0</div>
                            <span
                                class="text-secondary-emphasis text-xxsmall text-uppercase fw-medium mt-1 d-block text-center">Credit Notes</span>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div id="approvedCount" class="fs-4 fw-bold text-success">0</div>
                            <span class="text-success text-xxsmall text-uppercase fw-medium mt-1 d-block text-center">Approved</span>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div id="draftCount" class="fs-4 fw-bold text-warning">0</div>
                            <span class="text-warning text-xxsmall text-uppercase fw-medium mt-1 d-block text-center">Draft</span>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <div id="cancelledCount" class="fs-4 fw-bold text-danger">0</div>
                            <span class="text-danger text-xxsmall text-uppercase fw-medium mt-1 d-block text-center">Cancelled</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
            </div>
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable" data-module-url="credit-note">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>Credit Note No</th>
                        <th>Customer</th>
                        <th>Job No</th>
                        <th>Invoice No</th>
                        <th class="text-end">Total Excl. VAT (SAR)</th>
                        <th class="text-end">Tax Amount</th>
                        <th class="text-end">Total Amount</th>
                        <th>Credit Note Date</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.email.send-email')
    @include('modules.finance.credit-note.credit-note-view')
</x-app-layout>
