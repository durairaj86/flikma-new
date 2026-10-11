@section('page-title', __('Punch Log'))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

<div id="payrollAjaxRoot">
@include('modules.payroll._ajax')
<div class="container-fluid px-2 pt-4 pt-md-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('payroll.dashboard') }}">{{ __('Payroll') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Punch Log') }}</li>
                </ol>
            </nav>
            <h1 class="h4 fw-bold text-dark mb-0">{{ __('Punch Log') }}</h1>
            <div class="text-muted small">{{ __('Every punch received from your machines and what happened to it.') }}</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#punchModal" onclick="punchForm()"><i class="bi bi-plus-lg me-1"></i>{{ __('Add Punch') }}</button>
            <a href="{{ route('payroll.punch-import') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>{{ __('Import from Excel') }}</a>
            <a href="{{ route('payroll.devices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-hdd-network me-1"></i>{{ __('Punch Devices') }}</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3"><ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- Unmapped machine IDs --}}
    @if($unmapped->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>{{ __('Machine IDs not linked to any employee') }}</h6>
                    <form method="POST" action="{{ route('payroll.devices.reprocess') }}">@csrf<button class="btn btn-outline-secondary btn-sm">{{ __('Re-check') }}</button></form>
                </div>
                <div class="text-muted small mb-3">{{ __('These punches are saved but not in attendance yet. Link each ID to an employee (or set "Machine ID" on the employee in Masters → Employees).') }}</div>
                @foreach($unmapped as $u)
                    <form method="POST" action="{{ route('payroll.devices.map') }}" class="row g-2 align-items-center border-bottom py-2">
                        @csrf
                        <input type="hidden" name="device_user_id" value="{{ $u->device_user_id }}">
                        <div class="col-md-3 small"><strong class="font-monospace">{{ $u->device_user_id }}</strong>
                            <span class="text-muted">· {{ $u->punches }} {{ __('punches') }} · {{ \Illuminate\Support\Carbon::parse($u->last_at)->format('d-m-Y H:i') }}</span></div>
                        <div class="col-md-6">
                            <x-common.employees name="employee_id" :value="null" :placeholder="__('Select employee')" :required="true" />
                        </div>
                        <div class="col-md-3"><button class="btn btn-primary btn-sm">{{ __('Link & apply') }}</button></div>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recent punches --}}
    <div style="overflow:visible" class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
            <h6 class="fw-bold mb-0">{{ __('Punches') }} <span class="text-muted fw-normal small">({{ number_format($recent->total()) }})</span></h6>
        </div>
        <style>#plFilter .ts-wrapper .ts-control{min-height:31px;height:31px;display:flex;align-items:center;padding-top:0;padding-bottom:0;font-size:.875rem}#plFilter .ts-wrapper{margin:0}</style>
        <form id="plFilter" method="GET" action="{{ route('payroll.punch-log') }}" class="px-4 py-3 border-bottom bg-light row g-2 align-items-end flex-lg-nowrap">
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label small fw-bold text-muted mb-1">{{ __('From') }}</label>
                <input type="text" name="from" class="form-control form-control-sm datepicker" value="{{ request('from') }}" autocomplete="off" placeholder="dd-mm-yyyy">
            </div>
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label small fw-bold text-muted mb-1">{{ __('To') }}</label>
                <input type="text" name="to" class="form-control form-control-sm datepicker" value="{{ request('to') }}" autocomplete="off" placeholder="dd-mm-yyyy">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small fw-bold text-muted mb-1">{{ __('Employee') }}</label>
                <x-common.employees class="form-select-sm" name="employee_id" :value="request('employee_id')" placeholder="All Employees" />
            </div>
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label small fw-bold text-muted mb-1">{{ __('Device') }}</label>
                <select name="device_id" class="form-select form-select-sm tom-select">
                    <option value="">{{ __('All devices') }}</option>
                    @foreach($devices as $d)<option value="{{ $d->id }}" {{ (string) request('device_id') === (string) $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label small fw-bold text-muted mb-1">{{ __('Result') }}</label>
                <select name="status" class="form-select form-select-sm tom-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach(['processed' => 'Processed', 'unmapped' => 'Unmapped', 'locked' => 'Locked'] as $k => $l)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ __($l) }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label small fw-bold text-muted mb-1">{{ __('Machine ID') }}</label>
                <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ __('e.g. 17') }}">
            </div>
            <div class="col-12 col-lg-auto d-flex gap-2">
                <button class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-funnel me-1"></i>{{ __('Filter') }}</button>
                @if(request()->hasAny(['from', 'to', 'employee_id', 'device_id', 'status', 'q']))
                    <a href="{{ route('payroll.punch-log') }}" class="btn btn-light border btn-sm">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>
        <script>
    (function () {
        var go = function () { document.querySelectorAll('#plFilter select.tom-select').forEach(function (s) { if (!s.tomselect && typeof initTomSelectForm === 'function' && window.jQuery) { initTomSelectForm(null, false, s); } }); };
        if (document.readyState === 'complete') { go(); } else { window.addEventListener('load', go); }
    })();
</script>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 small">
                <thead class="table-light"><tr><th class="ps-4">#</th><th>{{ __('Time') }}</th><th>{{ __('Employee') }}</th><th>{{ __('Machine ID') }}</th><th>{{ __('Device') }}</th><th>{{ __('Direction') }}</th><th>{{ __('Result') }}</th><th>{{ __('Remarks') }}</th><th class="text-end px-3">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($recent as $p)
                        <tr>
                            <td class="ps-4 text-muted">{{ $recent->firstItem() + $loop->index }}</td>
                            <td class="tabular-nums">{{ $p->punched_at->format('d-m-Y H:i:s') }}</td>
                            <td>{{ $p->employee->name ?? '—' }}@if($p->employee?->employee_code)<div class="text-muted extra-small">{{ $p->employee->employee_code }}</div>@endif</td>
                            <td class="font-monospace">{{ $p->device_user_id }}</td>
                            <td>{{ $p->device->name ?? '—' }}</td>
                            <td>{{ $p->direction ? strtoupper($p->direction) : '—' }}</td>
                            <td><span @class(['badge border', 'bg-success-subtle text-success' => $p->status === 'processed', 'bg-danger-subtle text-danger' => $p->status === 'unmapped', 'bg-secondary-subtle text-secondary' => !in_array($p->status, ['processed', 'unmapped'])])>{{ ucfirst($p->status) }}</span>
                                @if($p->status === 'locked')<span class="text-muted extra-small">{{ __('payroll already generated') }}</span>@endif</td>
                            <td style="min-width:200px;max-width:320px">
                                @if($p->raw['manual'] ?? false)
                                    <span class="badge bg-info-subtle text-info-emphasis border">{{ __('Manual') }}</span>
                                    <span class="text-muted extra-small">{{ __('by') }} {{ $users[$p->raw['by'] ?? 0] ?? '—' }}</span>
                                @endif
                                @if(!empty($p->raw['note']))<div>{{ $p->raw['note'] }}</div>@endif
                                @if(!empty($p->raw['edited_by']))
                                    <div class="text-muted extra-small"><i class="bi bi-pencil-square me-1"></i>{{ __('Edited by :u on :d', ['u' => $users[$p->raw['edited_by']] ?? '—', 'd' => \Illuminate\Support\Carbon::parse($p->raw['edited_at'])->format('d-m-Y H:i')]) }}
                                        @if(!empty($p->raw['original_time']))· {{ __('was') }} {{ \Illuminate\Support\Carbon::parse($p->raw['original_time'])->format('d-m-Y H:i') }}@endif</div>
                                @endif
                                @if(!empty($p->raw['import_line']))<span class="text-muted extra-small">{{ __('Excel import, row :n', ['n' => $p->raw['import_line']]) }}</span>@endif
                            </td>
                            <td class="text-end px-3 text-nowrap">
                                @php($isManual = (bool) ($p->raw['manual'] ?? false))
                                @if($p->status !== 'locked')
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle opacity-75" title="{{ __('Edit') }}"
                                            data-bs-toggle="modal" data-bs-target="#punchModal"
                                            data-punch="{{ json_encode(['id' => $p->id, 'manual' => $isManual, 'employee_id' => $p->employee_id, 'date' => $p->punched_at->format('d-m-Y'), 'time' => $p->punched_at->format('H:i'), 'direction' => $p->direction, 'note' => $p->raw['note'] ?? '', 'machine' => $p->device_user_id]) }}"
                                            onclick="punchForm(JSON.parse(this.dataset.punch))"><i class="bi bi-pencil"></i></button>
                                @endif
                                @if($isManual && $p->status !== 'locked')
                                    <form method="POST" action="{{ route('payroll.punch-entry.destroy', $p) }}" class="d-inline" data-confirm="{{ __('Delete this manual punch? Attendance for that day is recalculated.') }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm rounded-circle opacity-75" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-4 text-center text-muted">{{ __('No punches found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recent->hasPages())<div class="px-4 py-2 border-top">{{ $recent->links() }}</div>@endif
    </div>


{{-- Add / edit punch --}}
<div class="modal fade" id="punchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="punchForm" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="_method" id="pMethod" value="POST">
            <div class="modal-header py-2"><h6 class="modal-title fw-bold" id="pTitle">{{ __('Add Punch') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3" id="pEmpWrap"><label class="form-label small fw-bold">{{ __('Employee') }} *</label>
                    <x-common.employees name="employee_id" :value="null" :placeholder="__('Select employee')" :activeOnly="true" id="pEmployee" /></div>
                <div class="alert alert-light border small py-2" id="pMachine" style="display:none"></div>
                <div class="row g-2 mb-3">
                    <div class="col-6"><label class="form-label small fw-bold">{{ __('Date') }} *</label>
                        <input type="text" name="date" id="pDate" class="form-control datepicker" autocomplete="off" required placeholder="dd-mm-yyyy"></div>
                    <div class="col-6"><label class="form-label small fw-bold">{{ __('Time') }} *</label>
                        <input type="time" name="time" id="pTime" class="form-control" required></div>
                </div>
                <div class="mb-3"><label class="form-label small fw-bold">{{ __('Direction') }}</label>
                    <select name="direction" id="pDir" class="form-select">
                        <option value="">{{ __('Automatic (first punch of the day = in, last = out)') }}</option>
                        <option value="in">{{ __('Check-in') }}</option>
                        <option value="out">{{ __('Check-out') }}</option>
                    </select></div>
                <div class="mb-1"><label class="form-label small fw-bold">{{ __('Note') }}</label>
                    <input type="text" name="note" id="pNote" class="form-control" maxlength="200" placeholder="{{ __('Why was this entered manually? (optional)') }}"></div>
                <div class="form-text">{{ __('A punch at the same minute for the same employee cannot be entered twice. Not allowed for months with generated payroll.') }}</div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
<script>
    function punchForm(p) {
        var f = document.getElementById('punchForm');
        var emp = document.getElementById('pEmployee');
        var manual = !p || p.manual;
        document.getElementById('pTitle').textContent = p ? '{{ __('Edit Punch') }}' : '{{ __('Add Punch') }}';
        f.action = p ? '{{ url('payroll/punch-log/entries') }}/' + p.id : '{{ route('payroll.punch-entry.store') }}';
        document.getElementById('pMethod').value = p ? 'PUT' : 'POST';
        var dEl = document.getElementById('pDate'), dVal = p ? p.date : '{{ now()->format('d-m-Y') }}';
        dEl.value = dVal;
        if (dEl._flatpickr) { dEl._flatpickr.setDate(dVal, false, 'd-m-Y'); }   // datepicker keeps its own display
        document.getElementById('pTime').value = p ? p.time : '';
        document.getElementById('pDir').value = p && p.direction ? p.direction : '';
        document.getElementById('pNote').value = p ? (p.note || '') : '';
        // Machine punches keep their employee / machine ID — only time and direction can be corrected.
        document.getElementById('pEmpWrap').style.display = manual ? '' : 'none';
        emp.disabled = !manual;
        if (emp.tomselect) { emp.tomselect.setValue(p && p.employee_id ? String(p.employee_id) : '', true); } else { emp.value = p && p.employee_id ? p.employee_id : ''; }
        var m = document.getElementById('pMachine');
        m.style.display = manual ? 'none' : '';
        m.textContent = manual ? '' : '{{ __('Machine punch — machine ID') }} ' + p.machine + '. {{ __('Only the time and direction can be corrected; the original time is kept in the record.') }}';
    }
</script>
</div>
</div>
</div>
</x-app-layout>
