@section('page-title', __('Job Overview'))
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
            <div class="d-flex flex-wrap justify-content-end align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <select id="dateRange" class="form-select form-select-sm" style="width:auto;min-width:140px;">
                        <option value="this_month" {{ $range==='this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                        <option value="last_month" {{ $range==='last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                        <option value="this_year" {{ $range==='this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                    </select>
                    <button class="btn btn-primary btn-sm px-3" id="btn-apply">
                        <i class="bi bi-arrow-repeat me-1"></i> {{ __('Apply') }}
                    </button>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="job-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">{{ __('Total Jobs') }}</div>
                            <div class="kpi-value" id="kpiTotalJobs">0</div>
                            <div class="kpi-sub">{{ __('Created this period') }}</div>
                        </div>
                        <div class="job-icon-circle" style="background:rgba(11,106,160,0.1);color:#0b6aa0;">
                            <i class="bi bi-briefcase"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">{{ __('Completed') }}</div>
                            <div class="kpi-value" id="kpiCompletedJobs">0</div>
                            <div class="kpi-sub">{{ __('Finished this period') }}</div>
                        </div>
                        <div class="job-icon-circle" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">{{ __('Pending') }}</div>
                            <div class="kpi-value" id="kpiPendingJobs" style="color:#dc2626;">0</div>
                            <div class="kpi-sub">{{ __('Currently in progress') }}</div>
                        </div>
                        <div class="job-icon-circle" style="background:rgba(220,38,38,0.1);color:#dc2626;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">{{ __('Invoiced Jobs') }}</div>
                            <div class="kpi-value" id="kpiInvoicedJobs">0</div>
                            <div class="kpi-sub">{{ __('Have a customer invoice') }}</div>
                        </div>
                        <div class="job-icon-circle" style="background:rgba(91,87,174,0.1);color:#5b57ae;">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Secondary KPIs --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">{{ __('Cancelled') }}</div>
                        <div class="kpi-value" id="kpiCancelledJobs" style="font-size:1.3rem;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">{{ __('Customers') }}</div>
                        <div class="kpi-value" id="kpiCustomerCount" style="font-size:1.3rem;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">{{ __('From Quotations') }}</div>
                        <div class="kpi-value" id="kpiFromQuotations" style="font-size:1.3rem;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">{{ __('Repeat Customers') }}</div>
                        <div class="kpi-value" id="kpiRepeat" style="font-size:1.3rem;">0%</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">{{ __('Avg Containers/Job') }}</div>
                        <div class="kpi-value" id="kpiAvgContainers" style="font-size:1.3rem;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="job-kpi-card text-center py-2">
                        <div class="kpi-label">{{ __('vs Last Month') }}</div>
                        <div class="kpi-value" id="kpiJobsChange" style="font-size:1.1rem;">0%</div>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-7">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-graph-up me-2" style="color:#0b6aa0;"></i>{{ __('Jobs Trend') }}</h6>
                            <span class="badge-job">{{ $range === 'this_year' ? __('Monthly') : __('Weekly') }}</span>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartJobsTrend" height="180"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-signpost-split me-2" style="color:#5b57ae;"></i>{{ __('Job Source') }}</h6>
                            <span class="badge-job">{{ __('Sales Pipeline') }}</span>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartJobSource" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-graph-up-arrow me-2" style="color:#16a34a;"></i>{{ __('Completion Rate') }}</h6>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartCompletionRate" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-truck me-2" style="color:#16a34a;"></i>{{ __('Top Carriers') }}</h6>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartTopCarriers" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-receipt-cutoff me-2" style="color:#0b6aa0;"></i>{{ __('Invoicing Coverage') }}</h6>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartInvoicingCoverage" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="job-card h-100">
                        <div class="job-card-header">
                            <h6><i class="bi bi-people me-2" style="color:#f59e0b;"></i>{{ __('Handled By') }}</h6>
                        </div>
                        <div class="job-card-body">
                            <canvas id="chartHandledBy" height="170"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tables Row --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="job-card">
                        <div class="job-card-header">
                            <h6><i class="bi bi-trophy me-2" style="color:#f59e0b;"></i>{{ __('Top 10 Customers by Job Count') }}</h6>
                            <span class="badge-job">{{ __('Jobs / Containers / Packages') }}</span>
                        </div>
                        <div class="job-card-body p-0">
                            <div style="max-height:380px;overflow:auto;">
                                <table class="table job-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Customer') }}</th>
                                            <th class="text-end">{{ __('Jobs') }}</th>
                                            <th class="text-end">{{ __('Containers') }}</th>
                                            <th class="text-end">{{ __('Packages') }}</th>
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
                            <h6><i class="bi bi-signpost-split me-2" style="color:#0b6aa0;"></i>{{ __('Top Routes') }}</h6>
                            <span class="badge-job">{{ __('POL') }} &rarr; {{ __('POD') }}</span>
                        </div>
                        <div class="job-card-body p-0">
                            <div style="max-height:380px;overflow:auto;">
                                <table class="table job-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Route') }}</th>
                                            <th class="text-end">{{ __('Jobs') }}</th>
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
                            <h6><i class="bi bi-list-check me-2" style="color:#0b6aa0;"></i>{{ __('Job Status Breakdown') }}</h6>
                        </div>
                        <div class="job-card-body p-0">
                            <table class="table job-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Count') }}</th>
                                        <th class="text-end">{{ __('% of Total') }}</th>
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
    function pct(n) { return (n * 100).toFixed(1) + '%'; }

    function render() {
        const d = DATA;

        // Primary KPIs
        document.getElementById('kpiTotalJobs').innerText = fmt(d.totalJobs);
        document.getElementById('kpiCompletedJobs').innerText = fmt(d.completedJobs);
        document.getElementById('kpiPendingJobs').innerText = fmt(d.pendingJobs);
        document.getElementById('kpiInvoicedJobs').innerText = fmt(d.invoicedJobs);

        // Secondary KPIs
        document.getElementById('kpiCancelledJobs').innerText = fmt(d.cancelledJobs);
        document.getElementById('kpiCustomerCount').innerText = fmt(d.customersCount);
        document.getElementById('kpiFromQuotations').innerText = fmt(d.fromQuotations);
        document.getElementById('kpiRepeat').innerText = pct(d.repeatRatio);
        document.getElementById('kpiAvgContainers').innerText = (d.avgContainersPerJob || 0).toFixed(1);

        // Monthly comparison
        const mc = d.monthlyComparison || {};
        const prevJobs = mc.previous?.jobs || 0;
        const jobsChange = prevJobs > 0 ? ((d.totalJobs - prevJobs) / prevJobs) * 100 : 0;
        const chgEl = document.getElementById('kpiJobsChange');
        chgEl.innerText = (jobsChange >= 0 ? '+' : '') + jobsChange.toFixed(1) + '%';
        chgEl.className = 'kpi-value' + (jobsChange >= 0 ? ' trend-up' : ' trend-down');

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
        document.getElementById('tableCustomers').innerHTML = custHtml || '<tr><td colspan="5" class="text-center text-muted py-3">{{ __('No data') }}</td></tr>';

        // Top routes table
        const routesHtml = (d.routes || []).map((r, idx) => {
            return `<tr>
                <td>${idx + 1}</td>
                <td>${r.route}</td>
                <td class="text-end">${fmt(r.jobs)}</td>
            </tr>`;
        }).join('');
        document.getElementById('tableRoutes').innerHTML = routesHtml || '<tr><td colspan="3" class="text-center text-muted py-3">{{ __('No data') }}</td></tr>';

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
        document.getElementById('tableJobStatuses').innerHTML = statusHtml || '<tr><td colspan="3" class="text-center text-muted py-3">{{ __('No data') }}</td></tr>';

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

    document.getElementById('btn-apply').addEventListener('click', () => {
        const range = document.getElementById('dateRange').value;
        window.location.href = '/operation/job-overview?range=' + range;
    });
    </script>
</x-app-layout>
