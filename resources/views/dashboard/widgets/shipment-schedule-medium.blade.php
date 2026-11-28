{{-- Medium dashboard widget: jobs arriving (ETA) and leaving (ETD) today, tomorrow, this week and next week. Uses $shipmentSchedule; live snapshot. --}}
@php
    $ssPeriods = ['today' => __('Today'), 'tomorrow' => __('Tomorrow'), 'this_week' => __('This week'), 'next_week' => __('Next week')];
    $ssRows = [
        'eta' => ['label' => __('Arriving'), 'hint' => __('ETA'), 'color' => '#2563eb'],
        'etd' => ['label' => __('Leaving'), 'hint' => __('ETD'), 'color' => '#16a34a'],
    ];
@endphp
<div class="kpi kpi-medium" style="--kc: #2563eb; --kbg: #e3efff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-calendar-week"></i></span>
        <span class="kpi-title">{{ __('Arrivals & Departures') }}</span>
        <span class="kpi-month-static">{{ __('Live') }}</span>
    </div>
    <table class="table table-sm mb-0 align-middle kpi-table" style="margin-top:10px;">
        <thead>
        <tr>
            <th></th>
            @foreach($ssPeriods as $label)
                <th class="text-center">{{ $label }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($ssRows as $col => $row)
            <tr>
                <td class="text-nowrap">
                    <em class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:{{ $row['color'] }};"></em>
                    <span class="fw-semibold">{{ $row['label'] }}</span> <small class="text-muted">{{ $row['hint'] }}</small>
                </td>
                @foreach($ssPeriods as $key => $label)
                    <td class="text-center">
                        <span class="fs-5 fw-bold tabular-nums" style="color: {{ $shipmentSchedule[$col][$key] > 0 ? $row['color'] : '#98a2b3' }};">{{ $shipmentSchedule[$col][$key] }}</span>
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="kpi-legend" style="margin-top:8px;">
        <span><em style="background:#dc2626"></em>{{ __('Overdue arrivals') }} <b>{{ $shipmentExtra['overdueEta'] }}</b></span>
        <span><em style="background:#f59e0b"></em>{{ __('Late departures') }} <b>{{ $shipmentExtra['lateDeparture'] }}</b></span>
    </div>
</div>
