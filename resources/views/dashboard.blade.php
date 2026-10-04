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
                            @php
                                $sc = $summaryCards;
                                $short = function ($n) {
                                    $a = abs($n);
                                    if ($a >= 1000000) return number_format($n / 1000000, 2) . 'M';
                                    if ($a >= 1000) return number_format($n / 1000, 2) . 'K';
                                    return number_format($n, 2);
                                };
                                $chg = fn ($v) => '<span class="sc-change ' . ($v >= 0 ? 'up' : 'down') . '">(' . ($v >= 0 ? '+' : '') . number_format($v, 2) . '% ' . ($v >= 0 ? '&uarr;' : '&darr;') . ')</span>';
                            @endphp
                            @php
                                $monthSelect = function () use ($summaryMonths, $sc) {
                                    $o = '';
                                    foreach ($summaryMonths as $val => $lbl) {
                                        $o .= '<option value="' . $val . '"' . ($val === $sc['month'] ? ' selected' : '') . '>' . \Illuminate\Support\Carbon::createFromFormat('Y-m', $val)->format('M') . '</option>';
                                    }
                                    return '<select class="kpi-month" onchange="window.location=\'?month=\'+this.value">' . $o . '</select>';
                                };
                                $change = fn ($cur, $prev) => $prev > 0 ? round((($cur - $prev) / $prev) * 100, 2) : ($cur > 0 ? 100.0 : 0.0);
                                $pill = fn ($v) => '<span class="kpi-pill ' . ($v >= 0 ? 'up' : 'down') . '">' . ($v >= 0 ? '&#9650;' : '&#9660;') . ' ' . number_format(abs($v), 1) . '%</span>';
                                $series = $sc['series'];
                            @endphp

                            {{-- Medium widgets (with charts) --}}
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.sales-medium')</div>
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.invoices-medium')</div>
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.customers-medium')</div>
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.profit-medium')</div>
                            </div>

                            {{-- Small widgets --}}
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-4">@include('dashboard.widgets.quotation-small')</div>
                                <div class="col-12 col-sm-4">@include('dashboard.widgets.payments-small')</div>
                                <div class="col-12 col-sm-4">@include('dashboard.widgets.collection-small')</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.eta-etd-medium')</div>
                                <div class="col-12 col-sm-6">@include('dashboard.widgets.ata-atd-medium')</div>
                            </div>

                            <div class="row g-3 mt-1 mb-3">
                                <!-- Job Status -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-semibold mb-0">{{ __('Job Status') }}</h6>
                                            <i class="fa-solid fa-truck-fast text-primary"></i>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>{{ __('Active Jobs') }}</span>
                                            <span class="fw-bold text-primary">{{ $activeJobs }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>{{ __('Completed This Month') }}</span>
                                            <span class="fw-bold text-success">{{ $completedJobsThisMonth }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payments -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-semibold mb-0">{{ __('Payments') }}</h6>
                                            <i class="fa-solid fa-money-bill-transfer text-success"></i>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>{{ __('To Collect') }}</span>
                                            <span class="fw-bold text-warning">₹{{ number_format($toCollect, 0) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>{{ __('To Pay') }}</span>
                                            <span class="fw-bold text-danger">₹{{ number_format($toPay, 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Middle: Two charts side-by-side -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">{{ __('Revenue vs Expenses') }}</h6>
                                            <div class="text-muted small">{{ __('Monthly') }}</div>
                                        </div>
                                        <div class="card-body" style="min-height:220px;">
                                            <canvas id="salesMainChart" style="height:220px;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">{{ __('Revenue Trend') }}</h6>
                                            <div class="text-muted small">{{ __('This month') }}</div>
                                        </div>
                                        <div class="card-body" style="min-height:220px;">
                                            <canvas id="revenueMainChart" style="height:220px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom: Recent transactions (wide) -->
                            <div class="card">
                                <div class="card-header d-flex flex-row justify-content-between align-items-center w-100">
                                    <h6 class="mb-0 me-2">{{ __('Recent Transactions') }}</h6>
                                    <a href="{{ route('invoices.customer') }}" class="btn btn-sm btn-outline-secondary ms-auto flex-shrink-0">{{ __('View All') }}</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 align-middle">
                                            <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Customer') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th class="text-end">{{ __('Amount') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($recentTransactions as $transaction)
                                                <tr>
                                                    <td>{{ $transaction->id }}</td>
                                                    <td>{{ $transaction->invoice_number ?? $transaction->row_no }}</td>
                                                    <td>{{ $transaction->customer->name ?? __('N/A') }}</td>
                                                    <td>{{ $transaction->invoice_date }}</td>
                                                    <td class="text-end">{{ number_format($transaction->grand_total, 0) }}</td>
                                                    <td>
                                                        @if($transaction->status == 'approved')
                                                            <span class="badge bg-success">{{ __('Paid') }}</span>
                                                        @elseif($transaction->status == 'draft')
                                                            <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                                                        @elseif($transaction->status == 'overdue')
                                                            <span class="badge bg-danger">{{ __('Overdue') }}</span>
                                                        @elseif($transaction->status == 'partial')
                                                            <span class="badge bg-info text-dark">{{ __('Part Paid') }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $transaction->status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">{{ __('No recent transactions found') }}</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-muted small">{{ __('Showing :shown of :total transactions', ['shown' => count($recentTransactions), 'total' => $totalInvoices]) }}</div>
                            </div>

                        </div> <!-- /.left-col -->

                        <!-- RIGHT: summary / mini panels (4/12) -->
                        <div class="col-lg-4 right-col">
                            <!-- Outstanding -->
                            <div class="mb-3">@include('dashboard.widgets.outstanding-medium')</div>

                            <div class="mb-3">@include('dashboard.widgets.awaiting-approval-medium')</div>

                            <!-- Cost Summary (mini-donut + stats) -->
                            <div class="right-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>{{ __('Cost Summary') }}</h6>
                                        <div class="muted-sm">{{ __('This month') }}</div>
                                    </div>
                                    <div style="width:120px;">
                                        <canvas id="costMiniChart" class="mini-canvas"></canvas>
                                    </div>
                                </div>

                                <hr class="my-2" />
                                <div class="small">
                                    <div class="d-flex justify-content-between mb-1"><div>{{ __('Material') }}</div><div>{{ $materialPercent }}%</div></div>
                                    <div class="d-flex justify-content-between mb-1"><div>{{ __('Labour') }}</div><div>{{ $labourPercent }}%</div></div>
                                    <div class="d-flex justify-content-between mb-0"><div>{{ __('Transport') }}</div><div>{{ $transportPercent }}%</div></div>
                                </div>
                            </div>

                            <!-- Revenue Summary (mini-line + totals) -->
                            <div class="right-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>{{ __('Revenue Summary') }}</h6>
                                        <div class="big">{{ number_format($currentMonthSales, 0) }}</div>
                                        <div class="muted-sm mt-1">{{ __('Net revenue (MTD)') }}</div>
                                    </div>
                                    <div style="width:120px;">
                                        <canvas id="revMiniChart" class="mini-canvas"></canvas>
                                    </div>
                                </div>

                                <hr class="my-2" />
                                <div class="d-flex justify-content-between small">
                                    <div>{{ __('Collected') }}</div>
                                    <div class="text-success">{{ number_format($currentMonthCollected, 0) }}</div>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <div>{{ __('Pending') }}</div>
                                    <div class="text-danger">{{ number_format($currentMonthPending, 0) }}</div>
                                </div>
                            </div>

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

            // Cost mini chart (doughnut)
            new Chart(document.getElementById('costMiniChart'), {
                type: 'doughnut',
                data: {
                    labels: [@json(__('Material')), @json(__('Labour')), @json(__('Transport'))],
                    datasets: [{
                        data: [@json($materialPercent), @json($labourPercent), @json($transportPercent)],
                        backgroundColor: ['#0d6efd','#ffc107','#20c997']
                    }]
                },
                options: { plugins: { legend: { display: false } }, cutout: '70%' }
            });

            // Revenue mini chart (sparkline line)
            new Chart(document.getElementById('revMiniChart'), {
                type: 'line',
                data: {
                    labels: ['D1','D2','D3','D4','D5','D6','D7'],
                    datasets: [{
                        data: @json($dailyRevenueData),
                        borderColor: 'rgba(13,110,253,0.95)',
                        tension: 0.3,
                        fill: false,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { display: false }, y: { display: false } },
                    elements: { line: { borderWidth: 2 } }
                }
            });

            // Medium widget charts (donut / area / bars) driven by data-* attributes
            document.querySelectorAll('canvas.kpi-chart').forEach(function (cv) {
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
        </script>



</x-app-layout>
