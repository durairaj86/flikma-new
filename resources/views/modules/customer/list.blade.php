@section('js','customer')
@section('page-title', __('Customer Directory'))
@section('hide-topbar', true)
<x-app-layout>
    <!-- Main Content -->
    <main class="gmail-content bg-white px-3">
        <style>
            /* Inactive status tabs all use the Pending tab's colour; the active tab keeps its own. */
            #listTabs .status-btn:not(.active) > span:first-child > i { color: var(--bs-warning) !important; }
            /* Active tab: icon turns white so it stays visible on the filled background. */
            #listTabs .status-btn.active > span:first-child > i { color: #fff !important; }
            /* Selected "Active" tab uses blue instead of the default green. */
            #listTabs .status-btn[id="confirmed"].active { background: rgb(13, 110, 253) !important; color: #fff !important; }
            /* Active / Blocked / Overdue look like the inactive Pending tab (same background and text). */
            #listTabs .status-btn[id="confirmed"]:not(.active),
            #listTabs .status-btn[id="blocked"]:not(.active),
            #listTabs .status-btn[id="overdue"]:not(.active) { background: #f1f3f5; color: #495057; }
        </style>
        <style>
        /* Table header sits flush at the top of the card; the chips row only takes space when filters are applied. */
        .cust-chips-row:has(#customerFilterChips:empty) { display: none !important; }
        #dataTable thead th:first-child { border-top-left-radius: 10px; }
        #dataTable thead th:last-child { border-top-right-radius: 10px; }
            .cust-title { display: none; }
            body:not(.has-top-header) .cust-title { display: block; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <h4 class="fw-bold text-dark mb-0 cust-title">@yield('page-title')</h4>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Customer') }}</button>
                <button class="btn btn-icon-search rounded-circle" id="import" title="{{ __('Import') }}" aria-label="{{ __('Import') }}"><i class="bi bi-upload"></i></button>
            </div>
        </div>
        <!-- Tabs -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between active status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle text-success me-1"></i> {{ __('Active') }} -</span>
                                <span class="status-count ms-2" id="confirmedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="blocked">
                                <span><i class="bi bi-slash-circle text-secondary me-1"></i> {{ __('Blocked') }} -</span>
                                <span class="status-count ms-2" id="blockedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="overdue">
                                <span><i class="bi bi-exclamation-triangle text-warning me-1"></i> {{ __('Overdue') }} -</span>
                                <span class="status-count ms-2" id="overdueCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="search-box position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search customers...') }}" aria-label="{{ __('Search customers...') }}">
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-icon-search rounded-circle position-relative" title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}" type="button" id="customerFilterBtn"
                                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <i class="bi bi-funnel"></i>
                            <span class="badge bg-primary rounded-pill d-none position-absolute top-0 start-100 translate-middle" id="customerFilterBadge">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-3 shadow" id="customerFilterForm" style="width:320px;">
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-1">{{ __('Salesman') }}</label>
                                <select class="tom-select avoid-filter" id="filter-salesman" placeholder="{{ __('All Salesmen') }}">
                                    <option value="">{{ __('All Salesmen') }}</option>
                                    @foreach(\App\Models\Master\Salesperson\Salesperson::orderBy('name')->get(['id', 'name']) as $sp)
                                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">{{ __('Joined Date') }}</label>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control datepicker" id="filter-joined-from" data-min-date="01-01-2000" data-max-date="31-12-2099" aria-label="{{ __('From') }}">
                                    <input type="text" class="form-control datepicker" id="filter-joined-to" data-min-date="01-01-2000" data-max-date="31-12-2099" aria-label="{{ __('To') }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-light" id="customer-filter-clear">{{ __('Clear') }}</button>
                                <button type="button" class="btn btn-sm btn-primary" id="customer-filter-apply">{{ __('Apply') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <!-- Table Section -->
        <div class="shadow bdr-r-10 pb-3">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 pt-3 flex-shrink-0 cust-chips-row">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                {{--<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 me-2 mb-2 small" style="font-size: 0.8rem;">
                    <span class="me-2">Date: 10-12-2024 / 10-12-2025</span>
                    <button type="button" class="btn btn-sm btn-light p-0 border-0 d-flex align-items-center justify-content-center"
                            style="width: 16px; height: 16px; line-height: 1;" aria-label="Close" onclick="clearDateLabel()">
                        &times;
                    </button>
                </div>--}}
                <div class="d-flex flex-wrap align-items-center" id="customerFilterChips"></div>
            </div>

            <!-- Table with scroll -->
            {{--<div class="flex-grow-1 <!--overflow-auto-->">
                <table class="table align-middle dataTable" id="dataTable" data-title="Customer" data-model-size="md" data-min-height="min-height:51vh;">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Currency</th>
                        <th>VAT #</th>
                        <th>Credit</th>
                        <th>Salesperson</th>
                        <th>Joined</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>--}}
            <div>
                {{--<div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-0 fw-bold">Customer Directory</h6>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                </div>--}}

                    <table class="table align-middle custom-table mb-0" id="dataTable" data-title="Customer"
                           data-model-size="md" data-min-height="min-height:51vh;">
                        <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Contact Info') }}</th>
                            <th>{{ __('Location') }}</th>
                            <th>{{ __('Currency') }}</th>
                            <th>{{ __('VAT #') }}</th>
                            <th>{{ __('Credit Limit') }}</th>
                            <th>{{ __('Due') }}</th>
                            <th>{{ __('Salesperson') }}</th>
                            <th>{{ __('Joined') }}</th>
                            <th class="text-center pe-4">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

            </div>
        </div>
    </main>

    @include('modules.customer.customer-view')
    @include('modules.email.send-email')
    @include('modules.customer.customer-import')

    @push('scripts')
        <script>
            // Sidebar collapse toggle
            document.getElementById('toggleSidebar').addEventListener('click', function () {
                document.getElementById('gmailSidebar').classList.toggle('collapsed');
            });
        </script>
    @endpush

</x-app-layout>
