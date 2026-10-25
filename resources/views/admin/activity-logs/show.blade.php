@extends('admin.layouts.app')
@section('title', 'Activity Log #' . $activityLog->id)
@section('content')
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-0">Activity Log #{{ $activityLog->id }}</h4>
    </div>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Details</span></div>
        <div class="card-body px-4 pb-4 pt-0">
            <table class="table table-borderless align-middle mb-0">
                <tbody>
                <tr><th class="text-secondary ps-0 w-25">Admin</th><td class="text-dark pe-0">{{ $activityLog->admin?->name ?? 'System' }} ({{ $activityLog->admin?->email ?? '-' }})</td></tr>
                <tr><th class="text-secondary ps-0">Action</th><td class="pe-0"><span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">{{ ucfirst($activityLog->action) }}</span></td></tr>
                <tr><th class="text-secondary ps-0">Module</th><td class="pe-0"><span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1">{{ ucfirst(str_replace('_', ' ', $activityLog->module)) }}</span></td></tr>
                <tr><th class="text-secondary ps-0">Record ID</th><td class="text-dark pe-0">{{ $activityLog->module_id ?? '-' }}</td></tr>
                <tr><th class="text-secondary ps-0">Description</th><td class="text-dark pe-0">{{ $activityLog->description ?? '-' }}</td></tr>
                <tr><th class="text-secondary ps-0">IP address</th><td class="text-dark pe-0">{{ $activityLog->ip_address ?? '-' }}</td></tr>
                <tr><th class="text-secondary ps-0">Browser</th><td class="text-muted fs-7 pe-0">{{ $activityLog->user_agent ?? '-' }}</td></tr>
                <tr><th class="text-secondary ps-0">Date / time</th><td class="text-muted fs-7 pe-0">{{ $activityLog->created_at->format('d-m-Y H:i:s') }}</td></tr>
                </tbody>
            </table>
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 mt-3"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
@endsection
