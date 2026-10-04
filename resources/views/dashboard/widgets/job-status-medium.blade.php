{{-- Medium dashboard widget: Job Status. Live snapshot; donut of active vs completed-this-month. --}}
@php
    $jNames = [__('Active Jobs'), __('Completed This Month')];
    $jColors = ['#2563eb', '#16a34a'];
    $jValues = [(int) $activeJobs, (int) $completedJobsThisMonth];
    $jColor = '#2563eb';
    $jTotal = (int) $activeJobs + (int) $completedJobsThisMonth;
    $jPct = $jTotal > 0 ? round($completedJobsThisMonth / $jTotal * 100) : 0;
@endphp
<div class="kpi kpi-medium" style="--kc: {{ $jColor }}; --kbg: #e3efff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-truck-fast"></i></span>
        <span class="kpi-title">{{ __('Job Status') }}</span>
        <span class="kpi-month-static">{{ __('Live') }}</span>
    </div>
    <div class="kpi-medium-body">
        <div class="kpi-medium-text">
            <div class="kpi-value">{{ $activeJobs }}</div>
            <div class="kpi-note">{{ __('Active Jobs') }}</div>
            <span class="kpi-pill up">{{ $jPct }}% {{ __('completed') }}</span>
        </div>
        <div class="kpi-medium-chart">
            <canvas class="kpi-chart" data-type="donut" data-color="{{ $jColor }}"
                    data-names='@json($jNames)'
                    data-colors='@json($jColors)'
                    data-values='@json($jValues)' data-labels='[]'></canvas>
        </div>
    </div>
    <div class="kpi-legend">
        <span><em style="background:#2563eb"></em>{{ __('Active Jobs') }} <b>{{ $activeJobs }}</b></span>
        <span><em style="background:#16a34a"></em>{{ __('Completed This Month') }} <b>{{ $completedJobsThisMonth }}</b></span>
    </div>
</div>
