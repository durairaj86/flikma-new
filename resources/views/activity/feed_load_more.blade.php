{{-- resources/views/activity/item_partial.blade.php --}}

@foreach ($activities as $date => $logs)
    {{-- Date Header --}}
    <div class="sticky-top bg-white pt-3 pb-2 date-header" style="top: -1px; z-index: 10;">
        <h6 class="fw-bold text-muted mb-0">
            {{ \Carbon\Carbon::parse($date)->isToday() ? 'TODAY' : strtoupper(\Carbon\Carbon::parse($date)->format('F j, Y')) }}
        </h6>
    </div>

    <ul class="list-unstyled timeline-list pt-2">
        @foreach ($logs as $log)
            <li class="timeline-item">
                {{--<div class="timeline-pin {{ $log->action == 'created' ? 'bg-success' : 'bg-warning' }}"></div>--}}
                <div class="card shadow-sm border-0 transition">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-sm text-dark">
                                    <span class="text-primary fw-bold">{{ $log->loggable_name_display }}</span>
                                    <span class="badge bg-secondary-subtle text-secondary me-1 ms-2">{{ $log->loggable_number_display }}</span>
                                    {!! $log->formatted_changes !!}.
                                </p>

                                @php
                                    $changes = json_decode($log->changes, true);
                                    $newChanges = $changes['new'] ?? [];
                                    $oldChanges = $changes['old'] ?? [];
                                    unset($newChanges['updated_at'], $oldChanges['updated_at']);
                                    $actionByLog = json_decode($log->user_id);
                                @endphp

                                @if (!empty($newChanges) && $log->action == 'updated')
                                    <ul class="list-unstyled small mt-2 mb-0 ms-3 border-start ps-3 py-1">
                                        @foreach ($newChanges as $key => $newValue)
                                            @php $oldValue = $oldChanges[$key] ?? 'N/A'; @endphp
                                            <li class="text-muted">
                                                <span class="fw-medium me-1 text-dark">{{ $key }}:</span>
                                                <span class="text-decoration-line-through me-1">{{ $oldValue }}</span>
                                                &rarr; <span class="fw-semibold text-success">{{ $newValue }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <span class="text-warning mt-2">By : {{ $actionByLog->name ?? '' }}</span>
                                @elseif ($log->action == 'created')
                                    <span class="text-success mt-2">By : {{ $actionByLog->name ?? '' }}</span>
                                @endif
                            </div>

                            <div class="text-end ps-3 flex-shrink-0">
                                <p class="small text-muted mb-0">
                                    {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                </p>
                                <p class="small text-muted mb-0">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
            </li>
        @endforeach
    </ul>
@endforeach
