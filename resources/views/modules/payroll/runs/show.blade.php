@section('page-title', __('Payroll') . ' ' . $record->payroll_number)
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

@php
    $period = \Illuminate\Support\Carbon::create($record->year, $record->month, 1)->translatedFormat('F Y');
    $statusClass = ['paid' => 'bg-success-subtle text-success border-success-subtle', 'draft' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle'][$record->status] ?? 'bg-secondary-subtle text-secondary border-secondary-subtle';
    $money = fn ($v) => number_format((float) $v, 2);
@endphp
<div class="container-fluid px-2 pt-4 pt-md-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('payroll.dashboard') }}">{{ __('Payroll') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payroll.runs.index', ['month' => $record->month, 'year' => $record->year]) }}">{{ __('Payroll Runs') }}</a></li>
                    <li class="breadcrumb-item active">{{ $record->payroll_number }}</li>
                </ol>
            </nav>
            <h1 class="h4 fw-bold text-dark mb-0">
                {{ $record->payroll_number }}
                <span class="badge rounded-pill px-2 py-1 border fs-6 ms-2 {{ $statusClass }}">{{ __(ucfirst($record->status)) }}</span>
            </h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.runs.slip', $record) }}" onclick="printReport(this.href); return false;" class="btn btn-primary shadow-sm"><i class="bi bi-printer me-1"></i>{{ __('Print Salary Slip') }}</a>
            <a href="{{ route('payroll.runs.index', ['month' => $record->month, 'year' => $record->year]) }}" class="btn btn-light border shadow-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="fw-bold mb-3">{{ __('Employee') }}</h6>
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">{{ __('Name') }}</dt><dd class="col-7 fw-semibold">{{ $record->employee->name ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('Employee Code') }}</dt><dd class="col-7">{{ $record->employee->employee_code ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('Department') }}</dt><dd class="col-7">{{ $record->employee->department ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('Pay Period') }}</dt><dd class="col-7">{{ $period }}</dd>
                    <dt class="col-5 text-muted fw-normal">{{ __('Generated On') }}</dt><dd class="col-7">{{ $record->created_at?->format('d-m-Y H:i') }}</dd>
                </dl>
            </div></div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="fw-bold mb-3">{{ __('Payment') }}</h6>
                @if($record->isPaid())
                    <dl class="row small mb-0">
                        <dt class="col-5 text-muted fw-normal">{{ __('Paid On') }}</dt><dd class="col-7">{{ $record->payment_date?->format('d-m-Y') }}</dd>
                        <dt class="col-5 text-muted fw-normal">{{ __('Processed By') }}</dt><dd class="col-7 fw-semibold">{{ $payer->name ?? '—' }}</dd>
                        <dt class="col-5 text-muted fw-normal">{{ __('Paid From') }}</dt><dd class="col-7">{{ $record->bankAccount ? $record->bankAccount->code . ' — ' . $record->bankAccount->name : '—' }}</dd>
                        <dt class="col-5 text-muted fw-normal">{{ __('Paid Via') }}</dt>
                        <dd class="col-7"><i class="bi bi-journal-text me-1"></i>{{ __('Payroll Voucher') }} {{ $record->payroll_number }}</dd>
                        <dt class="col-5 text-muted fw-normal">{{ __('Description') }}</dt>
                        <dd class="col-7">{{ $entries->first()->description ?? '—' }}</dd>
                    </dl>
                @else
                    <p class="text-muted small mb-0">{{ __('Not paid yet.') }}</p>
                @endif
            </div></div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="fw-bold mb-3">{{ __('Salary Breakdown') }}</h6>
                <table class="table table-sm small mb-0 align-middle">
                    <tbody>
                        <tr><td>{{ __('Basic Salary') }}</td><td class="text-end tabular-nums">{{ $money($record->basic_salary) }}</td></tr>
                        <tr><td>{{ __('Housing Allowance') }}</td><td class="text-end tabular-nums">{{ $money($record->housing_allowance) }}</td></tr>
                        <tr><td>{{ __('Transportation Allowance') }}</td><td class="text-end tabular-nums">{{ $money($record->transportation_allowance) }}</td></tr>
                        <tr><td>{{ __('Other Allowances') }}</td><td class="text-end tabular-nums">{{ $money($record->other_allowances) }}</td></tr>
                        <tr class="fw-bold table-light"><td>{{ __('Total Earnings') }}</td><td class="text-end tabular-nums">{{ $money($record->total_earnings) }}</td></tr>
                        <tr class="text-danger"><td>{{ __('Loan Deduction') }}</td><td class="text-end tabular-nums">− {{ $money($record->loan_deduction) }}</td></tr>
                        <tr class="text-danger"><td>{{ __('Other Deductions') }}</td><td class="text-end tabular-nums">− {{ $money($record->other_deductions) }}</td></tr>
                        <tr class="fw-bold table-light"><td>{{ __('Total Deductions') }}</td><td class="text-end tabular-nums">{{ $money($record->total_deductions) }}</td></tr>
                        <tr class="fw-bold fs-6"><td>{{ __('Net Payable') }}</td><td class="text-end tabular-nums">{{ $money($record->net_payable) }}</td></tr>
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="fw-bold mb-3">{{ __('Loan') }}</h6>
                @if((float) $record->loan_deduction > 0)
                    <p class="small mb-2">
                        {{ __('Withheld from this salary') }}: <strong>{{ $money($record->loan_deduction) }}</strong>
                        @if($record->isPaid())
                            · {{ __('Recorded against the loan') }}: <strong>{{ $money($recovered) }}</strong>
                        @endif
                    </p>
                @else
                    <p class="text-muted small mb-2">{{ __('No loan deduction in this salary.') }}</p>
                @endif
                @forelse($loans as $loan)
                    <div class="border rounded p-2 mb-2 small">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('employee-loans.edit', $loan) }}" class="fw-semibold">{{ __('Loan') }} #{{ $loan->id }}</a>
                            <span class="badge bg-light text-dark border">{{ __(ucfirst($loan->status)) }}</span>
                        </div>
                        <div class="text-muted">
                            {{ __('Amount') }} {{ $money($loan->loan_amount) }} ·
                            {{ __('Instalment') }} {{ $money($loan->installment_amount) }} ·
                            {{ __('Paid') }} {{ $money($loan->paid_amount) }} ·
                            {{ __('Remaining') }} <strong>{{ $money($loan->remaining_amount) }}</strong>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('This employee has no loans.') }}</p>
                @endforelse
            </div></div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <h6 class="fw-bold mb-3">{{ __('Accounting Entries') }}</h6>
                @if($entries->isEmpty())
                    <p class="text-muted small mb-0">{{ __('No ledger entries posted.') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm small align-middle mb-0">
                            <thead class="table-light"><tr>
                                <th>{{ __('Date') }}</th><th>{{ __('Account') }}</th><th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Debit') }}</th><th class="text-end">{{ __('Credit') }}</th>
                            </tr></thead>
                            <tbody>
                                @foreach($entries as $e)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Carbon::parse($e->reference_date)->format('d-m-Y') }}</td>
                                        <td>{{ $e->account->code ?? '' }} {{ $e->account->name ?? '' }}</td>
                                        <td class="text-muted">{{ $e->description }}</td>
                                        <td class="text-end tabular-nums">{{ (float) $e->debit ? $money($e->debit) : '' }}</td>
                                        <td class="text-end tabular-nums">{{ (float) $e->credit ? $money($e->credit) : '' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="fw-bold table-light">
                                    <td colspan="3" class="text-end">{{ __('Total') }}</td>
                                    <td class="text-end tabular-nums">{{ $money($entries->sum('debit')) }}</td>
                                    <td class="text-end tabular-nums">{{ $money($entries->sum('credit')) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div></div>
        </div>

        @if($audit->isNotEmpty())
            <div class="col-12">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <h6 class="fw-bold mb-3">{{ __('Activity') }}</h6>
                    @foreach($audit as $a)
                        <div class="small border-bottom py-1">
                            <span class="text-muted">{{ $a->created_at?->format('d-m-Y H:i') }}</span>
                            · <strong>{{ $a->user_id['name'] ?? '—' }}</strong> · {{ ucfirst($a->action) }}
                        </div>
                    @endforeach
                </div></div>
            </div>
        @endif
    </div>
</div>
</div>
</x-app-layout>
