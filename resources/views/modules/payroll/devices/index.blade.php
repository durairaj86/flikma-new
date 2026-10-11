@section('page-title', __('Punch Devices'))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

@php
    $base = rtrim(config('app.url') ?: url('/'), '/');
    $base = request()->getSchemeAndHttpHost();
@endphp
<div class="container-fluid px-2 pt-4 pt-md-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('settings.company.edit') }}">{{ __('Settings') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Punch Devices') }}</li>
                </ol>
            </nav>
            <h1 class="h4 fw-bold text-dark mb-0">{{ __('Punch Devices & Attendance API') }}</h1>
            <div class="text-muted small">{{ __('Connect fingerprint, face or card machines. Punches flow into Attendance automatically.') }}</div>
        </div>
        <button type="button" class="btn btn-primary shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#deviceModal" onclick="deviceForm()">
            <i class="bi bi-plus-lg"></i> {{ __('Add Device') }}
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3"><ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    @if (session('device_token'))
        <div class="alert alert-warning border-0 shadow-sm mb-3">
            <div class="fw-bold mb-1"><i class="bi bi-key-fill me-1"></i>{{ __('API key for :name — copy it now, it will not be shown again', ['name' => session('device_token_name')]) }}</div>
            <div class="input-group">
                <input type="text" class="form-control font-monospace" id="newToken" value="{{ session('device_token') }}" readonly>
                <button class="btn btn-dark" type="button" onclick="navigator.clipboard.writeText(document.getElementById('newToken').value); this.textContent='{{ __('Copied') }}'">{{ __('Copy') }}</button>
            </div>
        </div>
    @endif

    @php($tab = request('tab', 'devices'))
    <ul class="nav nav-tabs mb-3" id="devTabs">
        <li class="nav-item"><button class="nav-link {{ $tab === 'devices' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabDevices" type="button"><i class="bi bi-hdd-network me-1"></i>{{ __('Devices') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $tab === 'guide' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabGuide" type="button"><i class="bi bi-book me-1"></i>{{ __('How to Connect') }}</button></li>
    </ul>
    @if($unmappedCount > 0)
        <div class="alert alert-warning border-0 shadow-sm small mb-3"><i class="bi bi-exclamation-triangle me-1"></i>{{ trans_choice(':n machine ID is not linked to an employee yet.|:n machine IDs are not linked to employees yet.', $unmappedCount, ['n' => $unmappedCount]) }}
            <a href="{{ route('payroll.punch-log') }}">{{ __('Open Punch Log') }}</a></div>
    @endif
    <div class="tab-content">
    <div class="tab-pane fade {{ $tab === 'devices' ? 'show active' : '' }}" id="tabDevices">
    {{-- Devices --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small fw-bold text-uppercase text-secondary">
                        <th class="px-4 py-3">{{ __('Device') }}</th>
                        <th class="px-4 py-3">{{ __('Type') }}</th>
                        <th class="px-4 py-3">{{ __('Identifier') }}</th>
                        <th class="px-4 py-3">{{ __('Last Seen') }}</th>
                        <th class="px-4 py-3 text-end">{{ __('Punches') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($devices as $d)
                        <tr>
                            <td class="px-4 py-2 fw-bold text-dark">{{ $d->name }}<div class="text-muted extra-small fw-normal">{{ $d->timezone }}</div></td>
                            <td class="px-4 py-2">{{ \App\Models\Payroll\AttendanceDevice::PROTOCOLS[$d->protocol] ?? $d->protocol }}</td>
                            <td class="px-4 py-2 font-monospace">
                                @if($d->protocol === 'zkteco') SN {{ $d->serial_number }}
                                @else {{ $d->token_hint ? '••••' . $d->token_hint : '—' }} @endif
                            </td>
                            <td class="px-4 py-2 text-muted">
                                @if($d->last_seen_at)
                                    <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:{{ $d->isOnline() ? '#16a34a' : '#98a2b3' }}"></span>{{ $d->last_seen_at->diffForHumans() }}
                                    <div class="extra-small">{{ $d->last_ip }}</div>
                                @else {{ __('Never connected') }} @endif
                            </td>
                            <td class="px-4 py-2 text-end tabular-nums">{{ number_format($d->punches_count) }}</td>
                            <td class="px-4 py-2">
                                <span @class(['badge rounded-pill px-2 py-1 border', 'bg-success-subtle text-success border-success-subtle' => $d->is_active, 'bg-secondary-subtle text-secondary border-secondary-subtle' => !$d->is_active])>{{ $d->is_active ? __('Active') : __('Off') }}</span>
                            </td>
                            <td class="px-4 py-2 text-end text-nowrap">
                                <a href="{{ route('payroll.devices.configure', $d) }}" class="btn btn-outline-primary btn-sm me-1" title="{{ __('Connection & field mapping') }}"><i class="bi bi-sliders me-1"></i>{{ __('Configure') }}</a>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle opacity-75 me-1" title="{{ __('Rename / time zone') }}"
                                        data-bs-toggle="modal" data-bs-target="#deviceModal"
                                        data-device="{{ json_encode(['id' => $d->id, 'name' => $d->name, 'protocol' => $d->protocol, 'serial_number' => $d->serial_number, 'mode' => $d->mode, 'timezone' => $d->timezone, 'is_active' => $d->is_active]) }}"
                                        onclick="deviceForm(JSON.parse(this.dataset.device))"><i class="bi bi-pencil"></i></button>
                                @if($d->protocol === 'api')
                                    <form method="POST" action="{{ route('payroll.devices.regenerate', $d) }}" class="d-inline" onsubmit="return confirm('{{ __('Generate a new key? The device using the old key will stop working until updated.') }}')">
                                        @csrf
                                        <button class="btn btn-outline-warning btn-sm rounded-circle opacity-75 me-1" title="{{ __('New API key') }}"><i class="bi bi-key"></i></button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('payroll.devices.destroy', $d) }}" class="d-inline" onsubmit="return confirm('{{ __('Delete this device?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm rounded-circle opacity-75" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-5 text-center text-muted">{{ __('No devices yet. Add your first punching machine.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    </div>
    <div class="tab-pane fade {{ $tab === 'guide' ? 'show active' : '' }}" id="tabGuide">
        @include('modules.payroll.devices._guide')
    </div>
    </div>
</div>

{{-- Add / edit modal --}}
<div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="deviceForm" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="_method" id="deviceMethod" value="POST">
            <div class="modal-header py-2"><h6 class="modal-title fw-bold" id="deviceTitle">{{ __('Add Device') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label small fw-bold">{{ __('Device Name') }}</label>
                    <input type="text" name="name" id="dName" class="form-control" placeholder="{{ __('Main gate, Warehouse…') }}" required></div>
                <div class="mb-3"><label class="form-label small fw-bold">{{ __('Type') }}</label>
                    <select name="protocol" id="dProtocol" class="form-select" onchange="deviceToggle()">
                        @foreach(\App\Models\Payroll\AttendanceDevice::PROTOCOLS as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach
                    </select></div>
                <div class="mb-3" id="dModeWrap"><label class="form-label small fw-bold">{{ __('How does it send punches?') }}</label>
                    <select name="mode" id="dMode" class="form-select">
                        <option value="push">{{ __('The device / vendor sends to us (callback URL)') }}</option>
                        <option value="pull">{{ __('We fetch from the vendor API (their endpoint)') }}</option>
                    </select></div>
                <div class="mb-3" id="dSerialWrap" style="display:none"><label class="form-label small fw-bold">{{ __('Serial Number (SN)') }}</label>
                    <input type="text" name="serial_number" id="dSerial" class="form-control font-monospace"></div>
                <div class="mb-3"><label class="form-label small fw-bold">{{ __('Device Time Zone') }}</label>
                    <select name="timezone" id="dTz" class="form-select">@foreach($timezones as $tz)<option value="{{ $tz }}">{{ $tz }}</option>@endforeach</select></div>
                <div class="form-check form-switch" id="dActiveWrap" style="display:none">
                    <input class="form-check-input" type="checkbox" name="is_active" id="dActive" value="1" checked>
                    <label class="form-check-label small" for="dActive">{{ __('Active') }}</label>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
<script>
    function deviceToggle() {
        var zk = document.getElementById('dProtocol').value === 'zkteco';
        document.getElementById('dSerialWrap').style.display = zk ? '' : 'none';
        document.getElementById('dModeWrap').style.display = zk ? 'none' : '';
    }
    function deviceForm(d) {
        var f = document.getElementById('deviceForm');
        document.getElementById('deviceTitle').textContent = d ? '{{ __('Edit Device') }}' : '{{ __('Add Device') }}';
        f.action = d ? '{{ url('payroll/devices') }}/' + d.id : '{{ route('payroll.devices.store') }}';
        document.getElementById('deviceMethod').value = d ? 'PUT' : 'POST';
        document.getElementById('dName').value = d ? d.name : '';
        document.getElementById('dProtocol').value = d ? d.protocol : 'api';
        document.getElementById('dSerial').value = d ? (d.serial_number || '') : '';
        document.getElementById('dMode').value = d ? (d.mode || 'push') : 'push';
        document.getElementById('dTz').value = d ? d.timezone : 'Asia/Riyadh';
        document.getElementById('dActiveWrap').style.display = d ? '' : 'none';
        document.getElementById('dActive').checked = d ? !!d.is_active : true;
        deviceToggle();
    }
</script>
</div>
</x-app-layout>
