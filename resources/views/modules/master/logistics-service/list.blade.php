@section('js','logistics_service')
@section('page-title', __('Logistic Services'))
<x-app-layout>
    <main class="gmail-content bg-white d-flex" style="min-height: 100vh;">
        @include('includes.master-navigation')
        <!-- RIGHT CONTENT -->
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-page-title')
            <!-- Top toolbar -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="align-items-center flex-shrink-0">
                    <div class="search-box position-relative me-3">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Service') }}</button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="card flex-grow-1 shadow-sm border-0">
                <div class="table-responsive" style="max-height: calc(100vh - 180px);">
                    <table class="table align-middle mb-0" id="dataTable" data-model-size="md">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Service (In English)') }}</th>
                            <th>{{ __('Service (In Arabic)') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</x-app-layout>
