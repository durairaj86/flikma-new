@section('js','basic_salary')
@section('hide-topbar', true)
@section('page-title', __('Basic Salary'))
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div class="d-flex align-items-center gap-2 bsal-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Basic Salary') }}</button>
            </div>
        </div>
        <style>
            .bsal-title { display: none; }
            body:not(.has-top-header) .bsal-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
        </style>

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
                            {{--<div class="col-md-2">
                                <label class="form-label fw-medium filter-label-row">Date Range</label>
                                <select class="tom-select avoid-filter" id="presetDateRange">
                                    <option value="">Custom</option>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="thisMonth">This Month</option>
                                    <option value="lastMonth">Last Month</option>
                                    <option value="thisQuarter">This Quarter</option>
                                    <option value="lastQuarter">Last Quarter</option>
                                    <option value="thisYear">This Year</option>
                                    <option value="lastYear">Last Year</option>
                                </select>
                            </div>--}}

                            <div class="col-md-6 col-xl-4 form-filter">
                                <label class="form-label fw-medium filter-label-row">{{ __('Effective Date') }}</label>
                                <div class="filter-date-range">
<input type="date" class="form-control datepicker from-date default-filter" id="filter-from-date" name="filter-from-date"
                                           value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
<i class="bi bi-arrow-right filter-date-range-arrow"></i>
<input type="date" class="form-control datepicker to-date default-filter" id="filter-to-date" name="filter-to-date"
                                           value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
</div>
                            </div>

                            <div class="col-md-6 col-xl-4 form-filter">
                                <label class="form-label fw-medium filter-label-row">{{ __('Employee') }}</label>
                                <select class="tom-select" name="employee_id" id="filter-employee">
                                    <option value="">{{ __('All Employees') }}</option>
                                    @foreach(\App\Models\User::all() as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
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

        <div class="d-flex justify-content-end align-items-center gap-2 py-3">
            <div class="search-box position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                       placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
            </div>
            <button class="btn btn-icon-search rounded-circle" id="filter-box" type="button"
                    title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}"><i class="bi bi-funnel"></i></button>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
            </div>

            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Basic Salary" data-model-size="lg">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Basic Salary') }}</th>
                        <th>{{ __('Housing Allowance') }}</th>
                        <th>{{ __('Transportation Allowance') }}</th>
                        <th>{{ __('Food Allowance') }}</th>
                        <th>{{ __('Phone Allowance') }}</th>
                        <th>{{ __('Other Allowance') }}</th>
                        <th>{{ __('Total Salary') }}</th>
                        <th>{{ __('Effective Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.payroll.basic-salary.basic-salary-view')
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

    .status-active {
        background-color: #d1e7dd; /* Light green */
        color: #0f5132; /* Dark green */
    }

    .status-inactive {
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
