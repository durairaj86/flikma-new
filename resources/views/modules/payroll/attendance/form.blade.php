<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $attendance->row_no ?? 'New Attendance Record' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $attendance->id ?? '' }}">
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Employee -->
                    <div class="col-md-12">
                        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                        {{--<x-common.employee value="{{ $attendance->employee_id ?? '' }}"></x-common.employee>--}}
                        <select class="tom-select" id="employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employeeId => $employee)
                                <option
                                    value="{{ $employeeId }}" {{ isset($attendance) && $attendance->employee_id == $employeeId ? 'selected' : '' }}>
                                    {{ $employee }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select an employee.</div>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control datepicker" id="date" name="date"
                               value="{{ isset($attendance) ? $attendance->date : date('d-m-Y') }}" required>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="tom-select" id="status" name="status" required>
                            @foreach($statusOptions as $value => $label)
                                <option
                                    value="{{ $value }}" {{ isset($attendance) && $attendance->status == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a status.</div>
                    </div>

                    <!-- Check In -->
                    <div class="col-md-6">
                        <label for="check_in" class="form-label">Check In Time</label>
                        <input type="time" class="form-control timepicker" id="check_in" name="check_in" autocomplete="off"
                               value="{{ isset($attendance) ? $attendance->check_in : '' }}">
                    </div>

                    <!-- Check Out -->
                    <div class="col-md-6">
                        <label for="check_out" class="form-label">Check Out Time</label>
                        <input type="time" class="form-control timepicker" id="check_out" name="check_out" autocomplete="off"
                               value="{{ isset($attendance) ? $attendance->check_out : '' }}">
                    </div>

                    <!-- Remarks -->
                    <div class="col-md-12">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control h-100" id="remarks" name="remarks"
                                  rows="3">{{ isset($attendance) ? $attendance->remarks : '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
