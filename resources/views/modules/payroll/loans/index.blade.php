@section('page-title', 'Employee Loans')
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

<div id="payrollAjaxRoot">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Employee Loans</h1>
            <p class="text-muted small mb-0">Manage employee loan records.</p>
        </div>
        <div>
            <a href="{{ route('employee-loans.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> New Loan
            </a>
        </div>
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
    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <x-common.employees name="employee_id" class="form-select-sm" :value="request('employee_id')" placeholder="All Employees" />
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm tom-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small fw-bold text-uppercase text-secondary">
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Loan Amount</th>
                        <th class="px-4 py-3">Installments</th>
                        <th class="px-4 py-3">Paid</th>
                        <th class="px-4 py-3">Remaining</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="small text-dark">
                    @forelse($loans as $loan)
                        <tr>
                            <td class="px-4 py-2">{{ $loan->employee->name }}</td>
                            <td class="px-4 py-2">{{ number_format($loan->loan_amount, 2) }}</td>
                            <td class="px-4 py-2">{{ $loan->installments_count }}/{{ $loan->total_installments }}</td>
                            <td class="px-4 py-2">{{ number_format($loan->paid_amount, 2) }}</td>
                            <td class="px-4 py-2">{{ number_format($loan->remaining_amount, 2) }}</td>
                            <td class="px-4 py-2">
                                @if($loan->status == 'active')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Active</span>
                                @elseif($loan->status == 'closed')
                                    <span class="badge bg-success-subtle text-success border">Closed</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-end">
                                <a href="{{ route('employee-loans.show', $loan) }}" class="btn btn-outline-secondary btn-sm rounded-circle opacity-75 me-1"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('employee-loans.edit', $loan) }}" class="btn btn-outline-secondary btn-sm rounded-circle opacity-75 me-1"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('employee-loans.destroy', $loan) }}" method="POST" class="d-inline" data-confirm="Delete this loan?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-secondary btn-sm rounded-circle opacity-75"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-5 text-center text-muted">
                                <i class="bi bi-cash-stack display-6 d-block mb-2 opacity-25"></i>
                                No loans found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
            <div class="card-footer bg-white border-top-0 px-4 py-3">
                {{ $loans->links() }}
            </div>
        @endif
    </div>
<script>
    // TomSelect for this page's dropdowns (content sits outside #content-wrapper; also re-runs after in-place swaps).
    (function () {
        var go = function () { if (typeof initTomSelectForm === 'function') { initTomSelectForm($('#payrollAjaxRoot')); } };
        if (document.readyState === 'complete') { go(); } else { window.addEventListener('load', go); }
    })();
</script>
@include('modules.payroll._ajax')
</div>
</div>
</x-app-layout>
