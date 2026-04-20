@section('page-title','Sales Overview')
@section('page-sub-title','Sales performance dashboard with real-time data')
@section('js','enquiry')
<x-app-layout>
    <main class="gmail-content bg-white px-3">

            <style>
                :root{
                    --primary:#0b6aa0; /* hybrid primary */
                    --accent:#5b57ae;
                    --card-bg:#fff;
                    --page-bg:#f5f7fb;
                }

                .card-soft{ background:var(--card-bg); border-radius:12px; box-shadow:0 10px 30px rgba(25,40,60,0.06); padding:18px; }
                .kpi { border-radius:10px; padding:14px; }
                .kpi .label{ color:#6b7280; font-size:13px; }
                .kpi .value{ font-size:22px; font-weight:700; margin-top:6px; }
                .icon-circle{ width:46px; height:46px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; }
                .muted { color:#6b7280; font-size:13px; }
                .table-fixed thead th { position: sticky; top:0; background:var(--card-bg); z-index:2; }
                .mini { font-size:12px; color:#475569; }
                .badge-metric { font-weight:700; padding:6px 10px; border-radius:999px; }
                .hybrid-plate { display:flex; gap:12px; align-items:center; }
                .small-muted { color:#94a3b8; font-size:13px; }

                /* responsive minor */
                @media (max-width:900px){
                    .kpi .value { font-size:18px; }
                    .icon-circle { width:40px; height:40px; font-size:18px; }
                }

                /* hybrid color accents */
                .bg-primary-soft { background: rgba(11,106,160,0.08); color:var(--primary); }
                .bg-accent-soft { background: rgba(91,87,174,0.08); color:var(--accent); }
                .text-card-title { font-size:15px; font-weight:700; color:#0f1724; }
            </style>
        <div>
        <div class="page">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    {{--<h3 class="mb-0">Sales Overview</h3>
                    <div class="small-muted">Hybrid dashboard — Zoho + MyBillBook styling (dummy data)</div>--}}
                </div>

                <div class="d-flex align-items-center gap-2">
                    <select id="dateRange" class="form-select form-select-sm">
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                    </select>

                    <div class="btn-group">
                        <button class="btn btn-outline-secondary btn-sm" id="btn-apply">Apply</button>
                    </div>
                </div>
            </div>

            <!-- Top KPIs -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card-soft kpi">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="label">Sales (Invoiced)</div>
                                <div class="value" id="kpiSales">SAR 0</div>
                                <div class="mini small-muted">Period total invoiced</div>
                            </div>
                            <div class="hybrid-plate">
                                <div class="icon-circle bg-primary-soft"><i class="bi bi-currency-dollar"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card-soft kpi">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="label">Payments Collected</div>
                                <div class="value" id="kpiCollected">SAR 0</div>
                                <div class="mini small-muted">Total receipts</div>
                            </div>
                            <div class="hybrid-plate">
                                <div class="icon-circle bg-accent-soft"><i class="bi bi-wallet2"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card-soft kpi">
                        <div>
                            <div class="label">Outstanding</div>
                            <div class="value text-danger" id="kpiOutstanding">SAR 0</div>
                            <div class="mini small-muted">Unpaid invoices</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card-soft kpi">
                        <div>
                            <div class="label">Avg Invoice Value</div>
                            <div class="value" id="kpiAvgInvoice">SAR 0</div>
                            <div class="mini small-muted">Average invoice</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card-soft kpi">
                        <div>
                            <div class="label">Recurring Ratio</div>
                            <div class="value" id="kpiRecurring">0%</div>
                            <div class="mini small-muted">Returning customers</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts area -->
            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card-soft mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-card-title">Sales Trend</div>
                            <div class="small-muted">Monthly / Weekly trend</div>
                        </div>
                        <canvas id="chartSalesTrend" height="140"></canvas>
                    </div>

                    <div class="card-soft">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-card-title mb-2">Sales vs Payments (Period)</div>
                                <canvas id="chartSalesVsPayments" height="140"></canvas>
                            </div>

                            <div class="col-md-6">
                                <div class="text-card-title mb-2">Profit Analysis</div>
                                <canvas id="chartProfit" height="140"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column: pie, region, salesperson, top lists -->
                <div class="col-xl-4">
                    <div class="card-soft mb-3">
                        <div class="text-card-title mb-2">Sales by Category</div>
                        <canvas id="chartCategory" height="160"></canvas>
                    </div>

                    <div class="card-soft mb-3">
                        <div class="text-card-title mb-2">Sales by Region</div>
                        <canvas id="chartRegion" height="140"></canvas>
                    </div>

                    <div class="card-soft">
                        <div class="text-card-title mb-2">Salesperson Performance</div>
                        <canvas id="chartSalesperson" height="120"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tables: Top customers/items -->
            <div class="row g-3 mt-3">
                <div class="col-lg-7">
                    <div class="card-soft">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-card-title">Top 10 Customers (by revenue)</div>
                            <div class="small-muted">With % contribution</div>
                        </div>
                        <div style="max-height:360px; overflow:auto;">
                            <table class="table table-sm table-hover mb-0 table-fixed">
                                <thead class="table-light">
                                <tr>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Invoices</th>
                                    <th class="text-end">Revenue (SAR)</th>
                                    <th class="text-end">%</th>
                                </tr>
                                </thead>
                                <tbody id="tableCustomers"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card-soft">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-card-title">Top 10 Items (by revenue)</div>
                            <div class="small-muted">Qty & Revenue</div>
                        </div>
                        <div style="max-height:360px; overflow:auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Revenue (SAR)</th>
                                </tr>
                                </thead>
                                <tbody id="tableItems"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer KPIs (Summary small) -->
            <div class="row g-3 mt-3">
                <div class="col-md-3">
                    <div class="card-soft small p-3 text-center">
                        <div class="label">Top Customer</div>
                        <div id="footerTopCustomer" class="value">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-soft small p-3 text-center">
                        <div class="label">Top Item (Revenue)</div>
                        <div id="footerTopItem" class="value">-</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-soft small p-3 text-center">
                        <div class="label">Total Customers</div>
                        <div id="footerTotalCustomers" class="value">0</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-soft small p-3 text-center">
                        <div class="label">Total Invoices</div>
                        <div id="footerTotalInvoices" class="value">0</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Scripts -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            /* ======= REAL DATA: from controller ======= */
            const REAL_DATA = @json($data ?? []);

            // Create a structure similar to the original DUMMY object for compatibility
            const DUMMY = {
                this_month: REAL_DATA,
                last_month: REAL_DATA,
                this_year: REAL_DATA
            };

            /* ======= helper: format ======= */
            function fmt(n){ return new Intl.NumberFormat('en-IN').format(Math.round(n)); }
            function fmtCurrency(n){ return 'SAR ' + fmt(n); }
            function percent(n){ return (n*100).toFixed(1) + '%'; }

            /* ======= Chart references ======= */
            let charts = {};

            /* ======= Main update function ======= */
            function renderRange(rangeKey){
                const d = DUMMY[rangeKey] || {};

                // Ensure all required properties exist with defaults
                d.sales = d.sales || 0;
                d.collected = d.collected || 0;
                d.outstanding = d.outstanding || 0;
                d.invoices_avg = d.invoices_avg || 0;
                d.recurring_ratio = d.recurring_ratio || 0;
                d.invoices_count = d.invoices_count || 0;
                d.customers_count = d.customers_count || 0;
                d.salesTrend = d.salesTrend || [];
                d.categories = d.categories || [];
                d.regions = d.regions || [];
                d.salespeople = d.salespeople || [];
                d.customers = d.customers || [];
                d.items = d.items || [];
                d.cogs = d.cogs || 0;

                // KPIs
                document.getElementById('kpiSales').innerText = fmtCurrency(d.sales);
                document.getElementById('kpiCollected').innerText = fmtCurrency(d.collected);
                document.getElementById('kpiOutstanding').innerText = fmtCurrency(d.outstanding);
                document.getElementById('kpiAvgInvoice').innerText = fmtCurrency(d.invoices_avg);
                document.getElementById('kpiRecurring').innerText = percent(d.recurring_ratio);

                // footer small KPIs
                document.getElementById('footerTotalCustomers').innerText = d.customers.length > 0 ? d.customers.length : d.customers_count || 0;
                document.getElementById('footerTotalInvoices').innerText = d.invoices_count;

                // Top customer / top item
                const topCust = d.customers[0] || {name:'-', revenue:0};
                const topItem = d.items[0] || {name:'-', revenue:0};
                document.getElementById('footerTopCustomer').innerText = topCust.name;
                document.getElementById('footerTopItem').innerText = topItem.name;

                // Top customers table with % contribution
                const totalRevenue = d.sales;
                const customersHtml = (d.customers || []).slice(0,10).map(c=>{
                    const pct = totalRevenue ? ((c.revenue/totalRevenue)*100).toFixed(1) : 0;
                    return `<tr>
          <td>${c.name}</td>
          <td>${c.invoices}</td>
          <td class="text-end">${fmtCurrency(c.revenue)}</td>
          <td class="text-end">${pct}%</td>
        </tr>`;
                }).join('');
                document.getElementById('tableCustomers').innerHTML = customersHtml;

                // items table
                const itemsHtml = (d.items || []).slice(0,10).map(i=>{
                    return `<tr>
          <td>${i.name}</td>
          <td class="text-end">${fmt(i.qty)}</td>
          <td class="text-end">${fmtCurrency(i.revenue)}</td>
        </tr>`;
                }).join('');
                document.getElementById('tableItems').innerHTML = itemsHtml;

                // Charts: destroy previous if exists
                Object.keys(charts).forEach(k => {
                    try{ charts[k].destroy(); } catch(e){}
                });
                charts = {};

                // Sales Trend (line)
                const ctxTrend = document.getElementById('chartSalesTrend').getContext('2d');
                charts.trend = new Chart(ctxTrend, {
                    type:'line',
                    data:{
                        labels: generateLabels(d.salesTrend.length, rangeKey),
                        datasets:[{
                            label: 'Sales',
                            data: d.salesTrend,
                            borderColor: 'rgba(11,106,160,0.95)',
                            backgroundColor: gradient(ctxTrend, 'rgba(11,106,160,0.12)'),
                            tension: 0.27,
                            pointRadius: 3,
                            pointBackgroundColor: '#fff'
                        }]
                    },
                    options: baseLineOptions()
                });

                // Sales vs Payments (bar)
                const ctxSVP = document.getElementById('chartSalesVsPayments').getContext('2d');
                charts.svp = new Chart(ctxSVP, {
                    type:'bar',
                    data:{
                        labels:['Invoiced','Collected','Outstanding'],
                        datasets:[{
                            label:'SAR',
                            data:[d.sales, d.collected, d.outstanding],
                            backgroundColor:['rgba(11,106,160,0.9)','rgba(91,87,174,0.85)','rgba(239,68,68,0.9)']
                        }]
                    },
                    /*options: {
                        plugins:{legend:{display:false}},
                        scales:{ y:{ beginAtZero:true } },
                        maintainAspectRatio:false
                    }*/
                });

                // Profit analysis (d.sales - d.cogs)
                const profit = d.sales - (d.cogs || 0);
                const profitPct = d.sales ? ((profit/d.sales)*100).toFixed(1) : 0;
                charts.profit = new Chart(document.getElementById('chartProfit').getContext('2d'), {
                    type:'doughnut',
                    data:{
                        labels:['Cost (COGS)','Profit'],
                        datasets:[{
                            data:[d.cogs || 0, Math.max(profit,0)],
                            backgroundColor:['rgba(107,114,128,0.12)','rgba(16,185,129,0.9)']
                        }]
                    },
                   /* options:{
                        plugins:{legend:{position:'bottom'}},
                        maintainAspectRatio:false
                    }*/
                });

                // Categories (pie)
                charts.cat = new Chart(document.getElementById('chartCategory').getContext('2d'), {
                    type:'pie',
                    data:{
                        labels: d.categories.map(c=>c.label),
                        datasets:[{ data: d.categories.map(c=>c.value), backgroundColor:generateColorPalette(d.categories.length) }]
                    },
                    //options:{ plugins:{legend:{position:'bottom'}}, maintainAspectRatio:false }
                });

                // Regions (doughnut)
                charts.reg = new Chart(document.getElementById('chartRegion').getContext('2d'), {
                    type:'doughnut',
                    data:{
                        labels: d.regions.map(r=>r.label),
                        datasets:[{ data: d.regions.map(r=>r.value), backgroundColor:generateColorPalette(d.regions.length) }]
                    },
                    //options:{ plugins:{legend:{position:'bottom'}}, maintainAspectRatio:false }
                });

                // Salesperson (horizontal bar)
                charts.sp = new Chart(document.getElementById('chartSalesperson').getContext('2d'), {
                    type:'bar',
                    data:{
                        labels: d.salespeople.map(s=>s.name),
                        datasets:[{ data: d.salespeople.map(s=>s.value), backgroundColor:'rgba(11,106,160,0.85)' }]
                    },
                    /*options:{
                        indexAxis:'y',
                        plugins:{legend:{display:false}},
                        scales:{ x:{ beginAtZero:true } },
                        maintainAspectRatio:false
                    }*/
                });

            } // renderRange

            /* ======= helpers and chart options ======= */
            function generateLabels(n, rangeKey){
                // if year -> months, else weekly labels
                if(rangeKey === 'this_year') return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'].slice(0,n);
                // if small counts: W1..Wn
                return Array.from({length:n},(v,i)=>'W'+(i+1));
            }

            function generateColorPalette(n){
                const base = [
                    'rgba(11,106,160,0.9)',
                    'rgba(91,87,174,0.9)',
                    'rgba(14,165,233,0.9)',
                    'rgba(16,185,129,0.9)',
                    'rgba(249,115,22,0.9)',
                    'rgba(234,88,12,0.9)',
                    'rgba(168,85,247,0.9)',
                    'rgba(34,197,94,0.9)'
                ];
                return Array.from({length:n},(v,i)=> base[i % base.length]);
            }

            function baseLineOptions(){
                /*return {
                    plugins: { legend:{ display:false } },
                    scales: {
                        x: { grid:{display:false} },
                        y: { grid:{color:'rgba(15,23,42,0.04)'}, beginAtZero:true }
                    },
                    maintainAspectRatio:false
                };*/
            }

            function gradient(ctx, color){
                const g = ctx.createLinearGradient(0,0,0,200);
                g.addColorStop(0, color);
                g.addColorStop(1, 'rgba(255,255,255,0)');
                return g;
            }

            /* ======= init ======= */
            document.getElementById('btn-apply').addEventListener('click', ()=>{
                const range = document.getElementById('dateRange').value;
                // Update URL with the selected range
                window.location.href = '/sales/overview?range=' + range;
            });

            // Set the initial value of the date range selector
            document.getElementById('dateRange').value = '{{ $range ?? "this_month" }}';

            // render initial with the range from the controller
            renderRange('{{ $range ?? "this_month" }}');

            /* ======= OPTIONAL: allow clicking a KPI to filter (example) ======= */
            document.getElementById('kpiOutstanding').addEventListener('click', ()=>{
                alert('Outstanding clicked — implement drill-down to unpaid invoices.');
            });

        </script>
        </div>

    </main>
</x-app-layout>
