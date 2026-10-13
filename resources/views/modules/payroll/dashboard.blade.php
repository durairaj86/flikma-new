@section('page-title', __('Payroll'))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

    <div class="container-fluid px-2 pt-4 pt-md-0">
        <div class="mb-4">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h1 class="h4 fw-bold text-dark mb-0"><i class="bi bi-cash-coin me-2 d-none d-md-inline-block"></i>{{ __('Payroll') }}</h1>
                <button class="btn btn-link btn-sm p-0 text-decoration-none lh-1" id="payrollWatchVideo" data-bs-toggle="modal" data-bs-target="#payrollVideoModal" title="{{ __('Watch video') }}">
                    <i class="bi bi-play-circle-fill fs-6"></i><span class="ms-1" style="font-size:0.8rem;">{{ __('Watch video') }}</span>
                </button>
            </div>
            <p class="text-muted small mb-0">{{ __('Employees, attendance, shifts and salary structures.') }}</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold">{{ __('Employees') }}</div>
                        <div class="h3 fw-bold mb-0">{{ $employeeCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold">{{ __('Present Today') }}</div>
                        <div class="h3 fw-bold mb-0 text-success">{{ $attendanceToday->get('present', 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold">{{ __('Active Shifts') }}</div>
                        <div class="h3 fw-bold mb-0">{{ $shiftCount }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold">{{ __('Missing Salary Setup') }}</div>
                        <div class="h3 fw-bold mb-0 {{ $noSalaryStructureCount > 0 ? 'text-warning' : '' }}">{{ $noSalaryStructureCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($noShiftCount > 0)
            <div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ trans_choice(':count employee has no shift assigned.|:count employees have no shift assigned.', $noShiftCount, ['count' => $noShiftCount]) }}</span>
                <a href="{{ route('payroll.employees.index') }}" class="btn btn-sm btn-outline-dark">{{ __('Review') }}</a>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-md-4">
                <a href="{{ route('payroll.attendance.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-calendar-check text-primary fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Attendance') }}</div>
                                <div class="text-muted small">{{ __('Mark and review daily attendance') }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('payroll.punch-log') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-info-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-fingerprint text-info fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Punch Log') }}</div>
                                <div class="text-muted small">{{ __('Machine punches, manual entry and Excel import') }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('payroll.shifts.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-info-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-clock-history text-info fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Shifts') }}</div>
                                <div class="text-muted small">{{ __('Manage shift schedules & grace time') }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('payroll.salary-structures.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-success-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-cash-stack text-success fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Salary Structures') }}</div>
                                <div class="text-muted small">{{ __('Basic pay, allowances & effective dates') }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('payroll.employees.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-warning-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-person-badge text-warning-emphasis fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Employee Profiles') }}</div>
                                <div class="text-muted small">{{ __('Shift, bank details, GOSI & manager') }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('payroll.runs.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-danger-subtle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-cash-coin text-danger fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ __('Payroll Runs') }}</div>
                                <div class="text-muted small">{{ __('Generate and pay monthly salaries') }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <style>
        .hover-lift { transition: transform .15s ease, box-shadow .15s ease; }
        .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important; }
    </style>
</div>
@include('modules.payroll._video')
</x-app-layout>
