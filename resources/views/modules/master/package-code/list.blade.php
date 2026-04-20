@section('js','package_code')
@section('page-title','Package Codes')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="align-items-center flex-shrink-0">
                    <div class="search-box position-relative me-3">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary rounded-pill px-4" id="new">New Package Code</button>
                </div>
            </div>
            <div class="shadow bdr-r-10 py-3 flex-grow-1">
                <!-- Table with scroll -->
                <div class="flex-grow-1 overflow-auto">
                    <table class="table align-middle dataTable" id="dataTable">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
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
