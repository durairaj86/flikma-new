@section('page-title', __('Assets'))
@section('js','asset')
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div class="d-flex justify-content-between align-items-start">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4 py-2">
                    <h5 class="mb-0">{{ __('All Assets') }}</h5>
                </div>
            </div>
            <div class="d-flex justify-content-between pt-3">
                <div class="position-relative">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                    </div>
                </div>
                <button class="btn btn-primary rounded-pill px-4 ms-2" id="new">{{ __('New Asset') }}</button>
            </div>
        </div>
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable" data-module-url="asset" data-title="asset" data-model-size="md"
                       data-min-height="min-height:75vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>{{ __('Asset No') }}</th>
                        <th>{{ __('Asset Name') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Acquisition Date') }}</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Invoice No') }}</th>
                        <th>{{ __('Invoice Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Cost') }}</th>
                        <th class="text-end">{{ __('Accumulated') }}</th>
                        <th class="text-end">{{ __('Book Value') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        @include('modules.finance.asset.asset-view-drawer')
</main>
</x-app-layout>
