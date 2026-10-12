@section('page-title', $device->name . ' — ' . __('Punch Device'))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

@php
    $tab = request('tab', 'connection');
    $root = request()->getSchemeAndHttpHost();
    $map = $device->field_map ?? [];
    $isApi = $device->protocol === 'api';
    $mode = old('mode', $device->mode);
    $m = fn ($k, $d = '') => old($k, $map[$k] ?? $d);
@endphp
<div class="container-fluid px-2 pt-4 pt-md-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('settings.company.edit') }}">{{ __('Settings') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payroll.devices.index') }}">{{ __('Punch Devices') }}</a></li>
                    <li class="breadcrumb-item active">{{ $device->name }}</li>
                </ol>
            </nav>
            <h1 class="h4 fw-bold text-dark mb-0">{{ $device->name }}
                <span @class(['badge rounded-pill border ms-2 align-middle', 'bg-success-subtle text-success' => $device->is_active, 'bg-secondary-subtle text-secondary' => !$device->is_active]) style="font-size:.7rem;padding:.3em .65em">{{ $device->is_active ? __('Active') : __('Off') }}</span>
            </h1>
            <div class="text-muted small">{{ \App\Models\Payroll\AttendanceDevice::PROTOCOLS[$device->protocol] ?? '' }} · {{ $device->timezone }}
                @if($device->last_seen_at) · {{ __('last seen') }} {{ $device->last_seen_at->diffForHumans() }} @else · {{ __('never connected') }} @endif</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.devices.index', ['tab' => 'guide']) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-book me-1"></i>{{ __('How to connect') }}</a>
            <a href="{{ route('payroll.devices.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success border-0 shadow-sm mb-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>@endif
    @if (session('pull_error'))<div class="alert alert-danger border-0 shadow-sm mb-3"><i class="bi bi-x-circle-fill me-2"></i>{{ session('pull_error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger border-0 shadow-sm mb-3"><ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @if (session('device_token'))
        <div class="alert alert-warning border-0 shadow-sm mb-3">
            <div class="fw-bold mb-1"><i class="bi bi-key-fill me-1"></i>{{ __('API key — copy it now, it will not be shown again') }}</div>
            <div class="input-group"><input type="text" class="form-control font-monospace" id="newToken" value="{{ session('device_token') }}" readonly>
                <button class="btn btn-dark" type="button" onclick="navigator.clipboard.writeText(document.getElementById('newToken').value); this.textContent='{{ __('Copied') }}'">{{ __('Copy') }}</button></div>
        </div>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link {{ $tab === 'connection' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabConn" type="button"><i class="bi bi-plug me-1"></i>{{ __('Connection') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $tab === 'mapping' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabMap" type="button"><i class="bi bi-columns-gap me-1"></i>{{ __('Field Mapping') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $tab === 'test' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabTest" type="button"><i class="bi bi-activity me-1"></i>{{ __('Test') }}</button></li>
    </ul>

    <div class="tab-content">
    {{-- ───────────── CONNECTION ───────────── --}}
    <div class="tab-pane fade {{ $tab === 'connection' ? 'show active' : '' }}" id="tabConn">
        @if(!$isApi)
            <div class="card border-0 shadow-sm rounded-3 mb-3"><div class="card-body small">
                <h6 class="fw-bold">{{ __('Enter these on the machine') }} <span class="text-muted fw-normal">(Comm. → Cloud Server Setting)</span></h6>
                <table class="table table-sm w-auto mb-2">
                    <tr><td class="text-muted pe-4">{{ __('Server Address') }}</td><td class="font-monospace fw-bold">{{ request()->getHost() }}</td></tr>
                    <tr><td class="text-muted">{{ __('Server Port') }}</td><td class="font-monospace fw-bold">{{ request()->getPort() }}</td></tr>
                    <tr><td class="text-muted">HTTPS</td><td class="fw-bold">{{ request()->isSecure() ? 'On' : 'Off' }}</td></tr>
                    <tr><td class="text-muted">{{ __('Serial Number registered here') }}</td><td class="font-monospace fw-bold">{{ $device->serial_number }}</td></tr>
                </table>
                <div class="text-muted">{{ __('The machine contacts Flikma itself (path /iclock/cdata). No field mapping is needed for this type.') }}</div>
            </div></div>
        @else
            <form method="POST" action="{{ route('payroll.devices.connection', $device) }}" class="card border-0 shadow-sm rounded-3 mb-3">
                @csrf
                <div class="card-body small">
                    <h6 class="fw-bold mb-3">{{ __('How do punches reach Flikma?') }}</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="border rounded p-3 d-block h-100 {{ $mode === 'push' ? 'border-primary bg-primary-subtle' : '' }}" style="cursor:pointer">
                            <input type="radio" class="form-check-input me-2" name="mode" value="push" {{ $mode === 'push' ? 'checked' : '' }} onchange="connMode()">
                            <strong>{{ __('They send to us') }}</strong> <span class="text-muted">— {{ __('callback / webhook') }}</span>
                            <div class="text-muted mt-1">{{ __('You give the vendor / machine software our Callback URL.') }}</div></label></div>
                        <div class="col-md-6"><label class="border rounded p-3 d-block h-100 {{ $mode === 'pull' ? 'border-primary bg-primary-subtle' : '' }}" style="cursor:pointer">
                            <input type="radio" class="form-check-input me-2" name="mode" value="pull" {{ $mode === 'pull' ? 'checked' : '' }} onchange="connMode()">
                            <strong>{{ __('We fetch from them') }}</strong> <span class="text-muted">— {{ __('their API endpoint') }}</span>
                            <div class="text-muted mt-1">{{ __('You enter the vendor\'s API address and key; Flikma collects punches every few minutes.') }}</div></label></div>
                    </div>

                    {{-- push --}}
                    <div id="connPush">
                        <label class="form-label fw-bold">{{ __('Callback URL — give this to the vendor / device software') }}</label>
                        <div class="input-group mb-1">
                            <input type="text" class="form-control font-monospace" id="cbUrl" value="{{ $device->callbackUrl() }}" readonly>
                            <button class="btn btn-dark" type="button" onclick="navigator.clipboard.writeText(document.getElementById('cbUrl').value); this.textContent='{{ __('Copied') }}'">{{ __('Copy') }}</button>
                        </div>
                        <div class="text-muted mb-3">{{ __('Accepts POST (JSON or form) and GET. The URL itself is the password — keep it private.') }}</div>
                        <div class="fw-bold mb-1">{{ __('Or send with an API key header') }}</div>
<pre class="bg-light border rounded p-2 mb-2" style="white-space:pre-wrap">POST {{ $root }}/api/attendance/punch
Authorization: Bearer flk_••••{{ $device->token_hint }}
Content-Type: application/json

{ "user_id": "17", "timestamp": "2026-10-11 08:02:10", "type": "in" }</pre>
                        <div class="text-muted">{{ __('Test the key:') }} <code>GET {{ $root }}/api/attendance/ping</code>
                            · <a href="#" onclick="document.getElementById('regenForm').submit(); return false;">{{ __('Generate a new API key') }}</a></div>
                    </div>

                    {{-- pull --}}
                    <div id="connPull" style="display:none">
                        <div class="row g-3">
                            <div class="col-md-3"><label class="form-label fw-bold">{{ __('Method') }}</label>
                                <select name="pull_method" class="form-select">@foreach(['GET', 'POST'] as $mth)<option {{ old('pull_method', $device->pull_method) === $mth ? 'selected' : '' }}>{{ $mth }}</option>@endforeach</select></div>
                            <div class="col-md-9"><label class="form-label fw-bold">{{ __('Vendor API endpoint') }}</label>
                                <input type="text" name="pull_url" class="form-control font-monospace" value="{{ old('pull_url', $device->pull_url) }}" placeholder="https://api.vendor.com/v1/attendance/logs"></div>
                            <div class="col-md-6"><label class="form-label fw-bold">{{ __('Headers') }} <span class="text-muted fw-normal">({{ __('one per line') }})</span></label>
                                <textarea name="pull_headers" rows="4" class="form-control font-monospace" placeholder="Authorization: Bearer YOUR_TOKEN&#10;X-API-Key: abc123">{{ old('pull_headers', $headersText) }}</textarea>
                                <div class="form-text">{{ __('Stored encrypted.') }}</div></div>
                            <div class="col-md-6"><label class="form-label fw-bold">{{ __('Parameters') }} <span class="text-muted fw-normal">({{ __('query string, or JSON for POST') }})</span></label>
                                <textarea name="pull_params" rows="4" class="form-control font-monospace" placeholder="start={from_date}&end={to_date}&page_size=500">{{ old('pull_params', $device->pull_params) }}</textarea>
                                <div class="form-text">{{ __('Placeholders:') }} <code>{from}</code> <code>{to}</code> <code>{from_date}</code> <code>{to_date}</code> <code>{from_epoch}</code> <code>{to_epoch}</code></div></div>
                            <div class="col-md-3"><label class="form-label fw-bold">{{ __('Fetch every (minutes)') }}</label>
                                <input type="text" name="pull_interval" min="1" max="1440" class="integer form-control" value="{{ old('pull_interval', $device->pull_interval) }}"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pb-3"><button class="btn btn-primary btn-sm px-4">{{ __('Save connection') }}</button></div>
            </form>
            <form id="regenForm" method="POST" action="{{ route('payroll.devices.regenerate', $device) }}" onsubmit="return confirm('{{ __('Generate a new key? The old key stops working.') }}')">@csrf</form>
        @endif
    </div>

    {{-- ───────────── MAPPING ───────────── --}}
    <div class="tab-pane fade {{ $tab === 'mapping' ? 'show active' : '' }}" id="tabMap">
        @if(!$isApi)
            <div class="alert alert-info border-0 shadow-sm small">{{ __('ZKTeco / ADMS machines always send the same format, so there is nothing to map.') }}</div>
        @else
        <div class="row g-3">
            <div class="col-lg-6">
                <form method="POST" action="{{ route('payroll.devices.mapping', $device) }}" id="mapForm" class="card border-0 shadow-sm rounded-3 h-100">
                    @csrf
                    <div class="card-body small">
                        <h6 class="fw-bold">{{ __('Which column is which?') }}</h6>
                        <div class="text-muted mb-3">{{ __('Type the column names exactly as the device sends them (or click a field from the sample on the right). Leave everything empty for auto-detection.') }}</div>
                        <div class="mb-3"><label class="form-label fw-bold">{{ __('Records path') }} <span class="text-muted fw-normal">({{ __('where the punch list is') }})</span></label>
                            <input name="records_path" class="form-control font-monospace map-in" value="{{ $m('records_path') }}" placeholder="data.logs"></div>
                        <div class="row g-2 mb-3">
                            <div class="col-7"><label class="form-label fw-bold">{{ __('Employee field') }}</label>
                                <input name="employee_field" class="form-control font-monospace map-in" value="{{ $m('employee_field') }}" placeholder="emp_code"></div>
                            <div class="col-5"><label class="form-label fw-bold">{{ __('Match by') }}</label>
                                <select name="employee_match" class="form-select map-in">
                                    @foreach(['device_user_id' => __('Machine ID'), 'employee_code' => __('Employee Code'), 'employee_id' => __('Flikma ID')] as $k => $l)
                                        <option value="{{ $k }}" {{ $m('employee_match', 'device_user_id') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                                </select></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="form-label fw-bold">{{ __('Time field') }}</label>
                                <input name="time_field" class="form-control font-monospace map-in" value="{{ $m('time_field') }}" placeholder="punch_time"></div>
                            <div class="col-6"><label class="form-label fw-bold">{{ __('Date field') }} <span class="text-muted fw-normal">({{ __('optional') }})</span></label>
                                <input name="date_field" class="form-control font-monospace map-in" value="{{ $m('date_field') }}" placeholder="date"></div>
                        </div>
                        <div class="mb-3"><label class="form-label fw-bold">{{ __('Time format') }}</label>
                            <input name="time_format" list="tfList" class="form-control font-monospace map-in" value="{{ $m('time_format', 'auto') }}">
                            <datalist id="tfList"><option value="auto"><option value="epoch"><option value="Y-m-d H:i:s"><option value="d/m/Y H:i:s"><option value="d-m-Y h:i A"><option value="m/d/Y h:i:s A"></datalist>
                            <div class="form-text">{{ __('"auto" works for most. Custom uses PHP date letters, e.g. d/m/Y H:i:s.') }}</div></div>
                        <div class="row g-2 mb-3">
                            <div class="col-12"><label class="form-label fw-bold">{{ __('Direction field') }} <span class="text-muted fw-normal">({{ __('optional — in / out') }})</span></label>
                                <input name="direction_field" class="form-control font-monospace map-in" value="{{ $m('direction_field') }}" placeholder="state"></div>
                            <div class="col-6"><label class="form-label">{{ __('Values meaning IN') }}</label>
                                <input name="in_values" class="form-control font-monospace" value="{{ $m('in_values') }}" placeholder="0,I,IN"></div>
                            <div class="col-6"><label class="form-label">{{ __('Values meaning OUT') }}</label>
                                <input name="out_values" class="form-control font-monospace" value="{{ $m('out_values') }}" placeholder="1,O,OUT"></div>
                            <div class="col-12"><label class="form-label">{{ __('If the device sends no direction') }}</label>
                                <select name="default_direction" class="form-select">
                                    <option value="">{{ __('Decide automatically (first punch = in, last = out)') }}</option>
                                    <option value="in" {{ $m('default_direction') === 'in' ? 'selected' : '' }}>{{ __('Every punch is a check-in (e.g. entry-gate machine)') }}</option>
                                    <option value="out" {{ $m('default_direction') === 'out' ? 'selected' : '' }}>{{ __('Every punch is a check-out (e.g. exit-gate machine)') }}</option>
                                </select></div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3 d-flex gap-2">
                        <button class="btn btn-primary btn-sm px-4">{{ __('Save mapping') }}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('mapForm').reset()">{{ __('Reset') }}</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100"><div class="card-body small">
                    <h6 class="fw-bold">{{ __('Try it with a real sample') }}</h6>
                    <div class="text-muted mb-2">{{ __('Paste one JSON sample that the device / vendor sends, then press Preview. Nothing is saved.') }}</div>
                    <textarea id="sampleBox" rows="8" class="form-control font-monospace mb-2" placeholder='{"data":{"logs":[{"emp_code":"17","punch_time":"11/10/2026 08:02:10","state":"0"}]}}'></textarea>
                    <button type="button" class="btn btn-dark btn-sm mb-3" onclick="previewMap()"><i class="bi bi-eye me-1"></i>{{ __('Preview') }}</button>
                    <div id="prevOut"></div>
                </div></div>
            </div>
        </div>
        @endif
    </div>

    {{-- ───────────── TEST ───────────── --}}
    <div class="tab-pane fade {{ $tab === 'test' ? 'show active' : '' }}" id="tabTest">
        <div class="row g-3">
            @if($device->isPull())
            <div class="col-lg-6"><div class="card border-0 shadow-sm rounded-3 h-100"><div class="card-body small">
                <h6 class="fw-bold">{{ __('Vendor API') }}</h6>
                <div class="mb-2 text-muted">{{ __('Last fetch') }}:
                    @if($device->last_pulled_at)
                        <strong class="{{ $device->last_pull_status === 'ok' ? 'text-success' : 'text-danger' }}">{{ strtoupper($device->last_pull_status) }}</strong> · {{ $device->last_pulled_at->diffForHumans() }}
                        <div>{{ $device->last_pull_message }}</div>
                    @else {{ __('never') }} @endif</div>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="testPull(this)"><i class="bi bi-lightning me-1"></i>{{ __('Test fetch (nothing saved)') }}</button>
                    <form method="POST" action="{{ route('payroll.devices.pull-now', $device) }}">@csrf<button class="btn btn-primary btn-sm"><i class="bi bi-cloud-download me-1"></i>{{ __('Fetch & import now') }}</button></form>
                </div>
                <div id="pullOut"></div>
            </div></div></div>
            @else
            <div class="col-lg-6"><div class="card border-0 shadow-sm rounded-3 h-100"><div class="card-body small">
                <h6 class="fw-bold">{{ __('Send a test punch') }}</h6>
                @if($isApi)
                <div class="text-muted mb-2">{{ __('From a terminal (replace 17 with a real machine ID):') }}</div>
<pre class="bg-light border rounded p-2" style="white-space:pre-wrap">curl -X POST "{{ $device->callbackUrl() }}" \
  -H "Content-Type: application/json" \
  -d '{"user_id":"17","timestamp":"{{ now($device->timezone)->format('Y-m-d H:i:s') }}"}'</pre>
                @else
                <div class="text-muted">{{ __('Ask someone to punch on the machine. The device shows "Last Seen" within a minute and the punch appears below.') }}</div>
                @endif
            </div></div></div>
            @endif
            <div class="col-lg-6"><div class="card border-0 shadow-sm rounded-3 h-100"><div class="card-body small">
                <h6 class="fw-bold">{{ __('Latest punches from this device') }}</h6>
                @forelse($recent as $p)
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <span>{{ $p->punched_at->format('d-m-Y H:i:s') }} · <span class="font-monospace">{{ $p->device_user_id }}</span> {{ $p->employee->name ?? '' }}</span>
                        <span class="badge border {{ $p->status === 'processed' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">{{ ucfirst($p->status) }}</span>
                    </div>
                @empty <div class="text-muted">{{ __('Nothing received yet.') }}</div> @endforelse
            </div></div></div>
        </div>
    </div>
    </div>
</div>

<script>
    var CSRF = '{{ csrf_token() }}';
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; }); }

    function connMode() {
        var m = document.querySelector('input[name=mode]:checked');
        if (!m) return;
        document.getElementById('connPush').style.display = m.value === 'push' ? '' : 'none';
        document.getElementById('connPull').style.display = m.value === 'pull' ? '' : 'none';
    }
    connMode();

    var lastInput = null;
    document.querySelectorAll('.map-in').forEach(function (i) { i.addEventListener('focus', function () { lastInput = i; }); });

    function renderPreview(d, box) {
        var h = '';
        var keys = Object.keys(d.fields || {});
        if (keys.length) {
            h += '<div class="fw-bold mb-1">{{ __('Fields found (click to use in the focused box)') }}</div><div class="mb-3">';
            keys.forEach(function (k) {
                h += '<span class="badge bg-light text-dark border me-1 mb-1 fld" style="cursor:pointer;font-weight:500" data-k="' + esc(k) + '">' + esc(k) + ' <span class="text-muted">= ' + esc(String(d.fields[k]).slice(0, 30)) + '</span></span>';
            });
            h += '</div>';
        }
        h += '<div class="fw-bold mb-1">{{ __('How it will be read') }}</div>';
        if (d.punches && d.punches.length) {
            h += '<table class="table table-sm mb-0"><thead><tr><th>{{ __('Employee ID') }}</th><th>{{ __('Time') }}</th><th>{{ __('Direction') }}</th></tr></thead><tbody>';
            d.punches.forEach(function (p) { h += '<tr><td class="font-monospace">' + esc(p.id) + '</td><td>' + esc(p.time) + '</td><td>' + (p.direction ? p.direction.toUpperCase() : '—') + '</td></tr>'; });
            h += '</tbody></table>';
        } else {
            h += '<div class="text-danger">{{ __('No punch could be read. Check the employee field and time field names.') }}</div>';
        }
        box.innerHTML = h;
        box.querySelectorAll('.fld').forEach(function (b) {
            b.addEventListener('click', function () { if (lastInput) { lastInput.value = b.dataset.k; lastInput.focus(); } });
        });
    }

    function previewMap() {
        var box = document.getElementById('prevOut');
        var fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('sample', document.getElementById('sampleBox').value);
        document.querySelectorAll('#mapForm [name]').forEach(function (el) { if (el.name !== '_token') fd.append('map[' + el.name + ']', el.value); });
        box.innerHTML = '<div class="text-muted">…</div>';
        fetch('{{ route('payroll.devices.preview', $device) }}', {method: 'POST', body: fd, headers: {'Accept': 'application/json'}})
            .then(function (r) { return r.json(); })
            .then(function (d) { if (!d.ok) { box.innerHTML = '<div class="text-danger">' + esc(d.message) + '</div>'; return; } renderPreview(d, box); })
            .catch(function () { box.innerHTML = '<div class="text-danger">{{ __('Preview failed.') }}</div>'; });
    }

    function testPull(btn) {
        var box = document.getElementById('pullOut');
        btn.disabled = true; box.innerHTML = '<div class="text-muted">{{ __('Contacting the vendor API…') }}</div>';
        fetch('{{ route('payroll.devices.test-pull', $device) }}', {method: 'POST', headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'}})
            .then(function (r) { return r.json(); })
            .then(function (d) {
                btn.disabled = false;
                var h = '<div class="' + (d.ok ? 'text-success' : 'text-danger') + ' fw-bold mb-2">' + esc(d.message) + '</div>';
                box.innerHTML = h + '<div id="pullPrev" class="mb-2"></div>' +
                    (d.body ? '<div class="fw-bold mb-1">{{ __('Raw answer (first 4000 characters)') }}</div><pre class="bg-light border rounded p-2" style="white-space:pre-wrap;max-height:240px;overflow:auto">' + esc(d.body) + '</pre>' : '');
                if (d.ok) renderPreview(d, document.getElementById('pullPrev'));
            }).catch(function () { btn.disabled = false; box.innerHTML = '<div class="text-danger">{{ __('Test failed.') }}</div>'; });
    }
</script>
</div>
</x-app-layout>
