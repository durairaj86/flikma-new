<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $monthlySalary->row_no ?? 'New Monthly Salary' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Monthly Salary">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $monthlySalary->id ?? '' }}">

        <!-- Monthly Salary Header -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Employee -->
                    <div class="col-md-6">
                        <label class="form-label required">Employee <sup class="text-danger">*</sup></label>
                        <x-common.employee value="{{ $monthlySalary->employee_id ?? '' }}" id="employee_id"></x-common.employee>
                    </div>

                    <!-- Month and Year -->
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label required">Month <sup class="text-danger">*</sup></label>
                                <select name="month" class="tom-select" required>
                                    <option value="">Select Month</option>
                                    @foreach($months as $key => $month)
                                        <option value="{{ $key }}" @selected($salaryMonth == $key)>
                                            {{ $month }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Year <sup class="text-danger">*</sup></label>
                                <select name="year" class="tom-select" required>
                                    <option value="">Select Year</option>
                                    @foreach($years as $key => $year)
                                        <option value="{{ $key }}" @selected($salaryYear == $key)>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Details Section -->
                <div class="row g-3 mt-3">
                    <div class="col-12 border-bottom">
                        <div class="d-flex justify-content-between">
                            <h5>Salary Details</h5>
                            <button type="button" id="fetch-basic-salary" class="btn btn-sm btn-outline-primary mb-3">
                                <i class="bi bi-arrow-repeat"></i> Fetch Basic Salary Details
                            </button>
                        </div>
                    </div>

                    <!-- Basic Salary -->
                    <div class="col-md-4">
                        <label class="form-label required">Basic Salary <sup class="text-danger">*</sup></label>
                        <input type="text" name="basic_salary" id="basic_salary" class="form-control float"
                               value="{{ $monthlySalary->basic_salary ?? 0 }}" min="0" step="0.01" required>
                    </div>

                    <!-- Housing Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Housing Allowance</label>
                        <input type="text" name="housing_allowance" id="housing_allowance"
                               class="form-control allowance float"
                               value="{{ $monthlySalary->housing_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Transportation Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Transportation Allowance</label>
                        <input type="text" name="transportation_allowance" id="transportation_allowance"
                               class="form-control allowance float"
                               value="{{ $monthlySalary->transportation_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Food Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Food Allowance</label>
                        <input type="text" name="food_allowance" id="food_allowance"
                               class="form-control allowance float"
                               value="{{ $monthlySalary->food_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Phone Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Phone Allowance</label>
                        <input type="text" name="phone_allowance" id="phone_allowance"
                               class="form-control allowance float"
                               value="{{ $monthlySalary->phone_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Other Allowance -->
                    <div class="col-md-4">
                        <label class="form-label">Other Allowance</label>
                        <input type="text" name="other_allowance" id="other_allowance"
                               class="form-control allowance float"
                               value="{{ $monthlySalary->other_allowance ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Overtime Hours -->
                    <div class="col-md-4">
                        <label class="form-label">Overtime Hours</label>
                        <input type="text" name="overtime_hours" id="overtime_hours" class="form-control float"
                               value="{{ $monthlySalary->overtime_hours ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Overtime Amount -->
                    <div class="col-md-4">
                        <label class="form-label">Overtime Amount</label>
                        <input type="text" name="overtime_amount" id="overtime_amount"
                               class="form-control addition float"
                               value="{{ $monthlySalary->overtime_amount ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Bonus -->
                    <div class="col-md-4">
                        <label class="form-label">Bonus</label>
                        <input type="text" name="bonus" id="bonus" class="form-control addition float"
                               value="{{ $monthlySalary->bonus ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Deductions -->
                    <div class="col-md-4">
                        <label class="form-label">Deductions</label>
                        <input type="text" name="deductions" id="deductions" class="form-control deduction float"
                               value="{{ $monthlySalary->deductions ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Loan Deduction -->
                    <div class="col-md-4">
                        <label class="form-label">Loan Deduction</label>
                        <input type="text" name="loan_deduction" id="loan_deduction"
                               class="form-control deduction float"
                               value="{{ $monthlySalary->loan_deduction ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Total Salary -->
                    <div class="col-md-4">
                        <label class="form-label required">Total Salary <sup class="text-danger">*</sup></label>
                        <input type="text" name="total_salary" id="total_salary" class="form-control float"
                               value="{{ $monthlySalary->total_salary ?? 0 }}" min="0" step="0.01" required readonly disabled>
                    </div>

                    <!-- Payment Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Payment Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="payment_date" name="payment_date" class="form-control datepicker"
                               value="{{ isset($monthlySalary) ? showDate($monthlySalary->payment_date) : '' }}"
                               required>
                    </div>

                    <!-- Payment Method -->
                    <div class="col-md-4">
                        <label class="form-label required">Payment Method <sup class="text-danger">*</sup></label>
                        <select name="payment_method" class="tom-select" required>
                            <option value="">Select Payment Method</option>
                            @foreach($paymentMethods as $method)
                                <option
                                    value="{{ $method }}" @selected(isset($monthlySalary) && $monthlySalary->payment_method == $method)>
                                    {{ ucwords(str_replace('_', ' ', $method)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4">
                        <label class="form-label required">Status <sup class="text-danger">*</sup></label>
                        <select name="status" class="tom-select" required>
                            <option
                                value="pending" @selected(isset($monthlySalary) && $monthlySalary->status == 'pending')>
                                Pending
                            </option>
                            <option value="paid" @selected(isset($monthlySalary) && $monthlySalary->status == 'paid')>
                                Paid
                            </option>
                            <option
                                value="cancelled" @selected(isset($monthlySalary) && $monthlySalary->status == 'cancelled')>
                                Cancelled
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
                      placeholder="Any additional information...">{{ $monthlySalary->remarks ?? '' }}</textarea>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {


        // Fetch basic salary details
        document.getElementById('fetch-basic-salary').addEventListener('click', function () {
            let employeeId = document.getElementById('employee_id').value;
            if (!employeeId) {
                alert('Please select an employee first');
                return;
            }

            // Make AJAX request to get basic salary details
            fetch('/payroll/monthly/salary/get-employee-basic-salary', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({employee_id: employeeId})
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate form fields with basic salary details
                        document.getElementById('basic_salary').value = data.data.basic_salary;
                        document.getElementById('housing_allowance').value = data.data.housing_allowance;
                        document.getElementById('transportation_allowance').value = data.data.transportation_allowance;
                        document.getElementById('food_allowance').value = data.data.food_allowance;
                        document.getElementById('phone_allowance').value = data.data.phone_allowance;
                        document.getElementById('other_allowance').value = data.data.other_allowance;

                        // Recalculate total
                        calculateTotalSalary();
                    } else {
                        alert(data.message || 'Failed to fetch basic salary details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching basic salary details');
                });
        });

        // Calculate initial total
        calculateTotalSalary();
    });
</script>
