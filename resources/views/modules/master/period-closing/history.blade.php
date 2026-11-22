@php
    $actionBadges = [
        'created' => ['bg-primary-subtle text-primary', 'bi-plus-circle', __('Added')],
        'updated' => ['bg-secondary-subtle text-secondary', 'bi-pencil', __('Edited')],
        'closed' => ['bg-danger-subtle text-danger', 'bi-lock-fill', __('Closed')],
        'reopened' => ['bg-success-subtle text-success', 'bi-unlock-fill', __('Reopened')],
        'deleted' => ['bg-dark-subtle text-dark', 'bi-trash', __('Deleted')],
    ];
    $fieldLabels = ['year' => __('Year'), 'closing_date' => __('Closing Date'), 'notes' => __('Notes')];
@endphp
<div class="container px-4 py-3" id="modal-buttons" data-buttons="cancel">
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <span class="fw-semibold fs-5">{{ $title ? __('Period :year history', ['year' => $title]) : __('Period Closing History') }}</span>
            <div id="show-buttons"></div>
        </div>
    </div>
    @if($logs->isEmpty())
        <p class="text-muted text-center py-4 mb-0">{{ __('No history yet.') }}</p>
    @else
        <div class="table-responsive" style="max-height: 60vh;">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light sticky-top">
                <tr>
                    <th>{{ __('Date & Time') }}</th>
                    @unless($title)<th>{{ __('Year') }}</th>@endunless
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Closing Date') }}</th>
                    <th>{{ __('Changes') }}</th>
                    <th>{{ __('By') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    @php([$badgeClass, $icon, $label] = $actionBadges[$log->action] ?? ['bg-light text-body', 'bi-dot', ucfirst((string) $log->action)])
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at?->translatedFormat('d-m-Y h:i A') }}</td>
                        @unless($title)<td>{{ $log->loggable_number }}</td>@endunless
                        <td><span class="badge {{ $badgeClass }}"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</span></td>
                        <td class="text-nowrap">{{ $log->loggable_name ?: '—' }}</td>
                        <td class="small">
                            @forelse($log->changes ?? [] as $field => $change)
                                <div>{{ $fieldLabels[$field] ?? $field }}: <span class="text-muted text-decoration-line-through">{{ $change['old'] ?? '—' }}</span>
                                    → {{ $change['new'] ?? '—' }}</div>
                            @empty
                                —
                            @endforelse
                        </td>
                        <td>{{ $log->user_id['name'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
