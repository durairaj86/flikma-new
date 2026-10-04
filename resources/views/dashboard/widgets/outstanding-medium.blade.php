{{-- Medium dashboard widget: Outstanding. A current snapshot (no month filter); aging buckets drawn as a donut. --}}
@php
    $oNames = [__('0-30d'), __('31-60d'), __('60+d')];
    $oColors = ['#f59e0b', '#e11d48', '#64748b'];
    $oColor = '#e11d48';
    $oBuckets = [(float) $outstanding30d, (float) $outstanding60d, (float) $outstanding60Plus];
    $oTotal = (float) $outstanding;
    $oPill = '<span class="kpi-pill ' . ($outstandingChange <= 0 ? 'up' : 'down') . '">' . ($outstandingChange <= 0 ? '&#9660;' : '&#9650;') . ' ' . number_format(abs($outstandingChange), 1) . '%</span>';
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $oColor }}; --kbg: #ffe9ee;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-hourglass-half"></i></span>
        <span class="kpi-title">{{ __('Outstanding') }}</span>
        <span class="badge bg-danger">{{ __('Overdue') }}</span>
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ number_format($oTotal, 0) }}</div>
            {!! $oPill !!}
            <div class="kpi-note">{{ __('Total amount outstanding') }} &middot; {{ __('vs last month') }}</div>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="donut"
                    data-names='@json($oNames)'
                    data-colors='@json($oColors)'
                    data-color="{{ $oColor }}"
                    data-values='@json($oBuckets)' data-labels='[]'></canvas>
        </div>
    </div>
    <div class="kpi-legend kpi-legend-3">
        <span><em style="background:#f59e0b"></em>{{ __('0-30d') }} <b>{{ number_format($outstanding30d, 0) }}</b></span>
        <span><em style="background:#e11d48"></em>{{ __('31-60d') }} <b>{{ number_format($outstanding60d, 0) }}</b></span>
        <span><em style="background:#64748b"></em>{{ __('60+d') }} <b>{{ number_format($outstanding60Plus, 0) }}</b></span>
    </div>
</div>
