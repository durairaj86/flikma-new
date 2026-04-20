{{-- resources/views/activity/feed.blade.php --}}

<div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title fw-semibold text-dark" id="activityOffcanvasLabel">
        <svg class="w-6 h-6 me-2 text-primary" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        System Activity Log
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
</div>

<div class="offcanvas-body p-4">

    {{-- Remember to include the custom CSS for timeline-list/timeline-item/timeline-pin in your app.css --}}

    @forelse ($activities as $date => $logs)
        {{-- Date Header with Sticky Top --}}
        <div class="sticky-top bg-white pt-3 pb-2 -mx-4 px-4" style="top: -1px; z-index: 10;">
            <h6 class="fw-bold text-muted mb-0">
                {{ \Carbon\Carbon::parse($date)->isToday() ? 'TODAY' : strtoupper(\Carbon\Carbon::parse($date)->format('F j, Y')) }}
            </h6>
        </div>

        <ul class="list-unstyled timeline-list pt-2">
            @foreach ($logs as $log)
                <li class="timeline-item">
                    {{-- Timeline Pin (Point) --}}
                    <div class="timeline-pin {{ $log->action == 'created' ? 'bg-success' : 'bg-warning' }}"></div>

                    {{-- Activity Card --}}
                    <div class="card shadow-sm border-0 transition">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">

                                <div class="flex-grow-1">
                                    {{-- Primary Entity Identification (Name & Number) --}}
                                    <p class="mb-1 text-sm text-dark">
                                        <span class="text-primary fw-bold">{{ $log->loggable_name_display }}</span>
                                        <span class="badge bg-secondary-subtle text-secondary me-1 ms-2">{{ $log->loggable_number_display }}</span>
                                        {!! $log->formatted_changes !!}.
                                    </p>

                                    {{-- Detailed Changes as Proper Points/List --}}
                                    @php
                                        // Re-extract changes for detailed listing
                                        $changes = json_decode($log->changes, true);
                                        $newChanges = $changes['new'] ?? [];
                                        $oldChanges = $changes['old'] ?? [];
                                        unset($newChanges['updated_at'], $oldChanges['updated_at']);
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
                                    @elseif ($log->action == 'created')
                                        <span class="badge bg-success mt-2">New {{ $log->loggable_name_display }} Created</span>
                                    @endif
                                </div>

                                <div class="text-end ps-3 flex-shrink-0">
                                    {{-- Timestamp --}}
                                    <p class="small text-muted mb-0">
                                        {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                    </p>
                                    <p class="small text-muted mb-0">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @empty
        <div class="alert alert-info text-center mt-5" role="alert">
            No recent activity to display.
        </div>
    @endforelse
</div>
