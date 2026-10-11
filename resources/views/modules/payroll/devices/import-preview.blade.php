@section('page-title', __('Check Import'))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

@php
    $c = $analysis['counts'];
    $opts = function ($sel, $empty = true) use ($meta) {
        $h = $empty ? '<option value="">— ' . e(__('none')) . ' —</option>' : '';
        foreach ($meta['header'] as $i => $name) {
            $h .= '<option value="' . $i . '"' . ((string) $sel === (string) $i ? ' selected' : '') . '>' . e($name !== '' ? $name : 'Column ' . ($i + 1)) . '</option>';
        }
        return $h;
    };
    $badge = ['ok' => 'bg-success-subtle text-success', 'error' => 'bg-danger-subtle text-danger', 'duplicate' => 'bg-secondary-subtle text-secondary', 'unmapped' => 'bg-warning-subtle text-warning-emphasis', 'locked' => 'bg-secondary-subtle text-secondary'];
    $importable = $c['ok'] + $c['unmapped'] + $c['locked'];
@endphp
<div class="container-fluid px-2 pt-4 pt-md-0">
    <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
    <div class="min-w-0">
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('payroll.dashboard') }}">{{ __('Payroll') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('payroll.punch-log') }}">{{ __('Punch Log') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('payroll.punch-import') }}">{{ __('Import from Excel') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Check') }}</li>
            </ol>
        </nav>
        <h1 class="h4 fw-bold text-dark mb-0">{{ __('Check before importing') }}</h1>
        <div class="text-muted small"><i class="bi bi-file-earmark-excel me-1"></i>{{ $meta['name'] }} · {{ number_format($c['total']) }} {{ __('rows') }}</div>
    </div>
    <a href="{{ route('payroll.punch-import') }}" class="btn btn-outline-secondary btn-sm flex-shrink-0"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3"><ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- Step 2: which column is which --}}
    <form method="GET" action="{{ route('payroll.punch-import.preview', $token) }}" id="mapForm" class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body small">
            <h6 class="fw-bold mb-3"><span class="badge bg-primary rounded-circle me-2">2</span>{{ __('Which column is which?') }}
                <span class="text-muted fw-normal">({{ __('detected automatically — change if wrong') }})</span></h6>
            <div class="row g-2">
                <div class="col-md-3"><label class="form-label fw-bold">{{ __('Employee ID') }} *</label><select name="map[id]" class="form-select form-select-sm" onchange="this.form.submit()">{!! $opts($map['id']) !!}</select></div>
                <div class="col-md-3"><label class="form-label fw-bold">{{ __('Date & time (one column)') }}</label><select name="map[datetime]" class="form-select form-select-sm" onchange="this.form.submit()">{!! $opts($map['datetime']) !!}</select></div>
                <div class="col-md-2"><label class="form-label fw-bold">{{ __('or Date') }}</label><select name="map[date]" class="form-select form-select-sm" onchange="this.form.submit()">{!! $opts($map['date']) !!}</select></div>
                <div class="col-md-2"><label class="form-label fw-bold">{{ __('+ Time') }}</label><select name="map[time]" class="form-select form-select-sm" onchange="this.form.submit()">{!! $opts($map['time']) !!}</select></div>
                <div class="col-md-2"><label class="form-label fw-bold">{{ __('Direction') }}</label><select name="map[direction]" class="form-select form-select-sm" onchange="this.form.submit()">{!! $opts($map['direction']) !!}</select></div>
                <div class="col-md-3"><label class="form-label fw-bold">{{ __('Match employee by') }}</label>
                    <select name="map[match]" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(['device_user_id' => __('Machine ID (then Employee Code)'), 'employee_code' => __('Employee Code'), 'employee_id' => __('Flikma employee id')] as $k => $l)
                            <option value="{{ $k }}" {{ ($map['match'] ?? '') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                    </select></div>
                <div class="col-md-3"><label class="form-label fw-bold">{{ __('Time format') }}</label>
                    <input name="map[time_format]" list="tf" class="form-control form-control-sm font-monospace" value="{{ $map['time_format'] ?? 'auto' }}" onchange="this.form.submit()">
                    <datalist id="tf"><option value="auto"><option value="Y-m-d H:i:s"><option value="d/m/Y H:i:s"><option value="d-m-Y h:i A"><option value="m/d/Y h:i:s A"></datalist></div>
                <div class="col-md-3"><label class="form-label fw-bold">{{ __('Machine time zone') }}</label>
                    <select name="timezone" class="form-select form-select-sm" onchange="this.form.submit()">@foreach($timezones as $z)<option {{ $tz === $z ? 'selected' : '' }}>{{ $z }}</option>@endforeach</select></div>
            </div>
            @if(count($sample))
                <div class="table-responsive mt-3"><table class="table table-sm table-bordered mb-0" style="font-size:12px">
                    <thead class="table-light"><tr>@foreach($meta['header'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
                    <tbody>@foreach($sample as $r)<tr>@foreach($meta['header'] as $i => $h)<td>{{ is_scalar($r[$i] ?? null) ? $r[$i] : '' }}</td>@endforeach</tr>@endforeach</tbody>
                </table></div>
                <div class="text-muted extra-small mt-1">{{ __('First 3 rows of your file') }}</div>
            @endif
        </div>
    </form>

    @if($analysis['fatal'])
        <div class="alert alert-warning border-0 shadow-sm"><i class="bi bi-exclamation-triangle me-1"></i>{{ $analysis['fatal'] }}</div>
    @else
        {{-- Step 3: result of the check --}}
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><span class="badge bg-primary rounded-circle me-2">3</span>{{ __('Result of the check') }}</h6>
                <div class="row g-2 text-center mb-3">
                    @foreach([['ok', 'Ready', 'success'], ['unmapped', 'Unmapped ID', 'warning'], ['locked', 'Payroll locked', 'secondary'], ['duplicate', 'Duplicate', 'secondary'], ['error', 'Errors', 'danger']] as [$k, $l, $col])
                        <div class="col"><div class="border rounded p-2"><div class="h4 mb-0 text-{{ $col }}">{{ number_format($c[$k]) }}</div><div class="small text-muted">{{ __($l) }}</div></div></div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('payroll.punch-import.confirm', $token) }}" class="d-flex flex-wrap align-items-center gap-3">
                    @csrf
                    @foreach(['id', 'datetime', 'date', 'time', 'direction', 'match', 'time_format'] as $k)
                        <input type="hidden" name="map[{{ $k }}]" value="{{ $map[$k] ?? '' }}">
                    @endforeach
                    <input type="hidden" name="timezone" value="{{ $tz }}">
                    @if($c['error'] > 0)
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="skip_errors" value="1" id="skipErr">
                            <label class="form-check-label small" for="skipErr">{{ __('Skip the :n row(s) with errors and import the rest', ['n' => $c['error']]) }}</label></div>
                    @endif
                    <button class="btn btn-success btn-sm px-4" {{ $importable === 0 ? 'disabled' : '' }}><i class="bi bi-check2-circle me-1"></i>{{ __('Import :n row(s)', ['n' => number_format($importable)]) }}</button>
                    <a href="{{ route('payroll.punch-import') }}" class="btn btn-light border btn-sm">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-5">
            <div class="px-4 py-2 border-bottom small d-flex justify-content-between">
                <strong>{{ __('Rows') }}</strong>
                <span class="text-muted">{{ __('Problems are listed first (showing up to 300).') }}</span>
            </div>
            <div class="table-responsive" style="max-height:480px">
                <table class="table table-sm align-middle mb-0 small">
                    <thead class="table-light" style="position:sticky;top:0"><tr><th class="px-3">{{ __('Row') }}</th><th>{{ __('ID') }}</th><th>{{ __('Employee') }}</th><th>{{ __('Date & time') }}</th><th>{{ __('Dir.') }}</th><th>{{ __('Result') }}</th></tr></thead>
                    <tbody>
                        @php($sorted = collect($analysis['rows'])->sortBy(fn ($r) => $r['status'] === 'error' ? 0 : ($r['status'] === 'ok' ? 5 : 2))->take(300))
                        @foreach($sorted as $r)
                            <tr>
                                <td class="px-3 text-muted">{{ $r['line'] }}</td>
                                <td class="font-monospace">{{ $r['id'] }}</td>
                                <td>{{ $r['employee'] ?? '—' }}</td>
                                <td class="tabular-nums">{{ $r['time']?->format('d-m-Y H:i:s') ?? '—' }}</td>
                                <td>{{ $r['direction'] ? strtoupper($r['direction']) : '—' }}</td>
                                <td><span class="badge border {{ $badge[$r['status']] }}">{{ ucfirst($r['status']) }}</span> <span class="text-muted">{{ $r['message'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
</div>
</x-app-layout>
