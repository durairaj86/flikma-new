<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $employeeLoan->row_no ?? 'New Employee Loan' }}</span>
            </div>
        </div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Employee Loan">
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" id="data-id" value="{{ $employeeLoan->id ?? '' }}">

        <!-- Employee Loan Header -->
        <div class="mb-4 mt-3 border-0 px-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Employee -->
                    <div class="col-md-6">
                        <label class="form-label required">Employee <sup class="text-danger">*</sup></label>
                        <x-common.employee value="{{ $employeeLoan->employee_id ?? '' }}"></x-common.employee>
                    </div>

                    <!-- Loan Amount -->
                    <div class="col-md-6">
                        <label class="form-label required">Loan Amount <sup class="text-danger">*</sup></label>
                        <input type="text" name="loan_amount" id="loan_amount" class="form-control float"
                               value="{{ $employeeLoan->loan_amount ?? 0 }}" min="0" step="0.01" required>
                    </div>
                </div>

                <!-- Loan Details Section -->
                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Loan Details</h5>
                    </div>

                    <!-- Interest Rate -->
                    <div class="col-md-4">
                        <label class="form-label">Interest Rate (%)</label>
                        <input type="text" name="interest_rate" id="interest_rate" class="form-control float"
                               value="{{ $employeeLoan->interest_rate ?? 0 }}" min="0" step="0.01">
                    </div>

                    <!-- Number of Installments -->
                    <div class="col-md-4">
                        <label class="form-label required">Number of Installments <sup class="text-danger">*</sup></label>
                        <input type="text" name="number_of_installments" id="number_of_installments" class="form-control integer"
                               value="{{ $employeeLoan->number_of_installments ?? 1 }}" min="1" step="1" required>
                    </div>

                    <!-- Installment Amount -->
                    <div class="col-md-4">
                        <label class="form-label required">Installment Amount <sup class="text-danger">*</sup></label>
                        <input type="text" name="installment_amount" id="installment_amount" class="form-control float"
                               value="{{ $employeeLoan->installment_amount ?? 0 }}" min="0" step="0.01" required readonly disabled>
                    </div>

                    <!-- Loan Date -->
                    <div class="col-md-4">
                        <label class="form-label required">Loan Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="loan_date" name="loan_date" class="form-control datepicker"
                               value="{{ isset($employeeLoan) ? showDate($employeeLoan->loan_date) : '' }}" required>
                    </div>

                    <!-- First Payment Date -->
                    <div class="col-md-4">
                        <label class="form-label required">First Payment Date <sup class="text-danger">*</sup></label>
                        <input type="date" id="first_payment_date" name="first_payment_date" class="form-control datepicker"
                               value="{{ isset($employeeLoan) ? showDate($employeeLoan->first_payment_date) : '' }}" required>
                    </div>

                    <!-- Payment Method -->
                    <div class="col-md-4">
                        <label class="form-label required">Payment Method <sup class="text-danger">*</sup></label>
                        <select name="payment_method" class="tom-select" required>
                            <option value="">Select Payment Method</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method }}" @selected(isset($employeeLoan) && $employeeLoan->payment_method == $method)>
                                    {{ ucwords(str_replace('_', ' ', $method)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Remaining Amount -->
                    <div class="col-md-4">
                        <label class="form-label required">Remaining Amount <sup class="text-danger">*</sup></label>
                        <input type="text" name="remaining_amount" id="remaining_amount" class="form-control float"
                               value="{{ $employeeLoan->remaining_amount ?? 0 }}" min="0" step="0.01" required>
                    </div>

                    <!-- Remaining Installments -->
                    <div class="col-md-4">
                        <label class="form-label required">Remaining Installments <sup class="text-danger">*</sup></label>
                        <input type="text" name="remaining_installments" id="remaining_installments" class="form-control integer"
                               value="{{ $employeeLoan->remaining_installments ?? 0 }}" min="0" step="1" required>
                    </div>

                    <!-- Purpose -->
                    <div class="col-md-12">
                        <label class="form-label">Purpose</label>
                        <textarea name="purpose" class="form-control h-75" rows="3"
                                  placeholder="Purpose of the loan...">{{ $employeeLoan->purpose ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        <div class="mt-3 px-4">
            <label class="form-label fw-semibold">Remarks</label>
            <textarea name="remarks" class="form-control h-100" rows="4"
                      placeholder="Any additional information...">{{ $employeeLoan->remarks ?? '' }}</textarea>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate installment amount
        function calculateInstallmentAmount() {
            let loanAmount = parseFloat(document.getElementById('loan_amount').value) || 0;
            let interestRate = parseFloat(document.getElementById('interest_rate').value) || 0;
            let numberOfInstallments = parseInt(document.getElementById('number_of_installments').value) || 1;

            // Calculate total amount with interest
            let totalAmount = loanAmount * (1 + (interestRate / 100));

            // Calculate installment amount
            let installmentAmount = totalAmount / numberOfInstallments;

            document.getElementById('installment_amount').value = installmentAmount.toFixed(2);

            // Set initial remaining values for new loans
            if (!document.querySelector('input[name="data-id"]').value) {
                document.getElementById('remaining_amount').value = loanAmount.toFixed(2);
                document.getElementById('remaining_installments').value = numberOfInstallments;
            }
        }

        // Add event listeners to fields that affect the installment amount
        document.getElementById('loan_amount').addEventListener('input', calculateInstallmentAmount);
        document.getElementById('interest_rate').addEventListener('input', calculateInstallmentAmount);
        document.getElementById('number_of_installments').addEventListener('input', calculateInstallmentAmount);

        // Calculate initial installment amount
        calculateInstallmentAmount();
    });
</script>
