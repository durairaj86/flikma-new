{{-- Large dashboard widget (double medium height; the list scrolls): ATA / ATD. Uses $shipmentSeries; live snapshot, no month filter. --}}
@php
    $sLabels = $shipmentSeries['weekLabels'];
    $sDatasets = [
        ['label' => __('ATA'), 'data' => $shipmentSeries['ata'], 'color' => '#0891b2'],
        ['label' => __('ATD'), 'data' => $shipmentSeries['atd'], 'color' => '#dc2626'],
    ];
@endphp
<div class="kpi kpi-tall" style="--kc: #0891b2; --kbg: #e0f7fa;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-truck"></i></span>
        <span class="kpi-title">{{ __('ATA / ATD') }}</span>
        <span class="kpi-month-static">{{ __('This week') }}</span>
    </div>
    <div class="kpi-medium-body" style="margin-top:6px;">
        <div class="kpi-medium-text kpi-duo">
            <div>
                <div class="kpi-value" style="color: #0891b2">{{ $ataThisWeek }}</div>
                <div class="kpi-note">{{ __('ATA This Week') }}</div>
            </div>
            <div>
                <div class="kpi-value" style="color: #dc2626">{{ $atdThisWeek }}</div>
                <div class="kpi-note">{{ __('ATD This Week') }}</div>
            </div>
        </div>
        <div class="kpi-medium-chart" style="height:96px;">
            <canvas class="kpi-chart" data-type="dualbar" data-color="#0891b2"
                    data-datasets='@json($sDatasets)' data-values='[]' data-labels='@json($sLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em style="background:#0891b2"></em>{{ __('ATA') }} <b>{{ array_sum($shipmentSeries['ata']) }}</b></span>
        <span><em style="background:#dc2626"></em>{{ __('ATD') }} <b>{{ array_sum($shipmentSeries['atd']) }}</b></span>
    </div>


    <div class="kpi-legend" style="margin-top:8px;">
        <span><em style="background:#16a34a"></em>{{ __('On time') }} <b>{{ $shipmentExtra['onTime'] }}</b></span>
        <span><em style="background:#dc2626"></em>{{ __('Late') }} <b>{{ $shipmentExtra['late'] }}</b></span>
    </div>
    @php $modes = collect($ataAtdList)->groupBy(fn ($r) => $r['mode'] ?: 'other')->map->count(); @endphp
    <div class="kpi-modes">
        @foreach(['sea' => 'bi-water', 'air' => 'bi-airplane', 'road' => 'bi-truck', 'other' => 'bi-box-seam'] as $m => $ic)
            @if(($modes[$m] ?? 0) > 0 || $m !== 'other')
                <span><i class="bi {{ $ic }}"></i> {{ __(ucfirst($m)) }} <b>{{ $modes[$m] ?? 0 }}</b></span>
            @endif
        @endforeach
    </div>
    <div class="kpi-tall-scroll">
        <table class="table table-sm table-hover mb-0 align-middle kpi-table">
            <thead><tr><th>{{ __('Job') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Date') }}</th><th></th></tr></thead>
            <tbody>
            @forelse($ataAtdList as $row)
                <tr>
                    <td><div class="fw-semibold">{{ $row['job'] }}</div><small class="text-muted text-truncate d-block" style="max-width:120px;">{{ $row['route'] }}</small></td>
                    <td class="text-truncate" style="max-width:100px;">{{ $row['customer'] }}</td>
                    <td class="text-nowrap">{{ $row['date'] }}</td>
                    <td><span class="badge" style="background: {{ $row['event'] === 'ATA' ? '#0891b2' : '#dc2626' }};">{{ $row['event'] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No shipments in this period') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
