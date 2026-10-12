@section('page-title', 'Edit Loan')
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Edit Loan</h1>
        </div>
        <div>
            <a href="{{ route('employee-loans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('employee-loans.update', $employeeLoan) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Employee</label>
                        @if($employeeLoan->hasDeductions())
                            {{-- A disabled select does not submit, so the id travels in a hidden field. --}}
                            <input type="hidden" name="employee_id" value="{{ $employeeLoan->employee_id }}">
                            <x-common.employees name="employee_id_locked" :value="$employeeLoan->employee_id" :placeholder="null" disabled />
                            <div class="form-text small text-muted"><i class="bi bi-lock-fill me-1"></i>{{ __('Locked — instalments have already been deducted from this employee.') }}</div>
                        @else
                            <x-common.employees name="employee_id" :value="$employeeLoan->employee_id" :placeholder="null" :required="true" />
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Loan Amount</label>
                        <input type="text" name="loan_amount" class="decimal form-control{{ $employeeLoan->hasDeductions() ? ' bg-light' : '' }}" step="0.01" min="1" value="{{ $employeeLoan->loan_amount }}" required {{ $employeeLoan->hasDeductions() ? 'readonly' : '' }}>
                        @if($employeeLoan->hasDeductions())
                            <div class="form-text small text-muted"><i class="bi bi-lock-fill me-1"></i>{{ __('Locked — instalments have already been deducted.') }}</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Total Installments</label>
                        <input type="text" name="total_installments" class="integer form-control" min="1" value="{{ $employeeLoan->total_installments }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Installment Amount</label>
                        <input type="text" name="installment_amount" class="decimal form-control" step="0.01" min="1" value="{{ $employeeLoan->installment_amount }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Start Date</label>
                        <input type="text" name="start_date" class="form-control datepicker" value="{{ $employeeLoan->start_date->format('d-m-Y') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">End Date</label>
                        <input type="text" name="end_date" class="form-control datepicker" value="{{ $employeeLoan->end_date?->format('d-m-Y') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select tom-select" required>
                            <option value="active" {{ $employeeLoan->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="closed" {{ $employeeLoan->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="cancelled" {{ $employeeLoan->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $employeeLoan->description }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold required">Reason for update</label>
                        <textarea name="update_reason" class="form-control @error('update_reason') is-invalid @enderror" rows="2" minlength="3" maxlength="500" placeholder="Why are you changing this loan?" required>{{ old('update_reason') }}</textarea>
                        @error('update_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Update</button>
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
