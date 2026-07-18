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
                --txn_collect: #16a34a;
                --txn_pay: #dc2626;
                --txn_bg: #f8fafc;
                --txn_card_bg: #ffffff;
                --txn_radius: 12px;
                --txn_shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            }
            body { background: var(--txn_bg); }
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
            .txn-table th { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; border-bottom-width: 1px; }
            .txn-table td { font-size: .8rem; vertical-align: middle; color: #1e293b; }
            .badge-txn { background: rgba(11,106,160,0.1); color: #0b6aa0; font-weight: 600; font-size: .7rem; padding: .25em .7em; border-radius: 20px; }
            .trend-up { color: #16a34a; }
            .trend-down { color: #dc2626; }

            /* Overview strip: compact horizontal stat bar (distinct from KPI card grids) */
            .txn-strip {
                display: flex; flex-wrap: wrap;
            }
            .txn-strip .stat {
                flex: 1 1 0; min-width: 150px;
                padding: 1.1rem 1.25rem;
                border-right: 1px solid #f1f5f9;
            }
            .txn-strip .stat:last-child { border-right: 0; }
            .txn-strip .stat .label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
            .txn-strip .stat .value { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: .2rem; }

            /* Twin panel accents */
            .txn-panel-collect .txn-card-header { border-bottom-color: rgba(22,163,74,0.15); }
            .txn-panel-pay .txn-card-header { border-bottom-color: rgba(220,38,38,0.15); }
        </style>

        <div class="container-fluid px-lg-5">

            {{-- Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0" style="color:#0f172a;">Transactions Overview</h4>
                    <p class="text-muted mb-0 small">Real-time payments &amp; collections dashboard</p>
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

            {{-- Overview strip: single wide card with inline stats --}}
            <div class="txn-card mb-3">
                <div class="txn-strip">
                    <div class="stat">
                        <div class="label">Net Cash Flow</div>
                        <div class="value" id="kpiNetCashFlow">SAR 0</div>
                    </div>
                    <div class="stat">
                        <div class="label">Avg Transaction</div>
                        <div class="value" id="kpiAvgTransaction">SAR 0</div>
                    </div>
                    <div class="stat">
                        <div class="label">Payments / Collections</div>
                        <div class="value"><span id="kpiPaymentsCount">0</span> / <span id="kpiCollectionsCount">0</span></div>
                    </div>
                    <div class="stat">
                        <div class="label">vs Last Month</div>
                        <div class="value" id="kpiNetChange" style="font-size:1.2rem;">0%</div>
                    </div>
                </div>
            </div>

            {{-- Cash Flow Trend: prominent, full width --}}
            <div class="txn-card mb-3">
                <div class="txn-card-header">
                    <h6><i class="bi bi-graph-up me-2" style="color:#0b6aa0;"></i>Cash Flow Trend</h6>
                    <span class="badge-txn">{{ $range === 'this_year' ? 'Monthly' : 'Weekly' }}</span>
                </div>
                <div class="txn-card-body">
                    <canvas id="chartCashFlow" height="90"></canvas>
                </div>
            </div>

            {{-- Twin panels: Collections | Payments --}}
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="txn-card txn-panel-collect h-100">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-wallet2 me-2" style="color:#16a34a;"></i>Collections</h6>
                            <span class="badge-txn" style="background:rgba(22,163,74,0.12);color:#16a34a;">Money In</span>
                        </div>
                        <div class="txn-card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small">Total Collected</div>
                                    <div class="fw-bold fs-5" id="kpiCollected">SAR 0</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small">Pending</div>
                                    <div class="fw-bold fs-5 text-danger" id="kpiPendingCollections">0</div>
                                </div>
                            </div>
                            <canvas id="chartCollectionsByAccount" height="160"></canvas>
                            <hr>
                            <canvas id="chartCollectionStatus" height="120"></canvas>
                        </div>
                        <div class="table-responsive" style="max-height:260px;overflow:auto;">
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

                <div class="col-lg-6">
                    <div class="txn-card txn-panel-pay h-100">
                        <div class="txn-card-header">
                            <h6><i class="bi bi-cash-coin me-2" style="color:#dc2626;"></i>Payments</h6>
                            <span class="badge-txn" style="background:rgba(220,38,38,0.12);color:#dc2626;">Money Out</span>
                        </div>
                        <div class="txn-card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small">Total Paid</div>
                                    <div class="fw-bold fs-5" id="kpiPaid">SAR 0</div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small">Pending</div>
                                    <div class="fw-bold fs-5 text-danger" id="kpiPendingPayments">0</div>
                                </div>
                            </div>
                            <canvas id="chartPaymentsByAccount" height="160"></canvas>
                            <hr>
                            <canvas id="chartPaymentStatus" height="120"></canvas>
                        </div>
                        <div class="table-responsive" style="max-height:260px;overflow:auto;">
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

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const DATA = @json($data);

    function fmt(n) { return new Intl.NumberFormat('en-IN').format(Math.round(n)); }
    function fmtCurrency(n) { return 'SAR ' + fmt(n); }

    function render() {
        const d = DATA;

        // Overview strip
        const netEl = document.getElementById('kpiNetCashFlow');
        netEl.innerText = fmtCurrency(d.netCashFlow);
        netEl.style.color = d.netCashFlow >= 0 ? '#16a34a' : '#dc2626';
        document.getElementById('kpiAvgTransaction').innerText = fmtCurrency(d.avgTransactionValue);
        document.getElementById('kpiPaymentsCount').innerText = fmt(d.paymentsCount);
        document.getElementById('kpiCollectionsCount').innerText = fmt(d.collectionsCount);

        const mc = d.monthlyComparison || {};
        const prevNet = mc.previous?.net || 0;
        const netChange = prevNet !== 0 ? ((d.netCashFlow - prevNet) / Math.abs(prevNet)) * 100 : 0;
        const chgEl = document.getElementById('kpiNetChange');
        chgEl.innerText = (netChange >= 0 ? '+' : '') + netChange.toFixed(1) + '%';
        chgEl.className = 'value' + (netChange >= 0 ? ' trend-up' : ' trend-down');

        // Collections panel
        document.getElementById('kpiCollected').innerText = fmtCurrency(d.totalCollected);
        document.getElementById('kpiPendingCollections').innerText = fmt(d.pendingCollections);

        // Payments panel
        document.getElementById('kpiPaid').innerText = fmtCurrency(d.totalPaid);
        document.getElementById('kpiPendingPayments').innerText = fmt(d.pendingPayments);

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
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } } },
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
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } } },
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
    }

    document.addEventListener('DOMContentLoaded', render);

    document.getElementById('btn-apply').addEventListener('click', () => {
        const range = document.getElementById('dateRange').value;
        window.location.href = '/transaction/overview?range=' + range;
    });
    </script>
</x-app-layout>
