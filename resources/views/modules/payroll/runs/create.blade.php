@section('page-title', __('Generate Payroll'))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

    <div class="container-fluid px-2 pt-4 pt-md-0">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('payroll.dashboard') }}">{{ __('Payroll') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll.runs.index') }}">{{ __('Payroll Runs') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Generate') }}</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold text-dark mb-0">{{ __('Generate Payroll') }} — {{ $monthLabel }}</h1>
            </div>
            <a href="{{ route('payroll.runs.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> {{ __('Back to Payroll Runs') }}
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('payroll.runs.create') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">{{ __('Month') }}</label>
                        <select name="month" class="form-select" onchange="this.form.submit()">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ \Illuminate\Support\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">{{ __('Year') }}</label>
                        <select name="year" class="form-select" onchange="this.form.submit()">
                            @foreach(range(now()->year - 1, now()->year) as $y)
                                <option value="{{ $y }}" {{ $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('payroll.runs.store') }}" id="generatePayrollForm">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <span class="fw-bold small text-uppercase text-muted">{{ __('Preview — :count employees', ['count' => $preview->count()]) }}</span>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1" {{ $preview->where('status', 'ready')->isEmpty() ? 'disabled' : '' }}>
                        <i class="bi bi-check-circle"></i> {{ __('Generate Payroll') }}
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small fw-bold text-uppercase text-secondary">
                                <th class="px-4 py-3">
                                    <input type="checkbox" class="form-check-input" id="selectAllReady" {{ $preview->where('status', 'ready')->isNotEmpty() ? 'checked' : '' }}>
                                </th>
                                <th class="px-4 py-3">{{ __('Employee') }}</th>
                                <th class="px-4 py-3 text-end">{{ __('Gross Salary') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('Absent Days') }}</th>
                                <th class="px-4 py-3 text-end">{{ __('OT Hours') }}</th>
                                <th class="px-4 py-3 text-end">{{ __('Deductions') }}</th>
                                <th class="px-4 py-3 text-end">{{ __('Net Payable') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($preview as $row)
                                <tr class="{{ $row['status'] !== 'ready' ? 'opacity-50' : '' }}">
                                    <td class="px-4 py-2">
                                        @if($row['status'] === 'ready')
                                            <input type="checkbox" class="form-check-input ready-checkbox" name="employee_ids[]" value="{{ $row['employee']->id }}" checked>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 fw-bold text-dark">
                                        {{ $row['employee']->name }}
                                        <div class="text-muted extra-small fw-normal">{{ $row['employee']->department ?? '' }}</div>
                                    </td>
                                    @if($row['calc'])
                                        <td class="px-4 py-2 text-end tabular-nums">{{ number_format($row['calc']['total_earnings'], 2) }}</td>
                                        <td class="px-4 py-2 text-center tabular-nums">{{ $row['calc']['absent_days'] }}</td>
                                        <td class="px-4 py-2 text-end tabular-nums">{{ number_format($row['calc']['overtime_hours'], 2) }}</td>
                                        <td class="px-4 py-2 text-end tabular-nums text-danger">{{ number_format($row['calc']['total_deductions'], 2) }}</td>
                                        <td class="px-4 py-2 text-end tabular-nums fw-bold">{{ number_format($row['calc']['net_payable'], 2) }}</td>
                                    @else
                                        <td class="px-4 py-2 text-muted" colspan="4">—</td>
                                    @endif
                                    <td class="px-4 py-2">
                                        @if($row['status'] === 'ready')
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ __('Ready') }}</span>
                                        @elseif($row['status'] === 'exists')
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">{{ __('Already Generated') }}</span>
                                        @else
                                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">{{ __('No Salary Structure') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-5 text-center text-muted">
                                        <i class="bi bi-people display-6 d-block mb-2 opacity-25"></i>
                                        {{ __('No employees found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('selectAllReady').addEventListener('change', function () {
            document.querySelectorAll('.ready-checkbox').forEach(function (cb) {
                cb.checked = this.checked;
            }.bind(this));
        });
    </script>
</div>
</x-app-layout>
