@extends('admin.layouts.app')
@section('title', 'Enquiries')
@section('content')
    @php
        $status = $filters['status'] ?? null;
        $hasFilters = collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty();
        // stat cards double as the status filter
        $cards = [
            ['key' => null, 'label' => 'Inbox', 'value' => $stats['inbox'], 'color' => '#111827'],
            ['key' => 'unread', 'label' => 'Unread', 'value' => $stats['unread'], 'color' => '#4f46e5'],
            ['key' => 'unreplied', 'label' => 'Not replied', 'value' => $stats['unreplied'], 'color' => '#d97706'],
            ['key' => 'replied', 'label' => 'Replied', 'value' => $stats['replied'], 'color' => '#16a34a'],
            ['key' => 'month', 'label' => 'This month', 'value' => $stats['month'], 'color' => '#0ea5e9'],
            ['key' => 'closed', 'label' => 'Closed', 'value' => $stats['closed'], 'color' => '#0f766e'],
            ['key' => 'archived', 'label' => 'Archived', 'value' => $stats['archived'], 'color' => '#6b7280'],
        ];
        // a calm colour per topic so the same subject is always recognisable
        $topicPalette = ['#4f46e5', '#0ea5e9', '#16a34a', '#d97706', '#db2777', '#7c3aed', '#0d9488'];
        $topicColor = fn (?string $t) => $topicPalette[abs(crc32((string) $t)) % count($topicPalette)];
    @endphp

    <div class="mb-4">
        <h1 class="h3 fw-bold text-dark mb-1">Enquiries</h1>
        <p class="text-muted mb-0">Messages from the Flikma website contact form.</p>
    </div>

    <div class="en-stats mb-4">
        @foreach($cards as $card)
            @php
                $isOn = $card['key'] === null ? ! $hasFilters : $status === $card['key'];
                $href = $card['key'] === null ? route('admin.enquiries.index') : route('admin.enquiries.index', ['status' => $card['key']]);
            @endphp
            <a href="{{ $href }}" class="en-stat {{ $isOn ? 'is-on' : '' }}">
                <div class="en-stat-lbl">{{ $card['label'] }}</div>
                <div class="en-stat-num" style="color: {{ $card['color'] }}">{{ number_format($card['value']) }}</div>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.enquiries.index') }}" class="en-filters mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label small fw-semibold text-secondary">Search</label>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px;"></i>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control ps-5" placeholder="Name, email, phone or words in the message…">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Status</label>
                <select name="status" class="form-select">
                    <option value="">Inbox (all)</option>
                    @foreach(['unread' => 'Unread', 'read' => 'Read', 'unreplied' => 'Not replied', 'replied' => 'Replied', 'month' => 'This month', 'closed' => 'Closed', 'archived' => 'Archived'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($status === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Topic</label>
                <select name="topic" class="form-select">
                    <option value="">All topics</option>
                    @foreach($topics as $t)
                        <option value="{{ $t }}" @selected(($filters['topic'] ?? '') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">From</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">To</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
            <a href="{{ route('admin.enquiries.index') }}" class="btn btn-light border d-inline-flex align-items-center gap-1"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="bi bi-funnel"></i> Apply filters</button>
        </div>
    </form>

    <div class="text-muted small mb-2">
        Showing <strong class="text-dark">{{ $enquiries->count() }}</strong> of {{ number_format($enquiries->total()) }} enquiries
        @if($enquiries->lastPage() > 1) · page {{ $enquiries->currentPage() }} of {{ $enquiries->lastPage() }} @endif
    </div>

    <div class="en-list">
        @forelse($enquiries as $e)
            @php
                $initials = collect(preg_split('/\s+/', trim((string) $e->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                $tc = $topicColor($e->interest);
            @endphp
            <div class="en-row {{ ! $e->is_read ? 'is-unread' : '' }} {{ $e->closed_at ? 'is-closed' : '' }}" onclick="location.href='{{ route('admin.enquiries.show', $e) }}'" tabindex="0" onkeydown="if (event.key === 'Enter' && event.target === this) location.href='{{ route('admin.enquiries.show', $e) }}'">
                <div class="en-avatar" style="background: {{ $tc }}1a; color: {{ $tc }}">{{ $initials ?: '?' }}</div>
                <div class="en-who">
                    <div class="en-name">
                        @if(! $e->is_read)<span class="en-dot" title="Unread"></span>@endif{{ $e->name }}
                    </div>
                    <div class="en-contact">
                        <a href="mailto:{{ $e->email }}" onclick="event.stopPropagation()">{{ $e->email }}</a>
                        @if($e->company)<span>· {{ $e->company }}</span>@endif
                        @if($e->phone && strcasecmp($e->phone, 'Not provided') !== 0)<span>· {{ $e->phone }}</span>@endif
                    </div>
                </div>
                <div class="en-msg">
                    <span class="en-topic" style="color: {{ $tc }}; background: {{ $tc }}14; border-color: {{ $tc }}33">{{ $e->interest ?: 'General enquiry' }}</span>
                    <div class="en-preview">{{ \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $e->message), 220) }}</div>
                    <div class="en-tags">
                        @if($e->replied_at)<span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-reply-fill me-1"></i>Replied {{ $e->replied_at->diffForHumans() }}</span>@endif
                        @if($e->closed_at)<span class="badge bg-info-subtle text-info-emphasis text-wrap text-start" title="{{ $e->close_note }}"><i class="bi bi-check2-circle me-1"></i>Closed {{ $e->closed_at->diffForHumans() }}@if($e->close_reason) · {{ \Illuminate\Support\Str::limit($e->close_reason_label, 60) }}@endif</span>@endif
                        @if($e->assignee)<span class="badge bg-light text-secondary border"><i class="bi bi-person me-1"></i>{{ $e->assignee->name }}</span>@endif
                        @if($e->archived_at)<span class="badge bg-secondary-subtle text-secondary-emphasis"><i class="bi bi-archive me-1"></i>Archived</span>@endif
                    </div>
                </div>
                <div class="en-when" title="{{ $e->created_at->format('d-m-Y H:i') }}">
                    <div>{{ $e->created_at->diffForHumans(null, true, true) }}</div>
                    <div class="text-muted">{{ $e->created_at->format('d-m-Y') }}</div>
                </div>
                <div class="en-actions" onclick="event.stopPropagation()">
                    <a href="{{ route('admin.enquiries.show', $e) }}#reply" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"><i class="bi bi-reply"></i> Reply</a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown" aria-label="More"><i class="bi bi-three-dots"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('admin.enquiries.show', $e) }}"><i class="bi bi-eye text-primary me-2"></i>Open</a></li>
                            <li>
                                <form action="{{ route('admin.enquiries.mark-read', $e) }}" method="POST">@csrf
                                    <button class="dropdown-item"><i class="bi {{ $e->is_read ? 'bi-envelope' : 'bi-envelope-open' }} me-2"></i>Mark as {{ $e->is_read ? 'unread' : 'read' }}</button>
                                </form>
                            </li>
                            <li>
                                @if($e->closed_at)
                                    <form action="{{ route('admin.enquiries.close', $e) }}" method="POST">@csrf
                                        <button class="dropdown-item"><i class="bi bi-arrow-counterclockwise text-warning me-2"></i>Reopen</button>
                                    </form>
                                @else
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#closeEnquiryModal" data-close-url="{{ route('admin.enquiries.close', $e) }}" data-name="{{ $e->name }} · {{ $e->interest ?: 'General enquiry' }}"><i class="bi bi-check2-circle text-success me-2"></i>Close…</button>
                                @endif
                            </li>
                            <li>
                                <form action="{{ route('admin.enquiries.archive', $e) }}" method="POST">@csrf
                                    <button class="dropdown-item"><i class="bi {{ $e->archived_at ? 'bi-inbox' : 'bi-archive' }} me-2"></i>{{ $e->archived_at ? 'Move to inbox' : 'Archive' }}</button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('admin.enquiries.destroy', $e) }}" method="POST" onsubmit="return confirm('Delete this enquiry? This cannot be undone.')">@csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @empty
            <div class="en-empty">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                {{ $hasFilters ? 'No enquiries match these filters.' : 'No enquiries yet — contact form messages will appear here.' }}
            </div>
        @endforelse
    </div>

    @if($enquiries->hasPages())
        <div class="mt-3">{{ $enquiries->links('pagination::bootstrap-5') }}</div>
    @endif

    @include('admin.enquiries._close-modal')
@endsection

@push('styles')
<style>
    .en-stats { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 12px; }
    @media (max-width: 1199.98px) { .en-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .en-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .en-stat { display: block; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 18px; text-decoration: none; transition: border-color .15s, box-shadow .15s; }
    .en-stat:hover { border-color: #c7d2fe; box-shadow: 0 4px 14px rgba(79,70,229,.08); }
    .en-stat.is-on { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
    .en-stat-lbl { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
    .en-stat-num { font-size: 1.6rem; font-weight: 700; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .en-filters { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px 20px; }
    .en-list { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; }
    .en-row:first-child { border-radius: 11px 11px 0 0; }
    .en-row:last-child { border-radius: 0 0 11px 11px; }
    .en-row:only-child { border-radius: 11px; }
    .en-row { display: flex; align-items: flex-start; gap: 16px; padding: 14px 18px; border-bottom: 1px solid #f1f3f5; cursor: pointer; transition: background .12s; position: relative; }
    .en-row:last-child { border-bottom: 0; }
    .en-row:hover { background: #f8fafc; }
    .en-row.is-unread { background: #fafaff; box-shadow: inset 3px 0 0 #4f46e5; }
    .en-row.is-unread .en-name, .en-row.is-unread .en-preview { color: #111827; font-weight: 600; }
    .en-row.is-closed .en-who, .en-row.is-closed .en-preview, .en-row.is-closed .en-avatar { opacity: .6; }
    .en-avatar { flex: 0 0 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; }
    .en-who { flex: 0 0 230px; min-width: 0; }
    .en-name { font-weight: 500; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 6px; }
    .en-dot { flex: 0 0 8px; height: 8px; border-radius: 50%; background: #4f46e5; }
    .en-contact { font-size: .8rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .en-contact a { color: #6b7280; text-decoration: none; }
    .en-contact a:hover { color: #4f46e5; }
    .en-msg { flex: 1 1 auto; min-width: 0; }
    .en-topic { display: inline-block; font-size: .72rem; font-weight: 600; border: 1px solid; border-radius: 6px; padding: 1px 8px; margin-bottom: 4px; }
    .en-preview { font-size: .85rem; color: #4b5563; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .en-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .en-tags:empty { display: none; }
    .en-tags .badge { font-weight: 500; }
    .en-when { flex: 0 0 90px; text-align: right; font-size: .8rem; color: #374151; white-space: nowrap; }
    .en-actions { flex: 0 0 auto; display: flex; gap: 6px; align-items: center; }
    .en-empty { text-align: center; color: #6b7280; padding: 48px 16px; }
    @media (max-width: 991.98px) {
        .en-row { flex-wrap: wrap; }
        .en-who { flex: 1 1 calc(100% - 56px); }
        .en-msg { flex-basis: 100%; order: 3; }
        .en-when { order: 2; flex-basis: 100%; text-align: left; display: flex; gap: 6px; padding-left: 56px; font-size: .75rem; }
        .en-when > div + div::before { content: '· '; }
        .en-actions { order: 4; margin-left: auto; }
    }
</style>
@endpush
