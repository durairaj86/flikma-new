@php($prefix = $prefix ?? '')
@php($id = fn($name) => $prefix ? $prefix . '_salary_' . $name : 'salary_' . $name)

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label small fw-bold required">{{ __('Basic Salary') }}</label>
        <input type="number" step="0.01" min="0" name="basic_salary" id="{{ $id('basic') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">{{ __('Housing Allowance') }}</label>
        <input type="number" step="0.01" min="0" name="housing_allowance" id="{{ $id('housing') }}" class="form-control" value="0">
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">{{ __('Transportation Allowance') }}</label>
        <input type="number" step="0.01" min="0" name="transportation_allowance" id="{{ $id('transport') }}" class="form-control" value="0">
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">{{ __('Other Allowances') }}</label>
        <input type="number" step="0.01" min="0" name="other_allowances" id="{{ $id('other') }}" class="form-control" value="0">
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold required">{{ __('Effective From') }}</label>
        <input type="text" name="effective_from" id="{{ $id('from') }}" class="form-control datepicker" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">{{ __('Effective To') }}</label>
        <input type="text" name="effective_to" id="{{ $id('to') }}" class="form-control datepicker">
    </div>
</div>
