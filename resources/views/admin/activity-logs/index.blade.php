@extends('admin.layouts.app')
@section('title', 'Activity Logs')
@section('content')
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-1">Activity Logs</h4>
        <p class="text-muted small mb-0">Audit trail of every action performed in the admin panel.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <form method="GET" class="card-header bg-white border-0 py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-bold text-dark">All logs <span class="text-muted fw-normal small">({{ number_format($logs->total()) }})</span></span>
            <div class="d-flex gap-2">
                <select name="module" class="form-select form-select-sm" style="width:auto;" aria-label="Module" onchange="this.form.submit()">
                    <option value="">All modules</option>
                    @foreach($modules as $m)<option value="{{ $m }}" @selected(($filters['module'] ?? '') === $m)>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>@endforeach
                </select>
                <select name="action" class="form-select form-select-sm" style="width:auto;" aria-label="Action" onchange="this.form.submit()">
                    <option value="">All actions</option>
                    @foreach($actions as $a)<option value="{{ $a }}" @selected(($filters['action'] ?? '') === $a)>{{ ucfirst($a) }}</option>@endforeach
                </select>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th class="ps-4">Time</th><th>Admin</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th><th class="text-end pe-4">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="ps-4 text-nowrap"><small class="text-muted" title="{{ $log->created_at->diffForHumans() }}">{{ $log->created_at->format('d-m-Y H:i') }}</small></td>
                        <td class="text-dark">{{ $log->admin?->name ?? 'System' }}</td>
                        <td><span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">{{ ucfirst($log->action) }}</span></td>
                        <td><span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1">{{ ucfirst(str_replace('_', ' ', $log->module)) }}</span></td>
                        <td><small class="text-muted">{{ $log->description ?? '-' }}</small></td>
                        <td><small class="text-muted">{{ $log->ip_address ?? '-' }}</small></td>
                        <td class="text-end pe-4 text-nowrap">
                            <a href="{{ route('admin.activity-logs.show', $log) }}" class="btn btn-light btn-sm border" title="View" aria-label="View log"><i class="bi bi-eye"></i></a>
                            <form action="{{ route('admin.activity-logs.destroy', $log) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this log entry?')">@csrf @method('DELETE')
                                <button type="submit" class="btn btn-light btn-sm border text-danger" title="Delete" aria-label="Delete log"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-text display-4 mb-3 d-block opacity-50"></i>
                            <p class="mb-1 fw-semibold">No activity logs found</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-end">{{ $logs->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
