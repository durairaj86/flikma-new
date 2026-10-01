<div>
    <div class="shadow bdr-r-10 py-3 mb-4">
        <div class="d-flex justify-content-between px-3 mb-3">
            <h5 class="fw-bold" id="calendar-title">{{ __('Attendance Calendar') }} - {{ $months[intval($month)] }} {{ $year }}</h5>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="prevMonth">
                    <i class="bi bi-chevron-left"></i> {{ __('Previous') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="nextMonth">
                    {{ __('Next') }} <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="px-3">
            <!-- Filter Controls -->
            <div class="row">
                <div class="bg-light rounded p-3 mb-4 col-3">
                    <div class="g-3 align-items-end" wire:ignore id="content-wrapper">
                        <div class="d-flex justify-content-between mb-4">
                            <div>
                                <label class="form-label fw-medium">{{ __('Month') }}</label>
                                <select class="tom-select" wire:model.live="month" data-max-width="250">
                                    @foreach($months as $key => $monthName)
                                        <option
                                            value="{{ $key }}" @selected($currentMonth == $key)>{{ $monthName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label fw-medium">{{ __('Year') }}</label>
                                <select class="tom-select" wire:model.live="year">
                                    @foreach($years as $key => $yearValue)
                                        <option
                                            value="{{ $key }}" @selected($currentYear == $key)>{{ $yearValue }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-medium">{{ __('Employee') }}</label>
                            <select class="tom-select" data-live-search="true" wire:model.live="employeeId">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Weekly Calendar View -->
                <div class="table-responsive col-9">
                    <table class="table table-bordered attendance-calendar">
                        <thead>
                        <tr>
                            <th colspan="7" class="text-center">{{ $months[intval($month)] }} {{ $year }}</th>
                        </tr>
                        <tr>
                            <th class="text-center">{{ __('Sun') }}</th>
                            <th class="text-center">{{ __('Mon') }}</th>
                            <th class="text-center">{{ __('Tue') }}</th>
                            <th class="text-center">{{ __('Wed') }}</th>
                            <th class="text-center">{{ __('Thu') }}</th>
                            <th class="text-center">{{ __('Fri') }}</th>
                            <th class="text-center">{{ __('Sat') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($weeks as $week)
                            <tr>
                                @foreach($week as $day)
                                    @if($day === null)
                                        <td class="empty-day"></td>
                                    @else
                                        @php
                                            if(!empty($employeeId)) {
                                                $employeeData = collect($this->calendar)->firstWhere('id', $employeeId);
                                                $dayData = $employeeData ? collect($employeeData['days'])->firstWhere('day', $day['day']) : null;
                                            } else {
                                                $dayData = null;
                                            }
                                        @endphp
                                        <td class="day-cell attendance-cell {{ $day['is_weekend'] ? 'weekend' : '' }}"
                                            data-employee-id="{{ !empty($employeeId) ? $employeeId : '' }}"
                                            data-date="{{ $day['date'] }}"
                                            data-attendance-id="{{ (!empty($employeeId) && $dayData && isset($dayData['attendance_id'])) ? $dayData['attendance_id'] : '' }}">
                                            <div class="day-number">{{ $day['day'] }}</div>

                                            @if(!empty($employeeId))
                                                <!-- Single employee view -->

                                                @if($dayData && $dayData['status'])
                                                    <div class="status-badge status-{{ $dayData['status'] }}">
                                                        {{ ucfirst($dayData['status']) }}
                                                    </div>
                                                    @if($dayData['check_in'])
                                                        <div class="time-info">{{ $dayData['check_in'] }}</div>
                                                    @endif
                                                @endif
                                            @else
                                                <!-- Multiple employees summary view -->
                                                <div class="employee-summary">
                                                    @php
                                                        $presentCount = 0;
                                                        $absentCount = 0;
                                                        $lateCount = 0;
                                                        $leaveCount = 0;

                                                        foreach($this->calendar as $employee) {
                                                            $employeeDayData = collect($employee['days'])->firstWhere('day', $day['day']);
                                                            if($employeeDayData) {
                                                                if($employeeDayData['status'] == 'present') $presentCount++;
                                                                elseif($employeeDayData['status'] == 'absent') $absentCount++;
                                                                elseif($employeeDayData['status'] == 'late') $lateCount++;
                                                                elseif($employeeDayData['status'] == 'leave') $leaveCount++;
                                                            }
                                                        }
                                                    @endphp

                                                    @if($presentCount > 0)
                                                        <div class="status-count status-present">
                                                            P: {{ $presentCount }}</div>
                                                    @endif

                                                    @if($absentCount > 0)
                                                        <div class="status-count status-absent">
                                                            A: {{ $absentCount }}</div>
                                                    @endif

                                                    @if($lateCount > 0)
                                                        <div class="status-count status-late">L: {{ $lateCount }}</div>
                                                    @endif

                                                    @if($leaveCount > 0)
                                                        <div class="status-count status-leave">
                                                            Lv: {{ $leaveCount }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Employee List View (when no specific employee is selected) -->
            @if(empty($employeeId) && count($this->calendar) > 0)
                <div class="mt-4">
                    <h5 class="fw-bold mb-3">{{ __('Employee Attendance Summary') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th class="text-center">{{ __('Present') }}</th>
                                <th class="text-center">{{ __('Absent') }}</th>
                                <th class="text-center">{{ __('Late') }}</th>
                                <th class="text-center">{{ __('Leave') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($this->calendar as $employee)
                                @php
                                    $presentCount = collect($employee['days'])->where('status', 'present')->count();
                                    $absentCount = collect($employee['days'])->where('status', 'absent')->count();
                                    $lateCount = collect($employee['days'])->where('status', 'late')->count();
                                    $leaveCount = collect($employee['days'])->where('status', 'leave')->count();
                                @endphp
                                <tr>
                                    <td>{{ $employee['name'] }}</td>
                                    <td class="text-center">{{ $presentCount }}</td>
                                    <td class="text-center">{{ $absentCount }}</td>
                                    <td class="text-center">{{ $lateCount }}</td>
                                    <td class="text-center">{{ $leaveCount }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Detailed Employee View (when a specific employee is selected) -->
            @if(!empty($employeeId) && count($this->calendar) > 0)
                @php
                    $employeeData = collect($this->calendar)->firstWhere('id', $employeeId);
                @endphp

                @if($employeeData)
                    <div class="mt-4">
                        <h5 class="fw-bold mb-3">{{ __('Attendance Details for') }} {{ $employeeData['name'] }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Day') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Check In') }}</th>
                                    <th>{{ __('Check Out') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($employeeData['days'] as $day)
                                    @if($day['status'])
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
                                            <td>{{ $day['day_of_week'] }}</td>
                                            <td>
                                                    <span class="badge status-{{ $day['status'] }}">
                                                        {{ ucfirst($day['status']) }}
                                                    </span>
                                            </td>
                                            <td>{{ $day['check_in'] ?? __('N/A') }}</td>
                                            <td>{{ $day['check_out'] ?? __('N/A') }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
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
        }

        .attendance-calendar .weekend {
            background-color: #f8f9fa;
        }

        .day-cell {
            height: 100px;
            width: 14.28%;
            vertical-align: top;
            position: relative;
        }

        .empty-day {
            background-color: #f9f9f9;
        }

        .day-number {
            position: absolute;
            top: 5px;
            left: 5px;
            font-weight: bold;
        }

        .employee-summary {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .status-badge {
            margin-top: 25px;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .time-info {
            font-size: 0.75rem;
            margin-top: 5px;
        }

        .status-count {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 0.75rem;
        }

        /* Status Colors */
        .status-present {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-absent {
            background-color: #f8d7da;
            color: #842029;
        }

        .status-late {
            background-color: #fff3cd;
            color: #664d03;
        }

        .status-half-day {
            background-color: #cff4fc;
            color: #055160;
        }

        .status-leave {
            background-color: #e2e3e5;
            color: #41464b;
        }
    </style>
</div>
