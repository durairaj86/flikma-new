@section('page-title','Job Overview')
@section('page-sub-title','Operations performance dashboard with real-time data')
@section('print-footer')
<script>
    window.printFooter = {
        show: true,
        custom: 'Job Overview - Generated on {{ date('d-m-Y H:i') }}'
    };
</script>
@endsection
<x-app-layout>
    <div class="bg-light py-4">
        <style>
            :root{
                --job_primary: #0b6aa0;
                --job_secondary: #5b57ae;
                --job_accent: #16a34a;
                --job_bg: #f8fafc;
                --job_card_bg: #ffffff;
                --job_radius: 12px;
                --job_shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            }
            body { background: var(--job_bg); }
            .job-kpi-card {
                background: var(--job_card_bg);
                border-radius: var(--job_radius);
                box-shadow: var(--job_shadow);
                padding: 1.25rem;
                transition: box-shadow .2s;
                border: 1px solid rgba(0,0,0,0.04);
            }
            .job-kpi-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
            .job-kpi-card .kpi-label { font-size: .8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
            .job-kpi-card .kpi-value { font-size: 1.65rem; font-weight: 700; color: #0f172a; line-height: 1.2; margin-top: .25rem; }
            .job-kpi-card .kpi-sub { font-size: .78rem; color: #94a3b8; margin-top: .2rem; }
            .job-icon-circle { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
            .job-card {
                background: var(--job_card_bg);
                border-radius: var(--job_radius);
                box-shadow: var(--job_shadow);
                border: 1px solid rgba(0,0,0,0.04);
            }
            .job-card-header {
                display: flex; align-items: center; justify-content: space-between;
                padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
            }
            .job-card-header h6 { margin: 0; font-weight: 700; font-size: .9rem; color: #0f172a; }
            .job-card-body { padding: 1.25rem; }
            .job-table th { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; border-bottom-width: 1px; }
            .job-table td { font-size: .82rem; vertical-align: middle; color: #1e293b; }
            .badge-job { background: rgba(11,106,160,0.1); color: #0b6aa0; font-weight: 600; font-size: .7rem; padding: .25em .7em; border-radius: 20px; }
            .trend-up { color: #16a34a; }
            .trend-down { color: #dc2626; }
        </style>

        <div class="container-fluid px-lg-5">

            {{-- Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0" style="color:#0f172a;">Job Overview</h4>
                    <p class="text-muted mb-0 small">Real-time operations performance dashboard</p>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                    <select id="dateRange" class="form-select form-select-sm" style="width:auto;min-width:140px;">
                        <option value="this_month" {{ $range==='this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $range==='last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ $range==='this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                    <button class="btn btn-primary btn-sm px-3" id="btn-apply">
                        <i class="bi bi-arrow-repeat me-1"></i> Apply
                    </button>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="job-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Total Jobs</div>
                            <div class="kpi-value" id="kpiTotalJobs">0</div>
                            <div class="kpi-sub">Created this period</div>
                        </div>
                        <div class="job-icon-circle" style="background:rgba(11,106,160,0.1);color:#0b6aa0;">
                            <i class="bi bi-briefcase"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Completed</div>
                            <div class="kpi-value" id="kpiCompletedJobs">0</div>
                            <div class="kpi-sub">Finished this period</div>
                        </div>
                        <div class="job-icon-circle" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Pending</div>
                            <div class="kpi-value" id="kpiPendingJobs" style="color:#dc2626;">0</div>
                            <div class="kpi-sub">Currently in progress</div>
                        </div>
                        <div class="job-icon-circle" style="background:rgba(220,38,38,0.1);color:#dc2626;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Avg Job Value</div>
                            <div class="kpi-value" id="kpiAvgJobValue">SAR 0</div>
                            <div class="kpi-sub">Via linked invoices</div>
                        </div>
                        <div class="job-icon-circle" style="background:rgba(91,87,174,0.1);color:#5b57ae;">
                            <i class="bi bi-bar-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Secondary KPIs --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">Cancelled</div>
                        <div class="kpi-value" id="kpiCancelledJobs" style="font-size:1.3rem;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">Customers</div>
                        <div class="kpi-value" id="kpiCustomerCount" style="font-size:1.3rem;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">Repeat</div>
                        <div class="kpi-value" id="kpiRepeat" style="font-size:1.3rem;">0%</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">vs Last Month (Jobs)</div>
                        <div class="kpi-value" id="kpiJobsChange" style="font-size:1.1rem;">0%</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">Completion Rate</div>
                        <div class="kpi-value" id="kpiCompletionRate" style="font-size:1.1rem;">0%</div>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-7">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-graph-up me-2" style="color:#0b6aa0;"></i>Jobs Trend</h6>
                            <span class="badge-job">{{ $range === 'this_year' ? 'Monthly' : 'Weekly' }}</span>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartJobsTrend" height="180"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-pie-chart me-2" style="color:#5b57ae;"></i>Jobs by Service Type</h6>
                            <span class="badge-job">Top 6</span>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartServiceType" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-truck me-2" style="color:#16a34a;"></i>Jobs by Shipment Mode</h6>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartShipmentMode" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-people me-2" style="color:#0b6aa0;"></i>Handled By</h6>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartHandledBy" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-check-circle me-2" style="color:#5b57ae;"></i>Job Status</h6>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartStatus" height="170"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tables Row --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="job-card">
                        <div class="job-card-header">
                            <h6><i class="bi bi-trophy me-2" style="color:#f59e0b;"></i>Top 10 Customers by Job Count</h6>
                            <span class="badge-job">Jobs / Revenue / Outstanding</span>
                        </div>
                        <div class="job-card-body p-0">
                            <div style="max-height:380px;overflow:auto;">
                                <table class="table job-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Customer</th>
                                            <th class="text-end">Jobs</th>
                                            <th class="text-end">Revenue (SAR)</th>
                                            <th class="text-end">Outstanding</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableCustomers"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="job-card">
                        <div class="job-card-header">
                            <h6><i class="bi bi-signpost-split me-2" style="color:#0b6aa0;"></i>Top Routes</h6>
                            <span class="badge-job">POL &rarr; POD</span>
                        </div>
                        <div class="job-card-body p-0">
                            <div style="max-height:380px;overflow:auto;">
                                <table class="table job-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Route</th>
                                            <th class="text-end">Jobs</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableRoutes"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Job Status Summary --}}
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="job-card">
                        <div class="job-card-header">
                            <h6><i class="bi bi-list-check me-2" style="color:#0b6aa0;"></i>Job Status Breakdown</h6>
                        </div>
                        <div class="job-card-body p-0">
                            <table class="table job-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th class="text-end">Count</th>
                                        <th class="text-end">% of Total</th>
                                    </tr>
                                </thead>
                                <tbody id="tableJobStatuses"></tbody>
                            </table>
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
    function fmtCurrency(n) { return 'SAR ' + fmt(n); }
    function pct(n) { return (n * 100).toFixed(1) + '%'; }

    function render() {
        const d = DATA;

        // Primary KPIs
        document.getElementById('kpiTotalJobs').innerText = fmt(d.totalJobs);
        document.getElementById('kpiCompletedJobs').innerText = fmt(d.completedJobs);
        document.getElementById('kpiPendingJobs').innerText = fmt(d.pendingJobs);
        document.getElementById('kpiAvgJobValue').innerText = fmtCurrency(d.avgJobValue);

        // Secondary KPIs
        document.getElementById('kpiCancelledJobs').innerText = fmt(d.cancelledJobs);
        document.getElementById('kpiCustomerCount').innerText = fmt(d.customersCount);
        document.getElementById('kpiRepeat').innerText = pct(d.repeatRatio);

        // Monthly comparison
        const mc = d.monthlyComparison || {};
        const prevJobs = mc.previous?.jobs || 0;
        const jobsChange = prevJobs > 0 ? ((d.totalJobs - prevJobs) / prevJobs) * 100 : 0;
        const chgEl = document.getElementById('kpiJobsChange');
        chgEl.innerText = (jobsChange >= 0 ? '+' : '') + jobsChange.toFixed(1) + '%';
        chgEl.className = 'kpi-value' + (jobsChange >= 0 ? ' trend-up' : ' trend-down');

        // Completion rate
        const compRate = d.totalJobs > 0 ? (d.completedJobs / d.totalJobs) * 100 : 0;
        document.getElementById('kpiCompletionRate').innerText = compRate.toFixed(1) + '%';

        // Top customers table
        const custHtml = (d.customers || []).map((c, i) => {
            return `<tr>
                <td>${i + 1}</td>
                <td class="fw-medium">${c.name}</td>
                <td class="text-end">${c.jobs}</td>
                <td class="text-end fw-semibold">${fmtCurrency(c.revenue)}</td>
                <td class="text-end ${c.outstanding > 0 ? 'text-danger' : ''}">${fmtCurrency(c.outstanding)}</td>
            </tr>`;
        }).join('');
        document.getElementById('tableCustomers').innerHTML = custHtml || '<tr><td colspan="5" class="text-center text-muted py-3">No data</td></tr>';

        // Top routes table
        const routesHtml = (d.routes || []).map((r, idx) => {
            return `<tr>
                <td>${idx + 1}</td>
                <td>${r.route}</td>
                <td class="text-end">${fmt(r.jobs)}</td>
            </tr>`;
        }).join('');
        document.getElementById('tableRoutes').innerHTML = routesHtml || '<tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>';

        // Job status table
        const statuses = d.jobStatuses || [];
        const totalStatusCount = statuses.reduce((sum, s) => sum + s.count, 0);
        const statusHtml = statuses.map(s => {
            const badgeClass = s.status === 'completed' ? 'bg-success' : s.status === 'pending' ? 'bg-warning' : s.status === 'cancelled' ? 'bg-danger' : s.status === 'trashed' ? 'bg-secondary' : 'bg-secondary';
            const pctOfTotal = totalStatusCount ? ((s.count / totalStatusCount) * 100).toFixed(1) : 0;
            return `<tr>
                <td><span class="badge ${badgeClass}">${s.label}</span></td>
                <td class="text-end">${s.count}</td>
                <td class="text-end fw-semibold">${pctOfTotal}%</td>
            </tr>`;
        }).join('');
        document.getElementById('tableJobStatuses').innerHTML = statusHtml || '<tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>';

        // --- Charts ---
        const colorPalette = ['#0b6aa0','#5b57ae','#16a34a','#f59e0b','#dc2626','#8b5cf6','#06b6d4','#f97316'];

        // Jobs Trend
        const ctxTrend = document.getElementById('chartJobsTrend').getContext('2d');
        const trendLabels = '{{ $range }}' === 'this_year'
            ? ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'].slice(0, d.jobsTrend.length)
            : Array.from({length: d.jobsTrend.length}, (_, i) => 'W' + (i + 1));
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Jobs',
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

        // Service type doughnut
        new Chart(document.getElementById('chartServiceType').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: (d.serviceTypes || []).map(c => c.label),
                datasets: [{
                    data: (d.serviceTypes || []).map(c => c.value),
                    backgroundColor: colorPalette.slice(0, Math.max((d.serviceTypes || []).length, 1)),
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } },
                maintainAspectRatio: false
            }
        });

        // Shipment mode doughnut
        new Chart(document.getElementById('chartShipmentMode').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: (d.shipmentModes || []).map(r => r.label),
                datasets: [{
                    data: (d.shipmentModes || []).map(r => r.value),
                    backgroundColor: colorPalette.slice(0, Math.max((d.shipmentModes || []).length, 1)),
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } },
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
                    backgroundColor: '#0b6aa0',
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

        // Job Status bar
        new Chart(document.getElementById('chartStatus').getContext('2d'), {
            type: 'bar',
            data: {
                labels: statuses.map(s => s.label),
                datasets: [{
                    label: 'Count',
                    data: statuses.map(s => s.count),
                    backgroundColor: statuses.map(s =>
                        s.status === 'completed' ? '#16a34a' : s.status === 'pending' ? '#f59e0b' : s.status === 'cancelled' ? '#dc2626' : '#94a3b8'
                    ),
                    borderRadius: 4
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
    }

    document.addEventListener('DOMContentLoaded', render);

    document.getElementById('btn-apply').addEventListener('click', () => {
        const range = document.getElementById('dateRange').value;
        window.location.href = '/operation/job-overview?range=' + range;
    });
    </script>
</x-app-layout>
