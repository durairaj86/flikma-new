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
                {{-- Timeline Pin --}}
                <div class="timeline-pin {{ $log->action == 'created' ? 'bg-success' : 'bg-warning' }}"></div>

                {{-- Activity Card --}}
                <div class="card shadow-sm border-0 hover-shadow-lg transition">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">

                            <div class="flex-grow-1">
                                {{-- Activity Summary --}}
                                <p class="mb-1 text-sm text-dark">
                                    <span
                                        class="text-primary fw-bold">{{ $log->loggable_name }} ID {{ $log->loggable_id }}</span>
                                    {!! $log->formatted_changes !!}.
                                </p>

                                {{-- Detailed Changes as Points --}}
                                @php
                                    // Re-extract changes for detailed listing
                                    $changes = json_decode($log->changes, true);
                                    $newChanges = $changes['new'] ?? [];
                                    $oldChanges = $changes['old'] ?? [];
                                    unset($newChanges['updated_at'], $oldChanges['updated_at']);
                                @endphp

                                @if (!empty($newChanges) && $log->action == 'updated')
                                    <ul class="list-unstyled small mt-2 mb-0 ms-3">
                                        @foreach ($newChanges as $key => $newValue)
                                            @php $oldValue = $oldChanges[$key] ?? 'N/A'; @endphp
                                            <li>
                                                <span
                                                    class="badge rounded-pill bg-secondary-subtle text-secondary me-1">{{ $key }}</span>
                                                changed from **"{{ $oldValue }}"** to **"{{ $newValue }}"**
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($log->action == 'created')
                                    <span class="badge bg-success mt-2">New Record Created</span>
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
                                {{-- User ID if needed: (User {{ $log->user_id }}) --}}
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
