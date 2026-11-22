@php($types = \App\Models\Crm\CrmActivity::TYPES)
@php($today = \App\Models\Crm\CrmActivity::localNowNaive()->startOfDay())
@forelse($activities as $a)
    <div class="crm-tl-item d-flex gap-2 py-2 border-bottom" data-activity-id="{{ $a->id }}">
        <div class="crm-tl-icon flex-none"><i class="bi {{ $types[$a->type]['icon'] ?? 'bi-dot' }}"></i></div>
        <div class="flex-grow-1 small">
            <div class="d-flex justify-content-between gap-2">
                <span class="fw-semibold">{{ \App\Models\Crm\CrmActivity::typeLabel($a->type) }}
                    @if(!empty($numbers[$a->subject_id]))<span class="badge bg-light text-muted border fw-normal ms-1">{{ $numbers[$a->subject_id] }}</span>@endif
                </span>
                <span class="text-muted">{{ $a->activity_at?->translatedFormat('d-M-Y H:i') }}</span>
            </div>
            <div class="text-body" style="white-space:pre-line">{{ $a->notes }}</div>
            @if($a->follow_up_at)
                @php($late = !$a->follow_up_done_at && $a->follow_up_at->lt($today))
                <div class="mt-1 {{ $a->follow_up_done_at ? 'text-success' : ($late ? 'text-danger' : 'text-primary') }}">
                    <i class="bi {{ $a->follow_up_done_at ? 'bi-check2-circle' : 'bi-alarm' }} me-1"></i>
                    {{ __('Follow-up') }}: {{ $a->follow_up_at->translatedFormat('d-M-Y H:i') }}
                    @if($a->assignee) · {{ $a->assignee->name }} @endif
                    @if($a->follow_up_note) — {{ $a->follow_up_note }} @endif
                    @if(!$a->follow_up_done_at)
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 crm-tl-done" data-id="{{ $a->id }}">{{ __('Mark as Done') }}</button>
                    @endif
                </div>
            @endif
            <div class="text-muted">{{ __('By') }} {{ $a->creator->name ?? __('System') }}</div>
        </div>
    </div>
@empty
    <div class="text-muted small py-3 text-center">{{ __('No activity yet. Log the first call or meeting.') }}</div>
@endforelse
