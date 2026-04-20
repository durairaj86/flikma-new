@section('js','monthly_salary')
@section('page-title','Monthly Salary')
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div id="filterPanel" class="card shadow-sm border-0 d-none">
            <!-- Header -->
            <div class="card-header bg-light border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel-fill text-primary"></i>
                    <h6 class="mb-0 fw-semibold">Advanced Filters</h6>
                </div>
            </div>

            <div class="card-body">
                <form id="list-filter" method="post" novalidate="novalidate">
                    @csrf
                    <!-- Date Range Section -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Employee</label>
                                <select class="tom-select" name="employee_id" id="filter-employee">
                                    <option value="">All Employees</option>
                                    @foreach(\App\Models\User::all() as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 form-filter">
                                <label class="form-label fw-medium">Month/Year</label>
                                <div class="d-flex input-group-filter gap-2">
                                    <select class="tom-select" name="month" id="filter-month">
                                        <option value="">All Months</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <select class="tom-select" name="year" id="filter-year">
                                        <option value="">All Years</option>
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
                            <i class="bi bi-search me-1"></i> Search
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
                        Filter
                    </button>
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Monthly Salary</button>
            </div>
        </div>

        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0">
                <div id="filtered-data"></div>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
            </div>

            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;"
                       data-title="Monthly Salary" data-model-size="md">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>Employee</th>
                        <th>Month/Year</th>
                        <th>Basic Salary</th>
                        <th>Allowances</th>
                        <th>Overtime</th>
                        <th>Bonus</th>
                        <th>Deductions</th>
                        <th>Total Salary</th>
                        <th>Payment Date</th>
                        <th>Status</th>
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
