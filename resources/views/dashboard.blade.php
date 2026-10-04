@section('page-title', __('Dashboard'))
@section('page-sub-title', __('Overview of the company\'s performance'))
<x-app-layout>

    <style>
        .summary-card { height: 100%; min-height: 250px; padding: 18px 20px; border-radius: 22px; background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.07); display: flex; flex-direction: column; }
        .summary-card.sc-mint { background: #d6fbe8; }
        .summary-card.sc-blue { background: #e3efff; }
        .summary-card.sc-orange { background: #fff0d9; }
        .summary-card.sc-green { background: #dcf7e3; }
        .sc-orange .sc-boxes, .sc-green .sc-boxes { background: rgba(0,0,0,.06); border-radius: 14px; padding: 12px 14px; }
        .sc-orange .sc-box, .sc-green .sc-box { background: transparent; text-align: left; padding: 0; }
        .sc-orange .sc-box:last-child, .sc-green .sc-box:last-child { text-align: right; }
        .sc-head { display: flex; justify-content: space-between; align-items: center; }
        .sc-title { font-weight: 700; font-size: .95rem; letter-spacing: .02em; text-transform: uppercase; color: #111; }
        .sc-month { border: 0; background: transparent; font-size: .85rem; font-weight: 500; color: #111; cursor: pointer; outline: 0; }
        .sc-value { font-size: 2.4rem; white-space: nowrap; font-weight: 700; letter-spacing: -.03em; line-height: 1.1; color: #000; margin-top: auto; }
        .sc-value-md { font-size: 2rem; margin-top: 4px; }
        .sc-meta { font-size: .9rem; color: #333; margin-top: 4px; }
        .sc-meta-top { margin-top: 18px; color: #6c757d; }
        .sc-change { font-size: .75rem; margin-left: 4px; }
        .sc-change.up { color: #1a9d4a; } .sc-change.down { color: #e5383b; }
        .sc-boxes { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: auto; padding-top: 18px; }
        .sc-mint .sc-boxes, .sc-blue .sc-boxes { background: rgba(0,0,0,.06); border-radius: 14px; padding: 12px 14px; margin-top: auto; }
        .sc-box { background: #f6f8fb; border-radius: 14px; text-align: center; padding: 10px 6px; display: flex; flex-direction: column; font-size: .9rem; }
        .sc-box b { font-size: 1rem; }
        .sc-mint .sc-box, .sc-blue .sc-box { background: transparent; text-align: left; padding: 0; }
        .sc-mint .sc-box:last-child, .sc-blue .sc-box:last-child { text-align: right; }
        .sc-progress { position: relative; height: 56px; margin-top: 12px; border-radius: 14px; background: #f1f3f7; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .sc-progress div { position: absolute; inset: 0 auto 0 0; background: #cfe3ff; }
        .sc-progress span { position: relative; font-weight: 700; color: #6c757d; }
        .sc-foot { display: flex; justify-content: space-between; margin-top: auto; padding-top: 14px; font-size: .85rem; color: #6c757d; }
        .sc-foot div { display: flex; flex-direction: column; } .sc-foot b { color: #111; font-size: 1rem; }
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
                            <div class="row g-3 mb-3">
                                @php
                                    $monthSelect = function () use ($summaryMonths, $sc) {
                                        $o = '';
                                        foreach ($summaryMonths as $val => $lbl) {
                                            $o .= '<option value="' . $val . '"' . ($val === $sc['month'] ? ' selected' : '') . '>' . \Illuminate\Support\Carbon::createFromFormat('Y-m', $val)->format('M') . '</option>';
                                        }
                                        return '<select class="sc-month" onchange="window.location=\'?month=\'+this.value">' . $o . '</select>';
                                    };
                                @endphp

                                {{-- Total Sales --}}
                                <div class="col-12 col-sm-6"><div class="summary-card">
                                    <div class="sc-head"><span class="sc-title">{{ __('Total Sales') }}</span>{!! $monthSelect() !!}</div>
                                    <div class="sc-value">{{ number_format($sc['sales']['total'], 2) }}</div>
                                    <div class="sc-meta"><b>{{ $sc['sales']['count'] }}</b> {{ __('Invoices') }} {!! $chg($sc['sales']['change']) !!}</div>
                                    <div class="sc-boxes">
                                        <div class="sc-box"><span>{{ __('Collected') }}</span><b>{{ $short($sc['sales']['collected']) }}</b></div>
                                        <div class="sc-box"><span>{{ __('Pending') }}</span><b>{{ $short($sc['sales']['pending']) }}</b></div>
                                    </div>
                                </div></div>

                                {{-- Invoices --}}
                                <div class="col-12 col-sm-6"><div class="summary-card sc-mint">
                                    <div class="sc-head"><span class="sc-title">{{ __('Invoices') }}</span>{!! $monthSelect() !!}</div>
                                    <div class="sc-value">{{ number_format($sc['invoices']['count']) }}</div>
                                    <div class="sc-meta">{{ __('Due') }}: <b>{{ $dueInvoices }}</b> {!! $chg($sc['invoices']['change']) !!}</div>
                                    <div class="sc-boxes">
                                        <div class="sc-box"><span>{{ __('Approved') }}</span><b>{{ $sc['invoices']['approved'] }}</b></div>
                                        <div class="sc-box"><span>{{ __('Draft') }}</span><b>{{ $sc['invoices']['draft'] }}</b></div>
                                    </div>
                                </div></div>

                                {{-- Customers --}}
                                <div class="col-12 col-sm-6"><div class="summary-card sc-orange">
                                    <div class="sc-head"><span class="sc-title">{{ __('Customers') }}</span>{!! $monthSelect() !!}</div>
                                    <div class="sc-value">{{ number_format($sc['customers']['total']) }}</div>
                                    <div class="sc-meta"><b>{{ $sc['customers']['new'] }}</b> {{ __('New') }} {!! $chg($sc['customers']['change']) !!}</div>
                                    <div class="sc-boxes">
                                        <div class="sc-box"><span>{{ __('This month') }}</span><b>{{ $sc['customers']['new'] }}</b></div>
                                        <div class="sc-box"><span>{{ __('Last month') }}</span><b>{{ $sc['customers']['prevNew'] }}</b></div>
                                    </div>
                                </div></div>

                                {{-- Profit --}}
                                <div class="col-12 col-sm-6"><div class="summary-card sc-green">
                                    <div class="sc-head"><span class="sc-title">{{ __('Profit') }}</span>{!! $monthSelect() !!}</div>
                                    <div class="sc-value">{{ number_format($sc['profit']['total'], 2) }}</div>
                                    <div class="sc-meta">{{ __('Margin') }} <b>{{ $sc['profit']['margin'] }}%</b> {!! $chg($sc['profit']['change']) !!}</div>
                                    <div class="sc-boxes">
                                        <div class="sc-box"><span>{{ __('Revenue') }}</span><b>{{ $short($sc['profit']['revenue']) }}</b></div>
                                        <div class="sc-box"><span>{{ __('Expenses') }}</span><b>{{ $short($sc['profit']['expenses']) }}</b></div>
                                    </div>
                                </div></div>
                            </div>

                            <div class="row g-3 mb-3">
                                @foreach([
                                    ['key' => 'quotation', 'title' => __('Quotation'), 'cls' => ''],
                                    ['key' => 'payment', 'title' => __('Payments'), 'cls' => ''],
                                    ['key' => 'collection', 'title' => __('Collection'), 'cls' => 'sc-blue'],
                                ] as $card)
                                    @php $d = $sc[$card['key']]; @endphp
                                    <div class="col-12 col-sm-4">
                                        <div class="summary-card {{ $card['cls'] }}">
                                            <div class="sc-head"><span class="sc-title">{{ $card['title'] }}</span>{!! $monthSelect() !!}</div>
                                            @if($card['key'] === 'quotation')
                                                <div class="sc-value">{{ number_format($d['total'], 2) }}</div>
                                                <div class="sc-meta"><b>{{ $d['count'] }}</b> {{ __('Quotations') }} {!! $chg($d['change']) !!}</div>
                                                <div class="sc-boxes">
                                                    <div class="sc-box"><span>{{ __('Completed') }}</span><b>{{ $d['completed'] }}</b></div>
                                                    <div class="sc-box"><span>{{ __('Approved') }}</span><b>{{ $d['approved'] }}</b></div>
                                                </div>
                                            @else
                                                <div class="sc-meta sc-meta-top">{{ $card['key'] === 'payment' ? __('Total Payments') : __('Total Collections') }}</div>
                                                <div class="sc-value sc-value-md">{{ $short($d['total']) }}</div>
                                                <div class="sc-progress"><div style="width: {{ $d['percent'] }}%"></div><span>{{ $d['percent'] }}%</span></div>
                                                <div class="sc-foot">
                                                    <div><span>{{ __('Approved') }}</span><b>{{ number_format($d['approved'], 2) }}</b></div>
                                                    <div class="text-end"><span>{{ __('Draft') }}</span><b>{{ $short($d['draft']) }}</b></div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
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
