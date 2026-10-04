@section('page-title', __('Banks'))
@section('js','bank')
<x-app-layout>
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
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Bank') }}</button>
            </div>
            <div class="shadow bdr-r-10 py-3 flex-grow-1">
                <!-- Table with scroll -->
                <div class="flex-grow-1">
                    <table class="table align-middle dataTable" id="dataTable" data-model-size="md">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>{{ __('Bank') }}</th>
                            <th>{{ __('Account No') }}</th>
                            <th>{{ __('Account Holder') }}</th>
                            <th>{{ __('Currency') }}</th>
                            <th>{{ __('Iban Code') }}</th>
                            <th>{{ __('Swift Code') }}</th>
                            <th>{{ __('Address') }}</th>
                            <th class="text-end">{{ __('Display Order') }}</th>
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
    @include('modules.master.bank.bank-view')
</x-app-layout>
