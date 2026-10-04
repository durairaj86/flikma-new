@section('page-title', __('Dashboard'))
@section('page-sub-title', __('Overview of the company\'s performance'))
<x-app-layout>

    <style>
        .kpi { height: 100%; padding: 16px 18px 14px; border-radius: 18px; border: 0;
            background: var(--kbg, #fff);
            box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(15,23,42,.07); }
        .kpi-medium-body { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 10px; }
        .kpi-medium-text { min-width: 0; }
        .kpi-medium-chart { position: relative; width: 48%; height: 96px; flex-shrink: 0; }
        .kpi-medium .kpi-main, .kpi-medium .kpi-value { margin-top: 0; }
        .kpi-medium .kpi-legend { margin-top: 10px; }
        .kpi-legend.kpi-legend-3 { grid-template-columns: repeat(3, 1fr); font-size: .72rem; }
        .kpi-list { margin-top: 10px; background: rgba(255,255,255,.75); border-radius: 12px; padding: 4px 10px; }
        .kpi-list-row { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 7px 0; font-size: .8rem; color: #101828; border-bottom: 1px solid rgba(15,23,42,.06); }
        .kpi-list-row:last-child { border-bottom: 0; }
        .kpi-list-name { display: flex; flex-direction: column; min-width: 0; font-weight: 600; }
        .kpi-list-name small { font-weight: 400; color: #667085; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .kpi-month-static { font-size: .72rem; color: #667085; background: rgba(255,255,255,.7); border-radius: 999px; padding: 2px 8px; }
        .kpi-duo { display: flex; gap: 22px; }
        .kpi-duo .kpi-value { font-size: 2rem; }
        .kpi-cost-row { display: flex; align-items: center; gap: 8px; font-size: .88rem; color: #475467; padding: 3px 0; }
        .kpi-cost-row em { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
        .kpi-cost-row b { margin-left: auto; color: #101828; padding-left: 12px; }
        .kpi.kpi-chart-card { height: 236px; display: flex; flex-direction: column; overflow: hidden; }
        .kpi-chart-wrap { position: relative; flex: 1; min-height: 0; margin-top: 12px; }
        .kpi-medium { height: 236px; overflow-y: auto; }
        .kpi-tall { height: calc(2 * 236px + 1rem); display: flex; flex-direction: column; overflow: hidden; }
        .kpi-tall-scroll { flex: 1; min-height: 0; overflow-y: auto; margin-top: 10px; border-radius: 12px; }
        .kpi-tall-foot { font-size: .75rem; color: #667085; padding-top: 8px; }
        .kpi-table thead th { position: sticky; top: 0; background: #f8fafc; font-size: .72rem; text-transform: uppercase; color: #667085; z-index: 1; }
        .kpi-table td { font-size: .8rem; }
        .wd-loading { opacity: .6; transition: opacity .15s; }
        .wd-month { position: relative; flex-shrink: 0; }
        .wd-month-btn { white-space: nowrap; }
        .kpi-title { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wd-month-btn { display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(15,23,42,.1); background: rgba(255,255,255,.8);
            border-radius: 999px; padding: 4px 10px; font-size: .75rem; font-weight: 600; color: #344054; cursor: pointer; transition: background .15s, box-shadow .15s; }
        .wd-month-btn:hover, .wd-month-btn.is-open { background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.1); }
        .wd-month-btn i:first-child { color: var(--kc); }
        .wd-month-caret { font-size: .65rem; color: #98a2b3; transition: transform .2s; }
        .wd-month-btn.is-open .wd-month-caret { transform: rotate(180deg); }
        .wd-month-menu { position: absolute; right: 0; top: calc(100% + 6px); z-index: 30; min-width: 150px; max-height: 240px; overflow-y: auto;
            background: #fff; border: 1px solid #eaecf0; border-radius: 14px; padding: 6px; box-shadow: 0 16px 36px rgba(15,23,42,.16); }
        .wd-month-item { display: flex; align-items: center; justify-content: space-between; width: 100%; border: 0; background: transparent;
            padding: 7px 10px; border-radius: 9px; font-size: .8rem; color: #344054; cursor: pointer; text-align: left; }
        .wd-month-item:hover { background: #f2f4f7; }
        .wd-month-item.active { background: color-mix(in srgb, var(--kc, #2563eb) 12%, #fff); color: var(--kc, #2563eb); font-weight: 600; }
        .kpi-head { display: flex; align-items: center; gap: 10px; }
        .kpi-icon { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;
            color: #fff; background: var(--kc); font-size: .85rem; border-radius: 50%; width: 32px; height: 32px;
            box-shadow: 0 3px 8px color-mix(in srgb, var(--kc) 40%, transparent); }
        .kpi-title { font-weight: 600; font-size: .9rem; color: #475467; flex: 1; }
        .kpi-month { border: 1px solid #e4e7ec; background: #f9fafb; border-radius: 999px; font-size: .75rem; padding: 2px 8px; color: #344054; cursor: pointer; outline: 0; }
        .kpi-main { display: flex; align-items: baseline; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
        .kpi-value { font-size: 1.9rem; font-weight: 700; letter-spacing: -.02em; color: #101828; white-space: nowrap; line-height: 1.1; }
        .kpi-pill { font-size: .7rem; font-weight: 600; padding: 2px 8px; border-radius: 999px; }
        .kpi-pill.up { background: #dcfae6; color: #067647; } .kpi-pill.down { background: #fee4e2; color: #b42318; }
        .kpi-note { font-size: .8rem; color: #667085; margin-top: 4px; }
        .kpi-bar { height: 8px; border-radius: 99px; background: rgba(15,23,42,.08); overflow: hidden; margin-top: 16px; }
        .kpi-bar i { display: block; height: 100%; border-radius: 99px; background: var(--kc); }
        .kpi-legend { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 12px; font-size: .78rem; color: #667085; }
        .kpi-legend span { background: rgba(255,255,255,.75); border-radius: 12px; padding: 8px 10px; display: block; line-height: 1.5; }
        .kpi-legend b { color: #101828; font-size: .95rem; display: block; margin: 0; }
        .kpi-legend em { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 5px; }
        .kpi-legend .dot-a { background: var(--kc); } .kpi-legend .dot-b { background: #d0d5dd; }
        /* Apple-style widgets: large radius, tinted glass surface, small colored
           icon chip, big tight-tracked number, quiet secondary line. */
        .apple-widget {
            --aw-accent: #0a84ff; --aw-tint: rgba(10,132,255,.10);
            position: relative; height: 100%; min-height: 128px;
            padding: 16px 18px; border-radius: 22px;
            background: linear-gradient(145deg, #fff 0%, var(--aw-tint) 140%);
            border: 1px solid rgba(255,255,255,.7);
            box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.06);
            display: flex; flex-direction: column; justify-content: space-between;
            transition: transform .2s ease, box-shadow .2s ease;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Inter, sans-serif;
        }
        .apple-widget:hover { transform: translateY(-2px); box-shadow: 0 2px 4px rgba(0,0,0,.05), 0 14px 32px rgba(0,0,0,.10); }
        .aw-blue   { --aw-accent: #0a84ff; --aw-tint: rgba(10,132,255,.14); }
        .aw-teal   { --aw-accent: #30b0c7; --aw-tint: rgba(48,176,199,.16); }
        .aw-orange { --aw-accent: #ff9f0a; --aw-tint: rgba(255,159,10,.16); }
        .aw-green  { --aw-accent: #30d158; --aw-tint: rgba(48,209,88,.16); }
        .aw-top { display: flex; align-items: center; gap: 8px; }
        .aw-icon {
            width: 30px; height: 30px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--aw-accent); color: #fff; font-size: .8rem;
            box-shadow: 0 3px 8px color-mix(in srgb, var(--aw-accent) 45%, transparent);
        }
        .aw-label { font-size: .82rem; font-weight: 600; color: #6e6e73; letter-spacing: .01em; }
        .aw-value { font-size: 2rem; font-weight: 700; letter-spacing: -.03em; line-height: 1.1; color: #1d1d1f; margin-top: 10px; }
        .aw-sub { font-size: .8rem; font-weight: 500; color: #86868b; margin-top: 4px; }
        .aw-sub.aw-up { color: #248a3d; }
        .aw-sub.aw-down { color: #d70015; }
        /* Cards */
        .card { border-radius: 10px; box-shadow: 0 6px 18px rgba(20,20,50,0.04); border: 0; }
        .card-header { background: transparent; border-bottom: 1px solid rgba(0,0,0,0.04); padding: .9rem 1rem; }
        .card-body { padding: 1rem; }

        /* Stat boxes (top left) */
        .stat-box { padding: 18px; text-align: left; }
        .stat-box .stat-value { font-size: 1.25rem; font-weight: 600; margin-top: .25rem; }
        .stat-box .stat-label { font-size: .85rem; color: #6c757d; }
        .stat-icon { width: 56px; height: 56px; border-radius: 8px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }

        /* Right column summary boxes */
        .right-card { margin-bottom: 16px; padding: 14px; background: #fff; border-radius: .65rem; }
        .right-card h6 { margin:0; font-size: .92rem; color: #6c757d; }
        .right-card .big { font-size:1.35rem; font-weight:700; margin-top:6px; }
        .muted-sm { color:#6c757d; font-size:.82rem; }

        /* Mini chart canvases */
        .mini-canvas { width:100%; height:72px !important; }

        /* Recent transactions table */
        .table thead th { border-bottom: 0; font-weight:600; color:#495057; }
        .table tbody td { vertical-align: middle; }

        /* Make sure cards line up vertically */
        @media (min-width: 992px) {
            .left-col { padding-right: 12px; }
            .right-col { padding-left: 12px; /*max-width: 370px;*/ }
        }

        /* Footer small text */
        footer.small { color:#8a8f98; margin-top:12px; }
    </style>

    <div class="p-3">
                <!-- Top row: Title & timeframe -->
                <div class="container-fluid mb-3 d-none">
                    <div class="row align-items-center">
                        <div class="col">
                            {{--<h4 class="mb-0">Dashboard</h4>
                            <small class="muted-sm">Overview of the company's performance</small>--}}
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group" aria-label="Period">
                                <button class="btn btn-outline-secondary btn-sm">{{ __('Today') }}</button>
                                <button class="btn btn-outline-secondary btn-sm">{{ __('Week') }}</button>
                                <button class="btn btn-outline-secondary btn-sm active">{{ __('Month') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main two-column layout -->
                <div class="container-fluid">
                    <div class="row">
                        <!-- LEFT: Main analytics (8/12) -->
                        <div class="col-lg-8 left-col">

                            <!-- TOP: 4 stat boxes (grid) -->
                            <!-- Month summary cards -->
                            {{-- Medium widgets (with charts) --}}
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6"><livewire:widgets.sales size="medium" /></div>
                                <div class="col-12 col-sm-6"><livewire:widgets.invoices size="medium" /></div>
                                <div class="col-12 col-sm-6"><livewire:widgets.customers size="medium" /></div>
                                <div class="col-12 col-sm-6"><livewire:widgets.profit size="medium" /></div>
                            </div>

                            {{-- Small widgets --}}
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-4"><livewire:widgets.quotation size="small" /></div>
                                <div class="col-12 col-sm-4"><livewire:widgets.payments size="small" /></div>
                                <div class="col-12 col-sm-4"><livewire:widgets.collection size="small" /></div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.eta-etd-medium')</div>
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.ata-atd-medium')</div>
                            </div>

                            <div class="row g-3 mt-1 mb-3">
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.recent-transactions-tall')</div>
                                <div class="col-12 col-sm-6 d-flex flex-column gap-3">
                                    @include('dashboard.widgets.job-status-medium')
                                    @include('dashboard.widgets.to-collect-pay-medium')
                                </div>
                            </div>

                            <!-- Middle: Two charts side-by-side -->
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.revenue-expenses-medium')</div>
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.revenue-trend-medium')</div>
                            </div>

                        </div> <!-- /.left-col -->

                        <!-- RIGHT: summary / mini panels (4/12) -->
                        <div class="col-lg-4 right-col">
                            <!-- Outstanding -->
                            <div class="mb-3">@include('dashboard.widgets.outstanding-medium')</div>

                            <div class="mb-3">@include('dashboard.widgets.awaiting-approval-medium')</div>

                            <div class="mb-3">@include('dashboard.widgets.cost-summary-medium')</div>

                            <div class="mb-3">@include('dashboard.widgets.revenue-summary-medium')</div>

                        </div> <!-- /.right-col -->
                    </div> <!-- /.row -->
                </div> <!-- /.container-fluid -->







                <footer class="small text-center mt-3 mb-0">© <span id="y"></span> {{ companyName() }} — {{ __('All rights reserved.') }}</footer>
    </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

        <script>
            // footer year
            document.getElementById('y').innerText = new Date().getFullYear();

            // Revenue vs Expenses chart (monthly bars, in thousands)
            var monthlyLabels = @json($monthlyLabels);
            var monthlyRevenue = @json($monthlyRevenue);
            var monthlyExpenses = @json($monthlyExpenses);

            new Chart(document.getElementById('salesMainChart'), {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [
                        { label: @json(__('Revenue')), data: monthlyRevenue, backgroundColor: 'rgba(13,110,253,0.9)', borderRadius: 6 },
                        { label: @json(__('Expenses')), data: monthlyExpenses, backgroundColor: 'rgba(220,53,69,0.85)', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString()}K` } }
                    },
                    scales: { y: { beginAtZero: true, ticks: { callback: (v) => '₹' + v + 'K' } } }
                }
            });

            // Main Revenue chart (line)
            var weeklyLabels = @json($weeklyLabels);
            var weeklyRevenueData = @json($weeklyRevenueData);

            new Chart(document.getElementById('revenueMainChart'), {
                type: 'line',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        label: @json(__('Revenue')),
                        data: weeklyRevenueData,
                        borderColor: 'rgba(13,110,253,0.95)',
                        backgroundColor: 'rgba(13,110,253,0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            // Medium widget charts (donut / area / bars) driven by data-* attributes
            function initKpiCharts(root) {
              (root || document).querySelectorAll('canvas.kpi-chart').forEach(function (cv) {
                var existing = Chart.getChart(cv);
                if (existing) { existing.destroy(); }
                var type = cv.dataset.type, color = cv.dataset.color;
                var values = JSON.parse(cv.dataset.values), labels = JSON.parse(cv.dataset.labels);
                var base = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };
                if (type === 'donut') {
                    var colors = cv.dataset.colors ? JSON.parse(cv.dataset.colors) : [color, 'rgba(15,23,42,.12)'];
                    var names = cv.dataset.names ? JSON.parse(cv.dataset.names) : ['Approved', 'Draft'];
                    var total = values.reduce(function (a, b) { return a + b; }, 0);
                    new Chart(cv, { type: 'doughnut', data: { labels: total ? names : [''], datasets: [{ data: total ? values : [1], backgroundColor: total ? colors : ['rgba(15,23,42,.12)'], borderWidth: 0 }] },
                        options: Object.assign({}, base, { cutout: '68%' }) });
                    return;
                }
                if (type === 'dualbar') {
                    var ds = JSON.parse(cv.dataset.datasets).map(function (d) { return { label: d.label, data: d.data, backgroundColor: d.color, borderRadius: 3 }; });
                    new Chart(cv, { type: 'bar', data: { labels: labels, datasets: ds }, options: Object.assign({}, base, { scales: { x: { display: false }, y: { display: false } } }) });
                    return;
                }
                var axes = { scales: { x: { display: false }, y: { display: false } } };
                if (type === 'line') {
                    new Chart(cv, { type: 'line', data: { labels: labels, datasets: [{ data: values, borderColor: color, backgroundColor: color + '33', fill: true, tension: .4, pointRadius: 0, borderWidth: 2 }] },
                        options: Object.assign({}, base, axes) });
                } else {
                    new Chart(cv, { type: 'bar', data: { labels: labels, datasets: [{ data: values, borderRadius: 3, backgroundColor: type === 'profitbar' ? values.map(function (v) { return v >= 0 ? color : '#dc2626'; }) : color }] },
                        options: Object.assign({}, base, axes) });
                }
              });
            }
            initKpiCharts(document);

            // Each Livewire widget re-renders on its own (e.g. month change): redraw only its charts.
            document.addEventListener('livewire:init', function () {
                Livewire.hook('commit', function (ctx) {
                    ctx.succeed(function () { setTimeout(function () { initKpiCharts(ctx.component.el); }, 0); });
                });
            });
        </script>



</x-app-layout>
