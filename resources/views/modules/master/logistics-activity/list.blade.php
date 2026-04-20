@section('js','logistics_activity')
@section('page-title','Logistic Activities')
<x-app-layout>
    <main class="gmail-content bg-white d-flex" style="min-height: 100vh;">
        @include('includes.master-navigation')
        <!-- RIGHT CONTENT -->
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')
            <!-- Top toolbar -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="align-items-center flex-shrink-0">
                    <div class="search-box position-relative me-3">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary rounded-pill px-4" id="new">New Activity</button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="flex-grow-1">
                <table class="table align-middle mb-0" id="dataTable">
                    <thead class="table-light sticky-top bg-white">
                    <tr>
                        <th>#</th>
                        <th>Activity</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Service</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </main>
</x-app-layout>
