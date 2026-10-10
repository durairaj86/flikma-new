@section('page-title', __('Job Overview'))
@section('hide-topbar', true)
@section('page-subtitle', __('Real-time operations performance dashboard'))
@section('print-footer')
<script>
    window.printFooter = {
        show: true,
        custom: '{{ __('Job Overview - Generated on :date', ['date' => date('d-m-Y H:i')]) }}'
    };
</script>
@endsection
<x-app-layout>
    <div class="bg-light pb-4">
    <div class="jw-page pt-2">
        <style>
            :root { --job_bg: #f8fafc; }
            body { background: var(--job_bg); }
            .jw {
                --kbg: color-mix(in srgb, var(--kc) 8%, #fff);
                background: var(--kbg); border-radius: 16px; padding: 12px 14px; height: 100%;
                box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(15,23,42,.07);
            }
            .jw.white { background: #fff; }
            .jw-head { display: flex; align-items: center; gap: 10px; }
            .jw-icon { width: 26px; height: 26px; border-radius: 50%; background: var(--kc); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; flex-shrink: 0; box-shadow: 0 3px 8px color-mix(in srgb, var(--kc) 38%, transparent); }
            .jw-title { font-weight: 600; font-size: .82rem; color: #344054; flex: 1; min-width: 0; line-height: 1.2; }
            .jw-tag { font-size: .66rem; font-weight: 600; background: #fff; border: 1px solid #e5e7eb; color: #475467; border-radius: 999px; padding: 2px 8px; white-space: nowrap; }
            .jw-value-row { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
            .jw-value { font-size: 1.55rem; font-weight: 700; letter-spacing: -.02em; color: #101828; line-height: 1.1; }
            .jw-pill { font-size: .66rem; font-weight: 600; border-radius: 8px; padding: 2px 8px; background: #e0f2fe; color: #0369a1; }
            .jw-pill.up { background: #dcfce7; color: #15803d; }
            .jw-pill.down { background: #fee2e2; color: #b91c1c; }
            .jw-note { font-size: .72rem; color: #667085; margin-top: 2px; }
            .jw-track { height: 5px; border-radius: 99px; background: color-mix(in srgb, var(--kc, #94a3b8) 16%, #e5e7eb); overflow: hidden; margin-top: 8px; }
            .jw-track > span { display: block; height: 100%; border-radius: 99px; background: var(--kc, #0b6aa0); transition: width .5s; }
            .jw-legend { display: flex; justify-content: space-between; gap: 6px; margin-top: 8px; }
            .jw-legend > div { flex: 1; background: rgba(255,255,255,.75); border-radius: 9px; padding: 4px 8px; font-size: .68rem; color: #667085; display: flex; align-items: center; justify-content: space-between; gap: 4px; }
            .jw-legend strong { font-size: .8rem; color: #101828; margin-left: auto; }
            .jw-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; background: var(--kc); }
            .jw-split { display: flex; align-items: center; gap: 8px; }
            .jw-left { flex: 1; min-width: 0; }
            .jw-mini { position: relative; width: 96px; height: 96px; flex-shrink: 0; }
            /* compact stat widgets */
            .jw-sm { padding: 12px 14px; border-radius: 16px; display: flex; flex-direction: row; align-items: center; justify-content: flex-start; gap: 12px; }
            .jw-sm .jw-txt { display: flex; flex-direction: column; min-width: 0; width: 100%; }
            .jw-sm .jw-title { font-size: .72rem; color: #667085; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .jw-sm-value { font-size: 1.3rem; font-weight: 700; color: #101828; line-height: 1.1; }
            .trend-up { color: #16a34a !important; }
            .trend-down { color: #dc2626 !important; }
            /* chart / list widgets */
            .jw-body { margin-top: 8px; }
            .jw-row { display: flex; align-items: flex-start; gap: 8px; padding: 5px 0; }
            .jw-row + .jw-row { border-top: 1px dashed #eef0f3; }
            .jw-rank { width: 20px; height: 20px; border-radius: 50%; background: #f1f5f9; color: #475467; font-size: .66rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
            .jw-row-main { flex: 1; min-width: 0; }
            .jw-row-top { display: flex; justify-content: space-between; gap: 8px; font-size: .78rem; }
            .jw-row-name { font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .jw-row-val { font-weight: 700; color: #101828; }
            .jw-row-sub { font-size: .66rem; color: #94a3b8; }
            .jw-row .jw-track { margin-top: 3px; height: 4px; }
            .jw-stack { display: flex; height: 10px; border-radius: 99px; overflow: hidden; background: #eef0f3; }
            .jw-stack > span { display: block; height: 100%; }
            .jw-legend-item { display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border-radius: 999px; padding: 3px 10px; font-size: .72rem; margin: 8px 6px 0 0; color: #475467; }
            .jw-legend-item .jw-dot { margin: 0; }
            .jw-chart { position: relative; height: 150px; }
            .jw-chart.sm { height: 140px; }
        </style>

        <div class="container-fluid px-lg-5">
            <style>
                .jo-title { display: none; }
                body:not(.has-top-header) .jo-title { display: block; }
            </style>
            @php($rangeLabel = ['this_month' => __('This Month'), 'last_month' => __('Last Month'), 'this_year' => __('This Year')][$range] ?? '')
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="jo-title">
                    <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                    <div class="text-muted small mt-1">@yield('page-subtitle')</div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div id="dateRangeWrap" style="min-width:170px;"><select id="dateRange" class="tom-select" data-placeholder="{{ __('This Month') }}">
                        <option value="this_month" {{ $range==='this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                        <option value="last_month" {{ $range==='last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                        <option value="this_year" {{ $range==='this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                    </select></div>
                    <button class="btn btn-primary btn-sm px-3" id="btn-apply"><i class="bi bi-arrow-repeat me-1"></i> {{ __('Apply') }}</button>
                </div>
            </div>

            {{-- Main widgets --}}
            <div class="row g-2 mb-2">
                <div class="col-xl-3 col-md-6">
                    <div class="jw" style="--kc:#0b6aa0;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-briefcase"></i></span><span class="jw-title">{{ __('Total Jobs') }}</span></div>
                        <div class="jw-split"><div class="jw-left"><div class="jw-value-row"><span class="jw-value" id="kpiTotalJobs">0</span><span class="jw-pill up" id="totDelta"></span></div>
                        <div class="jw-note">{{ $rangeLabel }} &middot; <span id="kpiDonePill">0%</span> {{ __('completed') }}</div>
                        </div><div class="jw-mini"><canvas id="miniTotal"></canvas></div></div>
                        <div class="jw-legend">
                            <div><span class="jw-dot" style="background:#16a34a"></span>{{ __('Completed') }}<strong id="totDone">0</strong></div>
                            <div><span class="jw-dot" style="background:#dc2626"></span>{{ __('Cancelled') }}<strong id="totCancelled">0</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw" style="--kc:#16a34a;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-check-circle"></i></span><span class="jw-title">{{ __('Completed') }}</span></div>
                        <div class="jw-split"><div class="jw-left"><div class="jw-value-row"><span class="jw-value" id="kpiCompletedJobs">0</span></div>
                        <div class="jw-note">{{ $rangeLabel }} &middot; {{ __('finished') }}</div>
                        </div><div class="jw-mini"><canvas id="miniDone"></canvas></div></div>
                        <div class="jw-legend"><div>{{ __('Completion rate') }}<strong id="kpiDoneNote">0 / 0</strong></div></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw" style="--kc:#f59e0b;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-hourglass-split"></i></span><span class="jw-title">{{ __('Pending') }}</span><span class="jw-tag">{{ __('Live') }}</span></div>
                        <div class="jw-split"><div class="jw-left"><div class="jw-value-row"><span class="jw-value" id="kpiPendingJobs">0</span></div>
                        <div class="jw-note">{{ __('Currently in progress') }}</div>
                        </div><div class="jw-mini"><canvas id="miniPending"></canvas></div></div>
                        <div class="jw-legend">
                            <div><span class="jw-dot" style="background:#dc2626"></span>{{ __('Cancelled') }}<strong id="pendCancelled">0</strong></div>
                            <div><span class="jw-dot" style="background:#5b57ae"></span>{{ __('From Quotations') }}<strong id="pendQuotes">0</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw" style="--kc:#5b57ae;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-receipt"></i></span><span class="jw-title">{{ __('Invoiced Jobs') }}</span></div>
                        <div class="jw-split"><div class="jw-left"><div class="jw-value-row"><span class="jw-value" id="kpiInvoicedJobs">0</span><span class="jw-pill" id="kpiInvPill">0%</span></div>
                        <div class="jw-note">{{ __('Have a customer invoice') }}</div>
                        </div><div class="jw-mini"><canvas id="miniInv"></canvas></div></div>
                        <div class="jw-legend">
                            <div><span class="jw-dot"></span>{{ __('Invoiced') }}<strong id="invYes">0</strong></div>
                            <div><span class="jw-dot" style="background:#cbd5e1"></span>{{ __('Not Yet Invoiced') }}<strong id="invNo">0</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Small widgets --}}
            <div class="row g-2 mb-2">
                @foreach([
                    ['kpiCancelledJobs', '#dc2626', 'bi-x-circle', __('Cancelled'), '0'],
                    ['kpiCustomerCount', '#0b6aa0', 'bi-people', __('Customers'), '0'],
                    ['kpiFromQuotations', '#5b57ae', 'bi-file-earmark-text', __('From Quotations'), '0'],
                    ['kpiRepeat', '#16a34a', 'bi-arrow-repeat', __('Repeat Customers'), '0%'],
                    ['kpiAvgContainers', '#0891b2', 'bi-box-seam', __('Avg Containers/Job'), '0'],
                    ['kpiJobsChange', '#f59e0b', 'bi-graph-up', __('vs Last Month'), '0%'],
                ] as [$id, $c, $ic, $label, $zero])
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="jw jw-sm" style="--kc:{{ $c }};">
                            <span class="jw-icon"><i class="bi {{ $ic }}"></i></span>
                            <div class="jw-txt"><span class="jw-sm-value" id="{{ $id }}">{{ $zero }}</span><span class="jw-title" style="flex:none;">{{ $label }}</span></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Trend + source --}}
            <div class="row g-2 mb-2">
                <div class="col-xl-3 col-md-6">
                    <div class="jw white" style="--kc:#0b6aa0;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-graph-up"></i></span><span class="jw-title">{{ __('Jobs Trend') }}</span><span class="jw-tag">{{ $range === 'this_year' ? __('Monthly') : __('Weekly') }}</span></div>
                        <div class="jw-body jw-chart"><canvas id="chartJobsTrend"></canvas></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw white" style="--kc:#5b57ae;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-signpost-split"></i></span><span class="jw-title">{{ __('Job Source') }}</span><span class="jw-tag">{{ __('Sales Pipeline') }}</span></div>
                        <div class="jw-body jw-chart"><canvas id="chartJobSource"></canvas></div>
                    </div>
                </div>

                @foreach([
                    ['chartCompletionRate', '#16a34a', 'bi-graph-up-arrow', __('Completion Rate')],
                    ['chartTopCarriers', '#f97316', 'bi-truck', __('Top Carriers')],
                    ['chartInvoicingCoverage', '#0b6aa0', 'bi-receipt-cutoff', __('Invoicing Coverage')],
                    ['chartHandledBy', '#8b5cf6', 'bi-person-badge', __('Handled By')],
                ] as [$id, $c, $ic, $label])
                    <div class="col-xl-3 col-md-6">
                        <div class="jw white" style="--kc:{{ $c }};">
                            <div class="jw-head"><span class="jw-icon"><i class="bi {{ $ic }}"></i></span><span class="jw-title">{{ $label }}</span></div>
                            <div class="jw-body jw-chart sm"><canvas id="{{ $id }}"></canvas></div>
                        </div>
                    </div>
                @endforeach

                <div class="col-xl-3 col-md-6">
                    <div class="jw white" style="--kc:#f59e0b;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-trophy"></i></span><span class="jw-title">{{ __('Top 10 Customers by Job Count') }}</span><span class="jw-tag">{{ __('Jobs') }}</span></div>
                        <div class="jw-body" id="listCustomers" style="max-height:210px;overflow:auto;"></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw white" style="--kc:#0b6aa0;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-signpost-split"></i></span><span class="jw-title">{{ __('Top Routes') }}</span><span class="jw-tag">{{ __('POL') }} &rarr; {{ __('POD') }}</span></div>
                        <div class="jw-body" id="listRoutes" style="max-height:210px;overflow:auto;"></div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="jw white" style="--kc:#0b6aa0;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-list-check"></i></span><span class="jw-title">{{ __('Job Status Breakdown') }}</span><span class="jw-tag">{{ $rangeLabel }}</span></div>
                        <div class="jw-body">
                            <div class="jw-stack" id="statusBar"></div>
                            <div id="statusLegend"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const DATA = @json($data);

    function fmt(n) { return new Intl.NumberFormat('en-IN').format(Math.round(n)); }
    function pct(n) { return (n * 100).toFixed(1) + '%'; }

    function render() {
        const d = DATA;

        const total = d.totalJobs || 0;
        const setText = (id, v) => { const el = document.getElementById(id); if (el) el.innerText = v; };
        const pctOf = (n, t) => t ? Math.round((n / t) * 100) : 0;
        const setBar = (id, v) => { const el = document.getElementById(id); if (el) el.style.width = Math.min(100, Math.max(0, v)) + '%'; };

        // Main widgets
        setText('kpiTotalJobs', fmt(total));
        setText('kpiCompletedJobs', fmt(d.completedJobs));
        setText('kpiPendingJobs', fmt(d.pendingJobs));
        setText('kpiInvoicedJobs', fmt(d.invoicedJobs));

        const donePct = pctOf(d.completedJobs, total);
        setText('kpiDonePill', donePct + '%');
        setText('totDone', fmt(d.completedJobs));
        setText('totCancelled', fmt(d.cancelledJobs));
        setBar('barTotal', donePct);
        setText('kpiDoneNote', fmt(d.completedJobs) + ' / ' + fmt(total));
        setBar('barDone', donePct);
        setText('pendCancelled', fmt(d.cancelledJobs));
        setText('pendQuotes', fmt(d.fromQuotations));
        const invPct = pctOf(d.invoicedJobs, total);
        setText('kpiInvPill', invPct + '%');
        setText('invYes', fmt(d.invoicedJobs));
        setText('invNo', fmt(Math.max(0, total - d.invoicedJobs)));
        setBar('barInv', invPct);

        // Small widgets
        setText('kpiCancelledJobs', fmt(d.cancelledJobs));
        setText('kpiCustomerCount', fmt(d.customersCount));
        setText('kpiFromQuotations', fmt(d.fromQuotations));
        setText('kpiRepeat', pct(d.repeatRatio));
        setText('kpiAvgContainers', (d.avgContainersPerJob || 0).toFixed(1));

        // Change versus last month (only meaningful for the monthly views)
        const mc = d.monthlyComparison || {};
        const prevJobs = mc.previous?.jobs || 0;
        const jobsChange = prevJobs > 0 ? ((d.totalJobs - prevJobs) / prevJobs) * 100 : 0;
        const chgEl = document.getElementById('kpiJobsChange');
        chgEl.innerText = (jobsChange >= 0 ? '+' : '') + jobsChange.toFixed(1) + '%';
        chgEl.className = 'jw-sm-value ' + (jobsChange >= 0 ? 'trend-up' : 'trend-down');
        const pill = document.getElementById('totDelta');
        if (pill) {
            if ('{{ $range }}' === 'this_month' && prevJobs > 0) {
                pill.className = 'jw-pill ' + (jobsChange >= 0 ? 'up' : 'down');
                pill.innerHTML = (jobsChange >= 0 ? '&#9650; ' : '&#9660; ') + Math.abs(jobsChange).toFixed(1) + '%';
            } else { pill.style.display = 'none'; }
        }

        // List widgets with a share bar behind each row
        const rankRows = (rows, nameFn, subFn, valFn, color) => {
            const max = Math.max(1, ...rows.map(valFn));
            return rows.map((r, i) => `<div class="jw-row">
                <span class="jw-rank">${i + 1}</span>
                <div class="jw-row-main">
                    <div class="jw-row-top"><span class="jw-row-name">${nameFn(r)}</span><span class="jw-row-val">${fmt(valFn(r))}</span></div>
                    ${subFn ? `<div class="jw-row-sub">${subFn(r)}</div>` : ''}
                    <div class="jw-track"><span style="width:${Math.max(4, valFn(r) / max * 100)}%;background:${color};"></span></div>
                </div></div>`).join('');
        };
        const empty = '<div class="text-center text-muted py-4 small">{{ __('No data') }}</div>';
        document.getElementById('listCustomers').innerHTML = rankRows(d.customers || [], c => c.name,
            c => fmt(c.containers) + ' {{ __('Containers') }} · ' + fmt(c.packages) + ' {{ __('Packages') }}', c => c.jobs, '#0b6aa0') || empty;
        document.getElementById('listRoutes').innerHTML = rankRows(d.routes || [], r => r.route, null, r => r.jobs, '#5b57ae') || empty;

        // Status breakdown: one stacked bar plus a legend
        const statuses = d.jobStatuses || [];
        const totalStatusCount = statuses.reduce((sum, s) => sum + s.count, 0);
        const statusColor = {completed: '#16a34a', pending: '#f59e0b', cancelled: '#dc2626'};
        document.getElementById('statusBar').innerHTML = statuses.map(s =>
            `<span title="${s.label}" style="width:${totalStatusCount ? s.count / totalStatusCount * 100 : 0}%;background:${statusColor[s.status] || '#94a3b8'};"></span>`).join('');
        document.getElementById('statusLegend').innerHTML = statuses.map(s => `<div class="jw-legend-item">
                <span class="jw-dot" style="background:${statusColor[s.status] || '#94a3b8'};"></span>
                <span>${s.label}</span><strong>${fmt(s.count)}</strong>
                <small class="text-muted">${totalStatusCount ? ((s.count / totalStatusCount) * 100).toFixed(1) : 0}%</small></div>`).join('') || empty;


        // Mini charts inside the main widgets
        const mini = (id, cfg) => { const el = document.getElementById(id); if (el) new Chart(el.getContext('2d'), cfg); };
        const dn = (vals, colors) => ({ type: 'doughnut', data: { datasets: [{ data: vals, backgroundColor: colors, borderWidth: 0 }] },
            options: { cutout: '70%', plugins: { legend: { display: false }, tooltip: { enabled: false } }, maintainAspectRatio: false } });
        mini('miniTotal', { type: 'line', data: { labels: d.jobsTrend.map((_, i) => i + 1), datasets: [{ data: d.jobsTrend, borderColor: '#0b6aa0', backgroundColor: 'rgba(11,106,160,.15)', fill: true, tension: .4, pointRadius: 0, borderWidth: 2 }] },
            options: { plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false, beginAtZero: true } }, maintainAspectRatio: false } });
        mini('miniDone', dn([d.completedJobs, Math.max(0, total - d.completedJobs)], ['#16a34a', '#d1fae5']));
        const stColors = {completed: '#16a34a', pending: '#f59e0b', cancelled: '#dc2626'};
        mini('miniPending', dn((d.jobStatuses || []).map(s => s.count).concat((d.jobStatuses || []).length ? [] : [1]), (d.jobStatuses || []).map(s => stColors[s.status] || '#94a3b8').concat((d.jobStatuses || []).length ? [] : ['#e5e7eb'])));
        mini('miniInv', dn([d.invoicedJobs, Math.max(0, total - d.invoicedJobs)], ['#5b57ae', '#e0e0f5']));

        // --- Charts ---
        const colorPalette = ['#0b6aa0','#8b5cf6','#f59e0b','#06b6d4','#dc2626','#f97316'];
        const carrierColors = ['#f97316','#0ea5e9','#a855f7','#22c55e','#eab308','#ef4444'];
        const handledByColors = ['#8b5cf6','#06b6d4','#f97316','#ec4899','#10b981','#eab308'];

        // Jobs Trend
        const ctxTrend = document.getElementById('chartJobsTrend').getContext('2d');
        const trendLabels = '{{ $range }}' === 'this_year'
            ? ['{{ __('Jan') }}','{{ __('Feb') }}','{{ __('Mar') }}','{{ __('Apr') }}','{{ __('May') }}','{{ __('Jun') }}','{{ __('Jul') }}','{{ __('Aug') }}','{{ __('Sep') }}','{{ __('Oct') }}','{{ __('Nov') }}','{{ __('Dec') }}'].slice(0, d.jobsTrend.length)
            : Array.from({length: d.jobsTrend.length}, (_, i) => 'W' + (i + 1));
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: '{{ __('Jobs') }}',
                    data: d.jobsTrend,
                    borderColor: '#0b6aa0',
                    backgroundColor: (() => {
                        const g = ctxTrend.createLinearGradient(0, 0, 0, 200);
                        g.addColorStop(0, 'rgba(11,106,160,0.15)');
                        g.addColorStop(1, 'rgba(11,106,160,0)');
                        return g;
                    })(),
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0b6aa0',
                    pointBorderWidth: 2,
                    borderWidth: 2.5
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { stepSize: 1 } }
                },
                maintainAspectRatio: false
            }
        });

        // Job Source (linked to Sales pipeline) — horizontal bar
        new Chart(document.getElementById('chartJobSource').getContext('2d'), {
            type: 'bar',
            data: {
                labels: (d.jobSource || []).map(c => c.label),
                datasets: [{
                    data: (d.jobSource || []).map(c => c.value),
                    backgroundColor: ['#5b57ae', '#94a3b8'],
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { stepSize: 1 } },
                    y: { grid: { display: false } }
                },
                maintainAspectRatio: false
            }
        });

        // Completion Rate trend — green line (contrasts with the blue Jobs Trend line)
        const ctxCompletion = document.getElementById('chartCompletionRate').getContext('2d');
        new Chart(ctxCompletion, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: '{{ __('Completion Rate') }}',
                    data: d.completionRateTrend || [],
                    borderColor: '#16a34a',
                    backgroundColor: (() => {
                        const g = ctxCompletion.createLinearGradient(0, 0, 0, 200);
                        g.addColorStop(0, 'rgba(22,163,74,0.15)');
                        g.addColorStop(1, 'rgba(22,163,74,0)');
                        return g;
                    })(),
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#16a34a',
                    pointBorderWidth: 2,
                    borderWidth: 2.5
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
                },
                maintainAspectRatio: false
            }
        });

        // Top Carriers — vertical bar (linked to shipment/carrier data, contrasts with the horizontal bars around it)
        new Chart(document.getElementById('chartTopCarriers').getContext('2d'), {
            type: 'bar',
            data: {
                labels: (d.topCarriers || []).map(r => r.label),
                datasets: [{
                    data: (d.topCarriers || []).map(r => r.value),
                    backgroundColor: carrierColors.slice(0, Math.max((d.topCarriers || []).length, 1)),
                    borderRadius: 4
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { stepSize: 1 } }
                },
                maintainAspectRatio: false
            }
        });

        // Invoicing coverage (linked to Customer Invoice module) — doughnut
        const invCov = d.invoicingCoverage || [];
        new Chart(document.getElementById('chartInvoicingCoverage').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: invCov.map(c => c.label),
                datasets: [{
                    data: invCov.map(c => c.value),
                    backgroundColor: ['#0b6aa0', '#cbd5e1'],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } } },
                maintainAspectRatio: false
            }
        });

        // Handled By horizontal bar
        new Chart(document.getElementById('chartHandledBy').getContext('2d'), {
            type: 'bar',
            data: {
                labels: (d.handledBy || []).map(s => s.name),
                datasets: [{
                    data: (d.handledBy || []).map(s => s.value),
                    backgroundColor: handledByColors.slice(0, Math.max((d.handledBy || []).length, 1)),
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { stepSize: 1 } },
                    y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                },
                maintainAspectRatio: false
            }
        });
    }

    document.addEventListener('DOMContentLoaded', render);
    // Period dropdown: shared tom-select styling (the underlying <select> still holds the value).
    document.addEventListener('DOMContentLoaded', function () {
        if (window.initTomSelectForm && window.jQuery) { initTomSelectForm($('#dateRangeWrap')); }
    });

    document.getElementById('btn-apply').addEventListener('click', () => {
        const range = document.getElementById('dateRange').value;
        window.location.href = '/operation/job-overview?range=' + range;
    });
    </script>
</x-app-layout>
