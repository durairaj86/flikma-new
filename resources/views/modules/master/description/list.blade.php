@section('page-title', __('Descriptions'))
@section('js','description')
<x-app-layout>
    <!-- Main Content -->
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-page-title')
            <div class="d-flex justify-content-between pb-3">
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <!-- Search icon -->
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Description') }}</button>
            </div>

            <!-- Table Section -->
            <div class="shadow bdr-r-10 pt-0 pb-3 flex-grow-1 overflow-hidden">
                <!-- Table with scroll -->
                <div class="flex-grow-1 overflow-auto" style="min-height:320px;">
                    <table class="table align-middle dataTable" id="dataTable" data-title="{{ __('Description') }}" data-min-height="min-height:75vh;" data-model-size="md">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Description') }} <small>{{ __('(In English)') }}</small></th>
                            <th>{{ __('Description') }} <small>{{ __('(In Arabic)') }}</small></th>
                            <th>{{ __('Sale Account') }}</th>
                            <th>{{ __('Purchase Account') }}</th>
                            <th>{{ __('Created Date') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</x-app-layout>
