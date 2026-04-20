@section('js','customer')
@section('page-title','Customer Directory')
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
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                                <span><i class="bi bi-clock text-warning me-1"></i> Pending -</span>
                                <span class="status-count ms-2" id="pendingCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between active status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle text-success me-1"></i> Confirmed -</span>
                                <span class="status-count ms-2" id="confirmedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="blocked">
                                <span><i class="bi bi-slash-circle text-secondary me-1"></i> Blocked -</span>
                                <span class="status-count ms-2" id="blockedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="rejected">
                                <span><i class="bi bi-x-circle text-danger me-1"></i> Rejected -</span>
                                <span class="status-count ms-2" id="rejectedCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between pt-3">
                <div>
                    <button class="btn btn-primary rounded-pill px-4" id="new">New Customer</button>
                    <button class="btn btn-outline-primary rounded-pill px-4 ms-2" id="import">Import</button>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 ">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                {{--<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small" style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button" class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close" onclick="clearDateLabel()">
                        &times;
                    </button>
                </div>--}}
                <div></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search customers..." aria-label="Search customers...">
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            {{--<div class="flex-grow-1 <!--overflow-auto-->">
                <table class="table align-middle dataTable" id="dataTable" data-title="Customer" data-model-size="md" data-min-height="min-height:51vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Currency</th>
                        <th>VAT #</th>
                        <th>Credit</th>
                        <th>Salesperson</th>
                        <th>Joined</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>--}}
            <div class="mt-2">
                {{--<div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-0 fw-bold">Customer Directory</h6>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                </div>--}}

                    <table class="table align-middle custom-table mb-0" id="dataTable" data-title="Customer"
                           data-model-size="md" data-min-height="min-height:51vh;">
                        <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Customer</th>
                            <th>Contact Info</th>
                            <th>Location</th>
                            <th>Currency</th>
                            <th>VAT #</th>
                            <th>Credit Limit</th>
                            <th>Salesperson</th>
                            <th>Joined</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

            </div>
        </div>
    </main>

    @include('modules.customer.customer-view')
    @include('modules.email.send-email')
    @include('modules.customer.customer-import')

    @push('scripts')
        <script>
            // Sidebar collapse toggle
            document.getElementById('toggleSidebar').addEventListener('click', function () {
                document.getElementById('gmailSidebar').classList.toggle('collapsed');
            });
        </script>
    @endpush

</x-app-layout>
