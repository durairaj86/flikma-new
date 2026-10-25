@section('page-title', __('Transactions Overview'))
@section('hide-topbar', true)
@section('page-subtitle', __('Money in and out: collections, payments and what is still open'))
@section('print-footer')
<script>
    window.printFooter = {
        show: true,
        custom: '{{ __('Transactions Overview') }} - {{ __('Generated on') }} {{ date('d-m-Y H:i') }}'
    };
</script>
@endsection
<x-app-layout>
    <div class="bg-light pb-5">
    <div class="to-page pt-3">
        <style>
            body { background: #f8fafc; }
            .to-wrap { max-width: 1320px; margin: 0 auto; padding: 0 1.5rem; }
            .to-title { display: none; }
            body:not(.has-top-header) .to-title { display: block; }

            .to-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 1.25rem 1.4rem; height: 100%; }
            .to-card-h { display: flex; align-items: center; gap: .6rem; margin-bottom: 1rem; }
            .to-card-h .t { font-weight: 600; font-size: .95rem; color: #1f2937; flex: 1; min-width: 0; }
            .to-card-h .tag { font-size: .72rem; color: #6b7280; background: #f3f4f6; border-radius: 999px; padding: .15rem .6rem; white-space: nowrap; }
            .to-ico { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;
                      background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }

            /* period switch */
            .to-seg { display: inline-flex; background: #eef1f5; border-radius: 10px; padding: 3px; }
            .to-seg a { padding: .35rem .95rem; border-radius: 8px; font-size: .84rem; font-weight: 500; color: #4b5563; text-decoration: none; white-space: nowrap; }
            .to-seg a.on { background: #fff; color: #111827; box-shadow: 0 1px 2px rgba(0, 0, 0, .08); font-weight: 600; }
            .to-seg a:not(.on):hover { color: #111827; }

            /* the three headline figures */
            .to-kpi .lbl { font-size: .82rem; color: #6b7280; font-weight: 500; }
            .to-kpi .val { font-size: 1.95rem; font-weight: 700; letter-spacing: -.02em; color: #111827; line-height: 1.15; margin-top: .15rem; }
            .to-kpi .val small { font-size: .95rem; font-weight: 600; color: #6b7280; margin-right: .25rem; letter-spacing: 0; }
            .to-kpi .sub { font-size: .8rem; color: #6b7280; margin-top: .35rem; }
            .to-kpi .sub b { color: #374151; font-weight: 600; }
            .to-kpi .spark { height: 54px; margin-top: .9rem; }
            .to-delta { font-size: .74rem; font-weight: 600; border-radius: 999px; padding: .12rem .55rem; margin-left: .4rem; vertical-align: middle; }
            .to-delta.up { background: #dcfce7; color: #166534; } .to-delta.down { background: #fee2e2; color: #991b1b; }

            /* secondary figures in one strip */
            .to-strip { display: flex; flex-wrap: wrap; }
            .to-strip > div { flex: 1 1 200px; display: flex; align-items: center; gap: .8rem; padding: .35rem 1.2rem; border-left: 1px solid #eef0f3; }
            .to-strip > div:first-child { border-left: 0; padding-left: 0; }
            .to-strip .v { font-size: 1.25rem; font-weight: 700; color: #111827; line-height: 1.2; }
            .to-strip .l { font-size: .78rem; color: #6b7280; }

            /* lists */
            .to-row { padding: .65rem 0; }
            .to-row + .to-row { border-top: 1px solid #f1f3f6; }
            .to-row-top { display: flex; justify-content: space-between; gap: .75rem; align-items: baseline; }
            .to-row-name { font-weight: 600; color: #1f2937; font-size: .88rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .to-row-val { font-weight: 700; color: #111827; font-size: .88rem; white-space: nowrap; }
            .to-row-sub { font-size: .74rem; color: #9ca3af; margin-top: .1rem; }
            .to-bar { height: 6px; background: #eef1f5; border-radius: 99px; overflow: hidden; margin-top: .45rem; }
            .to-bar > span { display: block; height: 100%; border-radius: 99px; }
            .to-rank { display: inline-flex; width: 22px; height: 22px; border-radius: 50%; background: #f3f4f6; color: #4b5563; font-size: .72rem; font-weight: 700; align-items: center; justify-content: center; margin-right: .55rem; flex-shrink: 0; }

            .to-stack { display: flex; height: 12px; border-radius: 99px; overflow: hidden; background: #eef1f5; }
            .to-stack > span { display: block; height: 100%; }
            .to-leg { display: flex; flex-wrap: wrap; gap: .35rem 1rem; margin-top: .55rem; font-size: .78rem; color: #4b5563; }
            .to-leg i { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: .35rem; }
            .to-leg b { color: #111827; margin-left: .25rem; }
            .to-empty { text-align: center; color: #9ca3af; font-size: .85rem; padding: 2rem 0; }
            .to-chart { position: relative; }
            @media (max-width: 767.98px) { .to-wrap { padding: 0 1rem; } .to-strip > div { border-left: 0; padding-left: 0; } }
        </style>

        @php($ranges = ['this_month' => __('This Month'), 'last_month' => __('Last Month'), 'this_year' => __('This Year')])
        <div class="to-wrap">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div class="to-title">
                    <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                    <div class="text-muted small mt-1">@yield('page-subtitle')</div>
                </div>
                <div class="to-seg ms-auto" role="tablist" aria-label="{{ __('Period') }}">
                    @foreach($ranges as $key => $label)
                        <a href="{{ url('/transaction/overview?range=' . $key) }}" class="{{ $range === $key ? 'on' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            {{-- 1. Headline figures --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="to-card to-kpi" style="--c:#16a34a;">
                        <div class="to-card-h mb-2"><span class="to-ico"><i class="bi bi-arrow-down-left"></i></span><span class="t">{{ __('Money in') }}</span><span class="tag">{{ $ranges[$range] ?? '' }}</span></div>
                        <div class="lbl">{{ __('Total collected from customers') }}</div>
                        <div class="val" id="kpiCollected"><small>SAR</small>0</div>
                        <div class="sub"><b id="colCount">0</b> {{ __('collections') }} &middot; <b id="colPending">0</b> {{ __('pending') }}</div>
                        <div class="spark"><canvas id="sparkIn"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="to-card to-kpi" style="--c:#dc2626;">
                        <div class="to-card-h mb-2"><span class="to-ico"><i class="bi bi-arrow-up-right"></i></span><span class="t">{{ __('Money out') }}</span><span class="tag">{{ $ranges[$range] ?? '' }}</span></div>
                        <div class="lbl">{{ __('Total paid to suppliers') }}</div>
                        <div class="val" id="kpiPaid"><small>SAR</small>0</div>
                        <div class="sub"><b id="payCount">0</b> {{ __('payments') }} &middot; <b id="payPending">0</b> {{ __('pending') }}</div>
                        <div class="spark"><canvas id="sparkOut"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="to-card to-kpi" style="--c:#2563eb;">
                        <div class="to-card-h mb-2"><span class="to-ico"><i class="bi bi-arrow-left-right"></i></span><span class="t">{{ __('Net cash flow') }}</span><span class="tag">{{ $ranges[$range] ?? '' }}</span></div>
                        <div class="lbl">{{ __('Collected minus paid') }}<span class="to-delta" id="netDelta" style="display:none;"></span></div>
                        <div class="val" id="kpiNet"><small>SAR</small>0</div>
                        <div class="sub" id="netNote">&nbsp;</div>
                        <div class="spark"><canvas id="sparkNet"></canvas></div>
                    </div>
                </div>
            </div>

            {{-- 2. Secondary figures --}}
            <div class="to-card mb-4" style="padding-top: 1rem; padding-bottom: 1rem;">
                <div class="to-strip">
                    <div><span class="to-ico" style="--c:#f59e0b;"><i class="bi bi-hourglass-split"></i></span><div><div class="v" id="kpiPendingCollections">0</div><div class="l">{{ __('Pending collections') }}</div></div></div>
                    <div><span class="to-ico" style="--c:#f59e0b;"><i class="bi bi-hourglass-bottom"></i></span><div><div class="v" id="kpiPendingPayments">0</div><div class="l">{{ __('Pending payments') }}</div></div></div>
                    <div><span class="to-ico" style="--c:#7c3aed;"><i class="bi bi-bar-chart-line"></i></span><div><div class="v" id="kpiAvg">0</div><div class="l">{{ __('Average transaction') }}</div></div></div>
                    <div><span class="to-ico" style="--c:#0891b2;"><i class="bi bi-receipt"></i></span><div><div class="v" id="kpiOutstanding">0</div><div class="l">{{ __('Receivables outstanding') }}</div></div></div>
                </div>
            </div>

            {{-- 3. Trend + settlement --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="to-card">
                        <div class="to-card-h"><span class="to-ico" style="--c:#2563eb;"><i class="bi bi-graph-up"></i></span><span class="t">{{ __('Cash flow trend') }}</span><span class="tag">{{ $range === 'this_year' ? __('Monthly') : __('Weekly') }}</span></div>
                        <div class="to-chart" style="height: 280px;"><canvas id="chartCashFlow"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="to-card">
                        <div class="to-card-h"><span class="to-ico" style="--c:#0891b2;"><i class="bi bi-link-45deg"></i></span><span class="t">{{ __('Invoice settlement') }}</span><span class="tag">{{ __('All time') }}</span></div>
                        <div id="listSettlement"></div>
                    </div>
                </div>
            </div>

            {{-- 4. By account --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="to-card">
                        <div class="to-card-h"><span class="to-ico" style="--c:#16a34a;"><i class="bi bi-bank"></i></span><span class="t">{{ __('Collections by account') }}</span></div>
                        <div class="to-chart" style="height: 220px;"><canvas id="chartCollectionsByAccount"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="to-card">
                        <div class="to-card-h"><span class="to-ico" style="--c:#dc2626;"><i class="bi bi-bank2"></i></span><span class="t">{{ __('Payments by account') }}</span></div>
                        <div class="to-chart" style="height: 220px;"><canvas id="chartPaymentsByAccount"></canvas></div>
                    </div>
                </div>
            </div>

            {{-- 5. People and status --}}
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="to-card">
                        <div class="to-card-h"><span class="to-ico" style="--c:#2563eb;"><i class="bi bi-people"></i></span><span class="t">{{ __('Top customers') }}</span><span class="tag">{{ __('by collection') }}</span></div>
                        <div id="listCustomers"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="to-card">
                        <div class="to-card-h"><span class="to-ico" style="--c:#f59e0b;"><i class="bi bi-truck"></i></span><span class="t">{{ __('Top suppliers') }}</span><span class="tag">{{ __('by payment') }}</span></div>
                        <div id="listSuppliers"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="to-card">
                        <div class="to-card-h"><span class="to-ico" style="--c:#7c3aed;"><i class="bi bi-pie-chart"></i></span><span class="t">{{ __('Status') }}</span></div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small mb-2"><span class="fw-semibold">{{ __('Collections') }}</span><span class="text-muted" id="colTotal"></span></div>
                            <div id="statusCollections"></div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between small mb-2"><span class="fw-semibold">{{ __('Payments') }}</span><span class="text-muted" id="payTotal"></span></div>
                            <div id="statusPayments"></div>
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
    const RANGE = @json($range);

    const fmt = n => new Intl.NumberFormat('en-IN').format(Math.round(n));
    const money = n => '<small>SAR</small>' + fmt(n);
    function fmtShort(n) {
        const a = Math.abs(n), s = n < 0 ? '-' : '';
        if (a >= 1e6) return s + (a / 1e6).toFixed(a >= 1e7 ? 0 : 1) + 'M';
        if (a >= 1e4) return s + (a / 1e3).toFixed(0) + 'K';
        return s + fmt(a);
    }
    const esc = t => String(t ?? '').replace(/[&<>"]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]));
    const emptyHtml = '<div class="to-empty">{{ __('No data for this period') }}</div>';
    const statusColor = s => s === 'approved' ? '#16a34a' : s === 'draft' ? '#94a3b8' : s === 'cancelled' ? '#dc2626' : '#f59e0b';

    function render() {
        const d = DATA;
        const el = id => document.getElementById(id);
        const html = (id, v) => { const e = el(id); if (e) e.innerHTML = v; };
        const text = (id, v) => { const e = el(id); if (e) e.textContent = v; };

        // headline figures
        html('kpiCollected', money(d.totalCollected)); html('kpiPaid', money(d.totalPaid)); html('kpiNet', money(d.netCashFlow));
        el('kpiNet').style.color = d.netCashFlow >= 0 ? '#166534' : '#991b1b';
        text('colCount', fmt(d.collectionsCount)); text('colPending', fmt(d.pendingCollections));
        text('payCount', fmt(d.paymentsCount)); text('payPending', fmt(d.pendingPayments));
        text('kpiPendingCollections', fmt(d.pendingCollections)); text('kpiPendingPayments', fmt(d.pendingPayments));
        text('kpiAvg', 'SAR ' + fmtShort(d.avgTransactionValue));
        const settle = d.settlementSummary || [];
        text('kpiOutstanding', 'SAR ' + fmtShort(settle.length ? settle[0].outstanding : 0));

        // change against last month (this-month view only)
        const prevNet = d.monthlyComparison?.previous?.net || 0;
        if (RANGE === 'this_month' && prevNet !== 0) {
            const ch = (d.netCashFlow - prevNet) / Math.abs(prevNet) * 100;
            const p = el('netDelta'); p.style.display = ''; p.className = 'to-delta ' + (ch >= 0 ? 'up' : 'down');
            p.innerHTML = (ch >= 0 ? '&#9650; ' : '&#9660; ') + Math.abs(ch).toFixed(1) + '%';
            text('netNote', '{{ __('Compared with last month') }}: SAR ' + fmt(prevNet));
        } else {
            html('netNote', d.netCashFlow >= 0 ? '{{ __('More came in than went out') }}' : '{{ __('More went out than came in') }}');
        }

        // sparklines
        const flow = d.cashFlowTrend || {collected: [], paid: []};
        const spark = (id, data, color) => {
            const c = el(id); if (!c) return;
            new Chart(c.getContext('2d'), {type: 'line', data: {labels: data.map((_, i) => i + 1), datasets: [{data, borderColor: color, backgroundColor: color + '22', fill: true, tension: .4, pointRadius: 0, borderWidth: 2}]},
                options: {plugins: {legend: {display: false}, tooltip: {enabled: false}}, scales: {x: {display: false}, y: {display: false, beginAtZero: true}}, maintainAspectRatio: false}});
        };
        spark('sparkIn', flow.collected, '#16a34a'); spark('sparkOut', flow.paid, '#dc2626');
        spark('sparkNet', flow.collected.map((v, i) => v - (flow.paid[i] || 0)), '#2563eb');

        // trend chart
        const labels = RANGE === 'this_year'
            ? ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'].slice(0, flow.collected.length)
            : flow.collected.map((_, i) => 'W' + (i + 1));
        new Chart(el('chartCashFlow').getContext('2d'), {
            type: 'line',
            data: {labels, datasets: [
                {label: '{{ __('Collected') }}', data: flow.collected, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.10)', fill: true, tension: .35, pointRadius: 3, borderWidth: 2.5},
                {label: '{{ __('Paid') }}', data: flow.paid, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,.08)', fill: true, tension: .35, pointRadius: 3, borderWidth: 2.5}]},
            options: {plugins: {legend: {position: 'top', align: 'end', labels: {boxWidth: 10, usePointStyle: true, padding: 14, font: {size: 12}}}},
                scales: {x: {grid: {display: false}}, y: {grid: {color: 'rgba(0,0,0,.05)'}, beginAtZero: true, ticks: {callback: v => fmtShort(v)}}}, maintainAspectRatio: false, interaction: {mode: 'index', intersect: false}}
        });

        // by account: horizontal bars
        const bars = (id, rows, color) => {
            const c = el(id); if (!c) return;
            if (!rows.length) { c.parentElement.innerHTML = emptyHtml; return; }
            new Chart(c.getContext('2d'), {type: 'bar', data: {labels: rows.map(r => r.label), datasets: [{data: rows.map(r => r.value), backgroundColor: color, borderRadius: 6, barThickness: 22}]},
                options: {indexAxis: 'y', plugins: {legend: {display: false}, tooltip: {callbacks: {label: i => 'SAR ' + fmt(i.parsed.x)}}},
                    scales: {x: {grid: {color: 'rgba(0,0,0,.05)'}, beginAtZero: true, ticks: {callback: v => fmtShort(v)}}, y: {grid: {display: false}}}, maintainAspectRatio: false}});
        };
        bars('chartCollectionsByAccount', d.collectionsByAccount || [], '#16a34a');
        bars('chartPaymentsByAccount', d.paymentsByAccount || [], '#dc2626');

        // settlement
        html('listSettlement', settle.map(s => {
            const pct = (s.rate * 100).toFixed(1), c = s.rate >= .8 ? '#16a34a' : s.rate >= .4 ? '#f59e0b' : '#dc2626';
            return `<div class="to-row"><div class="to-row-top"><span class="to-row-name">${esc(s.type)}</span><span class="to-row-val" style="color:${c}">${pct}%</span></div>
                <div class="to-bar"><span style="width:${Math.max(2, pct)}%;background:${c}"></span></div>
                <div class="to-row-sub">{{ __('Settled') }} ${fmtShort(s.settled)} / ${fmtShort(s.approved)} &middot; <span class="${s.outstanding > 0 ? 'text-danger' : ''}">{{ __('Outstanding') }} ${fmtShort(s.outstanding)}</span></div></div>`;
        }).join('') || emptyHtml);

        // ranked lists
        const rank = (rows, nameFn, subFn, valFn, color) => {
            const max = Math.max(1, ...rows.map(valFn));
            return rows.map((r, i) => `<div class="to-row"><div class="to-row-top"><span class="d-flex align-items-center" style="min-width:0"><span class="to-rank">${i + 1}</span><span class="to-row-name">${esc(nameFn(r))}</span></span><span class="to-row-val">${fmtShort(valFn(r))}</span></div>
                <div class="to-row-sub" style="margin-left:2rem">${subFn(r)}</div><div class="to-bar" style="margin-left:2rem"><span style="width:${Math.max(4, valFn(r) / max * 100)}%;background:${color}"></span></div></div>`).join('');
        };
        html('listCustomers', rank(d.topCustomers || [], c => c.name, c => fmt(c.collections) + ' {{ __('collections') }}', c => c.total, '#2563eb') || emptyHtml);
        html('listSuppliers', rank(d.topSuppliers || [], s => s.name, s => fmt(s.payments) + ' {{ __('payments') }}', s => s.total, '#f59e0b') || emptyHtml);

        // status: one stacked bar each, with counts underneath
        const status = (id, totalId, rows) => {
            const total = rows.reduce((a, r) => a + r.count, 0);
            text(totalId, total ? fmt(total) + ' {{ __('in total') }}' : '');
            html(id, total
                ? `<div class="to-stack">${rows.map(r => `<span style="width:${r.count / total * 100}%;background:${statusColor(r.status)}" title="${esc(r.label)}: ${r.count}"></span>`).join('')}</div>
                   <div class="to-leg">${rows.map(r => `<span><i style="background:${statusColor(r.status)}"></i>${esc(r.label)}<b>${fmt(r.count)}</b></span>`).join('')}</div>`
                : '<div class="to-empty" style="padding:.6rem 0">{{ __('No data for this period') }}</div>');
        };
        status('statusCollections', 'colTotal', d.collectionStatuses || []);
        status('statusPayments', 'payTotal', d.paymentStatuses || []);
    }
    document.addEventListener('DOMContentLoaded', render);
    </script>
</x-app-layout>
