{{-- Large dashboard widget (double medium height; the list scrolls): ETA / ETD. Uses $shipmentSeries; live snapshot, no month filter. --}}
@php
    $sLabels = $shipmentSeries['upcomingLabels'];
    $sDatasets = [
        ['label' => __('ETA'), 'data' => $shipmentSeries['eta'], 'color' => '#2563eb'],
        ['label' => __('ETD'), 'data' => $shipmentSeries['etd'], 'color' => '#16a34a'],
    ];
@endphp
<div class="kpi kpi-tall" style="--kc: #2563eb; --kbg: #e3efff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-ship"></i></span>
        <span class="kpi-title">{{ __('ETA / ETD') }}</span>
        <span class="kpi-month-static">{{ __('Next 7 days') }}</span>
    </div>
    <div class="kpi-medium-body" style="margin-top:6px;">
        <div class="kpi-medium-text kpi-duo">
            <div>
                <div class="kpi-value" style="color: #2563eb">{{ $etaToday }}</div>
                <div class="kpi-note">{{ __('ETA Today') }}</div>
            </div>
            <div>
                <div class="kpi-value" style="color: #16a34a">{{ $etdTomorrow }}</div>
                <div class="kpi-note">{{ __('ETD Tomorrow') }}</div>
            </div>
        </div>
        <div class="kpi-medium-chart" style="height:70px;">
            <canvas class="kpi-chart" data-type="dualbar" data-color="#2563eb"
                    data-datasets='@json($sDatasets)' data-values='[]' data-labels='@json($sLabels)'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em style="background:#2563eb"></em>{{ __('ETA') }} <b>{{ array_sum($shipmentSeries['eta']) }}</b></span>
        <span><em style="background:#16a34a"></em>{{ __('ETD') }} <b>{{ array_sum($shipmentSeries['etd']) }}</b></span>
    </div>

    <div class="kpi-tall-scroll">
        <table class="table table-sm table-hover mb-0 align-middle kpi-table">
            <thead><tr><th>{{ __('Job') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Date') }}</th><th></th></tr></thead>
            <tbody>
            @forelse($etaEtdList as $row)
                <tr>
                    <td><div class="fw-semibold">{{ $row['job'] }}</div><small class="text-muted text-truncate d-block" style="max-width:120px;">{{ $row['route'] }}</small></td>
                    <td class="text-truncate" style="max-width:100px;">{{ $row['customer'] }}</td>
                    <td class="text-nowrap">{{ $row['date'] }}</td>
                    <td><span class="badge" style="background: {{ $row['event'] === 'ETA' ? '#2563eb' : '#16a34a' }};">{{ $row['event'] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No shipments in this period') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
