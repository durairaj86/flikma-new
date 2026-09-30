@section('page-title', __('Dashboard'))
@section('page-sub-title', __('Overview of the company\'s performance'))
<x-app-layout>

    <style>
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
                            <div class="row g-3 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="card stat-box">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 stat-icon bg-light border">
                                                <i class="fa-solid fa-dollar-sign text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="stat-label">{{ __('Total Sales') }}</div>
                                                <div class="stat-value">{{ number_format($totalSales, 0) }}</div>
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
                                                <div class="stat-label">{{ __('Invoices') }}</div>
                                                <div class="stat-value">{{ number_format($totalInvoices) }}</div>
                                                <div class="muted-sm">{{ __('Due') }}: {{ $dueInvoices }}</div>
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
                                                <div class="stat-label">{{ __('Customers') }}</div>
                                                <div class="stat-value">{{ number_format($totalCustomers) }}</div>
                                                <div class="muted-sm">{{ __('New') }}: {{ $newCustomers }}</div>
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
                                                <div class="stat-label">{{ __('Profit') }}</div>
                                                <div class="stat-value">{{ number_format($profit, 0) }}</div>
                                                <div class="muted-sm {{ $profitMargin > 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ __('Margin') }} {{ $profitMargin }}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <div class="row g-3">
                                <!-- ETA / ETD -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 p-3">
                                        <div class="row g-0 text-center">
                                            <div class="col-6 border-end">
                                                <i class="fa-solid fa-ship text-primary fs-3 mb-2"></i>
                                                <h6 class="fw-normal mb-1">{{ __('ETA Today') }}</h6>
                                                <h4 class="fw-bold text-primary mb-0">{{ $etaToday }}</h4>
                                            </div>
                                            <div class="col-6">
                                                <i class="fa-solid fa-plane-departure text-success fs-3 mb-2"></i>
                                                <h6 class="fw-normal mb-1">{{ __('ETD Tomorrow') }}</h6>
                                                <h4 class="fw-bold text-success mb-0">{{ $etdTomorrow }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ATA / ATD -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 p-3">
                                        <div class="row g-0 text-center">
                                            <div class="col-6 border-end">
                                                <i class="fa-solid fa-truck text-info fs-3 mb-2"></i>
                                                <h6 class="fw-normal mb-1">{{ __('ATA This Week') }}</h6>
                                                <h4 class="fw-bold text-info mb-0">{{ $ataThisWeek }}</h4>
                                            </div>
                                            <div class="col-6">
                                                <i class="fa-solid fa-plane-arrival text-danger fs-3 mb-2"></i>
                                                <h6 class="fw-normal mb-1">{{ __('ATD This Week') }}</h6>
                                                <h4 class="fw-bold text-danger mb-0">{{ $atdThisWeek }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                            <div class="right-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6>{{ __('Outstanding') }}</h6>
                                        <div class="big">{{ number_format($outstanding, 0) }}</div>
                                        <div class="muted-sm mt-1">{{ __('Total amount outstanding') }}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger">{{ __('Overdue') }}</span>
                                        <div class="muted-sm mt-2">
                                            {{ $outstandingChange >= 0 ? '+' : '' }}{{ $outstandingChange }}% {{ __('vs last month') }}
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-2" />
                                <div>
                                    <div class="d-flex justify-content-between small mb-1"><div>{{ __('Due') }} <small class="text-muted">{{ __('0-30d') }}</small></div><div>{{ number_format($outstanding30d, 0) }}</div></div>
                                    <div class="progress mb-2" style="height:8px;">
                                        <div class="progress-bar bg-warning" style="width:{{ $outstanding > 0 ? ($outstanding30d / $outstanding) * 100 : 0 }}%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between small mb-1"><div>{{ __('Due') }} <small class="text-muted">{{ __('31-60d') }}</small></div><div>{{ number_format($outstanding60d, 0) }}</div></div>
                                    <div class="progress mb-2" style="height:8px;">
                                        <div class="progress-bar bg-danger" style="width:{{ $outstanding > 0 ? ($outstanding60d / $outstanding) * 100 : 0 }}%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between small mb-1"><div>{{ __('Due') }} <small class="text-muted">{{ __('60+d') }}</small></div><div>{{ number_format($outstanding60Plus, 0) }}</div></div>
                                    <div class="progress mb-0" style="height:8px;">
                                        <div class="progress-bar bg-secondary" style="width:{{ $outstanding > 0 ? ($outstanding60Plus / $outstanding) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Awaiting Approval -->
                            <div class="right-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6>{{ __('Awaiting Approval') }}</h6>
                                        <div class="big">{{ $awaitingApproval->count() }} {{ __('Invoices') }}</div>
                                        <div class="muted-sm mt-1">{{ __('Total') }} {{ number_format($awaitingApprovalTotal, 0) }}</div>
                                    </div>
                                    <div>
                                        <a href="{{ route('invoices.customer') }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-check"></i> {{ __('Review') }}</a>
                                    </div>
                                </div>

                                <hr class="my-2" />
                                <!-- small list of invoices -->
                                <div class="list-group list-group-flush small">
                                    @forelse($awaitingApproval->take(3) as $invoice)
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between">
                                                <div>{{ $invoice->invoice_number ?? $invoice->row_no }}</div>
                                                <div class="text-end">{{ number_format($invoice->grand_total, 0) }}
                                                    <span class="text-muted d-block">{{ $invoice->customer->name ?? __('N/A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="list-group-item px-0">
                                            <div class="text-center">{{ __('No invoices awaiting approval') }}</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

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
        </script>



</x-app-layout>
