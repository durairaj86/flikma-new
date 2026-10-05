@section('js','supplier')
@section('page-title', __('Suppliers'))
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        @include('includes.inline-page-title')
        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle me-1"></i> {{ __('Active') }} -</span>
                                <span class="status-count ms-2"
                                      id="confirmedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="blocked">
                                <span><i class="bi bi-slash-circle me-1"></i> {{ __('Blocked') }} -</span>
                                <span class="status-count ms-2"
                                      id="blockedCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Supplier') }}</button>
                <button class="btn btn-icon-search rounded-circle ms-2" id="import" title="{{ __('Import') }}" aria-label="{{ __('Import') }}"><i class="bi bi-upload"></i></button>
            </div>
        </div>
        {{-- min-height guarantees room for a fully-expanded row action dropdown
             even with very few rows — overflow:hidden here clips the menu (a
             DOM descendant of this card) at its bottom edge once the menu's
             rendered height exceeds the card's natural content height. --}}
        <div class="shadow bdr-r-10 py-3 flex-grow-1" style="overflow: hidden;min-height:320px;">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                <div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small"
                     style="font-size: 0.8rem;">
                    <span class="me-2">{{ __('Date') }}: 10-12-2024 / 10-12-2025</span>
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
                               placeholder="{{ __('Search suppliers...') }}" aria-label="{{ __('Search suppliers...') }}">
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1 <!--overflow-auto-->">
                <table class="table align-middle dataTable" id="dataTable" data-title="Supplier" data-model-size="md" data-min-height="min-height:51vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Location') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th>{{ __('VAT #') }}</th>
                        <th>{{ __('Credit') }}</th>
                        <th>{{ __('Due') }}</th>
                        <th>{{ __('Joined') }}</th>
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
