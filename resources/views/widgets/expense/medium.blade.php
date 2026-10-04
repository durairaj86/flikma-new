{{-- Expense widget, medium: selected month (solid) vs previous month (dotted), running totals per day. --}}
@php
    $kColor = '#e11d48';
    $kLabels = $d['labels'];
    $kDatasets = [
        ['label' => $d['thisLabel'], 'data' => $d['this'], 'color' => $kColor, 'dashed' => false],
        ['label' => $d['prevLabel'], 'data' => $d['prev'], 'color' => '#98a2b3', 'dashed' => true],
    ];
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $kColor }}; --kbg: #fff1f3;" wire:loading.class="wd-loading" wire:target="setMonth">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-wallet"></i></span>
        <span class="kpi-title">{{ __('Expenses') }}</span>
        @include('widgets._month')
    </div>
    <div class="kpi-main">
        <div class="kpi-value">{{ \App\Support\WidgetFormat::short($d['total']) }}</div>
        {!! \App\Support\WidgetFormat::pill($d['change'], true) !!}
        <span class="kpi-note m-0">{{ __('vs') }} {{ \App\Support\WidgetFormat::short($d['last']) }} {{ $d['prevLabel'] }}</span>
    </div>
    <div class="kpi-compare">
        <canvas class="kpi-chart" data-type="compare" data-color="{{ $kColor }}" data-values='[]'
                data-labels='@json($kLabels)' data-datasets='@json($kDatasets)'></canvas>
    </div>
    <div class="kpi-legend kpi-legend-line">
        <span><i class="line-solid"></i>{{ $d['thisLabel'] }} <b>{{ \App\Support\WidgetFormat::short($d['total']) }}</b></span>
        <span><i class="line-dotted"></i>{{ $d['prevLabel'] }} <b>{{ \App\Support\WidgetFormat::short($d['last']) }}</b></span>
    </div>
</div>
