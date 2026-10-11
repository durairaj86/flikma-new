@section('page-title', 'New Loan')
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">New Employee Loan</h1>
        </div>
        <div>
            <a href="{{ route('employee-loans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('employee-loans.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Employee</label>
                        <x-common.employees name="employee_id" placeholder="Select Employee" :required="true" :active-only="true" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Loan Amount</label>
                        <input type="number" name="loan_amount" class="form-control" step="0.01" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Total Installments</label>
                        <input type="number" name="total_installments" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Installment Amount</label>
                        <input type="number" name="installment_amount" class="form-control" step="0.01" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Start Date</label>
                        <input type="text" name="start_date" class="form-control datepicker" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">End Date</label>
                        <input type="text" name="end_date" class="form-control datepicker">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Create Loan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<script>
    // TomSelect for this page's dropdowns (content sits outside #content-wrapper; also re-runs after in-place swaps).
    (function () {
        var go = function () { if (typeof initTomSelectForm === 'function') { initTomSelectForm($('form')); } };
        if (document.readyState === 'complete') { go(); } else { window.addEventListener('load', go); }
    })();
</script>
@include('modules.payroll.loans._calc')
</div>
</x-app-layout>
