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

            /* Sidebar summary list (distinct from the card-grid KPI style) */
            .job-summary-list .item {
                display: flex; align-items: center; justify-content: space-between;
                padding: .7rem 0; border-bottom: 1px dashed #e2e8f0;
            }
            .job-summary-list .item:last-child { border-bottom: 0; }
            .job-summary-list .item .label { font-size: .82rem; color: #64748b; display: flex; align-items: center; gap: .5rem; }
            .job-summary-list .item .value { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
            .job-summary-list .item .icon-dot {
                width: 30px; height: 30px; border-radius: 8px;
                display: flex; align-items: center; justify-content: center; font-size: .8rem; flex-shrink: 0;
            }
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

            <div class="row g-3">
                {{-- LEFT: Sidebar summary --}}
                <div class="col-lg-4">
                    <div class="job-card mb-3">
                        <div class="job-card-header">
                            <h6><i class="bi bi-clipboard-data me-2" style="color:#0b6aa0;"></i>Job Summary</h6>
                            <span class="badge-job">{{ $range === 'this_year' ? 'This Year' : ($range === 'last_month' ? 'Last Month' : 'This Month') }}</span>
                        </div>
                        <div class="job-card-body job-summary-list">
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(11,106,160,0.1);color:#0b6aa0;"><i class="bi bi-briefcase"></i></span> Total Jobs</span>
                                <span class="value" id="kpiTotalJobs">0</span>
                            </div>
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(22,163,74,0.1);color:#16a34a;"><i class="bi bi-check-circle"></i></span> Completed</span>
                                <span class="value" id="kpiCompletedJobs">0</span>
                            </div>
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(245,158,11,0.12);color:#f59e0b;"><i class="bi bi-hourglass-split"></i></span> Pending</span>
                                <span class="value" id="kpiPendingJobs">0</span>
                            </div>
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(220,38,38,0.1);color:#dc2626;"><i class="bi bi-x-circle"></i></span> Cancelled</span>
                                <span class="value" id="kpiCancelledJobs">0</span>
                            </div>
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(91,87,174,0.1);color:#5b57ae;"><i class="bi bi-people"></i></span> Customers</span>
                                <span class="value" id="kpiCustomerCount">0</span>
                            </div>
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(11,106,160,0.1);color:#0b6aa0;"><i class="bi bi-arrow-repeat"></i></span> Repeat Customers</span>
                                <span class="value" id="kpiRepeat">0%</span>
                            </div>
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(22,163,74,0.1);color:#16a34a;"><i class="bi bi-box-seam"></i></span> Avg Containers / Job</span>
                                <span class="value" id="kpiAvgContainers">0</span>
                            </div>
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(245,158,11,0.12);color:#f59e0b;"><i class="bi bi-graph-up-arrow"></i></span> vs Last Month</span>
                                <span class="value" id="kpiJobsChange" style="font-size:.9rem;">0%</span>
                            </div>
                            <div class="item">
                                <span class="label"><span class="icon-dot" style="background:rgba(91,87,174,0.1);color:#5b57ae;"><i class="bi bi-speedometer2"></i></span> Completion Rate</span>
                                <span class="value" id="kpiCompletionRate" style="font-size:.9rem;">0%</span>
                            </div>
                        </div>
                    </div>

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
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody id="tableJobStatuses"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Trend + breakdowns + tables, stacked --}}
                <div class="col-lg-8">
                    <div class="job-card mb-3">
                        <div class="job-card-header">
                            <h6><i class="bi bi-graph-up me-2" style="color:#0b6aa0;"></i>Jobs Trend</h6>
                            <span class="badge-job">{{ $range === 'this_year' ? 'Monthly' : 'Weekly' }}</span>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartJobsTrend" height="110"></canvas>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="job-card h-100">
                                <div class="job-card-header">
                                    <h6><i class="bi bi-pie-chart me-2" style="color:#5b57ae;"></i>By Service Type</h6>
                                </div>
                                <div class="job-card-body">
                                    <canvas id="chartServiceType" height="170"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="job-card h-100">
                                <div class="job-card-header">
                                    <h6><i class="bi bi-truck me-2" style="color:#16a34a;"></i>By Shipment Mode</h6>
                                </div>
                                <div class="job-card-body">
                                    <canvas id="chartShipmentMode" height="170"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="job-card mb-3">
                        <div class="job-card-header">
                            <h6><i class="bi bi-people me-2" style="color:#0b6aa0;"></i>Handled By</h6>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartHandledBy" height="130"></canvas>
                        </div>
                    </div>

                    <div class="job-card mb-3">
                        <div class="job-card-header">
                            <h6><i class="bi bi-trophy me-2" style="color:#f59e0b;"></i>Top Customers by Job Count</h6>
                            <span class="badge-job">Jobs / Containers / Packages</span>
                        </div>
                        <div class="job-card-body p-0">
                            <div style="max-height:300px;overflow:auto;">
                                <table class="table job-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Customer</th>
                                            <th class="text-end">Jobs</th>
                                            <th class="text-end">Containers</th>
                                            <th class="text-end">Packages</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableCustomers"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="job-card">
                        <div class="job-card-header">
                            <h6><i class="bi bi-signpost-split me-2" style="color:#0b6aa0;"></i>Top Routes</h6>
                            <span class="badge-job">POL &rarr; POD</span>
                        </div>
                        <div class="job-card-body p-0">
                            <div style="max-height:300px;overflow:auto;">
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

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const DATA = @json($data);

    function fmt(n) { return new Intl.NumberFormat('en-IN').format(Math.round(n)); }
    function pct(n) { return (n * 100).toFixed(1) + '%'; }

    function render() {
        const d = DATA;

        // Summary list
        document.getElementById('kpiTotalJobs').innerText = fmt(d.totalJobs);
        document.getElementById('kpiCompletedJobs').innerText = fmt(d.completedJobs);
        document.getElementById('kpiPendingJobs').innerText = fmt(d.pendingJobs);
        document.getElementById('kpiCancelledJobs').innerText = fmt(d.cancelledJobs);
        document.getElementById('kpiCustomerCount').innerText = fmt(d.customersCount);
        document.getElementById('kpiRepeat').innerText = pct(d.repeatRatio);
        document.getElementById('kpiAvgContainers').innerText = (d.avgContainersPerJob || 0).toFixed(1);

        // Monthly comparison
        const mc = d.monthlyComparison || {};
        const prevJobs = mc.previous?.jobs || 0;
        const jobsChange = prevJobs > 0 ? ((d.totalJobs - prevJobs) / prevJobs) * 100 : 0;
        const chgEl = document.getElementById('kpiJobsChange');
        chgEl.innerText = (jobsChange >= 0 ? '+' : '') + jobsChange.toFixed(1) + '%';
        chgEl.className = 'value' + (jobsChange >= 0 ? ' trend-up' : ' trend-down');

        // Completion rate
        const compRate = d.totalJobs > 0 ? (d.completedJobs / d.totalJobs) * 100 : 0;
        document.getElementById('kpiCompletionRate').innerText = compRate.toFixed(1) + '%';

        // Top customers table
        const custHtml = (d.customers || []).map((c, i) => {
            return `<tr>
                <td>${i + 1}</td>
                <td class="fw-medium">${c.name}</td>
                <td class="text-end">${c.jobs}</td>
                <td class="text-end">${c.containers}</td>
                <td class="text-end">${c.packages}</td>
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
    }

    document.addEventListener('DOMContentLoaded', render);

    document.getElementById('btn-apply').addEventListener('click', () => {
        const range = document.getElementById('dateRange').value;
        window.location.href = '/operation/job-overview?range=' + range;
    });
    </script>
</x-app-layout>
