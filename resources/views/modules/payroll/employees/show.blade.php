@section('page-title', __('Payroll Profile: :name', ['name' => $user->name]))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

    @php
        $row = fn ($label, $value) => '<div class="col-6 col-md-4"><div class="text-muted small">' . e($label) . '</div><div class="fw-semibold text-dark">' . ($value !== null && $value !== '' ? e($value) : '—') . '</div></div>';
    @endphp
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
                <h1 class="h4 fw-bold text-dark mb-0">{{ $user->name }}
                    @if($user->isTerminated())<span class="badge bg-danger-subtle text-danger border ms-2 fs-6">{{ __('Terminated') }}</span>@endif
                </h1>
                <div class="text-muted small">{{ $user->designation ?: '—' }} &middot; {{ $user->department ?: '—' }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('payroll.employees.edit', $user) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>{{ __('Edit') }}</a>
                <a href="{{ route('payroll.employees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul me-1"></i>{{ __('Back to list') }}</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">{{ __('Employment') }}</div>
                    <div class="card-body"><div class="row g-3">
                        {!! $row(__('Employment Type'), __(ucwords(str_replace('_', ' ', (string) $user->employment_type)))) !!}
                        {!! $row(__('Shift'), $user->shift->name ?? null) !!}
                        {!! $row(__('Reporting Manager'), $user->reportingManager->name ?? null) !!}
                        {!! $row(__('Date of Joining'), optional($user->joining_date)->format('d-m-Y')) !!}
                        {!! $row(__('Email'), $user->email) !!}
                        {!! $row(__('Phone'), $user->phone) !!}
                    </div></div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">{{ __('Identity & Bank') }}</div>
                    <div class="card-body"><div class="row g-3">
                        {!! $row(__('National ID'), $user->national_id) !!}
                        {!! $row(__('GOSI Number'), $user->gosi_number) !!}
                        {!! $row(__('Bank Name'), $user->bank_name) !!}
                        {!! $row(__('IBAN'), $user->iban) !!}
                        {!! $row(__('Emergency Contact'), $user->emergency_contact_name) !!}
                        {!! $row(__('Emergency Phone'), $user->emergency_contact_phone) !!}
                    </div></div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">{{ __('Recent Payroll') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light small text-uppercase"><tr><th class="ps-3">{{ __('Period') }}</th><th class="text-end">{{ __('Earnings') }}</th><th class="text-end">{{ __('Deductions') }}</th><th class="text-end">{{ __('Net Payable') }}</th><th class="pe-3">{{ __('Status') }}</th></tr></thead>
                            <tbody class="small">
                            @forelse($records as $r)
                                <tr><td class="ps-3">{{ str_pad($r->month, 2, '0', STR_PAD_LEFT) }}/{{ $r->year }}</td>
                                    <td class="text-end">{{ number_format($r->total_earnings, 2) }}</td>
                                    <td class="text-end">{{ number_format($r->total_deductions, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($r->net_payable, 2) }}</td>
                                    <td class="pe-3"><span class="badge bg-light text-dark border">{{ __(ucfirst((string) $r->status)) }}</span></td></tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No payroll records yet.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">{{ __('Current Salary') }}</div>
                    <div class="card-body small">
                        @if($current)
                            @foreach([['Basic Salary', 'basic_salary'], ['Housing Allowance', 'housing_allowance'], ['Transportation Allowance', 'transportation_allowance'], ['Other Allowances', 'other_allowances']] as [$l, $k])
                                <div class="d-flex justify-content-between mb-2"><span class="text-muted">{{ __($l) }}</span><span>{{ number_format($current->$k, 2) }}</span></div>
                            @endforeach
                            <hr class="my-2">
                            <div class="d-flex justify-content-between h6 fw-bold mb-1"><span>{{ __('Total') }}</span><span>{{ number_format($current->total_salary, 2) }}</span></div>
                            <div class="text-muted">{{ __('Effective from :date', ['date' => optional($current->effective_from)->format('d-m-Y')]) }}</div>
                        @else
                            <div class="text-warning-emphasis">{{ __('No current salary structure.') }}</div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">{{ __('Loans') }}</div>
                    <div class="card-body small">
                        @forelse($loans as $l)
                            <div class="d-flex justify-content-between mb-2"><span>{{ number_format($l->loan_amount, 2) }} <span class="text-muted">({{ __(ucfirst((string) $l->status)) }})</span></span><span class="text-muted">{{ __('Left') }} {{ number_format($l->remaining_amount, 2) }}</span></div>
                        @empty
                            <div class="text-muted">{{ __('No loans.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
