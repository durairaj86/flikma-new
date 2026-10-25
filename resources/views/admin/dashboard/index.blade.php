@extends('admin.layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .dg-card { display: block; height: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 18px; color: inherit; transition: border-color .15s, box-shadow .15s; }
    a.dg-card:hover { border-color: #c7d2fe; box-shadow: 0 4px 14px rgba(79,70,229,.08); }
    .dg-title { font-weight: 600; color: #1f2937; }
    .dg-num { font-size: 1.9rem; font-weight: 700; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .dg-num-prev { color: #6b7280; font-size: 1.4rem; }
    .dg-lbl { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; margin-top: 2px; }
    .dg-strip { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
    .dg-kpi { display: block; padding: 16px 20px; color: inherit; transition: background .15s; }
    .dg-kpi + .dg-kpi { border-left: 1px solid #f1f3f5; }
    a.dg-kpi:hover { background: #f8fafc; }
    .dg-kpi-num { font-size: 1.6rem; font-weight: 700; line-height: 1.2; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .dg-kpi-sub { font-size: .78rem; color: #6b7280; }
    .dg-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
    .dg-panel-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-bottom: 1px solid #f1f3f5; font-weight: 600; color: #1f2937; }
    .dg-chart { position: relative; height: 260px; padding: 14px 16px 10px; }
    .dg-table thead th { background: #f9fafb !important; padding: 10px 18px; border-bottom: 1px solid #e5e7eb; }
    .dg-table tbody td { padding: 10px 18px; border-color: #f1f3f5; font-size: .85rem; }
    .dg-activity { max-height: 360px; overflow-y: auto; }
    .dg-act { display: flex; align-items: center; gap: 10px; padding: 10px 18px; border-bottom: 1px solid #f1f3f5; min-width: 0; }
    .dg-act:last-child { border-bottom: 0; }
    @media (max-width: 1199.98px) { .dg-strip { grid-template-columns: repeat(3, minmax(0, 1fr)); } .dg-kpi:nth-child(4) { border-left: 0; } .dg-kpi:nth-child(n+4) { border-top: 1px solid #f1f3f5; } }
    @media (max-width: 767.98px) { .dg-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); } .dg-kpi:nth-child(odd) { border-left: 0; } .dg-kpi:nth-child(n+3) { border-top: 1px solid #f1f3f5; } }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">Dashboard</h4>
    <p class="text-muted small mb-0">Overview of companies, enquiries, emails and recent activity.</p>
</div>

@php
    // this month vs last month
    $trend = function (int $now, int $prev): array {
        if ($prev === 0) {
            return $now > 0 ? ['new', 'success', 'bi-arrow-up-right'] : ['—', 'secondary', 'bi-dash'];
        }
        $pct = (int) round(($now - $prev) / $prev * 100);

        return [($pct > 0 ? '+' : '') . $pct . '%', $pct >= 0 ? 'success' : 'danger', $pct >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right'];
    };
    $monthName = now()->format('F');
    $lastMonthName = now()->subMonthNoOverflow()->format('F');
    $growth = [
        ['title' => 'New companies', 'icon' => 'bi-building-add', 'color' => '#4f46e5', 'now' => $companiesThisMonth, 'prev' => $companiesLastMonth, 'url' => route('admin.companies.index'), 'extra' => null],
        ['title' => 'New enquiries', 'icon' => 'bi-chat-dots', 'color' => '#0ea5e9', 'now' => $enquiriesThisMonth, 'prev' => $enquiriesLastMonth, 'url' => route('admin.enquiries.index'),
            'extra' => $pendingEnquiries > 0 ? $pendingEnquiries . ' unread' : null],
    ];
    $kpis = [
        ['label' => 'Total companies', 'value' => number_format($totalCompanies), 'sub' => $companiesThisYear . ' this year', 'color' => '#111827', 'url' => route('admin.companies.index')],
        ['label' => 'Paid, active', 'value' => number_format($paidActive), 'sub' => 'plan end date ahead', 'color' => '#16a34a', 'url' => route('admin.companies.index', ['access' => 'paid'])],
        ['label' => 'Paid, expired', 'value' => number_format($paidExpired), 'sub' => 'need renewal', 'color' => '#dc2626', 'url' => route('admin.companies.index', ['access' => 'paid_expired'])],
        ['label' => 'On trial', 'value' => number_format($onTrial), 'sub' => 'trial running', 'color' => '#2563eb', 'url' => route('admin.companies.index', ['access' => 'trial'])],
        ['label' => 'Total revenue', 'value' => number_format($totalRevenue, 2), 'sub' => 'SAR, paid subscriptions', 'color' => '#4f46e5', 'url' => null],
        ['label' => 'Logged in today', 'value' => number_format($loginsToday), 'sub' => $failedEmails > 0 ? $failedEmails . ' failed email' . ($failedEmails === 1 ? '' : 's') : 'app users', 'color' => '#0ea5e9', 'url' => $failedEmails > 0 ? route('admin.email-queue.index', ['status' => 'gave_up']) : null],
    ];
    $months = range(1, 12);
    $regSeries = array_map(fn ($m) => (int) ($monthlyCompanyRegistrations[$m] ?? 0), $months);
    $revSeries = array_map(fn ($m) => round((float) ($monthlyRevenue[$m] ?? 0), 2), $months);
@endphp

<div class="row g-3 mb-4">
    @foreach($growth as $g)
        @php [$tLabel, $tColor, $tIcon] = $trend($g['now'], $g['prev']); @endphp
        <div class="col-md-6">
            <a href="{{ $g['url'] }}" class="dg-card text-decoration-none">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="dg-title"><i class="bi {{ $g['icon'] }} me-2" style="color: {{ $g['color'] }}"></i>{{ $g['title'] }}</div>
                    <span class="badge rounded-pill bg-{{ $tColor }}-subtle text-{{ $tColor }}-emphasis" title="vs {{ $lastMonthName }}"><i class="bi {{ $tIcon }} me-1"></i>{{ $tLabel }}</span>
                </div>
                <div class="d-flex align-items-end gap-4 mt-2">
                    <div>
                        <div class="dg-num" style="color: {{ $g['color'] }}">{{ $g['now'] }}</div>
                        <div class="dg-lbl">This month <span class="text-muted">({{ $monthName }})</span></div>
                    </div>
                    <div>
                        <div class="dg-num dg-num-prev">{{ $g['prev'] }}</div>
                        <div class="dg-lbl">Last month <span class="text-muted">({{ $lastMonthName }})</span></div>
                    </div>
                    @if($g['extra'])
                        <span class="ms-auto badge bg-danger-subtle text-danger-emphasis">{{ $g['extra'] }}</span>
                    @endif
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="dg-strip mb-4">
    @foreach($kpis as $k)
        <{{ $k['url'] ? 'a' : 'div' }} @if($k['url']) href="{{ $k['url'] }}" @endif class="dg-kpi text-decoration-none">
            <div class="dg-lbl">{{ $k['label'] }}</div>
            <div class="dg-kpi-num" style="color: {{ $k['color'] }}">{{ $k['value'] }}</div>
            <div class="dg-kpi-sub">{{ $k['sub'] }}</div>
        </{{ $k['url'] ? 'a' : 'div' }}>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="dg-panel h-100">
            <div class="dg-panel-head">
                <span><i class="bi bi-building-add me-2" style="color:#7c3aed"></i>Company registrations</span>
                <span class="text-muted small">{{ now()->year }} · {{ array_sum($regSeries) }} total</span>
            </div>
            <div class="dg-chart"><canvas id="registrationsChart" aria-label="Company registrations per month" role="img"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="dg-panel h-100">
            <div class="dg-panel-head">
                <span><i class="bi bi-cash-stack me-2" style="color:#4f46e5"></i>Monthly revenue</span>
                <span class="text-muted small">{{ now()->year }} · SAR {{ number_format(array_sum($revSeries), 2) }}</span>
            </div>
            <div class="dg-chart"><canvas id="revenueChart" aria-label="Revenue per month" role="img"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="dg-panel h-100">
            <div class="dg-panel-head">
                <span><i class="bi bi-building me-2 text-primary"></i>Recent companies</span>
                <a href="{{ route('admin.companies.index') }}" class="small text-decoration-none">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 dg-table">
                    <thead><tr><th>Company</th><th>Plan</th><th class="text-end">Registered</th></tr></thead>
                    <tbody>
                    @forelse($recentCompanies as $company)
                        @php
                            [$planLabel, $planState] = \App\Http\Controllers\Admin\CompanyController::planBadge($company);
                            $planClass = ['trial' => 'bg-primary-subtle text-primary-emphasis', 'paid' => 'bg-success-subtle text-success-emphasis', 'lapsed' => 'bg-danger-subtle text-danger-emphasis', 'trial_ended' => 'bg-light text-secondary border'][$planState];
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $company->name }}</div>
                                <div class="small text-muted">{{ $company->email ?: '—' }}</div>
                            </td>
                            <td><span class="badge rounded-pill {{ $planClass }}">{{ $planLabel }}</span></td>
                            <td class="text-end text-muted small">{{ $company->created_at?->format('d-m-Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4 text-muted">No companies yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="dg-panel h-100">
            <div class="dg-panel-head">
                <span><i class="bi bi-journal-text me-2 text-primary"></i>Recent activity</span>
                <a href="{{ route('admin.activity-logs.index') }}" class="small text-decoration-none">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="dg-activity">
                @forelse($recentActivities as $log)
                    @php $actColor = ['created' => 'success', 'updated' => 'primary', 'deleted' => 'danger', 'sent' => 'success', 'replied' => 'success'][$log->action] ?? 'secondary'; @endphp
                    <div class="dg-act">
                        <span class="badge rounded-pill bg-{{ $actColor }}-subtle text-{{ $actColor }}-emphasis">{{ ucfirst($log->action) }}</span>
                        <span class="text-capitalize small fw-semibold text-secondary">{{ str_replace('_', ' ', $log->module) }}</span>
                        <span class="small text-dark text-truncate flex-grow-1">{{ $log->description }}</span>
                        <span class="small text-muted text-nowrap" title="{{ $log->created_at->format('d-m-Y H:i') }}">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">No recent activity.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const current = {{ now()->month }} - 1;
        // past months coloured, current month darker, future months faint
        const shade = (base, dark, faint) => labels.map((_, i) => i === current ? dark : (i > current ? faint : base));
        const common = (tooltip, axis = v => v) => ({
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + tooltip(c.parsed.y) } } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, callback: v => axis(v), color: '#9ca3af' }, grid: { color: '#f1f3f5' }, border: { display: false } },
                x: { grid: { display: false }, ticks: { color: '#6b7280' }, border: { display: false } },
            },
        });
        new Chart(document.getElementById('registrationsChart'), {
            type: 'bar',
            data: { labels, datasets: [{ data: @json($regSeries), backgroundColor: shade('#a78bfa', '#7c3aed', '#ede9fe'), borderRadius: 6, maxBarThickness: 26 }] },
            options: common(v => v + (v === 1 ? ' company' : ' companies')),
        });
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: { labels, datasets: [{ data: @json($revSeries), backgroundColor: shade('#818cf8', '#4f46e5', '#e0e7ff'), borderRadius: 6, maxBarThickness: 26 }] },
            options: common(v => 'SAR ' + Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 }), v => Number(v).toLocaleString()),
        });
    })();
</script>
@endpush
