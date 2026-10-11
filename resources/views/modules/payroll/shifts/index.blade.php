@section('page-title', __('Shifts'))
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
                        <li class="breadcrumb-item active">{{ __('Shifts') }}</li>
                    </ol>
                </nav>
                <h1 class="h4 fw-bold text-dark mb-0">{{ __('Shifts') }}</h1>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createShiftModal">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Shift') }}
            </button>
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

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small fw-bold text-uppercase text-secondary">
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Type') }}</th>
                            <th class="px-4 py-3">{{ __('Timing') }}</th>
                            <th class="px-4 py-3">{{ __('Grace') }}</th>
                            <th class="px-4 py-3">{{ __('Week Off') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Employees') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @php($days = [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')])
                        @forelse($shifts as $shift)
                            <tr>
                                <td class="px-4 py-2 fw-bold text-dark">{{ $shift->name }}</td>
                                <td class="px-4 py-2 text-capitalize">{{ __($shift->type) }}</td>
                                <td class="px-4 py-2 tabular-nums">{{ \Illuminate\Support\Carbon::parse($shift->start_time)->format('h:i A') }} – {{ \Illuminate\Support\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
                                <td class="px-4 py-2">{{ $shift->grace_minutes }} {{ __('min') }}</td>
                                <td class="px-4 py-2">
                                    @if(!empty($shift->week_off_days))
                                        {{ collect($shift->week_off_days)->map(fn($d) => $days[$d] ?? $d)->implode(', ') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center">{{ $shift->employees_count }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if($shift->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm edit-shift-btn"
                                            data-bs-toggle="modal" data-bs-target="#editShiftModal"
                                            data-update-url="{{ route('payroll.shifts.update', $shift) }}"
                                            data-name="{{ $shift->name }}"
                                            data-type="{{ $shift->type }}"
                                            data-start-time="{{ \Illuminate\Support\Carbon::parse($shift->start_time)->format('H:i') }}"
                                            data-end-time="{{ \Illuminate\Support\Carbon::parse($shift->end_time)->format('H:i') }}"
                                            data-grace="{{ $shift->grace_minutes }}"
                                            data-week-off='@json($shift->week_off_days ?? [])'
                                            data-active="{{ $shift->is_active ? 1 : 0 }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('payroll.shifts.destroy', $shift) }}" method="POST" class="d-inline" data-confirm="{{ __('Delete this shift?') }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm ms-1"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-5 text-center text-muted">
                                    <i class="bi bi-clock-history display-6 d-block mb-2 opacity-25"></i>
                                    {{ __('No shifts created yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $shifts->links('pagination::bootstrap-5') }}
        </div>
    </div>

    @php($weekDayOptions = [0 => __('Sunday'), 1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'), 4 => __('Thursday'), 5 => __('Friday'), 6 => __('Saturday')])

    {{-- Create Shift Modal --}}
    <div class="modal fade" id="createShiftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('payroll.shifts.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('New Shift') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        @include('modules.payroll.shifts._fields')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Shift Modal --}}
    <div class="modal fade" id="editShiftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editShiftForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit Shift') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        @include('modules.payroll.shifts._fields', ['prefix' => 'edit'])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.edit-shift-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('editShiftForm').action = btn.dataset.updateUrl;
                document.getElementById('edit_shift_name').value = btn.dataset.name;
                document.getElementById('edit_shift_type').value = btn.dataset.type;
                document.getElementById('edit_shift_start_time').value = btn.dataset.startTime;
                document.getElementById('edit_shift_end_time').value = btn.dataset.endTime;
                document.getElementById('edit_shift_grace').value = btn.dataset.grace;
                document.getElementById('edit_shift_active').checked = btn.dataset.active === '1';

                var weekOff = JSON.parse(btn.dataset.weekOff || '[]');
                document.querySelectorAll('.edit-week-off-day').forEach(function (cb) {
                    cb.checked = weekOff.includes(parseInt(cb.value, 10));
                });
            });
        });
    </script>
@include('modules.payroll._ajax')
</div>
</div>
</x-app-layout>
