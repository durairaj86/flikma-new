@section('js','monthly_salary')
@section('page-title', __('Monthly Salary'))
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div id="filterPanel" class="card shadow-sm border-0 d-none">
            <!-- Header -->
            <div class="card-header bg-light border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">{{ __('Filters') }}</h6>
                </div>
            </div>

            <div class="card-body">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <!-- Date Range Section -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">{{ __('Employee') }}</label>
                                <select class="tom-select" name="employee_id" id="filter-employee">
                                    <option value="">{{ __('All Employees') }}</option>
                                    @foreach(\App\Models\User::all() as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">{{ __('Month/Year') }}</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <select class="tom-select" name="month" id="filter-month">
                                        <option value="">{{ __('All Months') }}</option>
                                        <option value="1">{{ __('January') }}</option>
                                        <option value="2">{{ __('February') }}</option>
                                        <option value="3">{{ __('March') }}</option>
                                        <option value="4">{{ __('April') }}</option>
                                        <option value="5">{{ __('May') }}</option>
                                        <option value="6">{{ __('June') }}</option>
                                        <option value="7">{{ __('July') }}</option>
                                        <option value="8">{{ __('August') }}</option>
                                        <option value="9">{{ __('September') }}</option>
                                        <option value="10">{{ __('October') }}</option>
                                        <option value="11">{{ __('November') }}</option>
                                        <option value="12">{{ __('December') }}</option>
                                    </select>
                                    <select class="tom-select" name="year" id="filter-year">
                                        <option value="">{{ __('All Years') }}</option>
                                        @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
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
                {{--<h3 class="fw-bold text-muted">Monthly Salary Management</h3>--}}
            </div>
            <div class="d-flex justify-content-between">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                    <button class="btn btn-outline-primary btn-round me-2" id="filter-box"><i class="bi bi-funnel"></i>
                        {{ __('Filter') }}
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Monthly Salary') }}</button>
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
                       data-title="Monthly Salary" data-model-size="md">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Month/Year') }}</th>
                        <th>{{ __('Basic Salary') }}</th>
                        <th>{{ __('Allowances') }}</th>
                        <th>{{ __('Overtime') }}</th>
                        <th>{{ __('Bonus') }}</th>
                        <th>{{ __('Deductions') }}</th>
                        <th>{{ __('Total Salary') }}</th>
                        <th>{{ __('Payment Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    @include('modules.payroll.monthly-salary.monthly-salary-view')
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

    .status-paid {
        background-color: #d1e7dd; /* Light green */
        color: #0f5132; /* Dark green */
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
