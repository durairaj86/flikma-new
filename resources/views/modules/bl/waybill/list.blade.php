@section('js','waybill')
@section('hide-topbar', true)
@section('page-title', __('Waybill'))
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .bl-way-title { display: none; }
            body:not(.has-top-header) .bl-way-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 bl-way-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new" data-loader-id="{{ $job_id ?? 'list' }}">{{ __('New Waybill') }}</button>
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
                            <label class="form-label fw-medium filter-label-row">{{ __('Waybill Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                       value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Customer') }}</label>
                            <x-common.customers multiple></x-common.customers>
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
                @if(isset($job_no))
                    <h3 class="fw-bold text-muted bg-info-subtle rounded p-3">
                        {{ $job_no }}
                    </h3>
                @endif

                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist" aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button class="nav-link px-3 py-2 d-flex align-items-center active justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="all">
                                <span><i class="bi bi-collection me-1"></i> {{ __('All') }} -</span>
                                <span class="status-count ms-2" id="allCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                                <span><i class="bi bi-clock me-1"></i> {{ __('Pending') }} -</span>
                                <span class="status-count ms-2" id="pendingCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="in_transit">
                                <span><i class="bi bi-truck me-1"></i> {{ __('In Transit') }} -</span>
                                <span class="status-count ms-2" id="in_transitCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="delivered">
                                <span><i class="bi bi-check-circle me-1"></i> {{ __('Delivered') }} -</span>
                                <span class="status-count ms-2" id="deliveredCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                           placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                </div>
                <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button"
                        title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
            </div>

            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Waybill" data-model-size="lg">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>{{ __('Waybill No') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Job No') }}</th>
                        <th>{{ __('POL') }} <i class="bi bi-arrow-right"></i> {{ __('POD') }}</th>
                        <th>{{ __('Delivery Address') }}</th>
                        <th>{{ __('Delivery Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Waybill Date') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.common.view-drawer', ['title' => __('Waybill Details'), 'width' => 60])
    @include('modules.common.linked-drawer', ['mainWidth' => 60, 'subWidth' => 35])
</x-app-layout>
<style>
    /* Typography and Alignment */
    .main-text {
        font-weight: 600;
        color: #212529;
    }

    .sub-text {
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* Status Pills */
    .status-pill {
        padding: 3px 8px;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.65rem;
        margin-bottom: 2px;
    }

    .status-delivered {
        background-color: #d1e7dd; /* Light green */
        color: #0f5132; /* Dark green */
    }

    .status-in-transit {
        background-color: #cfe2ff; /* Light blue */
        color: #084298; /* Dark blue */
    }

    .status-pending {
        background-color: #fff3cd; /* Light yellow */
        color: #664d03; /* Dark yellow */
    }

    .status-cancelled {
        background-color: #f8d7da; /* Light red */
        color: #842029; /* Dark red */
    }

    /* Action Button Styling */
    .btn-action {
        width: 30px;
        height: 30px;
        padding: 0;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
