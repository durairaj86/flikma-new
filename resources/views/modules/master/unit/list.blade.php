@section('page-title','Unit')
@section('js','unit')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')
            <div class="d-flex justify-content-between pb-3">
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <!-- Search icon -->
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Unit</button>
            </div>
            <div class="shadow bdr-r-10 py-3 flex-grow-1">
                <!-- Table with scroll -->
                <div class="flex-grow-1">
                    <table class="table align-middle dataTable" id="dataTable">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Unit Name</th>
                            <th>Unit Symbol</th>
                            <th>Created Date</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</x-app-layout>
