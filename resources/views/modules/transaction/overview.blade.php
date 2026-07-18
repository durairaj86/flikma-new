@section('page-title','Transactions Overview')
@section('page-sub-title','Payments & collections cash flow dashboard')
@section('print-footer')
<script>
    window.printFooter = {
        show: true,
        custom: 'Transactions Overview - Generated on {{ date('d-m-Y H:i') }}'
    };
</script>
@endsection
<x-app-layout>
    <div class="bg-light py-4">
        <style>
            :root{
                --txn_primary: #0b6aa0;
                --txn_secondary: #5b57ae;
                --txn_accent: #16a34a;
                --txn_bg: #f8fafc;
                --txn_card_bg: #ffffff;
                --txn_radius: 12px;
                --txn_shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            }
            body { background: var(--txn_bg); }
            .txn-kpi-card {
                background: var(--txn_card_bg);
                border-radius: var(--txn_radius);
                box-shadow: var(--txn_shadow);
                padding: 1.25rem;
                transition: box-shadow .2s;
                border: 1px solid rgba(0,0,0,0.04);
            }
            .txn-kpi-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
            .txn-kpi-card .kpi-label { font-size: .8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
            .txn-kpi-card .kpi-value { font-size: 1.65rem; font-weight: 700; color: #0f172a; line-height: 1.2; margin-top: .25rem; }
            .txn-kpi-card .kpi-sub { font-size: .78rem; color: #94a3b8; margin-top: .2rem; }
            .txn-icon-circle { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
            .txn-card {
                background: var(--txn_card_bg);
                border-radius: var(--txn_radius);
                box-shadow: var(--txn_shadow);
                border: 1px solid rgba(0,0,0,0.04);
            }
            .txn-card-header {
                display: flex; align-items: center; justify-content: space-between;
                padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
            }
            .txn-card-header h6 { margin: 0; font-weight: 700; font-size: .9rem; color: #0f172a; }
            .txn-card-body { padding: 1.25rem; }
            .txn-table th { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; border-bottom-width: 1px; }
            .txn-table td { font-size: .82rem; vertical-align: middle; color: #1e293b; }
            .badge-txn { background: rgba(11,106,160,0.1); color: #0b6aa0; font-weight: 600; font-size: .7rem; padding: .25em .7em; border-radius: 20px; }
            .trend-up { color: #16a34a; }
            .trend-down { color: #dc2626; }
        </style>

        <div class="container-fluid px-lg-5">

            {{-- Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0" style="color:#0f172a;">Transactions Overview</h4>
                    <p class="text-muted mb-0 small">Real-time payments & collections dashboard</p>
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
                    <div class="txn-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Total Collected</div>
                            <div class="kpi-value" id="kpiCollected">SAR 0</div>
                            <div class="kpi-sub">From customers this period</div>
                        </div>
                        <div class="txn-icon-circle" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="txn-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Total Paid</div>
                            <div class="kpi-value" id="kpiPaid">SAR 0</div>
                            <div class="kpi-sub">To suppliers this period</div>
                        </div>
                        <div class="txn-icon-circle" style="background:rgba(220,38,38,0.1);color:#dc2626;">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="txn-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Net Cash Flow</div>
                            <div class="kpi-value" id="kpiNetCashFlow">SAR 0</div>
                            <div class="kpi-sub">Collected minus paid</div>
                        </div>
                        <div class="txn-icon-circle" style="background:rgba(11,106,160,0.1);color:#0b6aa0;">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="txn-kpi-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Avg Transaction</div>
                            <div class="kpi-value" id="kpiAvgTransaction">SAR 0</div>
                            <div class="kpi-sub">Avg value per transaction</div>
                        </div>
                        <div class="txn-icon-circle" style="background:rgba(91,87,174,0.1);color:#5b57ae;">
                            <i class="bi bi-bar-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Secondary KPIs --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="txn-kpi-card text-center py-2">
                        <div class="kpi-label">Payments</div>
                        <div class="kpi-value" id="kpiPaymentsCount" style="font-size:1.3rem;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="txn-kpi-card text-center py-2">
                        <div class="kpi-label">Collections</div>
                        <div class="kpi-value" id="kpiCollectionsCount" style="font-size:1.3rem;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="txn-kpi-card text-center py-2">
                        <div class="kpi-label">Pending Payments</div>
                        <div class="kpi-value" id="kpiPendingPayments" style="font-size:1.3rem;color:#dc2626;">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <div class="txn-kpi-card text-center py-2">
                        <div class="kpi-label">Pending Collections</div>
                        <div class="kpi-value" id="kpiPendingCollections" style="font-size:1.3rem;color:#dc2626;">0</div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-6">
                    <div class="txn-kpi-card text-center py-2">
                        <div class="kpi-label">vs Last Month (Net Cash Flow)</div>
                        <div class="kpi-value" id="kpiNetChange" style="font-size:1.1rem;">0%</div>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-7">
                    <div class="txn-card h-100">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-graph-up me-2" style="color:#0b6aa0;"></i>Cash Flow Trend</h6>
                            <span class="badge-txn">{{ $range === 'this_year' ? 'Monthly' : 'Weekly' }}</span>
                        </div>
                        <div class="txn-card-body">
                            <canvas id="chartCashFlow" height="180"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="txn-card h-100">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-pie-chart me-2" style="color:#5b57ae;"></i>Collections by Account</h6>
                            <span class="badge-txn">Top 6</span>
                        </div>
                        <div class="txn-card-body">
                            <canvas id="chartCollectionsByAccount" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="txn-card h-100">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-bank me-2" style="color:#16a34a;"></i>Payments by Account</h6>
                        </div>
                        <div class="txn-card-body">
                            <canvas id="chartPaymentsByAccount" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="txn-card h-100">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-cash-coin me-2" style="color:#dc2626;"></i>Payment Status</h6>
                        </div>
                        <div class="txn-card-body">
                            <canvas id="chartPaymentStatus" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="txn-card h-100">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-wallet2 me-2" style="color:#5b57ae;"></i>Collection Status</h6>
                        </div>
                        <div class="txn-card-body">
                            <canvas id="chartCollectionStatus" height="170"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tables Row --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="txn-card">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-truck me-2" style="color:#f59e0b;"></i>Top Suppliers by Payment</h6>
                            <span class="badge-txn">Payments / Total</span>
                        </div>
                        <div class="txn-card-body p-0">
                            <div style="max-height:380px;overflow:auto;">
                                <table class="table txn-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Supplier</th>
                                            <th class="text-end">Payments</th>
                                            <th class="text-end">Total (SAR)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableSuppliers"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="txn-card">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-people me-2" style="color:#0b6aa0;"></i>Top Customers by Collection</h6>
                            <span class="badge-txn">Collections / Total</span>
                        </div>
                        <div class="txn-card-body p-0">
                            <div style="max-height:380px;overflow:auto;">
                                <table class="table txn-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Customer</th>
                                            <th class="text-end">Collections</th>
                                            <th class="text-end">Total (SAR)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableCustomers"></tbody>
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
    function fmtCurrency(n) { return 'SAR ' + fmt(n); }

    function render() {
        const d = DATA;

        // Primary KPIs
        document.getElementById('kpiCollected').innerText = fmtCurrency(d.totalCollected);
        document.getElementById('kpiPaid').innerText = fmtCurrency(d.totalPaid);
        const netEl = document.getElementById('kpiNetCashFlow');
        netEl.innerText = fmtCurrency(d.netCashFlow);
        netEl.style.color = d.netCashFlow >= 0 ? '#16a34a' : '#dc2626';
        document.getElementById('kpiAvgTransaction').innerText = fmtCurrency(d.avgTransactionValue);

        // Secondary KPIs
        document.getElementById('kpiPaymentsCount').innerText = fmt(d.paymentsCount);
        document.getElementById('kpiCollectionsCount').innerText = fmt(d.collectionsCount);
        document.getElementById('kpiPendingPayments').innerText = fmt(d.pendingPayments);
        document.getElementById('kpiPendingCollections').innerText = fmt(d.pendingCollections);

        // Monthly comparison
        const mc = d.monthlyComparison || {};
        const prevNet = mc.previous?.net || 0;
        const netChange = prevNet !== 0 ? ((d.netCashFlow - prevNet) / Math.abs(prevNet)) * 100 : 0;
        const chgEl = document.getElementById('kpiNetChange');
        chgEl.innerText = (netChange >= 0 ? '+' : '') + netChange.toFixed(1) + '%';
        chgEl.className = 'kpi-value' + (netChange >= 0 ? ' trend-up' : ' trend-down');

        // Top suppliers table
        const supHtml = (d.topSuppliers || []).map((s, i) => {
            return `<tr>
                <td>${i + 1}</td>
                <td class="fw-medium">${s.name}</td>
                <td class="text-end">${s.payments}</td>
                <td class="text-end fw-semibold">${fmtCurrency(s.total)}</td>
            </tr>`;
        }).join('');
        document.getElementById('tableSuppliers').innerHTML = supHtml || '<tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>';

        // Top customers table
        const custHtml = (d.topCustomers || []).map((c, i) => {
            return `<tr>
                <td>${i + 1}</td>
                <td class="fw-medium">${c.name}</td>
                <td class="text-end">${c.collections}</td>
                <td class="text-end fw-semibold">${fmtCurrency(c.total)}</td>
            </tr>`;
        }).join('');
        document.getElementById('tableCustomers').innerHTML = custHtml || '<tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>';

        // --- Charts ---
        const colorPalette = ['#0b6aa0','#5b57ae','#16a34a','#f59e0b','#dc2626','#8b5cf6','#06b6d4','#f97316'];

        // Cash Flow Trend (dual line)
        const ctxTrend = document.getElementById('chartCashFlow').getContext('2d');
        const trend = d.cashFlowTrend || { collected: [], paid: [] };
        const trendLabels = '{{ $range }}' === 'this_year'
            ? ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'].slice(0, trend.collected.length)
            : Array.from({length: trend.collected.length}, (_, i) => 'W' + (i + 1));
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'Collected',
                        data: trend.collected,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        borderWidth: 2.5
                    },
                    {
                        label: 'Paid',
                        data: trend.paid,
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220,38,38,0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        borderWidth: 2.5
                    }
                ]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { callback: v => 'SAR ' + fmt(v) } }
                },
                maintainAspectRatio: false
            }
        });

        // Collections by account doughnut
        new Chart(document.getElementById('chartCollectionsByAccount').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: (d.collectionsByAccount || []).map(c => c.label),
                datasets: [{
                    data: (d.collectionsByAccount || []).map(c => c.value),
                    backgroundColor: colorPalette.slice(0, Math.max((d.collectionsByAccount || []).length, 1)),
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } },
                maintainAspectRatio: false
            }
        });

        // Payments by account doughnut
        new Chart(document.getElementById('chartPaymentsByAccount').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: (d.paymentsByAccount || []).map(c => c.label),
                datasets: [{
                    data: (d.paymentsByAccount || []).map(c => c.value),
                    backgroundColor: colorPalette.slice(0, Math.max((d.paymentsByAccount || []).length, 1)),
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } },
                maintainAspectRatio: false
            }
        });

        // Payment status bar
        const paymentStatuses = d.paymentStatuses || [];
        new Chart(document.getElementById('chartPaymentStatus').getContext('2d'), {
            type: 'bar',
            data: {
                labels: paymentStatuses.map(s => s.label),
                datasets: [{
                    label: 'Count',
                    data: paymentStatuses.map(s => s.count),
                    backgroundColor: paymentStatuses.map(s =>
                        s.status === 'approved' ? '#16a34a' : s.status === 'draft' ? '#94a3b8' : s.status === 'cancelled' ? '#dc2626' : '#f59e0b'
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

        // Collection status bar
        const collectionStatuses = d.collectionStatuses || [];
        new Chart(document.getElementById('chartCollectionStatus').getContext('2d'), {
            type: 'bar',
            data: {
                labels: collectionStatuses.map(s => s.label),
                datasets: [{
                    label: 'Count',
                    data: collectionStatuses.map(s => s.count),
                    backgroundColor: collectionStatuses.map(s =>
                        s.status === 'approved' ? '#16a34a' : s.status === 'draft' ? '#94a3b8' : s.status === 'cancelled' ? '#dc2626' : '#f59e0b'
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
        window.location.href = '/transaction/overview?range=' + range;
    });
    </script>
</x-app-layout>
