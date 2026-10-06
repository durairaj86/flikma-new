@section('js','supplier')
@section('page-title', __('Suppliers'))
@section('hide-topbar', true)
<x-app-layout>
    <style>
        /* Inactive status tabs share the Pending tab's look; the active tab keeps its own colour with a white icon. */
        #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
        #listTabs .status-btn[id="confirmed"]:not(.active),
        #listTabs .status-btn[id="blocked"]:not(.active),
        #listTabs .status-btn[id="overdue"]:not(.active) { background: #f1f3f5; color: #495057; }
        #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
    </style>
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
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="overdue">
                                <span><i class="bi bi-exclamation-triangle me-1"></i> {{ __('Overdue') }} -</span>
                                <span class="status-count ms-2"
                                      id="overdueCount">0</span>
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
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <!-- Search & New -->
            <div class="d-flex justify-content-between align-items-center px-3 pt-1 pb-1 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <div class="d-flex flex-wrap align-items-center" id="supplierFilterChips"></div>
                <div class="d-flex align-items-center gap-2">
                    <div class="search-box position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search suppliers...') }}" aria-label="{{ __('Search suppliers...') }}">
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-icon-search rounded-circle position-relative" type="button" id="supplierFilterBtn"
                                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                                title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}">
                            <i class="bi bi-funnel"></i>
                            <span class="badge bg-primary rounded-pill d-none position-absolute top-0 start-100 translate-middle" id="supplierFilterBadge">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-3 shadow" id="supplierFilterForm" style="width:320px;">
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">{{ __('Joined Date') }}</label>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control datepicker" id="filter-joined-from" data-min-date="01-01-2000" data-max-date="31-12-2099" aria-label="{{ __('From') }}">
                                    <input type="text" class="form-control datepicker" id="filter-joined-to" data-min-date="01-01-2000" data-max-date="31-12-2099" aria-label="{{ __('To') }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-light" id="supplier-filter-clear">{{ __('Clear') }}</button>
                                <button type="button" class="btn btn-sm btn-primary" id="supplier-filter-apply">{{ __('Apply') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1 mt-2">
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
