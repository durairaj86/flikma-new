@extends('admin.layouts.app')
@section('title', 'Newsletters')
@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Newsletters</h4>
            <p class="text-muted small mb-0">Compose and send bulk email announcements to companies.</p>
        </div>
        <a href="{{ route('admin.newsletters.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> Create Newsletter
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th class="ps-4">Subject</th><th>Recipients</th><th>Status</th><th>Scheduled</th><th>Sent</th><th>Created by</th><th class="text-end pe-4">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($newsletters as $n)
                    @php $sc = ['sent' => 'success', 'scheduled' => 'warning'][$n->status] ?? 'secondary'; @endphp
                    <tr>
                        <td class="ps-4 fw-semibold"><a href="{{ route('admin.newsletters.show', $n) }}" class="text-dark text-decoration-none">{{ $n->subject }}</a></td>
                        <td>
                            <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1">{{ \App\Models\Admin\Newsletter::RECIPIENT_TYPES[$n->recipient_type] ?? ucfirst($n->recipient_type) }}</span>
                            @if($n->recipients_count)<div class="small text-muted">{{ $n->recipients_count }} queued</div>@endif
                        </td>
                        <td><span class="badge rounded-pill bg-{{ $sc }}-subtle text-{{ $sc }}-emphasis px-2 py-1">{{ ucfirst($n->status) }}</span></td>
                        <td><small class="text-muted">{{ $n->scheduled_at?->format('d-m-Y H:i') ?? '-' }}</small></td>
                        <td><small class="text-muted">{{ $n->sent_at?->format('d-m-Y H:i') ?? '-' }}</small></td>
                        <td class="text-muted fs-7">{{ $n->creator?->name ?? '-' }}</td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm border" type="button" data-bs-toggle="dropdown" aria-label="Actions"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.newsletters.show', $n) }}"><i class="bi bi-eye text-primary me-2"></i>View</a></li>
                                    @if($n->status !== 'sent')
                                        <li><form action="{{ route('admin.newsletters.send', $n) }}" method="POST" onsubmit="return confirm('Send this newsletter now?')">@csrf<button type="submit" class="dropdown-item"><i class="bi bi-send text-success me-2"></i>Send now</button></form></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form action="{{ route('admin.newsletters.destroy', $n) }}" method="POST" onsubmit="return confirm('Delete this newsletter?')">@csrf @method('DELETE')<button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button></form></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-envelope-paper display-4 mb-3 d-block opacity-50"></i>
                            <p class="mb-1 fw-semibold">No newsletters yet</p>
                            <p class="small">Create one to email every company, only active or expired subscribers, or companies you pick.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($newsletters->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-end">{{ $newsletters->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
