@section('js','item')
@section('hide-topbar', true)
@section('page-title', __('Items'))
<x-app-layout>
    <!-- Main Content -->
    <main class="gmail-content bg-white px-3">
        <style>
            .itm-title { display: none; }
            body:not(.has-top-header) .itm-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 itm-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Item') }}</button>
            </div>
        </div>

        <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card mt-3">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="filter-panel-icon"><i class="bi bi-funnel-fill"></i></span>
                    <h6 class="mb-0 fw-semibold">{{ __('Filters') }}</h6>
                </div>
            </div>
            <div class="card-body pt-3">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Created Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date" id="filter-from-date" name="filter-from-date">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date" id="filter-to-date" name="filter-to-date">
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Account Type') }}</label>
                            <select class="tom-select" name="account_type" id="filter-account-type">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="expense">{{ __('Expense') }}</option>
                                <option value="income">{{ __('Income') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-4 pt-2 border-top filter-panel-actions">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                                <span><i class="bi bi-grid text-primary me-1"></i> {{ __('All Items') }} -</span>
                                <span class="status-count ms-2" id="allCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search items...') }}" aria-label="{{ __('Search items...') }}">
                </div>
                <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button"
                        title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div id="filtered-data" class="px-3"></div>

            <!-- Table with scroll -->
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable" data-title="Item">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>#</th>
                        <th>{{ __('SKU Code') }}</th>
                        <th>{{ __('Name (EN)') }}</th>
                        <th>{{ __('Name (AR)') }}</th>
                        <th>{{ __('Account Type') }}</th>
                        <th>{{ __('Cost Price') }}</th>
                        <th>{{ __('Selling Price') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    @include('modules.inventory.item.item-view')

    @push('scripts')
        <script>
            // Sidebar collapse toggle
            document.getElementById('toggleSidebar')?.addEventListener('click', function () {
                document.getElementById('gmailSidebar').classList.toggle('collapsed');
            });
        </script>
    @endpush

</x-app-layout>
