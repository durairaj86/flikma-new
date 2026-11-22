{{-- Customer 360 (CRM). Buttons here are handled by the page script in modules/customer/show.blade.php (tab HTML is inserted, its scripts would not run). --}}
@php
    $tones = ['primary' => 'bg-primary-subtle text-primary-emphasis', 'danger' => 'bg-danger-subtle text-danger-emphasis', 'success' => 'bg-success-subtle text-success-emphasis'];
@endphp
@if(!$onlyItems)
    <div class="cust-card">
        <div class="cust-card-b">
            <div class="row text-center g-0">
                @if($summary['canSales'])
                    <div class="col border-end py-1">
                        <div class="small fw-bold text-uppercase text-muted">{{ __('Open quotations') }}</div>
                        <div class="fs-5 fw-bold">{{ $summary['openQuotations'] }} <span class="small text-muted fw-normal">· {{ number_format($summary['openValue'], 2) }} {{ $baseCurrency }}</span></div>
                    </div>
                    <div class="col border-end py-1">
                        <div class="small fw-bold text-uppercase text-muted">{{ __('Win rate') }} <span class="fw-normal">(12 m)</span></div>
                        <div class="fs-5 fw-bold">{{ $summary['winRate'] === null ? '—' : $summary['winRate'] . '%' }}</div>
                    </div>
                @endif
                @if($summary['canJobs'])
                    <div class="col border-end py-1">
                        <div class="small fw-bold text-uppercase text-muted">{{ __('Jobs') }} <span class="fw-normal">(12 m)</span></div>
                        <div class="fs-5 fw-bold">{{ $summary['jobs12m'] }}</div>
                    </div>
                @endif
                <div class="col border-end py-1">
                    <div class="small fw-bold text-uppercase text-muted">{{ __('Balance Due') }}</div>
                    <div class="fs-5 fw-bold {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balanceDue, 2) }}</div>
                </div>
                @if($summary['canActivities'])
                    <div class="col py-1">
                        <div class="small fw-bold text-uppercase text-muted">{{ __('Next follow-up') }}</div>
                        @if($summary['nextFollowUp'])
                            <div class="fw-bold {{ $summary['nextFollowUp']->lt(\App\Models\Crm\CrmActivity::localNowNaive()->startOfDay()) ? 'text-danger' : '' }}">{{ $summary['nextFollowUp']->format('d-m-Y H:i') }}</div>
                            @if($summary['nextFollowUpNote'])<div class="small text-muted text-truncate">{{ $summary['nextFollowUpNote'] }}</div>@endif
                        @else
                            <div class="fw-bold text-muted">{{ __('None') }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div class="d-flex flex-wrap gap-1" role="group" aria-label="{{ __('Show') }}">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-tl-filter="">{{ __('All') }}</button>
            @foreach($kinds as $key => [$label, $icon])
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-tl-filter="{{ $key }}"><i class="bi {{ $icon }} me-1"></i>{{ __($label) }}</button>
            @endforeach
        </div>
        @if($summary['canActivities'] && userCan('sales.activities', 'create'))
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-tl-log="{{ $customer->id }}"><i class="bi bi-plus-lg me-1"></i>{{ __('Log Activity') }}</button>
        @endif
    </div>
    <div class="cust-card"><div class="cust-card-b py-1" id="custTimeline">
@endif

@forelse($items as $it)
    @php($color = $kinds[$it['kind']][2] ?? '#6b7280')
    <div class="d-flex gap-3 py-2 border-bottom cust-tl-item" data-kind="{{ $it['kind'] }}">
        <div class="flex-none text-center" style="width:78px">
            <div class="small fw-semibold">{{ $it['at']->translatedFormat('d M Y') }}</div>
            @if($it['kind'] === 'activity')<div class="small text-muted">{{ $it['at']->format('H:i') }}</div>@endif
        </div>
        <div class="flex-none rounded-circle d-flex align-items-center justify-content-center" style="width:30px;height:30px;background:{{ $color }}1a;color:{{ $color }}">
            <i class="bi {{ $it['icon'] }}"></i>
        </div>
        <div class="flex-grow-1 small" style="min-width:0">
            <div class="d-flex justify-content-between gap-2 flex-wrap">
                <span class="fw-semibold">
                    @if($it['url'])<a href="{{ $it['url'] }}" class="text-decoration-none">{{ $it['title'] }}</a>@else{{ $it['title'] }}@endif
                    @if($it['status'])<span class="badge rounded-pill bg-light text-muted border fw-normal ms-1">{{ $it['status'] }}</span>@endif
                    @if($it['chip'])<span class="badge rounded-pill fw-normal ms-1 {{ $tones[$it['chipTone']] ?? $tones['primary'] }}">{{ $it['chip'] }}</span>@endif
                </span>
                @if($it['amount'] !== null)<span class="fw-semibold">{{ number_format($it['amount'], 2) }} {{ $it['currency'] ?: $baseCurrency }}</span>@endif
            </div>
            @if($it['text'])<div class="text-body" style="white-space:pre-line">{{ $it['text'] }}</div>@endif
            @if($it['by'])<div class="text-muted">{{ __('By') }} {{ $it['by'] }}</div>@endif
        </div>
    </div>
@empty
    @if(!$onlyItems)<div class="text-muted text-center py-4">{{ __('Nothing has happened with this customer yet.') }}</div>@endif
@endforelse

@if($hasMore)
    <div class="text-center py-2 cust-tl-more">
        <button type="button" class="btn btn-sm btn-light border rounded-pill px-4" data-tl-more="{{ $olderThan->timestamp }}">{{ __('Show older') }}</button>
    </div>
@endif

@if(!$onlyItems)
    </div></div>
@endif
