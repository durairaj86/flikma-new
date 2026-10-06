@section('page-title', __('Prospects'))
@section('js','prospect')
@section('hide-topbar', true)
<x-app-layout>
    <!-- Main Content -->
    <style>
        /* Selected tab uses the same blue as the Customers "Active" tab, with a white icon. */
        #listTabs .status-btn[id="all"].active { background: rgb(13, 110, 253) !important; color: #fff !important; }
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
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between active status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                                <span><i class="bi bi-person-lines-fill me-1"></i> {{ __('Prospect') }} -</span>
                                <span class="status-count ms-2" id="allCount">0</span>
                            </button>
                        </li>
                        {{--<li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between active status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle text-success me-1"></i> Confirmed -</span>
                                <span class="status-count ms-2" id="confirmedCount">0</span>
                            </button>
                        </li>--}}
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Prospect') }}</button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <div class="d-flex flex-wrap align-items-center" id="prospectFilterChips"></div>
                <div class="d-flex align-items-center gap-2">
                    <div class="search-box position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search prospects...') }}" aria-label="{{ __('Search prospects...') }}">
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-icon-search rounded-circle position-relative" type="button" id="prospectFilterBtn"
                                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                                title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}">
                            <i class="bi bi-funnel"></i>
                            <span class="badge bg-primary rounded-pill d-none position-absolute top-0 start-100 translate-middle" id="prospectFilterBadge">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-3 shadow" id="prospectFilterForm" style="width:320px;">
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
                                <label class="form-label small text-muted mb-1">{{ __('Created Date') }}</label>
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control datepicker" id="filter-joined-from" data-min-date="01-01-2000" data-max-date="31-12-2099" aria-label="{{ __('From') }}">
                                    <input type="text" class="form-control datepicker" id="filter-joined-to" data-min-date="01-01-2000" data-max-date="31-12-2099" aria-label="{{ __('To') }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-light" id="prospect-filter-clear">{{ __('Clear') }}</button>
                                <button type="button" class="btn btn-sm btn-primary" id="prospect-filter-apply">{{ __('Apply') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1 overflow-auto" style="min-height:320px;">
                <table class="table align-middle dataTable" id="dataTable" data-title="Prospect">
                    <thead class="table-light sticky-top bg-white">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Prospect') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Salesperson') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>

    @include('modules.email.send-email')

    @push('scripts')
        <script>
            // Sidebar collapse toggle
            document.getElementById('toggleSidebar').addEventListener('click', function () {
                document.getElementById('gmailSidebar').classList.toggle('collapsed');
            });
        </script>
    @endpush

</x-app-layout>
