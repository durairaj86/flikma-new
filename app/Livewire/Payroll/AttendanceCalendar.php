<?php

namespace App\Livewire\Payroll;

use Livewire\Component;
use App\Models\Payroll\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceCalendar extends Component
{
    public $month;
    public $year;
    public $employeeId;
    public $months;
    public $currentMonth;
    public $currentYear;
    public $years;
    public $employees;
    public $calendar = [];
    public $daysInMonth;
    public $weeks = [];

    public function mount($month = null, $year = null, $employeeId = null)
    {
        $this->month = $month ?? date('m');
        $this->year = $year ?? date('Y');
        $this->employeeId = $employeeId;
        $this->currentMonth = date('m');
        $this->currentYear = date('Y');

        // Initialize months
        $this->months = [];
        for ($i = 1; $i <= 12; $i++) {
            $this->months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // Initialize years
        $this->years = [];
        $currentYear = date('Y');
        for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
            $this->years[$i] = $i;
        }

        // Get employees
        $this->employees = User::orderBy('name')->get();

        $this->loadCalendarData();
    }

    public function loadCalendarData()
    {
        // Calculate days in month
        $this->daysInMonth = Carbon::createFromDate($this->year, $this->month, 1)->daysInMonth;

        // Get all attendance records for the month
        $query = Attendance::whereMonth('date', $this->month)
            ->whereYear('date', $this->year);

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        $attendanceRecords = $query->get()->keyBy(function ($item) {
            return $item->employee_id . '-' . Carbon::parse($item->date)->format('Y-m-d');
        });

        // Get all employees
        $employeeQuery = User::orderBy('name');
        if ($this->employeeId) {
            $employeeQuery->where('id', $this->employeeId);
        }
        $employees = $employeeQuery->get();

        // Build calendar data
        $this->calendar = [];
        foreach ($employees as $employee) {
            $employeeData = [
                'id' => $employee->id,
                'name' => $employee->name,
                'days' => []
            ];

            for ($day = 1; $day <= $this->daysInMonth; $day++) {
                $date = Carbon::createFromDate($this->year, $this->month, $day);
                $isWeekend = $date->isWeekend();
                $dateString = $date->format('Y-m-d');
                $key = $employee->id . '-' . $dateString;

                $attendanceData = [
                    'date' => $dateString,
                    'is_weekend' => $isWeekend,
                    'day_of_week' => $date->format('D'),
                    'day' => $day,
                    'attendance_id' => null,
                    'status' => null,
                    'check_in' => null,
                    'check_out' => null
                ];

                if (isset($attendanceRecords[$key])) {
                    $record = $attendanceRecords[$key];
                    $attendanceData['attendance_id'] = $record->id;
                    $attendanceData['status'] = $record->status;
                    $attendanceData['check_in'] = $record->check_in;
                    $attendanceData['check_out'] = $record->check_out;
                }

                $employeeData['days'][] = $attendanceData;
            }

            $this->calendar[] = $employeeData;
        }

        // Organize days into weeks
        $this->organizeIntoWeeks();
    }

    public function organizeIntoWeeks()
    {
        $this->weeks = [];
        $firstDayOfMonth = Carbon::createFromDate($this->year, $this->month, 1);
        $startDayOfWeek = $firstDayOfMonth->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        // Create weeks array
        $currentWeek = [];

        // Add empty cells for days before the 1st of the month
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $currentWeek[] = null;
        }

        // Add days of the month
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $date = Carbon::createFromDate($this->year, $this->month, $day);
            $currentWeek[] = [
                'day' => $day,
                'date' => $date->format('Y-m-d'),
                'day_of_week' => $date->format('D'),
                'is_weekend' => $date->isWeekend()
            ];

            // If we've reached the end of the week or the end of the month, start a new week
            if ($date->dayOfWeek == 6 || $day == $this->daysInMonth) {
                $this->weeks[] = $currentWeek;
                $currentWeek = [];
            }
        }

        // Add empty cells for days after the last day of the month in the last week
        $lastDayOfMonth = Carbon::createFromDate($this->year, $this->month, $this->daysInMonth);
        if ($lastDayOfMonth->dayOfWeek < 6) {
            for ($i = $lastDayOfMonth->dayOfWeek + 1; $i <= 6; $i++) {
                $currentWeek[] = null;
            }
            if (count($currentWeek) > 0) {
                $this->weeks[] = $currentWeek;
            }
        }
    }

    public function updatedMonth()
    {
        $this->loadCalendarData();
    }

    public function updatedYear()
    {
        $this->loadCalendarData();
    }

    public function updatedEmployeeId()
    {
        $this->loadCalendarData();
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->format('m');
        $this->year = $date->format('Y');
        $this->loadCalendarData();
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->month = $date->format('m');
        $this->year = $date->format('Y');
        $this->loadCalendarData();
    }

    public function render()
    {
        return view('livewire.payroll.attendance-calendar');
    }
}
