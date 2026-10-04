{{-- Medium dashboard widget: Revenue vs Expenses (monthly bars). Chart is initialised by dashboard.blade.php via #salesMainChart. --}}
<div class="kpi kpi-chart-card" style="--kc: #2563eb; --kbg: #ffffff;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="fa-solid fa-chart-column"></i></span>
        <span class="kpi-title">{{ __('Revenue vs Expenses') }}</span>
        <span class="kpi-month-static">{{ __('Monthly') }}</span>
    </div>
    <div class="kpi-chart-wrap">
        <canvas id="salesMainChart"></canvas>
    </div>
</div>
