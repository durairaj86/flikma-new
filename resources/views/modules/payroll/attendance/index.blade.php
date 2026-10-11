@section('page-title', __('Attendance'))
@section('hide-topbar', true)
@section('js', 'payroll_attendance')
<x-app-layout>
<div class="pr-wrap">

<div id="attendanceRoot">
    <div class="container-fluid px-2 pt-4 pt-md-0"
         x-data="{ calendarOpen: localStorage.getItem('attendance-calendar-open') !== 'false' }"
         x-init="$watch('calendarOpen', val => localStorage.setItem('attendance-calendar-open', val))">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('payroll.dashboard') }}">{{ __('Payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Attendance') }}</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold text-dark mb-0">{{ __('Attendance Calendar') }} — {{ $month->format('F Y') }}</h1>
            </div>

            <div class="d-none d-md-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        @click="calendarOpen = !calendarOpen"
                        :class="calendarOpen ? 'active' : ''"
                        :title="calendarOpen ? '{{ __('Hide Calendar') }}' : '{{ __('Show Calendar') }}'">
                    <i class="bi bi-calendar3"></i>
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                    <i class="bi bi-plus-lg me-1"></i>{{ __('New Attendance Record') }}
                </button>
            </div>

            <div class="d-flex d-md-none w-100 gap-2 align-items-center justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill"
                        @click="calendarOpen = !calendarOpen"
                        :class="calendarOpen ? 'active' : ''">
                    <i class="bi bi-calendar3"></i> {{ __('Calendar') }}
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $calStart = $month->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $calEnd = $month->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SATURDAY);
            $prevMonth = $month->copy()->subMonth()->toDateString();
            $nextMonth = $month->copy()->addMonth()->toDateString();
            $todayStr = now()->toDateString();
        @endphp

        <div class="row g-4">
            {{-- Sidebar filters --}}
            <div :class="calendarOpen ? 'col-lg-3' : 'col-lg-12'">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('payroll.attendance.index') }}" id="calendarFilterForm"
                              :class="calendarOpen ? '' : 'row g-3 align-items-start'">
                            <input type="hidden" name="date" id="calendarDateHidden" value="{{ $date }}">

                            <div :class="calendarOpen ? 'mb-3' : 'col-md-4 mb-0'">
                                <label class="form-label small fw-bold text-muted">{{ __('Month') }}</label>
                                <select class="form-select tom-select" id="calendarMonthSelect" onchange="updateCalendarDate()">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ $month->month === $m ? 'selected' : '' }}>{{ \Illuminate\Support\Carbon::create()->month($m)->format('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div :class="calendarOpen ? 'mb-3' : 'col-md-4 mb-0'">
                                <label class="form-label small fw-bold text-muted">{{ __('Year') }}</label>
                                <select class="form-select tom-select" id="calendarYearSelect" onchange="updateCalendarDate()">
                                    @foreach(range(now()->year - 1, now()->year) as $y)
                                        <option value="{{ $y }}" {{ $month->year === $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div :class="calendarOpen ? 'mb-0' : 'col-md-4 mb-0'">
                                <label class="form-label small fw-bold text-muted">{{ __('Employee') }}</label>
                                <x-common.employees name="employee_id" :value="request('employee_id')" placeholder="All Employees" onchange="attendanceSubmitFilter()" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Calendar --}}
            <div class="col-lg-9" x-show="calendarOpen" x-cloak>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                        <h6 class="fw-bold mb-0">{{ $month->format('F Y') }}</h6>
                        <div class="btn-group">
                            <a href="{{ route('payroll.attendance.index', array_filter(['date' => $prevMonth, 'employee_id' => request('employee_id')])) }}"
                               class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left me-1"></i>{{ __('Previous') }}</a>
                            <a href="{{ route('payroll.attendance.index', array_filter(['date' => $nextMonth, 'employee_id' => request('employee_id')])) }}"
                               class="btn btn-sm btn-outline-secondary">{{ __('Next') }}<i class="bi bi-chevron-right ms-1"></i></a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="calendar-grid">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $wd)
                                <div class="calendar-weekday">{{ __($wd) }}</div>
                            @endforeach

                            @php
                                $cursor = $calStart->copy();
                                $calendarDays = [];
                                while ($cursor->lte($calEnd)) {
                                    $dStr = $cursor->toDateString();
                                    $calendarDays[] = [
                                        'date' => $dStr,
                                        'day' => $cursor->day,
                                        'inMonth' => $cursor->month === $month->month,
                                        'summary' => $monthSummary[$dStr] ?? collect(),
                                    ];
                                    $cursor->addDay();
                                }
                            @endphp
                            @foreach($calendarDays as $cd)
                                <button type="button"
                                   data-date="{{ $cd['date'] }}"
                                   class="calendar-day calendar-day-btn {{ !$cd['inMonth'] ? 'is-outside' : '' }} {{ $cd['date'] === $date ? 'is-selected' : '' }} {{ $cd['date'] === $todayStr ? 'is-today' : '' }}">
                                    <span class="day-number">{{ $cd['day'] }}</span>
                                    @if($cd['summary']->isNotEmpty())
                                        @php
                                            $leaveCount = ($cd['summary']->get('leave') ?? 0) + ($cd['summary']->get('half_day') ?? 0);
                                        @endphp
                                        <span class="day-counts">
                                            @if($cd['summary']->get('present'))
                                                <span class="cal-count cal-count-present" title="{{ __('Present') }}">P:{{ $cd['summary']->get('present') }}</span>
                                            @endif
                                            @if($cd['summary']->get('absent'))
                                                <span class="cal-count cal-count-absent" title="{{ __('Absent') }}">A:{{ $cd['summary']->get('absent') }}</span>
                                            @endif
                                            @if($leaveCount)
                                                <span class="cal-count cal-count-leave" title="{{ __('Leave') }}">L:{{ $leaveCount }}</span>
                                            @endif
                                            @if($cd['summary']->get('holiday'))
                                                <span class="cal-count cal-count-holiday" title="{{ __('Holiday') }}">H:{{ $cd['summary']->get('holiday') }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
            .calendar-weekday {
                text-align: left; padding: 0.75rem 0.75rem 0.5rem; font-size: 0.75rem; font-weight: 700;
                text-transform: uppercase; color: #6c757d; border-bottom: 1px solid #e9ecef;
            }
            .calendar-day {
                display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start;
                padding: 0.6rem; text-decoration: none; color: #212529; background: #fff;
                border: 1px solid #f1f3f5; min-height: 90px; transition: background .15s ease;
            }
            /* The day cells are <button> elements (so a click can open the modal directly,
               with no page navigation) — reset the browser's default button chrome so they
               still look and lay out exactly like the plain boxes they used to be. */
            .calendar-day-btn {
                width: 100%; font: inherit; text-align: left; cursor: pointer; border-radius: 0;
            }
            .calendar-day:hover { background: #f8f9fa; }
            .calendar-day.is-outside { color: #ced4da; background: #fafafa; }
            .calendar-day.is-today .day-number {
                display: inline-flex; align-items: center; justify-content: center;
                width: 22px; height: 22px; border-radius: 50%; background: #0d6efd; color: #fff; font-weight: 700;
            }
            .calendar-day.is-selected { background: #eef4ff; border-color: #0d6efd; }
            .calendar-day .day-number { font-size: 0.85rem; font-weight: 600; }
            .day-counts { display: flex; flex-wrap: wrap; gap: 3px; margin-top: auto; padding-top: 0.5rem; }
            .cal-count {
                font-size: 0.6rem; font-weight: 700; line-height: 1; padding: 2px 4px;
                border-radius: 4px; white-space: nowrap;
            }
            .cal-count-present { background: #d1f7e0; color: #146c43; }
            .cal-count-absent { background: #fde0e0; color: #b02a37; }
            .cal-count-leave { background: #fff3cd; color: #997404; }
            .cal-count-holiday { background: #e2e3e5; color: #41464b; }

            .employee-month-row:hover { background: #f8f9fa; }
            .employee-month-chevron { transition: transform .15s ease; display: inline-block; }
            .employee-month-chevron.rotate-90 { transform: rotate(90deg); }
            .employee-month-detail table input,
            .employee-month-detail table select { font-size: .78rem; }
        </style>

        @php
            $selectedDaySummary = $monthSummary[$date] ?? collect();
            $selectedLeaveCount = ($selectedDaySummary->get('leave') ?? 0) + ($selectedDaySummary->get('half_day') ?? 0);
            $statusBadgeClasses = [
                'present' => 'bg-success-subtle text-success border-success-subtle',
                'absent' => 'bg-danger-subtle text-danger border-danger-subtle',
                'leave' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                'half_day' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                'holiday' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
            ];
        @endphp
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mt-4">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="fw-bold small text-uppercase text-muted">
                        @if($viewMode === 'monthly')
                            {{ __('Records for :month', ['month' => $month->format('F Y')]) }}
                        @else
                            {{ __('Records for :date', ['date' => \Illuminate\Support\Carbon::parse($date)->format('d M Y')]) }}
                        @endif
                    </span>
                    @if($viewMode === 'daily' && $selectedDaySummary->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @if($selectedDaySummary->get('present'))
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    {{ __('Present') }}: {{ $selectedDaySummary->get('present') }}
                                </span>
                            @endif
                            @if($selectedDaySummary->get('absent'))
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    {{ __('Absent') }}: {{ $selectedDaySummary->get('absent') }}
                                </span>
                            @endif
                            @if($selectedLeaveCount)
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                    {{ __('Leave') }}: {{ $selectedLeaveCount }}
                                </span>
                            @endif
                            @if($selectedDaySummary->get('holiday'))
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                    {{ __('Holiday') }}: {{ $selectedDaySummary->get('holiday') }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('payroll.attendance.index', array_filter(['date' => $date, 'employee_id' => request('employee_id'), 'view' => 'daily'])) }}"
                           class="btn {{ $viewMode === 'daily' ? 'btn-primary' : 'btn-outline-secondary' }}">{{ __('Daily') }}</a>
                        <a href="{{ route('payroll.attendance.index', array_filter(['date' => $date, 'employee_id' => request('employee_id'), 'view' => 'monthly'])) }}"
                           class="btn {{ $viewMode === 'monthly' ? 'btn-primary' : 'btn-outline-secondary' }}">{{ __('Monthly') }}</a>
                    </div>
                    {{--<button type="button" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                        <i class="bi bi-plus-lg"></i> {{ __('New Attendance Record') }}
                    </button>--}}
                </div>
            </div>

            @if($viewMode === 'daily')
            {{-- Full roster for the selected day — every employee, marked present/absent/
                 not-marked, not just the ones who already have a row for it. --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small fw-bold text-uppercase text-secondary">
                            <th class="px-4 py-3">{{ __('Employee') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Check In') }}</th>
                            <th class="px-4 py-3">{{ __('Check Out') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Overtime') }}</th>
                            <th class="px-4 py-3">{{ __('Remarks') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($dailyRows as $row)
                            @php($record = $row['record'])
                            <tr>
                                <td class="px-4 py-2 fw-bold text-dark">
                                    {{ $row['employee']->name }}
                                    <div class="text-muted extra-small fw-normal">{{ $row['employee']->department ?? '' }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    @if($record)
                                        <span class="badge rounded-pill px-2 py-1 border {{ $statusBadgeClasses[$record->status] ?? '' }}">
                                            {{ __(ucwords(str_replace('_', ' ', $record->status))) }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-2 py-1 border bg-light text-muted border-secondary-subtle">
                                            {{ __('Not Marked') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 tabular-nums">{{ $record->check_in ?? '—' }}</td>
                                <td class="px-4 py-2 tabular-nums">{{ $record->check_out ?? '—' }}</td>
                                <td class="px-4 py-2 text-end tabular-nums">{{ $record ? number_format($record->overtime_hours, 2) : '—' }}</td>
                                <td class="px-4 py-2 text-muted">{{ $record->remarks ?? '—' }}</td>
                                <td class="px-4 py-2 text-end">
                                    @if($record)
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm edit-attendance-btn"
                                                data-bs-toggle="modal" data-bs-target="#editAttendanceModal"
                                                data-id="{{ $record->id }}"
                                                data-employee="{{ $row['employee']->name }}"
                                                data-status="{{ $record->status }}"
                                                data-check-in="{{ $record->check_in }}"
                                                data-check-out="{{ $record->check_out }}"
                                                data-overtime="{{ $record->overtime_hours }}"
                                                data-remarks="{{ $record->remarks }}"
                                                data-update-url="{{ route('payroll.attendance.update', $record) }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm mark-attendance-btn"
                                                data-employee-id="{{ $row['employee']->id }}" title="{{ __('Mark Attendance') }}">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-5 text-center text-muted">
                                    <i class="bi bi-people display-6 d-block mb-2 opacity-25"></i>
                                    {{ __('No employees found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-md-none">
                <div class="list-group list-group-flush">
                    @forelse($dailyRows as $row)
                        @php($record = $row['record'])
                        <div class="list-group-item border-bottom px-3 py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-0">{{ $row['employee']->name }}</h6>
                                    <div class="text-muted small">{{ $row['employee']->department ?? '' }}</div>
                                </div>
                                @if($record)
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm edit-attendance-btn"
                                            data-bs-toggle="modal" data-bs-target="#editAttendanceModal"
                                            data-id="{{ $record->id }}"
                                            data-employee="{{ $row['employee']->name }}"
                                            data-status="{{ $record->status }}"
                                            data-check-in="{{ $record->check_in }}"
                                            data-check-out="{{ $record->check_out }}"
                                            data-overtime="{{ $record->overtime_hours }}"
                                            data-remarks="{{ $record->remarks }}"
                                            data-update-url="{{ route('payroll.attendance.update', $record) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm mark-attendance-btn"
                                            data-employee-id="{{ $row['employee']->id }}">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="small text-muted">{{ $record->check_in ?? '—' }} → {{ $record->check_out ?? '—' }}</span>
                                @if($record)
                                    <span class="badge rounded-pill px-2 py-1 border {{ $statusBadgeClasses[$record->status] ?? '' }}" style="font-size: 0.7rem;">
                                        {{ __(ucwords(str_replace('_', ' ', $record->status))) }}
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-1 border bg-light text-muted border-secondary-subtle" style="font-size: 0.7rem;">
                                        {{ __('Not Marked') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-people display-6 d-block mb-2 opacity-25"></i>
                            {{ __('No employees found.') }}
                        </div>
                    @endforelse
                </div>
            </div>
            @else
            {{-- Monthly roll-up — per-employee present/absent/leave/holiday day counts;
                 click a row to drill into an editable day-by-day breakdown. --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0"
                       x-data="{
                            expanded: null,
                            toggle(id, monthDate) {
                                this.expanded = (this.expanded === id) ? null : id;
                                if (this.expanded === id) {
                                    window.PAYROLL_ATTENDANCE.loadEmployeeMonth(id, monthDate);
                                }
                            }
                       }">
                    <thead class="table-light">
                        <tr class="small fw-bold text-uppercase text-secondary">
                            <th class="px-4 py-3">{{ __('Employee') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Present') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Absent') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Leave') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Holiday') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($employeeMonthRows as $row)
                            @php($empId = $row['employee']->id)
                            <tr class="employee-month-row" role="button"
                                @click="toggle({{ $empId }}, '{{ $month->toDateString() }}')">
                                <td class="px-4 py-2 fw-bold text-dark">
                                    <i class="bi bi-chevron-right me-1 employee-month-chevron" :class="expanded === {{ $empId }} ? 'rotate-90' : ''"></i>
                                    {{ $row['employee']->name }}
                                    <div class="text-muted extra-small fw-normal ms-4">{{ $row['employee']->department ?? '' }}</div>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span id="month-count-present-{{ $empId }}" class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2">{{ $row['present'] }}</span>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span id="month-count-absent-{{ $empId }}" class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2">{{ $row['absent'] }}</span>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span id="month-count-leave-{{ $empId }}" class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2">{{ $row['leave'] }}</span>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span id="month-count-holiday-{{ $empId }}" class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2">{{ $row['holiday'] }}</span>
                                </td>
                                <td class="px-4 py-2 text-end">
                                    <i class="bi bi-arrows-expand text-muted"></i>
                                </td>
                            </tr>
                            <tr x-show="expanded === {{ $empId }}" x-cloak>
                                <td colspan="6" class="p-0 bg-light">
                                    <div class="p-3 employee-month-detail" id="employee-month-detail-{{ $empId }}">
                                        <div class="text-center text-muted py-3">
                                            <span class="spinner-border spinner-border-sm"></span> {{ __('Loading...') }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-5 text-center text-muted">
                                    <i class="bi bi-people display-6 d-block mb-2 opacity-25"></i>
                                    {{ __('No employees found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- New Attendance Record Modal --}}
    <div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="markAttendanceForm" action="{{ route('payroll.attendance.store') }}" method="POST">
                    @csrf
                    <div class="modal-header d-flex align-items-center justify-content-between">
                        <h5 class="modal-title fw-bold mb-0">{{ __('New Attendance Record') }}</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light border btn-sm d-flex align-items-center gap-1" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold required">{{ __('Employee') }}</label>
                            <x-common.employees name="employee_id" :class="$errors->has('employee_id') ? 'is-invalid' : ''" :value="old('employee_id')" placeholder="Select employee..." />
                            @error('employee_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold required">{{ __('Date') }}</label>
                                <input type="text" id="markAttendanceDate" name="date" value="{{ old('date', $dateDisplay) }}" class="form-control datepicker @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold required">{{ __('Status') }}</label>
                                <select name="status" class="form-select tom-select @error('status') is-invalid @enderror">
                                    @foreach(['present' => __('Present'), 'absent' => __('Absent'), 'leave' => __('Leave'), 'half_day' => __('Half Day'), 'holiday' => __('Holiday')] as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', 'present') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Check In') }}</label>
                                <input type="time" name="check_in" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Check Out') }}</label>
                                <input type="time" name="check_out" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Overtime Hours') }}</label>
                                <input type="number" step="0.5" min="0" max="24" name="overtime_hours" class="form-control" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">{{ __('Remarks') }}</label>
                                <textarea name="remarks" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Attendance Modal --}}
    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editAttendanceForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header d-flex align-items-center justify-content-between">
                        <h5 class="modal-title fw-bold mb-0">{{ __('Edit Attendance Record') }} — <span id="editAttendanceEmployee"></span></h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light border btn-sm d-flex align-items-center gap-1" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold required">{{ __('Status') }}</label>
                                <select name="status" id="editAttendanceStatus" class="form-select tom-select" required>
                                    <option value="present">{{ __('Present') }}</option>
                                    <option value="absent">{{ __('Absent') }}</option>
                                    <option value="leave">{{ __('Leave') }}</option>
                                    <option value="half_day">{{ __('Half Day') }}</option>
                                    <option value="holiday">{{ __('Holiday') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Overtime Hours') }}</label>
                                <input type="number" step="0.5" min="0" max="24" name="overtime_hours" id="editAttendanceOvertime" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Check In') }}</label>
                                <input type="time" name="check_in" id="editAttendanceCheckIn" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Check Out') }}</label>
                                <input type="time" name="check_out" id="editAttendanceCheckOut" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">{{ __('Remarks') }}</label>
                                <textarea name="remarks" id="editAttendanceRemarks" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        {{-- Reopens the create modal with its validation errors after a failed submit
             (the form posts back to this same page, so a plain page load is all that's
             needed here — no query param involved). --}}
        @if($errors->any() && (old('employee_id') !== null || $errors->has('employee_id') || $errors->has('date')))
            document.addEventListener('DOMContentLoaded', function () {
                new bootstrap.Modal(document.getElementById('markAttendanceModal')).show();
            });
        @endif

        if (!window.attendanceNavigate) {
            window.attendanceNavigate = function (url, push) {
                const root = document.getElementById('attendanceRoot');
                if (!root) { window.location.href = url; return; }
                fetch(url, { headers: { 'Accept': 'text/html' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) throw new Error(); return r.text(); })
                    .then(function (html) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const fresh = doc.getElementById('attendanceRoot');
                        if (!fresh) throw new Error();
                        root.querySelectorAll('.modal.show').forEach(function (m) { const i = bootstrap.Modal.getInstance(m); if (i) i.hide(); });
                        // Alpine only styles the new markup a moment after it is inserted, which made the calendar
                        // button / panels flash. Pre-apply the current calendar state so nothing visibly changes.
                        (function () {
                            var open = true;
                            try { var cur = root.querySelector('[x-data*="calendarOpen"]'); if (cur && window.Alpine) open = !!Alpine.$data(cur).calendarOpen; } catch (e) {}
                            var host = fresh.querySelector('[x-data*="calendarOpen"]');
                            if (host) host.setAttribute('x-data', '{ calendarOpen: ' + open + ' }');
                            fresh.querySelectorAll('[\\:class*="calendarOpen"]').forEach(function (el) {
                                try {
                                    var cls = new Function('calendarOpen', 'return ' + el.getAttribute(':class'))(open);
                                    if (typeof cls === 'string' && cls) cls.split(/\s+/).forEach(function (c) { if (c) el.classList.add(c); });
                                } catch (e) {}
                            });
                            fresh.querySelectorAll('[x-show="calendarOpen"]').forEach(function (el) {
                                el.removeAttribute('x-cloak');
                                el.style.display = open ? '' : 'none';
                            });
                        })();
                        const scripts = Array.from(fresh.querySelectorAll('script')).map(function (sc) { const t = sc.textContent; sc.remove(); return t; });
                        root.innerHTML = fresh.innerHTML;
                        scripts.forEach(function (code) {
                            const el = document.createElement('script');
                            el.textContent = code;
                            document.body.appendChild(el).remove();
                        });
                        if (typeof datepicker === 'function') datepicker();
                        if (typeof initTomSelectForm === 'function' && window.jQuery) initTomSelectForm(window.jQuery('#content-wrapper'));
                        if (doc.title) document.title = doc.title;
                        if (push !== false) history.pushState({ attendance: true }, '', url);
                    })
                    .catch(function () { window.location.href = url; })
                    .finally(function () { root.style.opacity = ''; });
            };
            window.addEventListener('popstate', function () {
                if (document.getElementById('attendanceRoot')) attendanceNavigate(location.href, false);
            });
            document.addEventListener('click', function (e) {
                const a = e.target.closest('#attendanceRoot a[href]');
                if (!a || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
                const u = new URL(a.href, location.href);
                if (u.pathname !== location.pathname) return;
                e.preventDefault();
                attendanceNavigate(u.href);
            });
        }

        // TomSelect keeps its own display, so programmatic value changes / form.reset() must go through it.
        function attSetSelect(el, value) { if (el.tomselect) { el.tomselect.setValue(String(value), true); } else { el.value = value; } }
        function attSyncSelects(form) { form.querySelectorAll('select').forEach(function (s) { if (s.tomselect) s.tomselect.sync(); }); }
        // Page content sits outside #content-wrapper: init TomSelect here (on load and after each in-place navigation).
        (function () {
            var go = function () { if (typeof initTomSelectForm === 'function') { initTomSelectForm($('#attendanceRoot')); } };
            if (document.readyState === 'complete') { go(); } else { window.addEventListener('load', go); }
        })();

        function updateCalendarDate() {
            const month = document.getElementById('calendarMonthSelect').value.padStart(2, '0');
            const year = document.getElementById('calendarYearSelect').value;
            document.getElementById('calendarDateHidden').value = `${year}-${month}-01`;
            attendanceSubmitFilter();
        }

        // Filters, month navigation and roster/monthly tabs load in place: the page is fetched in
        // the background and only #attendanceRoot is swapped (no full reload), like the list pages.
        function attendanceSubmitFilter() {
            const form = document.getElementById('calendarFilterForm');
            const params = new URLSearchParams(new FormData(form));
            [...params.keys()].forEach(function (k) { if (!params.get(k)) params.delete(k); });
            attendanceNavigate(form.action + '?' + params.toString());
        }

        // Clicking a calendar day opens the "New Attendance Record" modal directly, with
        // that day pre-filled — no page navigation/reload first.
        document.querySelectorAll('.calendar-day-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.calendar-day-btn.is-selected').forEach(function (el) {
                    el.classList.remove('is-selected');
                });
                btn.classList.add('is-selected');

                const form = document.getElementById('markAttendanceForm');
                form.reset();
                attSyncSelects(form);

                const [y, m, d] = btn.dataset.date.split('-');
                const displayDate = `${d}-${m}-${y}`;
                const dateInput = document.getElementById('markAttendanceDate');
                if (dateInput._flatpickr) {
                    dateInput._flatpickr.setDate(displayDate, true);
                } else {
                    dateInput.value = displayDate;
                }

                new bootstrap.Modal(document.getElementById('markAttendanceModal')).show();
            });
        });

        document.querySelectorAll('.edit-attendance-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('editAttendanceEmployee').textContent = btn.dataset.employee;
                attSetSelect(document.getElementById('editAttendanceStatus'), btn.dataset.status);
                document.getElementById('editAttendanceOvertime').value = btn.dataset.overtime;
                document.getElementById('editAttendanceCheckIn').value = btn.dataset.checkIn || '';
                document.getElementById('editAttendanceCheckOut').value = btn.dataset.checkOut || '';
                document.getElementById('editAttendanceRemarks').value = btn.dataset.remarks || '';
                document.getElementById('editAttendanceForm').action = btn.dataset.updateUrl;
            });
        });

        // Daily roster: "Not Marked" employees get a quick-mark button that opens the
        // same create modal, pre-filled with that employee and the day being viewed.
        document.querySelectorAll('.mark-attendance-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const form = document.getElementById('markAttendanceForm');
                form.reset();
                attSyncSelects(form);
                attSetSelect(document.querySelector('#markAttendanceModal select[name="employee_id"]'), btn.dataset.employeeId);

                const dateInput = document.getElementById('markAttendanceDate');
                if (dateInput._flatpickr) {
                    dateInput._flatpickr.setDate(@json($dateDisplay), true);
                }

                new bootstrap.Modal(document.getElementById('markAttendanceModal')).show();
            });
        });

        // Monthly view: per-employee day-by-day drill-down, loaded on first expand and
        // editable inline — each row saves straight to the existing store endpoint
        // (it upserts by employee+date, so the same call works whether that day already
        // had a record or not) without reloading the page.
        window.PAYROLL_ATTENDANCE = window.PAYROLL_ATTENDANCE || {};

        var employeeMonthCache = {};
        var statusLabels = {
            '': '{{ __('Not Marked') }}',
            present: '{{ __('Present') }}',
            absent: '{{ __('Absent') }}',
            leave: '{{ __('Leave') }}',
            half_day: '{{ __('Half Day') }}',
            holiday: '{{ __('Holiday') }}',
        };

        function attendanceCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        }

        function updateEmployeeMonthCounts(employeeId, days) {
            const counts = { present: 0, absent: 0, leave: 0, holiday: 0 };
            days.forEach(function (day) {
                if (day.status === 'present') counts.present++;
                else if (day.status === 'absent') counts.absent++;
                else if (day.status === 'leave' || day.status === 'half_day') counts.leave++;
                else if (day.status === 'holiday') counts.holiday++;
            });
            ['present', 'absent', 'leave', 'holiday'].forEach(function (key) {
                const el = document.getElementById('month-count-' + key + '-' + employeeId);
                if (el) el.textContent = counts[key];
            });
        }

        function renderEmployeeMonthTable(container, employeeId, days) {
            let html = '<div class="table-responsive"><table class="table table-sm table-bordered bg-white mb-0">' +
                '<thead><tr><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th><th>{{ __('Check In') }}</th>' +
                '<th>{{ __('Check Out') }}</th><th>{{ __('Overtime') }}</th><th></th></tr></thead><tbody>';

            days.forEach(function (day) {
                html += '<tr data-date="' + day.date + '">' +
                    '<td class="align-middle">' + day.label + '</td>' +
                    '<td><select class="form-select form-select-sm day-status">' +
                        Object.keys(statusLabels).map(function (key) {
                            return '<option value="' + key + '"' + (key === (day.status || '') ? ' selected' : '') + '>' + statusLabels[key] + '</option>';
                        }).join('') +
                    '</select></td>' +
                    '<td><input type="time" class="form-control form-control-sm day-check-in" value="' + (day.check_in || '') + '"></td>' +
                    '<td><input type="time" class="form-control form-control-sm day-check-out" value="' + (day.check_out || '') + '"></td>' +
                    '<td><input type="number" step="0.5" min="0" max="24" class="form-control form-control-sm day-overtime" value="' + (day.overtime_hours || 0) + '"></td>' +
                    '<td class="text-end"><button type="button" class="btn btn-primary btn-sm day-save-btn">{{ __('Save') }}</button></td>' +
                    '</tr>';
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;

            container.querySelectorAll('.day-save-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const row = btn.closest('tr');
                    const date = row.dataset.date;
                    const status = row.querySelector('.day-status').value;

                    if (!status) {
                        toastr.warning('{{ __('Select a status first.') }}');
                        return;
                    }

                    const body = new URLSearchParams();
                    body.set('employee_id', employeeId);
                    body.set('date', date);
                    body.set('status', status);
                    body.set('check_in', row.querySelector('.day-check-in').value);
                    body.set('check_out', row.querySelector('.day-check-out').value);
                    body.set('overtime_hours', row.querySelector('.day-overtime').value || 0);

                    btn.disabled = true;
                    fetch(@json(route('payroll.attendance.store')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': attendanceCsrfToken(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: body.toString(),
                    })
                        .then(function (res) {
                            return res.json().then(function (data) { return { ok: res.ok, data: data }; });
                        })
                        .then(function (result) {
                            btn.disabled = false;
                            if (result.ok) {
                                toastr.success(result.data.message);
                                const dayEntry = days.find(function (d) { return d.date === date; });
                                if (dayEntry) {
                                    dayEntry.status = status;
                                    dayEntry.check_in = result.data.attendance.check_in;
                                    dayEntry.check_out = result.data.attendance.check_out;
                                    dayEntry.overtime_hours = result.data.attendance.overtime_hours;
                                }
                                updateEmployeeMonthCounts(employeeId, days);
                            } else {
                                const firstError = result.data.errors ? Object.values(result.data.errors)[0][0] : null;
                                toastr.error(firstError || '{{ __('Something went wrong.') }}');
                            }
                        })
                        .catch(function () {
                            btn.disabled = false;
                            toastr.error('{{ __('Network error. Please try again.') }}');
                        });
                });
            });
        }

        window.PAYROLL_ATTENDANCE.loadEmployeeMonth = function (employeeId, monthDate) {
            const container = document.getElementById('employee-month-detail-' + employeeId);
            if (!container) return;

            const cacheKey = employeeId + ':' + monthDate;
            if (employeeMonthCache[cacheKey]) {
                renderEmployeeMonthTable(container, employeeId, employeeMonthCache[cacheKey]);
                return;
            }

            fetch(@json(url('/payroll/attendance/employee-month')) + '/' + employeeId + '?month=' + monthDate, {
                headers: { 'Accept': 'application/json' },
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    employeeMonthCache[cacheKey] = data.days;
                    renderEmployeeMonthTable(container, employeeId, data.days);
                })
                .catch(function () {
                    container.innerHTML = '<div class="text-center text-danger py-3">{{ __('Failed to load attendance.') }}</div>';
                });
        };
    </script>
</div>
</div>
</x-app-layout>
