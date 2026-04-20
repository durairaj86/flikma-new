<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $basicSalary->row_no ?? 'New Basic Salary' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Basic Salary">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $basicSalary->id ?? '' }}">

        <!-- Basic Salary Header -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Employee -->
                    <div class="col-md-6">
                        <label class="form-label required">Employee <sup class="text-danger">*</sup></label>
                        <x-common.employee value="{{ $basicSalary->employee_id ?? '' }}"></x-common.employee>
                    </div>

                    <!-- Effective Date -->
                    <div class="col-md-6">
                        <label class="form-label required">Effective Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="effective_date" name="effective_date" class="form-control datepicker"
                               value="{{ isset($basicSalary) ? showDate($basicSalary->effective_date) : '' }}" required>
                    </div>
                </div>

                <!-- Salary Details Section -->
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Salary Details</h5>
                    </div>

                    <!-- Basic Salary -->
                    <div class="col-md-4">
                        <label class="form-label required">Basic Salary <sup class="text-danger">*</sup></label>
                        <input type="text" name="basic_salary" class="form-control float"
                               value="{{ $basicSalary->basic_salary ?? 0 }}" min="0" step="0.01" required>
                    </div>

                    <!-- Housing Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Housing Allowance</label>
                        <input type="text" name="housing_allowance" class="form-control float"
                               value="{{ $basicSalary->housing_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Transportation Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Transportation Allowance</label>
                        <input type="text" name="transportation_allowance" class="form-control float"
                               value="{{ $basicSalary->transportation_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Food Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Food Allowance</label>
                        <input type="text" name="food_allowance" class="form-control float"
                               value="{{ $basicSalary->food_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Phone Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Phone Allowance</label>
                        <input type="text" name="phone_allowance" class="form-control float"
                               value="{{ $basicSalary->phone_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Other Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Other Allowance</label>
                        <input type="text" name="other_allowance" class="form-control float"
                               value="{{ $basicSalary->other_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Status -->
                    <div class="col-md-4">
                        <label class="form-label required">Status <sup class="text-danger">*</sup></label>
                        <select name="status" class="tom-select" required>
                            <option value="active" @selected(isset($basicSalary) && $basicSalary->status == 'active')>
                                Active
                            </option>
                            <option
                                value="inactive" @selected(isset($basicSalary) && $basicSalary->status == 'inactive')>
                                Inactive
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        <div class="mt-3 px-4">
            <label class="form-label fw-semibold">Remarks</label>
            <textarea name="remarks" class="form-control h-100" rows="4"
                      placeholder="Any additional information...">{{ $basicSalary->remarks ?? '' }}</textarea>
        </div>
    </form>
</div>
