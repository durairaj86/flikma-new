@php
    $v = $rate->validity();
    $vMap = [
        'active' => ['label' => __('Active'), 'class' => 'bg-success-subtle text-success'],
        'expiring' => ['label' => __('Expiring'), 'class' => 'bg-warning-subtle text-warning'],
        'expired' => ['label' => __('Expired'), 'class' => 'bg-danger-subtle text-danger'],
        'upcoming' => ['label' => __('Upcoming'), 'class' => 'bg-info-subtle text-info'],
        'inactive' => ['label' => __('Inactive'), 'class' => 'bg-secondary-subtle text-secondary'],
    ];
    $vInfo = $vMap[$v];
    $modeLabel = ['sea' => __('Sea'), 'air' => __('Air'), 'road' => __('Road')][$rate->shipment_mode] ?? $rate->shipment_mode;
    $margin = (float) $rate->sell_rate - (float) $rate->buy_rate;
    $pct = (float) $rate->sell_rate > 0 ? round($margin / (float) $rate->sell_rate * 100, 1) : 0;
    $daysLeft = $rate->valid_to ? today()->diffInDays($rate->valid_to, false) : null;
@endphp
<style>
    .section {
        margin-bottom: 1.5rem;
    }
    .section h6 {
        font-size: 14px;
        font-weight: 600;
        background: #f7f7f9;
        padding: 8px 10px;
        border-radius: 4px;
        /*border-left: 4px solid #0d6efd;*/
        margin-bottom: 1rem;
    }
    table.table {
        font-size: 13px;
    }
    table.table th {
        background: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem 1.5rem;
        font-size: 13.5px;
        line-height: 1.6;
    }
    .info-grid div {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px dotted #eee;
        padding-bottom: 3px;
    }
    .info-grid strong {
        color: #333;
        min-width: 140px;
        font-weight: 600;
    }
    .info-grid span {
        color: #555;
        flex: 1;
        text-align: left;
        margin-left: 8px;
    }
    .total-table td {
        padding: 4px 10px;
        font-size: 13.5px;
    }
    .invoice-no-heading {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 1rem;
    }
    /* two-sided time frame: Rate events */
    .rt-timeline { list-style: none; margin: 0; padding: 0; position: relative; }
    .rt-timeline::before { content: ''; position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; margin-left: -1px; background: #e2e8f0; }
    .rt-timeline li { position: relative; width: 100%; padding: 0 2rem .9rem 0; font-size: 13.5px; text-align: right; }
    .rt-timeline li.side-r { margin-left: 50%; padding: 0 0 .9rem 2rem; text-align: left; }
    .rt-timeline .dot { position: absolute; top: 0; right: -.75rem; width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #e7f0fe; color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; z-index: 1; }
    .rt-timeline li.side-r .dot { right: auto; left: -.75rem; }
    .rt-timeline .t-mod { display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: #0d6efd; background: #e7f0fe; border-radius: 10px; padding: 0 .5rem; margin-bottom: .15rem; }
    .rt-timeline .t-label { font-weight: 600; color: #0f172a; }
    .rt-timeline .t-meta { color: #64748b; font-size: 12.5px; }
    .x-small { font-size: .75rem; }
    /* one-sided timeline for a rate */
    .rt-timeline::before { left: .75rem; }
    .rt-timeline li, .rt-timeline li.side-l { width: 100%; margin-left: 0; text-align: left; padding: 0 0 .9rem 2rem; }
    .rt-timeline .dot { left: 0 !important; right: auto !important; }
</style>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#rtDetailsTab" type="button" role="tab">
            <i class="bi bi-tags me-1"></i> {{ __('Details') }}
        </button>
    </li>
    <li class="nav-item ms-auto">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#rtTimeFrameTab" type="button" role="tab"
                title="{{ __('Time Frame') }}" aria-label="{{ __('Time Frame') }}">
            <i class="bi bi-clock-history fs-5"></i>
        </button>
    </li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="rtDetailsTab" role="tabpanel">

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $rate->row_no }}
        <span class="badge {{ $vInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $vInfo['label'] }}</span>
    </div>
</div>

<div class="section">
    <h6>{{ __('Lane') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Mode') }}:</strong><span>{{ $modeLabel }}</span></div>
        <div><strong>{{ __('Carrier') }}:</strong><span>{{ $rate->carrier->name ?? __('Any carrier') }}</span></div>
        <div><strong>{{ __('Origin') }}:</strong><span>{{ $rate->origin }}</span></div>
        <div><strong>{{ __('Destination') }}:</strong><span>{{ $rate->destination }}</span></div>
        <div><strong>{{ __('Container / Equipment') }}:</strong><span>{{ $rate->container_type ?: '-' }}</span></div>
        <div><strong>{{ __('Transit Days') }}:</strong><span>{{ $rate->transit_days ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Rate') }}</h6>
    <table class="total-table ms-auto" style="min-width:340px;">
        <tr><td><strong>{{ __('Charged') }}</strong></td><td class="text-end">{{ __(\App\Models\Sales\RateSheet::BASIS[$rate->basis] ?? $rate->basis) }}</td></tr>
        <tr><td><strong>{{ __('Buy Rate') }}</strong></td><td class="text-end">{{ number_format((float) $rate->buy_rate, 2) }} {{ $rate->currency }}</td></tr>
        <tr><td><strong>{{ __('Sell Rate') }}</strong></td><td class="text-end">{{ number_format((float) $rate->sell_rate, 2) }} {{ $rate->currency }}</td></tr>
        <tr class="table-secondary"><td><strong>{{ __('Margin') }}</strong></td>
            <td class="text-end fw-bold {{ $margin < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($margin, 2) }} {{ $rate->currency }} ({{ $pct }}%)</td></tr>
        @if($rate->min_charge)
            <tr><td><strong>{{ __('Minimum Charge') }}</strong></td><td class="text-end">{{ number_format((float) $rate->min_charge, 2) }} {{ $rate->currency }}</td></tr>
        @endif
    </table>
</div>

<div class="section">
    <h6>{{ __('Validity') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Valid From') }}:</strong><span>{{ $rate->valid_from?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Valid To') }}:</strong><span>{{ $rate->valid_to?->format('d-m-Y') ?? '-' }}
            @if($daysLeft !== null && $v !== 'inactive')
                <small class="{{ $daysLeft < 0 ? 'text-danger' : ($daysLeft <= 30 ? 'text-warning' : 'text-muted') }}">
                    ({{ $daysLeft < 0 ? __(':n days ago', ['n' => abs($daysLeft)]) : __(':n days left', ['n' => $daysLeft]) }})
                </small>
            @endif</span></div>
    </div>
</div>

@if($rate->remarks)
    <div class="section"><h6>{{ __('Remarks') }}</h6><p class="mb-0">{{ $rate->remarks }}</p></div>
@endif

</div>

<div class="tab-pane fade" id="rtTimeFrameTab" role="tabpanel">
    <div class="section">
        <h6>{{ __('Time Frame') }}</h6>
        <ul class="rt-timeline">
            @foreach($timeline as $step)
                <li class="side-l" style="padding-right:0;text-align:left;padding-left:2rem;">
                    <span class="dot" style="left:0;right:auto;"><i class="bi {{ $step['icon'] }}"></i></span>
                    <div class="t-label">{{ $step['label'] }}@if(!empty($step['meta'])) <span class="text-muted fw-normal">· {{ $step['meta'] }}</span>@endif</div>
                    <div class="t-meta">
                        {{ \Carbon\Carbon::parse($step['at'])->format('d-m-Y H:i') }}
                        @if(!empty($step['by'])) &middot; {{ __('by') }} {{ $step['by'] }} @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
</div>
