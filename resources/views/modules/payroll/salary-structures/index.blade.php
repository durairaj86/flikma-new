@section('page-title', __('Salary Structures'))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

<div id="payrollAjaxRoot">
    <div class="container-fluid px-2 pt-4 pt-md-0">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('payroll.dashboard') }}">{{ __('Payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Salary Structures') }}</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold text-dark mb-0">{{ __('Salary Structures') }}</h1>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSalaryModal">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Salary Structure') }}
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small fw-bold text-uppercase text-secondary">
                            <th class="px-4 py-3">{{ __('Employee') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Basic') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Housing') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Transport') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Other') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Total') }}</th>
                            <th class="px-4 py-3">{{ __('Effective From') }}</th>
                            <th class="px-4 py-3">{{ __('Effective To') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($structures as $structure)
                            <tr>
                                <td class="px-4 py-2 fw-bold text-dark">{{ $structure->employee->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-end tabular-nums">{{ number_format($structure->basic_salary, 2) }}</td>
                                <td class="px-4 py-2 text-end tabular-nums">{{ number_format($structure->housing_allowance, 2) }}</td>
                                <td class="px-4 py-2 text-end tabular-nums">{{ number_format($structure->transportation_allowance, 2) }}</td>
                                <td class="px-4 py-2 text-end tabular-nums">{{ number_format($structure->other_allowances, 2) }}</td>
                                <td class="px-4 py-2 text-end tabular-nums fw-bold">{{ number_format($structure->total_salary, 2) }}</td>
                                <td class="px-4 py-2">{{ $structure->effective_from->format('d-m-Y') }}</td>
                                <td class="px-4 py-2">
                                    @if($structure->effective_to)
                                        {{ $structure->effective_to->format('d-m-Y') }}
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ __('Current') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm edit-salary-btn"
                                            data-bs-toggle="modal" data-bs-target="#editSalaryModal"
                                            data-update-url="{{ route('payroll.salary-structures.update', $structure) }}"
                                            data-employee="{{ $structure->employee->name ?? '' }}"
                                            data-basic="{{ $structure->basic_salary }}"
                                            data-housing="{{ $structure->housing_allowance }}"
                                            data-transport="{{ $structure->transportation_allowance }}"
                                            data-other="{{ $structure->other_allowances }}"
                                            data-from="{{ $structure->effective_from->format('d-m-Y') }}"
                                            data-to="{{ $structure->effective_to?->format('d-m-Y') }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('payroll.salary-structures.destroy', $structure) }}" method="POST" class="d-inline" data-confirm="{{ __('Delete this salary structure?') }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm ms-1"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-5 text-center text-muted">
                                    <i class="bi bi-cash-stack display-6 d-block mb-2 opacity-25"></i>
                                    {{ __('No salary structures created yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $structures->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- Create Salary Structure Modal --}}
    <div class="modal fade" id="createSalaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('payroll.salary-structures.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('New Salary Structure') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold required">{{ __('Employee') }}</label>
                            <x-common.employees name="employee_id" placeholder="{{ __('Select employee...') }}" :required="true" :active-only="true" />
                            <div class="form-text small">{{ __('Any currently open structure for this employee will be closed out on the day before this one starts.') }}</div>
                        </div>
                        @include('modules.payroll.salary-structures._fields')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Salary Structure Modal --}}
    <div class="modal fade" id="editSalaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editSalaryForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit Salary Structure') }} — <span id="editSalaryEmployee"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        @include('modules.payroll.salary-structures._fields', ['prefix' => 'edit'])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // The modals sit outside #content-wrapper, so the global TomSelect init skips them; init here (also re-run after in-place saves).
        (function () {
            var go = function () { if (typeof initTomSelectForm === 'function') { initTomSelectForm($('#payrollAjaxRoot')); } };
            if (document.readyState === 'complete') { go(); } else { window.addEventListener('load', go); }
        })();

        function setDateFieldValue(id, value) {
            const el = document.getElementById(id);
            if (el._flatpickr) {
                el._flatpickr.setDate(value || null, true);
            } else {
                el.value = value || '';
            }
        }

        document.querySelectorAll('.edit-salary-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('editSalaryForm').action = btn.dataset.updateUrl;
                document.getElementById('editSalaryEmployee').textContent = btn.dataset.employee;
                document.getElementById('edit_salary_basic').value = btn.dataset.basic;
                document.getElementById('edit_salary_housing').value = btn.dataset.housing;
                document.getElementById('edit_salary_transport').value = btn.dataset.transport;
                document.getElementById('edit_salary_other').value = btn.dataset.other;
                setDateFieldValue('edit_salary_from', btn.dataset.from);
                setDateFieldValue('edit_salary_to', btn.dataset.to);
            });
        });
    </script>
@include('modules.payroll._ajax')
</div>
</div>
</x-app-layout>
