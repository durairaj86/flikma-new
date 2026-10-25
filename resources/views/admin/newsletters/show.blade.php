@extends('admin.layouts.app')
@section('title', $newsletter->subject)
@section('content')
    @php
        $sc = ['sent' => 'success', 'scheduled' => 'warning'][$newsletter->status] ?? 'secondary';
        // delivery comes from each queued email
        $byStatus = $newsletter->recipients->groupBy(fn ($r) => $r->queuedEmail?->status ?? 'missing')->map->count();
    @endphp
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-0">{{ $newsletter->subject }}</h4>
    </div>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Details</span></div>
                <div class="card-body px-4 pb-4 pt-0">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                        <tr><th class="text-secondary ps-0 w-25">Recipients</th><td class="pe-0"><span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1">{{ \App\Models\Admin\Newsletter::RECIPIENT_TYPES[$newsletter->recipient_type] ?? $newsletter->recipient_type }}</span></td></tr>
                        <tr><th class="text-secondary ps-0">Status</th><td class="pe-0"><span class="badge rounded-pill bg-{{ $sc }}-subtle text-{{ $sc }}-emphasis px-2 py-1">{{ ucfirst($newsletter->status) }}</span></td></tr>
                        @if($newsletter->scheduled_at)<tr><th class="text-secondary ps-0">Scheduled</th><td class="text-muted fs-7 pe-0">{{ $newsletter->scheduled_at->format('d-m-Y H:i') }}</td></tr>@endif
                        <tr><th class="text-secondary ps-0">Created</th><td class="text-muted fs-7 pe-0">{{ $newsletter->created_at->format('d-m-Y H:i') }}</td></tr>
                        <tr><th class="text-secondary ps-0">Sent</th><td class="text-muted fs-7 pe-0">{{ $newsletter->sent_at?->format('d-m-Y H:i') ?? '-' }}</td></tr>
                        <tr><th class="text-secondary ps-0">By</th><td class="text-dark pe-0">{{ $newsletter->creator?->name ?? '-' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                @if($newsletter->status !== 'sent')
                    <div class="card-footer bg-white border-0 pt-0 pb-4 px-4">
                        <form action="{{ route('admin.newsletters.send', $newsletter) }}" method="POST" onsubmit="return confirm('Send this newsletter now?')">@csrf
                            <button type="submit" class="btn btn-success w-100 d-inline-flex align-items-center justify-content-center gap-1"><i class="bi bi-send"></i> Send now</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Email body</span></div>
                <div class="card-body">{!! $newsletter->body !!}</div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex flex-wrap align-items-center gap-2">
                    <span class="fw-bold text-dark">Delivery</span>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">{{ $newsletter->recipients->count() }}</span>
                    @foreach(['sent' => 'success', 'pending' => 'primary', 'processing' => 'primary', 'failed' => 'danger', 'skipped' => 'secondary'] as $st => $c)
                        @if($byStatus[$st] ?? 0)<span class="badge bg-{{ $c }}-subtle text-{{ $c }}-emphasis">{{ $byStatus[$st] }} {{ $st }}</span>@endif
                    @endforeach
                    <a href="{{ route('admin.email-queue.index', ['type' => 'newsletter']) }}" class="ms-auto small text-decoration-none">Open in Email Queue <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th class="ps-4">Email</th><th>Status</th><th class="pe-4">Sent at</th></tr></thead>
                        <tbody>
                        @forelse($newsletter->recipients as $r)
                            @php
                                $q = $r->queuedEmail;
                                $c = ['sent' => 'success', 'failed' => 'danger', 'skipped' => 'secondary'][$q?->status] ?? 'primary';
                            @endphp
                            <tr>
                                <td class="ps-4 text-dark">{{ $r->email }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $c }}-subtle text-{{ $c }}-emphasis px-2 py-1">{{ $q?->status ?? 'missing' }}</span>
                                    @if($q?->error_message)<div class="small text-danger text-truncate" style="max-width: 320px;" title="{{ $q->error_message }}">{{ $q->error_message }}</div>@endif
                                </td>
                                <td class="pe-4"><small class="text-muted">{{ $q?->sent_at?->format('d-m-Y H:i') ?? '-' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted">Not sent yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
