@section('js','supplier')
@section('page-title','Suppliers')
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-start">
            <div class="align-items-center flex-shrink-0 pb-3">
                <div class="gap-4">
                    <ul class="nav status-tabs align-items-center border-bottom mb-0" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle me-1"></i> Active -</span>
                                <span class="status-count ms-2"
                                      id="confirmedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="blocked">
                                <span><i class="bi bi-slash-circle me-1"></i> Blocked -</span>
                                <span class="status-count ms-2"
                                      id="blockedCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between pt-3">
                <button class="btn btn-primary rounded-pill px-4" id="new">New Supplier</button>
                <button class="btn btn-outline-primary rounded-pill px-4 ms-2" id="import">Import</button>
            </div>
        </div>
        <div class="shadow bdr-r-10 py-3 flex-grow-1" style="overflow: hidden;">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
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
                               placeholder="Search suppliers..." aria-label="Search suppliers...">
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1 <!--overflow-auto-->">
                <table class="table align-middle dataTable" id="dataTable" data-title="Supplier" data-model-size="md" data-min-height="min-height:51vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Currency</th>
                        <th>VAT #</th>
                        <th>Credit</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            <!--end::Row-->
        </div>
    </main>
    @include('modules.supplier.supplier-view')
    @include('modules.supplier.supplier-import')
</x-app-layout>
