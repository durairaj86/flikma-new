@section('js','attendance')
@section('page-title','Attendance')
<x-app-layout>
    <main class="gmail-content bg-white px-3">


        <div class="d-flex justify-content-between align-items-start py-3">
            <div class="align-items-center flex-shrink-0"></div>
            <div class="d-flex justify-content-between">
                <div class="position-relative">
                    <!-- Compact Filter button -->
                </div>
                <button class="btn btn-primary rounded-pill px-4" id="new">New Attendance Record</button>
            </div>
        </div>

        <!-- Calendar View -->
        @livewire('payroll.attendance-calendar', ['month' => $month, 'year' => $year])

        <!-- List View -->
        <div class="shadow bdr-r-10 py-3 flex-grow-1">
            <div class="d-flex justify-content-between px-3 flex-shrink-0 mb-3">
                <h5 class="fw-bold">Attendance Records</h5>
                <div class="align-items-center gap-2">
                    <div class="search-box position-relative me-2">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="customSearch" class="form-control rounded-pill ps-5"
                               placeholder="Search..." aria-label="Search...">
                    </div>
                </div>
            </div>

            <div class="">
                <table class="table align-middle dataTable" id="dataTable" data-min-height="min-height:75vh;" data-title="Attendance" data-model-size="md">
                    <thead class="table-light bg-white">
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                        <th>Remarks</th>
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
