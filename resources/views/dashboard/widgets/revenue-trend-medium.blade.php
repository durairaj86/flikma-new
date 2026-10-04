{{-- Medium dashboard widget: Revenue Trend (line). Chart is initialised by dashboard.blade.php via #revenueMainChart. --}}
<div class="kpi kpi-chart-card" style="--kc: #0d9488; --kbg: #e0f7f1;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-chart-line"></i></span>
        <span class="kpi-title">{{ __('Revenue Trend') }}</span>
        <span class="kpi-month-static">{{ __('This month') }}</span>
    </div>
    <div class="kpi-chart-wrap">
        <canvas id="revenueMainChart"></canvas>
    </div>
</div>
