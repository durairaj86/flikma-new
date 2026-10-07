@section('js','attendance')
@section('hide-topbar', true)
@section('page-title', __('Attendance'))
<x-app-layout>
    <main class="gmail-content bg-white px-3">


        <style>
            .att-title { display: none; }
            body:not(.has-top-header) .att-title { display: block; }
            #dataTable thead th { background-color: #f8f9fa !important; }
            #dataTable thead th:first-child { border-top-left-radius: 8px; }
            #dataTable thead th:last-child { border-top-right-radius: 8px; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-3">
            <div class="d-flex align-items-center gap-2 att-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @stack('page-title-action')
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="btn btn-primary rounded-pill px-4" id="new">{{ __('New Attendance Record') }}</button>
            </div>
        </div>

        <!-- Calendar View -->
        @livewire('payroll.attendance-calendar', ['month' => $month, 'year' => $year])

        <!-- List View -->
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
                            <label class="form-label fw-medium filter-label-row">{{ __('Date') }}</label>
                            <div class="filter-date-range">
                                <input type="date" class="form-control datepicker from-date" id="filter-from-date" name="filter-from-date">
                                <i class="bi bi-arrow-right filter-date-range-arrow"></i>
                                <input type="date" class="form-control datepicker to-date" id="filter-to-date" name="filter-to-date">
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Employee') }}</label>
                            <select class="tom-select" name="employee_id" id="list-filter-employee">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach(\App\Models\User::all() as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-4 form-filter">
                            <label class="form-label fw-medium filter-label-row">{{ __('Status') }}</label>
                            <select class="tom-select" name="status" id="list-filter-status">
                                <option value="">{{ __('All Statuses') }}</option>
                                @foreach(\App\Models\Payroll\Attendance::getStatusOptions() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
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

        <div class="shadow bdr-r-10 py-3 flex-grow-1 mt-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 flex-shrink-0 mb-2">
                <h5 class="fw-bold mb-0">{{ __('Attendance Records') }}</h5>
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
            <div id="filtered-data" class="px-3"></div>

            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Attendance" data-model-size="md">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Day') }}</th>
                        <th>{{ __('Check In') }}</th>
                        <th>{{ __('Check Out') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Remarks') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
    <style>
        /* Calendar Styling */
        .attendance-calendar {
            border-collapse: collapse;
        }

        .attendance-calendar th,
        .attendance-calendar td {
            text-align: center;
            padding: 8px;
            font-size: 0.9rem;
        }

        .attendance-calendar th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .attendance-calendar .day-header {
            min-width: 40px;
        }

        .attendance-calendar .weekend {
            background-color: #f8f9fa;
        }

        .attendance-calendar .employee-name {
            text-align: left;
            font-weight: 600;
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 5;
        }

        /* Status Colors */
        .status-present {
            background-color: #d1e7dd;
            color: #0f5132;
            border-radius: 4px;
        }

        .status-absent {
            background-color: #f8d7da;
            color: #842029;
            border-radius: 4px;
        }

        .status-late {
            background-color: #fff3cd;
            color: #664d03;
            border-radius: 4px;
        }

        .status-half-day {
            background-color: #cff4fc;
            color: #055160;
            border-radius: 4px;
        }

        .status-leave {
            background-color: #e2e3e5;
            color: #41464b;
            border-radius: 4px;
        }

        .attendance-cell {
            cursor: pointer;
            transition: all 0.2s;
        }

        .attendance-cell:hover {
            transform: scale(1.05);
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
    </style>
</x-app-layout>
