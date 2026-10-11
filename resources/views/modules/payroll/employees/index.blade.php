@section('page-title', __('Employee Payroll Profiles'))
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
                        <li class="breadcrumb-item active">{{ __('Employees') }}</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold text-dark mb-0">{{ __('Employee Payroll Profiles') }}</h1>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small fw-bold text-uppercase text-secondary">
                            <th class="px-4 py-3">{{ __('Employee') }}</th>
                            <th class="px-4 py-3">{{ __('Employment Type') }}</th>
                            <th class="px-4 py-3">{{ __('Shift') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Salary Structure') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($employees as $emp)
                            <tr>
                                <td class="px-4 py-2 fw-bold text-dark">
                                    {{ $emp->name }}
                                    <div class="text-muted extra-small fw-normal">{{ $emp->department }}</div>
                                </td>
                                <td class="px-4 py-2 text-capitalize">{{ __(str_replace('_', ' ', $emp->employment_type)) }}</td>
                                <td class="px-4 py-2">{{ $emp->shift->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if($emp->has_current_salary)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ __('Set') }}</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ __('Missing') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-end">
                                    <a href="{{ route('payroll.employees.show', $emp) }}" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-1" title="{{ __('View') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('payroll.employees.edit', $emp) }}" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm" title="{{ __('Edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-5 text-center text-muted">
                                    <i class="bi bi-people display-6 d-block mb-2 opacity-25"></i>
                                    {{ __('No employees found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $employees->links('pagination::bootstrap-5') }}
        </div>
    </div>
@include('modules.payroll._ajax')
</div>
</div>
</x-app-layout>
