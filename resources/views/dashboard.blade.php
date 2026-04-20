@section('page-title','Dashboard')
@section('page-sub-title','Overview of the company\'s performance')
<x-app-layout>

    <style>
        /* Page background & wrapper */
        .wrapper { min-height: 100vh; }

        /* Sidebar look (light, minimal) */
        .main-sidebar { width: 240px; background: #ffffff; border-right: 1px solid rgba(0,0,0,0.04); }
        .brand-link { height: 56px; border-bottom: 1px solid rgba(0,0,0,0.04); }
        .nav-sidebar .nav-link { color: #495057; }
        .nav-sidebar .nav-link.active { background: rgba(13,110,253,0.06); color: #0d6efd; border-left: 3px solid #0d6efd; }

        /* Content area spacing */
        .content-wrapper { /*margin-left: 240px;*/ padding: 20px; }

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

    <div class="hold-transition">

        <div class="wrapper d-flex">
            <!-- Sidebar -->
            <aside class="main-sidebar sidebar-light-primary elevation-2 position-fixed h-100 d-none">
                <a href="#" class="brand-link d-flex align-items-center p-3">
                    <img src="https://cdn.jsdelivr.net/npm/admin-lte@4.6.6/dist/img/AdminLTELogo.png" alt="logo" class="brand-image img-circle me-2" style="width:34px;height:34px;opacity:.9;">
                    <span class="brand-text fw-semibold">AdminLTE Lite</span>
                </a>

                <div class="sidebar pt-2">
                    <nav class="mt-3">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                            <li class="nav-item"><a href="#" class="nav-link active"><i class="nav-icon fa fa-home"></i><p>Dashboard</p></a></li>
                            <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fa fa-file-invoice"></i><p>Invoices</p></a></li>
                            <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fa fa-people-group"></i><p>Customers</p></a></li>
                            <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fa fa-chart-simple"></i><p>Reports</p></a></li>
                            <li class="nav-item"><a href="#" class="nav-link"><i class="nav-icon fa fa-gear"></i><p>Settings</p></a></li>
                        </ul>
                    </nav>
                </div>
            </aside>

            <!-- Content -->
            <div class="content-wrapper flex-grow-1">
                <!-- Top row: Title & timeframe -->
                <div class="container-fluid mb-3 d-none">
                    <div class="row align-items-center">
                        <div class="col">
                            {{--<h4 class="mb-0">Dashboard</h4>
                            <small class="muted-sm">Overview of the company's performance</small>--}}
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group" aria-label="Period">
                                <button class="btn btn-outline-secondary btn-sm">Today</button>
                                <button class="btn btn-outline-secondary btn-sm">Week</button>
                                <button class="btn btn-outline-secondary btn-sm active">Month</button>
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
                            <div class="row g-3 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="card stat-box">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 stat-icon bg-light border">
                                                <i class="fa-solid fa-dollar-sign text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="stat-label">Total Sales</div>
                                                <div class="stat-value">₹ {{ number_format($totalSales, 0) }}</div>
                                                <div class="muted-sm {{ $salesGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                                    <i class="fa fa-arrow-{{ $salesGrowth >= 0 ? 'up' : 'down' }}"></i> {{ abs($salesGrowth) }}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3">
                                    <div class="card stat-box">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 stat-icon bg-light border">
                                                <i class="fa-solid fa-file-invoice text-info"></i>
                                            </div>
                                            <div>
                                                <div class="stat-label">Invoices</div>
                                                <div class="stat-value">{{ number_format($totalInvoices) }}</div>
                                                <div class="muted-sm">Due: {{ $dueInvoices }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3">
                                    <div class="card stat-box">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 stat-icon bg-light border">
                                                <i class="fa-solid fa-users text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="stat-label">Customers</div>
                                                <div class="stat-value">{{ number_format($totalCustomers) }}</div>
                                                <div class="muted-sm">New: {{ $newCustomers }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3">
                                    <div class="card stat-box">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 stat-icon bg-light border">
                                                <i class="fa-solid fa-chart-line text-success"></i>
                                            </div>
                                            <div>
                                                <div class="stat-label">Profit</div>
                                                <div class="stat-value">₹ {{ number_format($profit, 0) }}</div>
                                                <div class="muted-sm {{ $profitMargin > 0 ? 'text-success' : 'text-danger' }}">
                                                    Margin {{ $profitMargin }}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <div class="row g-3">
                                <!-- ETA -->
                                <div class="col-md-3">
                                    <div class="card text-center shadow-sm border-0 p-3">
                                        <i class="fa-solid fa-ship text-primary fs-3 mb-2"></i>
                                        <h6 class="fw-normal mb-1">ETA Today</h6>
                                        <h4 class="fw-bold text-primary mb-0">{{ $etaToday }}</h4>
                                    </div>
                                </div>

                                <!-- ETD -->
                                <div class="col-md-3">
                                    <div class="card text-center shadow-sm border-0 p-3">
                                        <i class="fa-solid fa-plane-departure text-success fs-3 mb-2"></i>
                                        <h6 class="fw-normal mb-1">ETD Tomorrow</h6>
                                        <h4 class="fw-bold text-success mb-0">{{ $etdTomorrow }}</h4>
                                    </div>
                                </div>

                                <!-- ATA -->
                                <div class="col-md-3">
                                    <div class="card text-center shadow-sm border-0 p-3">
                                        <i class="fa-solid fa-truck text-info fs-3 mb-2"></i>
                                        <h6 class="fw-normal mb-1">ATA This Week</h6>
                                        <h4 class="fw-bold text-info mb-0">{{ $ataThisWeek }}</h4>
                                    </div>
                                </div>

                                <!-- ATD -->
                                <div class="col-md-3">
                                    <div class="card text-center shadow-sm border-0 p-3">
                                        <i class="fa-solid fa-plane-arrival text-danger fs-3 mb-2"></i>
                                        <h6 class="fw-normal mb-1">ATD This Week</h6>
                                        <h4 class="fw-bold text-danger mb-0">{{ $atdThisWeek }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <!-- Job Follow-ups -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-semibold mb-0">Job Follow-ups</h6>
                                            <i class="fa-solid fa-clipboard-list text-primary"></i>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Today</span>
                                            <span class="fw-bold text-primary">{{ $jobFollowupsToday }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>This Week</span>
                                            <span class="fw-bold text-success">{{ $jobFollowupsThisWeek }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payments -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-semibold mb-0">Payments</h6>
                                            <i class="fa-solid fa-money-bill-transfer text-success"></i>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>To Collect</span>
                                            <span class="fw-bold text-warning">₹{{ number_format($toCollect, 0) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>To Pay</span>
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
                                            <h6 class="mb-0">Sales Overview</h6>
                                            <div class="text-muted small">Monthly</div>
                                        </div>
                                        <div class="card-body" style="min-height:220px;">
                                            <canvas id="salesMainChart" style="height:220px;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">Revenue Trend</h6>
                                            <div class="text-muted small">This month</div>
                                        </div>
                                        <div class="card-body" style="min-height:220px;">
                                            <canvas id="revenueMainChart" style="height:220px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom: Recent transactions (wide) -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Recent Transactions</h6>
                                    <div><a href="#" class="btn btn-sm btn-outline-secondary">View All</a></div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 align-middle">
                                            <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice</th>
                                                <th>Customer</th>
                                                <th>Date</th>
                                                <th class="text-end">Amount</th>
                                                <th>Status</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($recentTransactions as $transaction)
                                                <tr>
                                                    <td>{{ $transaction->id }}</td>
                                                    <td>{{ $transaction->invoice_number ?? $transaction->row_no }}</td>
                                                    <td>{{ $transaction->customer->name ?? 'N/A' }}</td>
                                                    <td>{{ $transaction->invoice_date }}</td>
                                                    <td class="text-end">₹ {{ number_format($transaction->grand_total, 0) }}</td>
                                                    <td>
                                                        @if($transaction->status == 'approved')
                                                            <span class="badge bg-success">Paid</span>
                                                        @elseif($transaction->status == 'draft')
                                                            <span class="badge bg-warning text-dark">Pending</span>
                                                        @elseif($transaction->status == 'overdue')
                                                            <span class="badge bg-danger">Overdue</span>
                                                        @elseif($transaction->status == 'partial')
                                                            <span class="badge bg-info text-dark">Part Paid</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $transaction->status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">No recent transactions found</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-muted small">Showing {{ count($recentTransactions) }} of {{ $totalInvoices }} transactions</div>
                            </div>

                        </div> <!-- /.left-col -->

                        <!-- RIGHT: summary / mini panels (4/12) -->
                        <div class="col-lg-4 right-col">
                            <!-- Outstanding -->
                            <div class="right-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6>Outstanding</h6>
                                        <div class="big">₹ {{ number_format($outstanding, 0) }}</div>
                                        <div class="muted-sm mt-1">Total amount outstanding</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger">Overdue</span>
                                        <div class="muted-sm mt-2">
                                            {{ $outstandingChange >= 0 ? '+' : '' }}{{ $outstandingChange }}% vs last month
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-2" />
                                <div>
                                    <div class="d-flex justify-content-between small mb-1"><div>Due <small class="text-muted">0-30d</small></div><div>₹ {{ number_format($outstanding30d, 0) }}</div></div>
                                    <div class="progress mb-2" style="height:8px;">
                                        <div class="progress-bar bg-warning" style="width:{{ $outstanding > 0 ? ($outstanding30d / $outstanding) * 100 : 0 }}%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between small mb-1"><div>Due <small class="text-muted">31-60d</small></div><div>₹ {{ number_format($outstanding60d, 0) }}</div></div>
                                    <div class="progress mb-2" style="height:8px;">
                                        <div class="progress-bar bg-danger" style="width:{{ $outstanding > 0 ? ($outstanding60d / $outstanding) * 100 : 0 }}%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between small mb-1"><div>Due <small class="text-muted">60+d</small></div><div>₹ {{ number_format($outstanding60Plus, 0) }}</div></div>
                                    <div class="progress mb-0" style="height:8px;">
                                        <div class="progress-bar bg-secondary" style="width:{{ $outstanding > 0 ? ($outstanding60Plus / $outstanding) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Awaiting Approval -->
                            <div class="right-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Awaiting Approval</h6>
                                        <div class="big">{{ $awaitingApproval->count() }} Invoices</div>
                                        <div class="muted-sm mt-1">Total ₹ {{ number_format($awaitingApprovalTotal, 0) }}</div>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary"><i class="fa fa-check"></i> Review</button>
                                    </div>
                                </div>

                                <hr class="my-2" />
                                <!-- small list of invoices -->
                                <div class="list-group list-group-flush small">
                                    @forelse($awaitingApproval->take(3) as $invoice)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between">
                                                <div>{{ $invoice->invoice_number ?? $invoice->row_no }}</div>
                                                <div class="text-end">₹ {{ number_format($invoice->grand_total, 0) }}
                                                    <span class="text-muted d-block">{{ $invoice->customer->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="list-group-item px-0">
                                            <div class="text-center">No invoices awaiting approval</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Cost Summary (mini-donut + stats) -->
                            <div class="right-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Cost Summary</h6>
                                        <div class="muted-sm">This month</div>
                                    </div>
                                    <div style="width:120px;">
                                        <canvas id="costMiniChart" class="mini-canvas"></canvas>
                                    </div>
                                </div>

                                <hr class="my-2" />
                                <div class="small">
                                    <div class="d-flex justify-content-between mb-1"><div>Material</div><div>{{ $materialPercent }}%</div></div>
                                    <div class="d-flex justify-content-between mb-1"><div>Labour</div><div>{{ $labourPercent }}%</div></div>
                                    <div class="d-flex justify-content-between mb-0"><div>Transport</div><div>{{ $transportPercent }}%</div></div>
                                </div>
                            </div>

                            <!-- Revenue Summary (mini-line + totals) -->
                            <div class="right-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>Revenue Summary</h6>
                                        <div class="big">₹ {{ number_format($currentMonthSales, 0) }}</div>
                                        <div class="muted-sm mt-1">Net revenue (MTD)</div>
                                    </div>
                                    <div style="width:120px;">
                                        <canvas id="revMiniChart" class="mini-canvas"></canvas>
                                    </div>
                                </div>

                                <hr class="my-2" />
                                <div class="d-flex justify-content-between small">
                                    <div>Online</div>
                                    <div>₹ {{ number_format($onlineRevenue, 0) }}</div>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <div>Offline</div>
                                    <div>₹ {{ number_format($offlineRevenue, 0) }}</div>
                                </div>
                            </div>

                        </div> <!-- /.right-col -->
                    </div> <!-- /.row -->
                </div> <!-- /.container-fluid -->







                <footer class="small text-center mt-3 mb-0">© <span id="y"></span> Your Company — All rights reserved.</footer>
            </div> <!-- /.content-wrapper -->
        </div> <!-- /.wrapper -->

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.6.6/dist/js/adminlte.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

        <script>
            // footer year
            document.getElementById('y').innerText = new Date().getFullYear();

            // Main Sales chart (bar stacked-like)
            var monthlyLabels = @json($monthlyLabels);
            var onlineSales = @json($onlineSales);
            var offlineSales = @json($offlineSales);

            new Chart(document.getElementById('salesMainChart'), {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [
                        { label: 'Online', data: onlineSales, backgroundColor: 'rgba(13,110,253,0.9)', borderRadius: 6 },
                        { label: 'Offline', data: offlineSales, backgroundColor: 'rgba(25,135,84,0.85)', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
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
                        label: 'Revenue',
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
                    labels: ['Material','Labour','Transport'],
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
        </script>
    </div>



</x-app-layout>
