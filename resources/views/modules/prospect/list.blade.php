@section('page-title','Prospects')
@section('js','prospect')
<x-app-layout>
    <!-- Main Content -->
    <main class="gmail-content bg-white px-3">
        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-start">
            <div class="align-items-center flex-shrink-0 pb-3">
                <div class="gap-4">
                    <ul class="nav status-tabs align-items-center border-bottom mb-0" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                                <span><i class="bi bi-clock me-1"></i> Prospect -</span>
                                <span class="status-count ms-2" id="allCount">0</span>
                            </button>
                        </li>
                        {{--<li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between active status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle text-success me-1"></i> Confirmed -</span>
                                <span class="status-count ms-2" id="confirmedCount">0</span>
                            </button>
                        </li>--}}
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between pt-3">
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
                            <div class="d-flex justify-content-between align-items-center mb-1">
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
                            </select>

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
                <button class="btn btn-primary rounded-pill px-4" id="new">New Prospect</button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                <div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small" style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button" class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close" onclick="clearDateLabel()">
                        &times;
                    </button>
                </div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search prospects..." aria-label="Search prospects...">
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1 overflow-auto">
                <table class="table align-middle dataTable" id="dataTable" data-title="Prospect">
                    <thead class="table-light sticky-top bg-white">
                    <tr>
                        <th>#</th>
                        <th>Prospect</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Salesperson</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    @include('modules.email.send-email')

    @push('scripts')
        <script>
            // Sidebar collapse toggle
            document.getElementById('toggleSidebar').addEventListener('click', function () {
                document.getElementById('gmailSidebar').classList.toggle('collapsed');
            });
        </script>
    @endpush

</x-app-layout>
