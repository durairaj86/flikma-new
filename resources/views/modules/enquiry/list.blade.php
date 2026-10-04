@section('page-title', __('Enquiries'))
@section('js','enquiry')
@section('extra-js','customer,prospect')
@push('page-title-action')
    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none lh-1"
            data-bs-toggle="modal" data-bs-target="#enquiryWorkflowModal"
            title="{{ __('How enquiries work') }}">
        <i class="bi bi-info-circle fs-6"></i><span class="d-none d-md-inline ms-1" style="font-size:0.8rem;">{{ __('How it works') }}</span>
    </button>
@endpush
<x-app-layout>
    <main class="gmail-content bg-white px-3">

        <div id="filterPanel" class="card shadow-sm border-0 d-none filter-panel-card">

            <!-- Header -->
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="filter-panel-icon"><i class="bi bi-funnel-fill"></i></span>
                    <h6 class="mb-0 fw-semibold">{{ __('Advanced Filters') }}</h6>
                </div>
            </div>

            <div class="card-body pt-3">

                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <!-- Filter Fields -->
                    <div class="row g-4">

                        <div class="col-md-3 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Enquiry Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                       value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                       value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                            </div>
                        </div>

                        <div class="col-md-3 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Customer') }}</label>
                            <x-common.customers multiple></x-common.customers>
                        </div>

                        <div class="col-md-3 form-filter pol-pod-select">
                            <div class="d-flex align-items-center justify-content-between filter-label-row">
                                <label class="form-label fw-medium mb-0">
                                    {{ __('POL') }} <span class="text-muted fw-normal">({{ __('Port of Loading') }})</span>
                                </label>

                                <!-- Sea / Air toggle -->
                                <div class="shipment-toggle">
                                    <input type="radio" class="btn-check sync-sea avoid-filter" name="shipment_mode" id="polSea"
                                           value="sea" checked>
                                    <label for="polSea">{{ __('Sea') }}</label>

                                    <input type="radio" class="btn-check sync-air avoid-filter" name="shipment_mode" id="polAir"
                                           value="air">
                                    <label for="polAir">{{ __('Air') }}</label>
                                </div>
                            </div>

                            <!-- POL -->
                            <select id="filter-pol" name="filter-pol"
                                    class="tom-select-search"
                                    data-placeholder="{{ __('Select Port of Loading') }}">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-3 pol-pod-select">
                            <div class="d-flex align-items-center justify-content-between filter-label-row">
                                <label class="form-label fw-medium mb-0">
                                    {{ __('POD') }} <span class="text-muted fw-normal">({{ __('Port of Discharge') }})</span>
                                </label>

                                <!-- Sea / Air toggle -->
                                <div class="shipment-toggle">
                                    <input type="radio" class="btn-check sync-sea avoid-filter" name="shipment_mode_2" id="polSea2"
                                           checked
                                           value="sea">
                                    <label for="polSea2">{{ __('Sea') }}</label>

                                    <input type="radio" class="btn-check sync-air avoid-filter" name="shipment_mode_2" id="polAir2"
                                           value="air">
                                    <label for="polAir2">{{ __('Air') }}</label>
                                </div>
                            </div>

                            <!-- POD -->
                            <select id="filter-pod" name="filter-pod"
                                    class="tom-select-search"
                                    data-placeholder="{{ __('Select Port of Discharge') }}">
                                <option value=""></option>
                            </select>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4 pt-2 border-top filter-panel-actions">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0">
                <div class="gap-4">
                    <ul class="nav align-items-center" id="listTabs" role="tablist"
                        aria-label="Navigation 13">
                        <li class="nav-item me-2">
                            <button
                                class="nav-link px-3 py-2 d-flex align-items-center justify-content-between active status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="pending">
                                <span><i class="bi bi-clock me-1"></i> {{ __('Pending') }} -</span>
                                <span class="status-count ms-2" id="pendingCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button
                                class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="confirmed">
                                <span><i class="bi bi-check-circle me-1"></i> {{ __('Confirmed') }} -</span>
                                <span class="status-count ms-2" id="confirmedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item me-2">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="completed">
                                <span><i class="bi bi-arrow-repeat me-1"></i> {{ __('Converted to Quotation') }} -</span>
                                <span class="status-count ms-2" id="completedCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-2 d-flex align-items-center justify-content-between status-btn"
                                    data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" id="cancelled">
                                <span><i class="bi bi-x-circle me-1"></i> {{ __('Cancelled / Expired') }} -</span>
                                <span class="status-count ms-2" id="cancelledCount">0</span>
                            </button>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-primary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        {{ __('Filter') }}
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Enquiry') }}</button>
            </div>
        </div>
        <!-- Table Section. min-height guarantees room for a fully-expanded row
             action dropdown even with very few rows — overflow:hidden here
             clips the menu (a DOM descendant of this card) at its bottom edge
             once the menu's rendered height exceeds the card's natural
             content height. See quotation/list.blade.php for the same fix. -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1" style="overflow: hidden;min-height:320px;">
            <!-- Search & New -->
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                {{--<div id="searchLabels" class="mb-3 d-flex flex-wrap gap-2"></div>--}}

                <!-- Example static label -->
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>

                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search enquiries...') }}" aria-label="{{ __('Search enquiries...') }}">
                    </div>
                </div>
            </div>

            <!-- Table with scroll -->
            <div class="flex-grow-1">
                <table class="table align-middle dataTable" id="dataTable"
                       data-title="Enquiry" data-model-size="md" data-min-height="650px">
                    <thead class="table-light sticky-top bg-white" style="z-index: 10;">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('POL') }}</th>
                        <th>{{ __('POD') }}</th>
                        <th>{{ __('Pickup Date') }}</th>
                        {{--<th>Weight(kg)/Volume (m³)</th>--}}
                        <th>{{ __('Expiry Date') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.enquiry.enquiry-view')
    @include('modules.email.send-email')

    <style>
        .wf-box {
            border: 2px solid;
            border-radius: 10px;
            padding: 10px 20px;
            text-align: center;
            min-width: 170px;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.4;
        }
        .wf-box.wf-neutral  { border-color: #6c757d; background: #f8f9fa;  color: #495057; }
        .wf-box.wf-pending  { border-color: #ffc107; background: #fffbf0;  color: #856404; }
        .wf-box.wf-action   { border-color: #0d6efd; background: #f0f7ff;  color: #084298; }
        .wf-box.wf-success  { border-color: #198754; background: #f0fff4;  color: #0f5132; }
        .wf-box.wf-danger   { border-color: #dc3545; background: #fff5f5;  color: #842029; }
        .wf-box.wf-job      { border-color: #0dcaf0; background: #f0fdff;  color: #055160; }
        .wf-box.wf-decision { border-color: #6f42c1; background: #f8f0ff;  color: #432874; }
        .wf-arrow { color: #adb5bd; font-size: 1.3rem; line-height: 1.3; text-align: center; }
        .wf-badge { font-size: 0.7rem; border-radius: 20px; padding: 2px 8px; display: inline-block; margin-top: 4px; }
    </style>

    @include('modules.workflows.enquiry')
</x-app-layout>
