@section('js','seaport')
@section('page-title','Seaports')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav status-tabs align-items-center border-bottom m-0">
                        <li class="nav-item me-2">
                            <a href="{{ asset('masters/transport/directories/seaports') }}"
                               class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                               data-turbo="false">
                                <span><i class="bi bi-geo-alt me-1"></i> Seaport</span>
                            </a>
                        </li>
                        <li class="nav-item me-2">
                            <a href="{{ asset('masters/transport/directories/airports') }}"
                               class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                               data-turbo="false">
                                <span><i class="bi bi-airplane me-1"></i> Airport</span>
                            </a>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button">
                                <span><i class="fas fa-ship me-1"></i> Shipping Lines</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button">
                                <span><i class="fas fa-plane-departure me-1"></i> Air Lines</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between py-3">
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <!-- Search icon -->
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search customers..." aria-label="Search customers...">
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Seaport</button>
            </div>
            <div class="shadow bdr-r-10 py-3 flex-grow-1">
                <!-- Table with scroll -->
                <div class="flex-grow-1 overflow-auto">
                    <table class="table align-middle dataTable" id="dataTable">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Port Name</th>
                            <th>Port Code</th>
                            <th>Country Name</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!--end::Row-->
            </div>
        </section>
    </main>
</x-app-layout>
