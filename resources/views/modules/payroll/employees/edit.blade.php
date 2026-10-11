@section('page-title', __('Edit Payroll Profile: :name', ['name' => $user->name]))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

    <div class="container-fluid px-2 pt-4 pt-md-0">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('payroll.dashboard') }}">{{ __('Payroll') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll.employees.index') }}">{{ __('Employees') }}</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold text-dark mb-0">{{ __('Payroll Profile') }}: {{ $user->name }}</h1>
            </div>
            <a href="{{ route('payroll.employees.index') }}" class="btn btn-outline-secondary shadow-sm d-flex align-items-center" style="height: 38px;">
                <i class="bi bi-list-ul me-1"></i> {{ __('Back to list') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('payroll.employees.update', $user) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold required">{{ __('Employment Type') }}</label>
                                    <select name="employment_type" class="form-select" required>
                                        @foreach(['full_time' => __('Full-time'), 'part_time' => __('Part-time'), 'contract' => __('Contract'), 'hourly' => __('Hourly')] as $value => $label)
                                            <option value="{{ $value }}" {{ old('employment_type', $user->employment_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('Shift') }}</label>
                                    <select name="shift_id" class="form-select">
                                        <option value="">{{ __('No shift assigned') }}</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id', $user->shift_id) == $shift->id ? 'selected' : '' }}>{{ $shift->name }} ({{ __($shift->type) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('Reporting Manager') }}</label>
                                    <select name="reporting_manager_id" class="form-select">
                                        <option value="">{{ __('None') }}</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('reporting_manager_id', $user->reporting_manager_id) == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('National ID / Iqama') }}</label>
                                    <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $user->national_id) }}">
                                </div>

                                <div class="col-12"><hr class="my-2"></div>
                                <div class="col-12">
                                    <h6 class="fw-bold text-uppercase small text-muted">{{ __('Bank Details') }}</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('Bank Name') }}</label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $user->bank_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('IBAN') }}</label>
                                    <input type="text" name="iban" class="form-control" value="{{ old('iban', $user->iban) }}" placeholder="SA00 0000 0000 0000 0000 0000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('GOSI Number') }}</label>
                                    <input type="text" name="gosi_number" class="form-control" value="{{ old('gosi_number', $user->gosi_number) }}">
                                </div>

                                <div class="col-12"><hr class="my-2"></div>
                                <div class="col-12">
                                    <h6 class="fw-bold text-uppercase small text-muted">{{ __('Emergency Contact') }}</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('Contact Name') }}</label>
                                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('Contact Phone') }}</label>
                                    <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('payroll.employees.index') }}" class="btn btn-light border text-secondary">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary shadow-sm px-4">{{ __('Save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">{{ __('Current Salary Structure') }}</div>
                    <div class="card-body">
                        @php($current = $user->currentSalaryStructure())
                        @if($current)
                            <dl class="row mb-0 small">
                                <dt class="col-7 text-muted">{{ __('Basic') }}</dt>
                                <dd class="col-5 text-end fw-bold">{{ number_format($current->basic_salary, 2) }}</dd>
                                <dt class="col-7 text-muted">{{ __('Housing') }}</dt>
                                <dd class="col-5 text-end">{{ number_format($current->housing_allowance, 2) }}</dd>
                                <dt class="col-7 text-muted">{{ __('Transportation') }}</dt>
                                <dd class="col-5 text-end">{{ number_format($current->transportation_allowance, 2) }}</dd>
                                <dt class="col-7 text-muted">{{ __('Other') }}</dt>
                                <dd class="col-5 text-end">{{ number_format($current->other_allowances, 2) }}</dd>
                                <dt class="col-7 fw-bold">{{ __('Total') }}</dt>
                                <dd class="col-5 text-end fw-bold text-success">{{ number_format($current->total_salary, 2) }}</dd>
                            </dl>
                        @else
                            <p class="text-muted small mb-2">{{ __('No active salary structure.') }}</p>
                            <a href="{{ route('payroll.salary-structures.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Set up salary structure') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
