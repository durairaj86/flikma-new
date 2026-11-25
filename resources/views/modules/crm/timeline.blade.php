@php($today = \App\Models\Crm\CrmActivity::localNowNaive()->startOfDay())
@forelse($activities as $a)
    @php($category = $a->action->category ?? null)
    <div class="crm-tl-item d-flex gap-2 py-2 border-bottom" data-activity-id="{{ $a->id }}">
        <div class="crm-tl-icon flex-none" style="{{ \App\Models\Crm\CrmAction::badgeStyle($category) }}"><i class="bi {{ \App\Models\Crm\CrmAction::icon($category) }}"></i></div>
        <div class="flex-grow-1 small">
            <div class="d-flex justify-content-between gap-2">
                <span class="fw-semibold">{{ $a->actionLabel() }}
                    @if($a->auto_key)<span class="badge bg-light text-secondary border fw-normal ms-1">{{ __('Auto') }}</span>@endif
                    @if(!empty($numbers[$a->subject_id]))<span class="badge bg-light text-muted border fw-normal ms-1">{{ $numbers[$a->subject_id] }}</span>@endif
                </span>
                @unless($a->follow_up_at)
                    <span class="text-muted">{{ $a->activity_at?->translatedFormat('d-M-Y H:i') }}</span>
                @endunless
            </div>
            @if($a->notes)<div class="text-body" style="white-space:pre-line">{{ $a->notes }}</div>@endif
            @if($a->follow_up_at)
                @php($late = !$a->follow_up_done_at && $a->follow_up_at->lt($today))
                <div class="mt-1 {{ $a->follow_up_done_at ? 'text-success' : ($late ? 'text-danger' : 'text-primary') }}">
                    <i class="bi {{ $a->follow_up_done_at ? 'bi-check2-circle' : 'bi-alarm' }} me-1"></i>
                    {{ __('Due') }}: {{ $a->follow_up_at->translatedFormat('d-M-Y H:i') }}
                    @if($a->assignee) · {{ $a->assignee->name }} @endif
                    @if($a->follow_up_done_at) · {{ __('Done') }} {{ $a->follow_up_done_at->translatedFormat('d-M-Y') }} @endif
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
