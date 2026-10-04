@section('page-title', __('Departments'))
@section('js','department')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-page-title')
            <div class="d-flex justify-content-between pb-3">
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Department') }}</button>
            </div>
            <div class="shadow bdr-r-10 py-3 flex-grow-1">
                <div class="flex-grow-1">
                    <table class="table align-middle dataTable" id="dataTable" data-title="{{ __('Department') }}" data-model-size="md">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Users') }}</th>
                            <th>{{ __('Created Date') }}</th>
                            <th>{{ __('Status') }}</th>
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
