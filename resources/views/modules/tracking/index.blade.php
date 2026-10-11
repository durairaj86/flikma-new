@section('page-title', __('Shipment Tracking'))
@section('page-subtitle', __('Where every shipment is right now'))
@section('js','tracking')
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <style>
            .trk-card { border: 1px solid #e9ecef; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: .75rem; }
            .trk-line { display: flex; gap: 0; margin-top: .9rem; }
            .trk-step { flex: 1; text-align: center; position: relative; font-size: .72rem; color: #868e96; }
            .trk-step::before { content: ''; position: absolute; top: 9px; left: -50%; width: 100%; height: 3px; background: #dee2e6; z-index: 0; }
            .trk-step:first-child::before { display: none; }
            .trk-step.done::before { background: #198754; }
            .trk-dot { width: 20px; height: 20px; border-radius: 50%; background: #dee2e6; margin: 0 auto .3rem; position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .7rem; }
            .trk-step.done .trk-dot { background: #198754; }
            .trk-step.done { color: #212529; }
            .trk-step.skip::before { background: #f59e0b; }
            .trk-step.skip .trk-dot { background: #f59e0b; }
            .trk-step.skip { color: #c2410c; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-3">
            <h4 class="fw-bold text-dark mb-0">{{ __('Shipment Tracking') }}</h4>
            <form class="d-flex gap-2 ms-auto" method="get">
                <input type="text" name="q" value="{{ $q }}" class="form-control rounded-pill" placeholder="{{ __('Search job, customer, B/L, container...') }}" style="min-width:280px">
                <select name="show" class="form-select rounded-pill" onchange="this.form.submit()" style="width:auto">
                    <option value="">{{ __('Active jobs') }}</option>
                    <option value="all" @selected($show === 'all')>{{ __('All jobs') }}</option>
                </select>
            </form>
        </div>
        @forelse($rows as $r)
            @php $j = $r['job']; @endphp
            <div class="trk-card">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <span class="fw-bold">{{ $j->row_no }}</span>
                        <span class="text-muted ms-2">{{ $j->customer?->name_en }}</span>
                        <div class="small text-muted">{{ $j->pol_name ?? $j->origin }} → {{ $j->pod_name ?? $j->destination }}
                            @if($j->eta) · ETA {{ \App\Services\Job\JobInsights::date($j->eta)?->format('d-M-Y') ?? $j->eta }} @endif</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">{{ $r['done'] }}/{{ $r['total'] }}@if($r['skipped']) · {{ $r['skipped'] }} {{ __('skipped') }}@endif @if($r['next']) · {{ __('Next') }}: {{ __($r['next']) }} @else · {{ __('Complete') }} @endif</span>
                        <button class="btn btn-sm btn-primary rounded-pill px-3 trk-update" data-id="{{ $j->id }}" data-title="{{ $j->row_no }}">{{ __('Update') }}</button>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill trk-share" data-id="{{ $j->id }}"><i class="bi bi-link-45deg"></i> {{ __('Share') }}</button>
                    </div>
                </div>
                <div class="trk-line">
                    @foreach($r['steps'] as $s)
                        <div class="trk-step {{ $s['actual'] ? 'done' : ($s['skipped'] ? 'skip' : '') }}">
                            <div class="trk-dot">@if($s['actual'])<i class="bi bi-check"></i>@elseif($s['skipped'])<i class="bi bi-dash"></i>@endif</div>
                            <div>{{ __($s['label']) }}</div>
                            @if($s['actual'])<div class="text-muted">{{ $s['actual']->format('d-M-Y') }}</div>@elseif($s['skipped'])<div>{{ __('Skipped') }}</div>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">{{ __('No shipments to track.') }}</div>
        @endforelse
    </main>
</x-app-layout>
