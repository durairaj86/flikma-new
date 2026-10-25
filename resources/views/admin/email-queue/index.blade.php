@extends('admin.layouts.app')

@section('title', 'Email Queue')

@section('content')
    @php
        $cards = [
            ['key' => null, 'label' => 'Total', 'value' => $stats['total'], 'color' => '#111827'],
            ['key' => 'sent', 'label' => 'Sent', 'value' => $stats['sent'], 'color' => '#16a34a'],
            ['key' => 'pending', 'label' => 'Pending', 'value' => $stats['pending'], 'color' => '#2563eb'],
            ['key' => 'retrying', 'label' => 'Retrying', 'value' => $stats['retrying'], 'color' => '#d97706'],
            ['key' => 'gave_up', 'label' => 'Failed', 'value' => $stats['gave_up'], 'color' => '#dc2626'],
            ['key' => 'skipped', 'label' => 'Skipped', 'value' => $stats['skipped'], 'color' => '#6b7280'],
            ['key' => 'today', 'label' => 'Sent today', 'value' => $stats['today'], 'color' => '#4f46e5'],
        ];
        $status = $filters['status'] ?? null;
        $hasFilters = collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty();
    @endphp

    <div class="mb-4">
        <h1 class="h3 fw-bold text-dark mb-1">Email Queue</h1>
        <p class="text-muted mb-0">Every email Flikma queued — sent within a minute by <code>emails:process</code>. Failed emails are retried automatically up to {{ $maxAttempts }} times.</p>
    </div>

    {{-- stat cards (click = filter) --}}
    <div class="ad-stats mb-4" style="--cols: 7">
        @foreach($cards as $card)
            @php
                $href = $card['key'] === null ? route('admin.email-queue.index')
                    : ($card['key'] === 'today' ? route('admin.email-queue.index', ['status' => 'sent', 'from' => today()->toDateString()])
                    : route('admin.email-queue.index', ['status' => $card['key']]));
                $isOn = $card['key'] === null ? ! $hasFilters : ($card['key'] === 'today' ? ($status === 'sent' && ($filters['from'] ?? null) === today()->toDateString()) : $status === $card['key'] && empty($filters['from']));
            @endphp
            <a href="{{ $href }}" class="ad-stat {{ $isOn ? 'is-on' : '' }}">
                <div class="ad-stat-lbl">{{ $card['label'] }}</div>
                <div class="ad-stat-num" style="color: {{ $card['color'] }}">{{ number_format($card['value']) }}</div>
            </a>
        @endforeach
    </div>

    {{-- filters --}}
    <form method="GET" action="{{ route('admin.email-queue.index') }}" class="ad-box ad-filters mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label small fw-semibold text-secondary">Search</label>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px;"></i>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control ps-5" placeholder="Recipient email or subject…">
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['sent' => 'Sent', 'pending' => 'Pending', 'retrying' => 'Failed — retrying', 'gave_up' => 'Failed — gave up', 'failed' => 'Failed (all)', 'skipped' => 'Skipped'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($status === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Type</label>
                <select name="type" class="form-select">
                    <option value="">All</option>
                    @foreach($types as $view => $label)
                        <option value="{{ $view }}" @selected(($filters['type'] ?? '') === $view)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Queued from</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold text-secondary">Queued to</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
            <a href="{{ route('admin.email-queue.index') }}" class="btn btn-light border d-inline-flex align-items-center gap-1"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="bi bi-funnel"></i> Apply filters</button>
        </div>
    </form>

    <div class="text-muted small mb-2">
        Showing <strong class="text-dark">{{ $emails->count() }}</strong> of {{ number_format($emails->total()) }} emails
        @if($emails->lastPage() > 1) · page {{ $emails->currentPage() }} of {{ $emails->lastPage() }} @endif
    </div>

    <div class="eq-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0 eq-table">
                <thead>
                <tr>
                    <th>#</th><th>Type</th><th>To</th><th>Subject</th><th>Status</th><th class="text-center">Attempts</th><th>Queued</th><th>Sent / Failed / Skipped</th><th class="text-end"></th>
                </tr>
                </thead>
                <tbody>
                @forelse($emails as $email)
                    @php
                        $gaveUp = $email->status === 'failed' && $email->attempts >= $maxAttempts;
                        [$badgeClass, $badgeText] = match (true) {
                            $email->status === 'sent' => ['bg-success-subtle text-success-emphasis border-success-subtle', 'Sent'],
                            in_array($email->status, ['pending', 'processing'], true) => ['bg-primary-subtle text-primary-emphasis border-primary-subtle', $email->status === 'processing' ? 'Sending' : 'Pending'],
                            $email->status === 'skipped' => ['bg-secondary-subtle text-secondary-emphasis border-secondary-subtle', 'Skipped'],
                            $gaveUp => ['bg-danger-subtle text-danger-emphasis border-danger-subtle', 'Failed'],
                            default => ['bg-warning-subtle text-warning-emphasis border-warning-subtle', 'Retrying'],
                        };
                        $doneAt = match ($email->status) { 'sent' => $email->sent_at, 'failed' => $email->failed_at, 'skipped' => $email->skipped_at, default => null };
                    @endphp
                    <tr class="eq-row {{ $email->status === 'skipped' ? 'is-skipped' : '' }}" data-url="{{ route('admin.email-queue.show', $email) }}" tabindex="0">
                        <td class="text-muted">{{ $email->id }}</td>
                        <td><span class="eq-type">{{ $typeLabels[$email->type] ?? ($email->type ? ucfirst(str_replace('_', ' ', $email->type)) : $email->view) }}</span></td>
                        <td class="fw-medium text-truncate" style="max-width: 220px;">{{ $email->to }}</td>
                        <td class="text-truncate" style="max-width: 340px;" title="{{ $email->subject }}">
                            {{ $email->subject }}
                            @if($email->error_message)
                                <div class="small text-danger text-truncate" title="{{ $email->error_message }}"><i class="bi bi-exclamation-triangle me-1"></i>{{ $email->error_message }}</div>
                            @endif
                        </td>
                        <td><span class="badge rounded-pill border {{ $badgeClass }}">{{ $badgeText }}</span></td>
                        <td class="text-center">{{ $email->attempts }}</td>
                        <td class="text-nowrap">{{ $email->created_at?->format('d-m-Y H:i') }}</td>
                        <td class="text-nowrap {{ $email->status === 'failed' ? 'text-danger' : '' }}">{{ $doneAt?->format('d-m-Y H:i') ?? '—' }}</td>
                        <td class="text-end text-nowrap">
                            @if($email->status === 'failed')
                                <form action="{{ route('admin.email-queue.retry', $email) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1"><i class="bi bi-arrow-repeat"></i> Retry</button>
                                </form>
                                <form action="{{ route('admin.email-queue.skip', $email) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-secondary d-inline-flex align-items-center gap-1" title="Stop retrying and don't count it as failed"><i class="bi bi-skip-forward"></i> Skip</button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-sm btn-light border eq-view" title="Details"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-envelope-open fs-1 d-block mb-2 opacity-50"></i>
                        {{ $hasFilters ? 'No emails match these filters.' : 'No emails queued yet.' }}
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($emails->hasPages())
        <div class="mt-3">{{ $emails->links('pagination::bootstrap-5') }}</div>
    @endif

    {{-- details panel --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="eqDetails" style="width: 480px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold">Email <span id="eqId" class="text-muted"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body" id="eqBody"><div class="text-muted">Loading…</div></div>
    </div>
@endsection

@push('styles')
<style>
    .eq-row.is-skipped td { color: #9ca3af; }
    .eq-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; }
    .eq-card { overflow: hidden; }
    .eq-table thead th { background: #f9fafb !important; color: #6b7280; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; padding: 10px 14px; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
    .eq-table tbody td { padding: 10px 14px; font-size: .85rem; color: #374151; border-color: #f1f3f5; }
    .eq-row { cursor: pointer; }
    .eq-row:hover td { background: #f8fafc; }
    .eq-type { display: inline-block; font-size: .72rem; font-weight: 600; color: #4338ca; background: #eef2ff; border-radius: 6px; padding: 2px 8px; white-space: nowrap; }
    .eq-dl { display: grid; grid-template-columns: 110px 1fr; row-gap: 8px; font-size: .875rem; }
    .eq-dl dt { color: #6b7280; font-weight: 400; } .eq-dl dd { margin: 0; word-break: break-word; }
    .eq-pre { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; font-size: .78rem; white-space: pre-wrap; word-break: break-word; max-height: 320px; overflow: auto; }
</style>
@endpush

@push('scripts')
<script>
    // row click → details panel (recipient, timings, error, the data the email was built from)
    (function () {
        const panel = new bootstrap.Offcanvas(document.getElementById('eqDetails'));
        const body = document.getElementById('eqBody');
        const esc = s => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
        document.querySelectorAll('.eq-row').forEach(function (row) {
            const open = function () {
                body.innerHTML = '<div class="text-muted">Loading…</div>';
                panel.show();
                fetch(row.dataset.url, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(e => {
                        document.getElementById('eqId').textContent = '#' + e.id;
                        body.innerHTML =
                            '<dl class="eq-dl mb-3">' +
                                '<dt>Status</dt><dd class="fw-semibold">' + esc(e.status) + ' (' + e.attempts + ' attempt' + (e.attempts === 1 ? '' : 's') + ')</dd>' +
                                '<dt>Type</dt><dd>' + esc(e.type) + '</dd>' +
                                '<dt>To</dt><dd>' + esc(e.to) + '</dd>' +
                                (e.from ? '<dt>From</dt><dd>' + esc(e.from) + '</dd>' : '') + (e.cc ? '<dt>Cc</dt><dd>' + esc(e.cc) + '</dd>' : '') +
                                '<dt>Subject</dt><dd>' + esc(e.subject) + '</dd>' +
                                '<dt>Queued</dt><dd>' + esc(e.created_at) + '</dd>' +
                                '<dt>Sent</dt><dd>' + esc(e.sent_at || '—') + '</dd>' +
                                (e.failed_at ? '<dt>Failed</dt><dd class="text-danger">' + esc(e.failed_at) + '</dd>' : '') +
                                (e.skipped_at ? '<dt>Skipped</dt><dd class="text-secondary">' + esc(e.skipped_at) + '</dd>' : '') +
                            '</dl>' +
                            (e.error ? '<div class="fw-semibold small text-danger mb-1">Error</div><div class="eq-pre text-danger mb-3">' + esc(e.error) + '</div>' : '') +
                            '<div class="fw-semibold small text-secondary mb-1">Email data</div><div class="eq-pre">' + esc(typeof e.data === 'string' ? e.data : JSON.stringify(e.data, null, 2)) + '</div>';
                    })
                    .catch(() => { body.innerHTML = '<div class="text-danger">Could not load this email.</div>'; });
            };
            row.addEventListener('click', open);
            row.addEventListener('keydown', e => { if (e.key === 'Enter' && e.target === row) open(); });
        });
    })();
</script>
@endpush
