@section('page-title', __('Payroll Runs'))
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
                        <li class="breadcrumb-item active">{{ __('Payroll Runs') }}</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold text-dark mb-0">{{ __('Payroll Runs') }} — {{ $monthLabel }}</h1>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('payroll.runs.store') }}" id="runAllPayrollForm"
                      data-confirm="{{ __('Generate payroll for all :count employees for :month? Employees already generated or missing a salary structure are skipped automatically.', ['count' => $employees->count(), 'month' => $monthLabel]) }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    @foreach($employees as $emp)
                        <input type="hidden" name="employee_ids[]" value="{{ $emp->id }}">
                    @endforeach
                    <button type="submit" class="btn btn-outline-primary btn-sm" {{ $employees->isEmpty() ? 'disabled' : '' }}>
                        <i class="bi bi-people me-1"></i>{{ __('Run Payroll for All Employees') }}
                    </button>
                </form>
                <a href="{{ route('payroll.runs.create', ['month' => $month, 'year' => $year]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>{{ __('Generate Payroll') }}
                </a>
            </div>
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('payroll.runs.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">{{ __('Month') }}</label>
                        <select name="month" class="form-select tom-select" onchange="this.form.requestSubmit()">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ \Illuminate\Support\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">{{ __('Year') }}</label>
                        <select name="year" class="form-select tom-select" onchange="this.form.requestSubmit()">
                            @foreach(range(now()->year - 1, now()->year) as $y)
                                <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">{{ __('Employee') }}</label>
                        <x-common.employees name="employee_id" :value="request('employee_id')" placeholder="All Employees" onchange="this.form.requestSubmit()" />
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white fw-bold small text-uppercase text-muted py-3">
                {{ __('Records for :month', ['month' => $monthLabel]) }}
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small fw-bold text-uppercase text-secondary">
                            <th class="px-4 py-3">{{ __('Employee') }}</th>
                            <th class="px-4 py-3">{{ __('Payroll #') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Net Payable') }}</th>
                            <th class="px-4 py-3">{{ __('Status') }}</th>
                            <th class="px-4 py-3">{{ __('Paid Via') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($records as $record)
                            <tr>
                                <td class="px-4 py-2 fw-bold text-dark">
                                    {{ $record->employee->name ?? '—' }}
                                    <div class="text-muted extra-small fw-normal">{{ $record->employee->department ?? '' }}</div>
                                </td>
                                <td class="px-4 py-2 tabular-nums">{{ $record->payroll_number }}</td>
                                <td class="px-4 py-2 text-end tabular-nums fw-bold">{{ number_format($record->net_payable, 2) }}</td>
                                <td class="px-4 py-2">
                                    <span @class(['badge rounded-pill px-2 py-1 border', 'bg-success-subtle text-success border-success-subtle' => $record->status === 'paid',
                                            'bg-warning-subtle text-warning-emphasis border-warning-subtle' => $record->status === 'draft',
                                            'bg-secondary-subtle text-secondary border-secondary-subtle' => $record->status === 'cancelled',])>
                                        {{ __(ucfirst($record->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-muted">
                                    @if($record->status === 'paid')
                                        <i class="bi bi-journal-text me-1"></i>{{ __('Payroll Voucher') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-end">
                                    <a href="{{ route('payroll.runs.show', $record) }}" class="btn btn-outline-secondary btn-sm rounded-circle opacity-75 me-1" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('payroll.runs.slip', $record) }}" onclick="printReport(this.href); return false;" class="btn btn-outline-secondary btn-sm rounded-circle opacity-75 me-1" title="{{ __('Print Salary Slip') }}"><i class="bi bi-printer"></i></a>
                                    @if($record->status === 'draft')
                                        <button type="button" class="btn btn-primary btn-sm shadow-sm pay-btn"
                                                data-bs-toggle="modal" data-bs-target="#paySalaryModal"
                                                data-pay-url="{{ route('payroll.runs.pay', $record) }}"
                                                data-employee="{{ $record->employee->name ?? '' }}"
                                                data-net-payable="{{ number_format($record->net_payable, 2) }}">
                                            <i class="bi bi-cash-coin me-1"></i>{{ __('Pay') }}
                                        </button>
                                        <form method="POST" action="{{ route('payroll.runs.destroy', $record) }}" class="d-inline" data-confirm="{{ __('Delete this payroll record?') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm shadow-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @elseif($record->status === 'paid')
                                        <form method="POST" action="{{ route('payroll.runs.disapprove', $record) }}" class="d-inline"
                                              data-confirm="{{ __('Disapprove this salary payment? The payment entries are removed and the record goes back to draft.') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-sm shadow-sm">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Disapprove') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-5 text-center text-muted">
                                    <i class="bi bi-cash-stack display-6 d-block mb-2 opacity-25"></i>
                                    {{ __('No payroll records for this month yet.') }}
                                    <div class="mt-2">
                                        <a href="{{ route('payroll.runs.create', ['month' => $month, 'year' => $year]) }}" class="btn btn-primary btn-sm">
                                            {{ __('Generate Payroll') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pay Salary Modal --}}
    <div class="modal fade" id="paySalaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="paySalaryForm" method="POST">
                    @csrf
                    <div class="modal-header d-flex align-items-center justify-content-between">
                        <h5 class="modal-title fw-bold mb-0">{{ __('Pay Salary') }} — <span id="paySalaryEmployee"></span></h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light border btn-sm d-flex align-items-center gap-1" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                                <i class="bi bi-check-lg"></i> {{ __('Confirm Payment') }}
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border small mb-3">
                            {{ __('Net Payable:') }} <strong id="paySalaryNetPayable" class="tabular-nums"></strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold required">{{ __('Pay From Account') }}</label>
                            <select name="bank_account_id" class="form-select tom-select" data-live-search="true" required>
                                <option value="">{{ __('Select account...') }}</option>
                                @foreach($paymentAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold required">{{ __('Payment Date') }}</label>
                            <input type="text" name="payment_date" value="{{ now()->format('d-m-Y') }}" class="form-control datepicker">
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold">{{ __('Description') }}</label>
                            <textarea name="description" rows="2" class="form-control" placeholder="{{ __('Optional — defaults to a standard salary payment note.') }}"></textarea>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Page content sits outside #content-wrapper, so init TomSelect here (runs on load and after every in-place swap).
        (function () {
            var go = function () { if (typeof initTomSelectForm === 'function') { initTomSelectForm($('#payrollAjaxRoot')); } };
            if (document.readyState === 'complete') { go(); } else { window.addEventListener('load', go); }
        })();
        document.querySelectorAll('.pay-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('paySalaryForm').action = btn.dataset.payUrl;
                document.getElementById('paySalaryEmployee').textContent = btn.dataset.employee;
                document.getElementById('paySalaryNetPayable').textContent = btn.dataset.netPayable;
            });
        });
    </script>
@include('modules.payroll._ajax')
</div>
</div>
</x-app-layout>
