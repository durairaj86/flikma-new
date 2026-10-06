@section('page-title', __('Sales Overview'))
@section('hide-topbar', true)
@section('page-subtitle', __('Real-time sales performance dashboard'))
@section('print-footer')
<script>
    window.printFooter = {
        show: true,
        custom: '{{ __('Sales Overview - Generated on :date', ['date' => date('d-m-Y H:i')]) }}'
    };
</script>
@endsection
<x-app-layout>
    <div class="bg-light py-4">
        <style>
            :root{
                --sales_primary: #0b6aa0;
                --sales_secondary: #5b57ae;
                --sales_accent: #16a34a;
                --sales_bg: #f8fafc;
                --sales_card_bg: #ffffff;
                --sales_radius: 12px;
                --sales_shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            }
            body { background: var(--sales_bg); }
            .sales-kpi-card {
                background: var(--sales_card_bg);
                border-radius: var(--sales_radius);
                box-shadow: var(--sales_shadow);
                padding: 1.25rem;
                transition: box-shadow .2s;
                border: 1px solid rgba(0,0,0,0.04);
            }
            .sales-kpi-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
            .sales-kpi-card .kpi-label { font-size: .8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
            .sales-kpi-card .kpi-value { font-size: 1.65rem; font-weight: 700; color: #0f172a; line-height: 1.2; margin-top: .25rem; }
            .sales-kpi-card .kpi-sub { font-size: .78rem; color: #94a3b8; margin-top: .2rem; }
            .sales-icon-circle { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
            .sales-card {
                background: var(--sales_card_bg);
                border-radius: var(--sales_radius);
                box-shadow: var(--sales_shadow);
                border: 1px solid rgba(0,0,0,0.04);
            }
            .sales-card-header {
                display: flex; align-items: center; justify-content: space-between;
                padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
            }
            .sales-card-header h6 { margin: 0; font-weight: 700; font-size: .9rem; color: #0f172a; }
            .sales-card-body { padding: 1.25rem; }
            .sales-table th { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; border-bottom-width: 1px; }
            .sales-table td { font-size: .82rem; vertical-align: middle; color: #1e293b; }
            .badge-sales { background: rgba(11,106,160,0.1); color: #0b6aa0; font-weight: 600; font-size: .7rem; padding: .25em .7em; border-radius: 20px; }
            /* Dashboard-widget look for the KPI and chart cards. */
            .sales-kpi-card.wd {
                --kbg: color-mix(in srgb, var(--kc) 9%, #fff);
                background: var(--kbg); border: 0; border-radius: 18px; padding: 16px 18px 14px;
                box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(15,23,42,.07);
                display: block; height: 100%;
            }
            .sales-kpi-card.wd:hover { box-shadow: 0 1px 2px rgba(0,0,0,.05), 0 12px 30px rgba(15,23,42,.11); }
            .wd-head { display: flex; align-items: center; gap: 10px; }
            .wd-icon { width: 32px; height: 32px; border-radius: 50%; background: var(--kc); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0;
                       box-shadow: 0 3px 8px color-mix(in srgb, var(--kc) 40%, transparent); }
            .wd-title { font-weight: 600; font-size: .9rem; color: #475467; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .wd-value { font-size: 1.9rem; font-weight: 700; letter-spacing: -.02em; color: #101828; line-height: 1.1; margin-top: 14px; white-space: nowrap; }
            .wd-note { font-size: .8rem; color: #667085; margin-top: 4px; }
            .sales-kpi-card.wd-sm { padding: 12px 14px 12px; }
            .sales-kpi-card.wd-sm .wd-value { font-size: 1.35rem; margin-top: 8px; }
            .sales-kpi-card.wd-sm .wd-title { font-size: .78rem; }
            .sales-kpi-card.wd-sm .wd-icon { width: 26px; height: 26px; font-size: .72rem; }
            .sales-card { border: 0; border-radius: 18px; box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(15,23,42,.07); overflow: hidden; }
            .sales-card-header { border-bottom: 0; padding: 16px 18px 6px; }
            .sales-card-header h6 { font-size: .9rem; font-weight: 600; color: #475467; }
            .sales-card-body { padding: 8px 18px 18px; }
            .trend-up { color: #16a34a; }
            .trend-down { color: #dc2626; }
            .sales-filter-card {
                background: var(--sales_card_bg);
                border-radius: var(--sales_radius);
                box-shadow: var(--sales_shadow);
                border: 1px solid rgba(0,0,0,0.04);
                padding: .75rem 1.25rem;
            }
        </style>

        <div class="container-fluid px-lg-5">

            {{-- Title row: title on the left (when the header is off), period filter at the right end --}}
            <style>
                .so-title { display: none; }
                body:not(.has-top-header) .so-title { display: block; }
            </style>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="so-title">
                    <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                    <div class="text-muted small mt-1">@yield('page-subtitle')</div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div id="dateRangeWrap" style="min-width:170px;"><select id="dateRange" class="tom-select" data-placeholder="{{ __('This Month') }}">
                        <option value="this_month" {{ $range==='this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                        <option value="last_month" {{ $range==='last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                        <option value="this_year" {{ $range==='this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                    </select></div>
                    <button class="btn btn-primary btn-sm px-3" id="btn-apply">
                        <i class="bi bi-arrow-repeat me-1"></i> {{ __('Apply') }}
                    </button>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="sales-kpi-card wd" style="--kc: #0b6aa0;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-cart-check"></i></span><span class="wd-title">{{ __('Total Sales') }}</span></div>
                        <div class="wd-value" id="kpiSales">SAR 0</div>
                        <div class="wd-note">{{ __('Invoiced this period') }}</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="sales-kpi-card wd" style="--kc: #16a34a;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-wallet2"></i></span><span class="wd-title">{{ __('Pending Approval') }}</span></div>
                        <div class="wd-value" id="kpiPendingApproval">SAR 0</div>
                        <div class="wd-note">{{ __('Draft & sent invoices') }}</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="sales-kpi-card wd" style="--kc: #dc2626;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-exclamation-triangle"></i></span><span class="wd-title">{{ __('Outstanding') }}</span></div>
                        <div class="wd-value" id="kpiOutstanding" style="color:#dc2626;">SAR 0</div>
                        <div class="wd-note">{{ __('Balance due') }}</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="sales-kpi-card wd" style="--kc: #5b57ae;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-bar-chart-line"></i></span><span class="wd-title">{{ __('Avg Invoice') }}</span></div>
                        <div class="wd-value" id="kpiAvgInvoice">SAR 0</div>
                        <div class="wd-note">{{ __('Avg value per invoice') }}</div>
                    </div>
                </div>
            </div>

            {{-- Secondary KPIs --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="sales-kpi-card wd wd-sm" style="--kc: #0b6aa0;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-receipt"></i></span><span class="wd-title">{{ __('Invoices') }}</span></div>
                        <div class="wd-value" id="kpiInvoiceCount">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="sales-kpi-card wd wd-sm" style="--kc: #5b57ae;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-people"></i></span><span class="wd-title">{{ __('Customers') }}</span></div>
                        <div class="wd-value" id="kpiCustomerCount">0</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="sales-kpi-card wd wd-sm" style="--kc: #16a34a;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-arrow-repeat"></i></span><span class="wd-title">{{ __('Recurring') }}</span></div>
                        <div class="wd-value" id="kpiRecurring">0%</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="sales-kpi-card wd wd-sm" style="--kc: #f59e0b;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-graph-up"></i></span><span class="wd-title">{{ __('vs Last Month (Sales)') }}</span></div>
                        <div class="wd-value" id="kpiSalesChange">0%</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="sales-kpi-card wd wd-sm" style="--kc: #0891b2;">
                        <div class="wd-head"><span class="wd-icon"><i class="bi bi-patch-check"></i></span><span class="wd-title">{{ __('Approval Rate') }}</span></div>
                        <div class="wd-value" id="kpiApprovalRate">0%</div>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-7">
                    <div class="sales-card h-100">
                        <div class="sales-card-header">
                            <h6><i class="bi bi-graph-up me-2" style="color:#0b6aa0;"></i>{{ __('Sales Trend') }}</h6>
                            <span class="badge-sales">{{ $range === 'this_year' ? __('Monthly') : __('Weekly') }}</span>
                        </div>
                        <div class="sales-card-body">
                            <canvas id="chartSalesTrend" height="180"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="sales-card h-100">
                        <div class="sales-card-header">
                            <h6><i class="bi bi-pie-chart me-2" style="color:#5b57ae;"></i>{{ __('Sales by Service Type') }}</h6>
                            <span class="badge-sales">{{ __('Top 6') }}</span>
                        </div>
                        <div class="sales-card-body">
                            <canvas id="chartCategory" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="sales-card h-100">
                        <div class="sales-card-header">
                            <h6><i class="bi bi-geo-alt me-2" style="color:#16a34a;"></i>{{ __('Sales by Region') }}</h6>
                        </div>
                        <div class="sales-card-body">
                            <canvas id="chartRegion" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sales-card h-100">
                        <div class="sales-card-header">
                            <h6><i class="bi bi-people me-2" style="color:#0b6aa0;"></i>{{ __('Salesperson Performance') }}</h6>
                        </div>
                        <div class="sales-card-body">
                            <canvas id="chartSalesperson" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sales-card h-100">
                        <div class="sales-card-header">
                            <h6><i class="bi bi-check-circle me-2" style="color:#5b57ae;"></i>{{ __('Invoice Status') }}</h6>
                        </div>
                        <div class="sales-card-body">
                            <canvas id="chartStatus" height="170"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tables Row --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="sales-card">
                        <div class="sales-card-header">
                            <h6><i class="bi bi-trophy me-2" style="color:#f59e0b;"></i>{{ __('Top 10 Customers by Revenue') }}</h6>
                            <span class="badge-sales">{{ __('Revenue / Invoices / Outstanding') }}</span>
                        </div>
                        <div class="sales-card-body p-0">
                            <div style="max-height:380px;overflow:auto;">
                                <table class="table sales-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Customer') }}</th>
                                            <th class="text-end">{{ __('Invoices') }}</th>
                                            <th class="text-end">{{ __('Revenue (SAR)') }}</th>
                                            <th class="text-end">{{ __('Outstanding') }}</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableCustomers"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="sales-card">
                        <div class="sales-card-header">
                            <h6><i class="bi bi-box me-2" style="color:#0b6aa0;"></i>{{ __('Top 10 Items by Revenue') }}</h6>
                            <span class="badge-sales">{{ __('Qty & Revenue') }}</span>
                        </div>
                        <div class="sales-card-body p-0">
                            <div style="max-height:380px;overflow:auto;">
                                <table class="table sales-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Item / Service') }}</th>
                                            <th class="text-end">{{ __('Qty') }}</th>
                                            <th class="text-end">{{ __('Revenue (SAR)') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableItems"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invoice Status Summary --}}
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="sales-card">
                        <div class="sales-card-header">
                            <h6><i class="bi bi-list-check me-2" style="color:#0b6aa0;"></i>{{ __('Invoice Status Breakdown') }}</h6>
                        </div>
                        <div class="sales-card-body p-0">
                            <table class="table sales-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Count') }}</th>
                                        <th class="text-end">{{ __('Total (SAR)') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="tableInvoiceStatuses"></tbody>
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
        document.getElementById('kpiSales').innerText = fmtCurrency(d.sales);
        document.getElementById('kpiPendingApproval').innerText = fmtCurrency(d.pendingApproval);
        document.getElementById('kpiOutstanding').innerText = fmtCurrency(d.outstanding);
        document.getElementById('kpiAvgInvoice').innerText = fmtCurrency(d.invoices_avg);

        // Secondary KPIs
        document.getElementById('kpiInvoiceCount').innerText = fmt(d.invoices_count);
        document.getElementById('kpiCustomerCount').innerText = fmt(d.customers_count);
        document.getElementById('kpiRecurring').innerText = pct(d.recurring_ratio);

        // Monthly comparison
        const mc = d.monthlyComparison || {};
        const prevSales = mc.previous?.sales || 0;
        const salesChange = prevSales > 0 ? ((d.sales - prevSales) / prevSales) * 100 : 0;
        const chgEl = document.getElementById('kpiSalesChange');
        chgEl.innerText = (salesChange >= 0 ? '+' : '') + salesChange.toFixed(1) + '%';
        chgEl.className = 'wd-value' + (salesChange >= 0 ? ' trend-up' : ' trend-down');

        // Approval rate
        document.getElementById('kpiApprovalRate').innerText = pct(d.approvalRate);

        // Top customers table
        const totalRev = d.sales;
        const custHtml = (d.customers || []).map((c, i) => {
            const pc = totalRev ? ((c.revenue / totalRev) * 100).toFixed(1) : 0;
            return `<tr>
                <td>${i + 1}</td>
                <td class="fw-medium">${c.name}</td>
                <td class="text-end">${c.invoices}</td>
                <td class="text-end fw-semibold">${fmtCurrency(c.revenue)}</td>
                <td class="text-end ${c.outstanding > 0 ? 'text-danger' : ''}">${fmtCurrency(c.outstanding)}</td>
                <td class="text-end">${pc}%</td>
            </tr>`;
        }).join('');
        document.getElementById('tableCustomers').innerHTML = custHtml || '<tr><td colspan="6" class="text-center text-muted py-3">{{ __('No data') }}</td></tr>';

        // Top items table
        const itemsHtml = (d.items || []).map((i, idx) => {
            return `<tr>
                <td>${idx + 1}</td>
                <td>${i.name}</td>
                <td class="text-end">${fmt(i.qty)}</td>
                <td class="text-end fw-semibold">${fmtCurrency(i.revenue)}</td>
            </tr>`;
        }).join('');
        document.getElementById('tableItems').innerHTML = itemsHtml || '<tr><td colspan="4" class="text-center text-muted py-3">{{ __('No data') }}</td></tr>';

        // Invoice status table
        const statuses = d.invoiceStatuses || [];
        const statusHtml = statuses.map(s => {
            const badgeClass = s.status === 'approved' ? 'bg-success' : s.status === 'draft' ? 'bg-secondary' : s.status === 'cancelled' ? 'bg-danger' : s.status === 'rejected' ? 'bg-danger' : s.status === 'converted' ? 'bg-info' : 'bg-warning';
            return `<tr>
                <td><span class="badge ${badgeClass}">${s.label}</span></td>
                <td class="text-end">${s.count}</td>
                <td class="text-end fw-semibold">${fmtCurrency(s.total)}</td>
            </tr>`;
        }).join('');
        document.getElementById('tableInvoiceStatuses').innerHTML = statusHtml || '<tr><td colspan="3" class="text-center text-muted py-3">{{ __('No data') }}</td></tr>';

        // --- Charts ---
        const colorPalette = ['#0b6aa0','#5b57ae','#16a34a','#f59e0b','#dc2626','#8b5cf6','#06b6d4','#f97316'];
        const alpha = (c, a) => c + Math.round(a * 255).toString(16).padStart(2, '0');

        // Sales Trend
        const ctxTrend = document.getElementById('chartSalesTrend').getContext('2d');
        const trendLabels = '{{ $range }}' === 'this_year'
            ? ['{{ __('Jan') }}','{{ __('Feb') }}','{{ __('Mar') }}','{{ __('Apr') }}','{{ __('May') }}','{{ __('Jun') }}','{{ __('Jul') }}','{{ __('Aug') }}','{{ __('Sep') }}','{{ __('Oct') }}','{{ __('Nov') }}','{{ __('Dec') }}'].slice(0, d.salesTrend.length)
            : Array.from({length: d.salesTrend.length}, (_, i) => 'W' + (i + 1));
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: '{{ __('Sales') }}',
                    data: d.salesTrend,
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
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { callback: v => 'SAR ' + fmt(v) } }
                },
                maintainAspectRatio: false
            }
        });

        // Sales by Service Type — vertical bar
        new Chart(document.getElementById('chartCategory').getContext('2d'), {
            type: 'bar',
            data: {
                labels: (d.categories || []).map(c => c.label),
                datasets: [{
                    data: (d.categories || []).map(c => c.value),
                    backgroundColor: colorPalette.slice(0, Math.max((d.categories || []).length, 1)),
                    borderRadius: 4
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { callback: v => 'SAR ' + fmt(v) } }
                },
                maintainAspectRatio: false
            }
        });

        // Sales by Region — horizontal bar
        new Chart(document.getElementById('chartRegion').getContext('2d'), {
            type: 'bar',
            data: {
                labels: (d.regions || []).map(r => r.label),
                datasets: [{
                    data: (d.regions || []).map(r => r.value),
                    backgroundColor: colorPalette.slice(0, Math.max((d.regions || []).length, 1)),
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { callback: v => 'SAR ' + fmt(v) } },
                    y: { grid: { display: false } }
                },
                maintainAspectRatio: false
            }
        });

        // Salesperson horizontal bar
        new Chart(document.getElementById('chartSalesperson').getContext('2d'), {
            type: 'bar',
            data: {
                labels: (d.salespeople || []).map(s => s.name),
                datasets: [{
                    data: (d.salespeople || []).map(s => s.value),
                    backgroundColor: '#0b6aa0',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { callback: v => 'SAR ' + fmt(v) } },
                    y: { grid: { display: false } }
                },
                maintainAspectRatio: false
            }
        });

        // Invoice Status bar
        new Chart(document.getElementById('chartStatus').getContext('2d'), {
            type: 'bar',
            data: {
                labels: statuses.map(s => s.label),
                datasets: [{
                    label: '{{ __('Count') }}',
                    data: statuses.map(s => s.count),
                    backgroundColor: statuses.map(s =>
                        s.status === 'approved' ? '#16a34a' : s.status === 'draft' ? '#94a3b8' : s.status === 'cancelled' ? '#dc2626' : s.status === 'rejected' ? '#dc2626' : s.status === 'converted' ? '#0dcaf0' : '#f59e0b'
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

        // Footer stats
        const topCust = d.customers?.[0];
        const topItem = d.items?.[0];
    }

    document.addEventListener('DOMContentLoaded', render);
    // Period dropdown: shared tom-select styling (the underlying <select> still holds the value).
    document.addEventListener('DOMContentLoaded', function () {
        if (window.initTomSelectForm && window.jQuery) { initTomSelectForm($('#dateRangeWrap')); }
    });

    document.getElementById('btn-apply').addEventListener('click', () => {
        const range = document.getElementById('dateRange').value;
        window.location.href = '/sales/overview?range=' + range;
    });
    </script>
</x-app-layout>
