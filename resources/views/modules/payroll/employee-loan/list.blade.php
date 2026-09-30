@section('js','employee_loan')
@section('page-title', __('Employee Loan'))
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div id="filterPanel" class="card shadow-sm border-0 d-none">
            <!-- Header -->
            <div class="card-header bg-light border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">{{ __('Advanced Filters') }}</h6>
                </div>
            </div>

            <div class="card-body">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <!-- Date Range Section -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row g-3 align-items-end">
                            {{--<div class="col-md-2">
                                <label class="form-label fw-medium">Date Range</label>
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

                            <div class="col-md-4 form-filter">
                                <label class="form-label fw-medium">{{ __('Loan Date') }}</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <input type="date" class="form-control datepicker from-date default-filter"
                                           id="filter-from-date" name="filter-from-date"
                                           value="{{ \Carbon\Carbon::today()->subMonth(6)->startOfMonth()->format('d-m-Y') }}">
                                    <input type="date" class="form-control datepicker to-date default-filter"
                                           id="filter-to-date" name="filter-to-date"
                                           value="{{ \Carbon\Carbon::today()->format('d-m-Y') }}">
                                </div>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">{{ __('Employee') }}</label>
                                <select class="tom-select" name="employee_id" id="filter-employee">
                                    <option value="">{{ __('All Employees') }}</option>
                                    @foreach(\App\Models\User::where('status', 'active')->get() as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">{{ __('Status') }}</label>
                                <select class="tom-select" name="status" id="filter-status">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="pending">{{ __('Pending') }}</option>
                                    <option value="approved">{{ __('Approved') }}</option>
                                    <option value="rejected">{{ __('Rejected') }}</option>
                                    <option value="paid">{{ __('Paid') }}</option>
                                    <option value="partially_paid">{{ __('Partially Paid') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-round px-4" type="button" id="apply-filter">
                            <i class="bi bi-search me-1"></i> {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0">
                {{--<h3 class="fw-bold text-muted">Employee Loan Management</h3>--}}
            </div>
            <div class="d-flex justify-content-between">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-primary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        {{ __('Filter') }}
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Employee Loan') }}</button>
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="{{ __('Search...') }}" aria-label="{{ __('Search...') }}">
                    </div>
                </div>
            </div>

            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;"
                       data-title="Employee Loan" data-model-size="md">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Loan Amount') }}</th>
                        <th>{{ __('Installment') }}</th>
                        <th>{{ __('Remaining') }}</th>
                        <th>{{ __('Loan Date') }}</th>
                        <th>{{ __('First Payment') }}</th>
                        <th>{{ __('Payment Method') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.payroll.employee-loan.employee-loan-view')
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

    .status-pending {
        background-color: #fff3cd; /* Light yellow */
        color: #664d03; /* Dark yellow */
    }

    .status-approved {
        background-color: #d1e7dd; /* Light green */
        color: #0f5132; /* Dark green */
    }

    .status-rejected {
        background-color: #f8d7da; /* Light red */
        color: #842029; /* Dark red */
    }

    .status-paid {
        background-color: #d1e7dd; /* Light green */
        color: #0f5132; /* Dark green */
    }

    .status-partially_paid {
        background-color: #cfe2ff; /* Light blue */
        color: #084298; /* Dark blue */
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
