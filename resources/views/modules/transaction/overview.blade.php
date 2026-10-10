@section('page-title', __('Transactions Overview'))
@section('hide-topbar', true)
@section('page-subtitle', __('Real-time payments & collections dashboard'))
@section('print-footer')
<script>
    window.printFooter = {
        show: true,
        custom: '{{ __('Transactions Overview') }} - {{ __('Generated on') }} {{ date('d-m-Y H:i') }}'
    };
</script>
@endsection
<x-app-layout>
    <div class="bg-light pb-4">
    <div class="jw-page pt-2">
        <style>
            :root { --txn_bg: #f8fafc; }
            body { background: var(--txn_bg); }
            .jw {
                --kbg: color-mix(in srgb, var(--kc) 8%, #fff);
                background: var(--kbg); border-radius: 16px; padding: 12px 14px; height: 100%;
                box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(15,23,42,.07);
            }
            .jw.white { background: #fff; }
            .jw-head { display: flex; align-items: center; gap: 10px; }
            .jw-icon { width: 26px; height: 26px; border-radius: 50%; background: var(--kc); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; flex-shrink: 0; box-shadow: 0 3px 8px color-mix(in srgb, var(--kc) 38%, transparent); }
            .jw-title { font-weight: 600; font-size: .82rem; color: #344054; flex: 1; min-width: 0; line-height: 1.2; }
            .jw-tag { font-size: .66rem; font-weight: 600; background: #fff; border: 1px solid #e5e7eb; color: #475467; border-radius: 999px; padding: 2px 8px; white-space: nowrap; }
            .jw-value-row { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
            .jw-value { font-size: 1.55rem; font-weight: 700; letter-spacing: -.02em; color: #101828; line-height: 1.1; }
            .jw-pill { font-size: .66rem; font-weight: 600; border-radius: 8px; padding: 2px 8px; background: #e0f2fe; color: #0369a1; }
            .jw-pill.up { background: #dcfce7; color: #15803d; }
            .jw-pill.down { background: #fee2e2; color: #b91c1c; }
            .jw-note { font-size: .72rem; color: #667085; margin-top: 2px; }
            .jw-track { height: 5px; border-radius: 99px; background: color-mix(in srgb, var(--kc, #94a3b8) 16%, #e5e7eb); overflow: hidden; margin-top: 8px; }
            .jw-track > span { display: block; height: 100%; border-radius: 99px; background: var(--kc, #0b6aa0); transition: width .5s; }
            .jw-legend { display: flex; justify-content: space-between; gap: 6px; margin-top: 8px; }
            .jw-legend > div { flex: 1; background: rgba(255,255,255,.75); border-radius: 9px; padding: 4px 8px; font-size: .68rem; color: #667085; display: flex; align-items: center; justify-content: space-between; gap: 4px; }
            .jw-legend strong { font-size: .8rem; color: #101828; margin-left: auto; }
            .jw-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; background: var(--kc); }
            .jw-split { display: flex; align-items: center; gap: 8px; }
            .jw-left { flex: 1; min-width: 0; }
            .jw-mini { position: relative; width: 96px; height: 96px; flex-shrink: 0; }
            /* compact stat widgets */
            .jw-sm { padding: 12px 14px; border-radius: 16px; display: flex; flex-direction: row; align-items: center; justify-content: flex-start; gap: 12px; }
            .jw-sm .jw-txt { display: flex; flex-direction: column; min-width: 0; width: 100%; }
            .jw-sm .jw-title { font-size: .72rem; color: #667085; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .jw-sm-value { font-size: 1.3rem; font-weight: 700; color: #101828; line-height: 1.1; }
            .trend-up { color: #16a34a !important; }
            .trend-down { color: #dc2626 !important; }
            /* chart / list widgets */
            .jw-body { margin-top: 8px; }
            .jw-row { display: flex; align-items: flex-start; gap: 8px; padding: 5px 0; }
            .jw-row + .jw-row { border-top: 1px dashed #eef0f3; }
            .jw-rank { width: 20px; height: 20px; border-radius: 50%; background: #f1f5f9; color: #475467; font-size: .66rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
            .jw-row-main { flex: 1; min-width: 0; }
            .jw-row-top { display: flex; justify-content: space-between; gap: 8px; font-size: .78rem; }
            .jw-row-name { font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .jw-row-val { font-weight: 700; color: #101828; }
            .jw-row-sub { font-size: .66rem; color: #94a3b8; }
            .jw-row .jw-track { margin-top: 3px; height: 4px; }
            .jw-stack { display: flex; height: 10px; border-radius: 99px; overflow: hidden; background: #eef0f3; }
            .jw-stack > span { display: block; height: 100%; }
            .jw-legend-item { display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border-radius: 999px; padding: 3px 10px; font-size: .72rem; margin: 8px 6px 0 0; color: #475467; }
            .jw-legend-item .jw-dot { margin: 0; }
            .jw-chart { position: relative; height: 150px; }
            .jw-chart.sm { height: 140px; }
        </style>

        <div class="container-fluid px-lg-5" style="max-width:1300px;margin-left:auto;margin-right:auto;">
            <style>
                .jo-title { display: none; }
                body:not(.has-top-header) .jo-title { display: block; }
            </style>
            @php($rangeLabel = ['this_month' => __('This Month'), 'last_month' => __('Last Month'), 'this_year' => __('This Year')][$range] ?? '')
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="jo-title">
                    <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                    <div class="text-muted small mt-1">@yield('page-subtitle')</div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div id="dateRangeWrap" style="min-width:170px;"><select id="dateRange" class="tom-select" data-placeholder="{{ __('This Month') }}">
                        <option value="this_month" {{ $range==='this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                        <option value="last_month" {{ $range==='last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                        <option value="this_year" {{ $range==='this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                    </select></div>
                    <button class="btn btn-primary btn-sm px-3" id="btn-apply"><i class="bi bi-arrow-repeat me-1"></i> {{ __('Apply') }}</button>
                </div>
            </div>

            {{-- Main widgets --}}
            <div class="row g-2 mb-2">
                <div class="col-xl-3 col-md-6">
                    <div class="jw" style="--kc:#16a34a;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-wallet2"></i></span><span class="jw-title">{{ __('Total Collected') }}</span></div>
                        <div class="jw-split"><div class="jw-left">
                            <div class="jw-value-row"><span class="jw-value" id="kpiCollected">0</span></div>
                            <div class="jw-note">{{ $rangeLabel }} &middot; {{ __('from customers') }}</div>
                        </div><div class="jw-mini"><canvas id="miniCollected"></canvas></div></div>
                        <div class="jw-legend">
                            <div>{{ __('Collections') }}<strong id="colCount">0</strong></div>
                            <div><span class="jw-dot" style="background:#f59e0b"></span>{{ __('Pending') }}<strong id="colPending">0</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw" style="--kc:#dc2626;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-cash-coin"></i></span><span class="jw-title">{{ __('Total Paid') }}</span></div>
                        <div class="jw-split"><div class="jw-left">
                            <div class="jw-value-row"><span class="jw-value" id="kpiPaid">0</span></div>
                            <div class="jw-note">{{ $rangeLabel }} &middot; {{ __('to suppliers') }}</div>
                        </div><div class="jw-mini"><canvas id="miniPaid"></canvas></div></div>
                        <div class="jw-legend">
                            <div>{{ __('Payments') }}<strong id="payCount">0</strong></div>
                            <div><span class="jw-dot" style="background:#f59e0b"></span>{{ __('Pending') }}<strong id="payPending">0</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw" style="--kc:#0b6aa0;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-arrow-left-right"></i></span><span class="jw-title">{{ __('Net Cash Flow') }}</span></div>
                        <div class="jw-split"><div class="jw-left">
                            <div class="jw-value-row"><span class="jw-value" id="kpiNetCashFlow">0</span><span class="jw-pill" id="netDelta"></span></div>
                            <div class="jw-note">{{ __('Collected minus paid') }}</div>
                        </div><div class="jw-mini"><canvas id="miniNet"></canvas></div></div>
                        <div class="jw-legend">
                            <div><span class="jw-dot" style="background:#16a34a"></span>{{ __('Collected') }}<strong id="netIn">0</strong></div>
                            <div><span class="jw-dot" style="background:#dc2626"></span>{{ __('Paid') }}<strong id="netOut">0</strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw" style="--kc:#5b57ae;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-bar-chart-line"></i></span><span class="jw-title">{{ __('Avg Transaction') }}</span></div>
                        <div class="jw-split"><div class="jw-left">
                            <div class="jw-value-row"><span class="jw-value" id="kpiAvgTransaction">0</span></div>
                            <div class="jw-note">{{ __('Avg value per transaction') }}</div>
                        </div><div class="jw-mini"><canvas id="miniMix"></canvas></div></div>
                        <div class="jw-legend">
                            <div><span class="jw-dot" style="background:#16a34a"></span>{{ __('Collections') }}<strong id="mixIn">0</strong></div>
                            <div><span class="jw-dot" style="background:#dc2626"></span>{{ __('Payments') }}<strong id="mixOut">0</strong></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Small widgets --}}
            <div class="row g-2 mb-2">
                @foreach([
                    ['kpiPaymentsCount', '#dc2626', 'bi-arrow-up-right-circle', __('Payments'), '0'],
                    ['kpiCollectionsCount', '#16a34a', 'bi-arrow-down-left-circle', __('Collections'), '0'],
                    ['kpiPendingPayments', '#f59e0b', 'bi-hourglass-split', __('Pending Payments'), '0'],
                    ['kpiPendingCollections', '#f59e0b', 'bi-hourglass-bottom', __('Pending Collections'), '0'],
                    ['kpiNetChange', '#0b6aa0', 'bi-graph-up', __('vs Last Month (Net)'), '0%'],
                    ['kpiOutstanding', '#5b57ae', 'bi-receipt', __('Receivables Outstanding'), '0'],
                ] as [$id, $c, $ic, $label, $zero])
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="jw jw-sm" style="--kc:{{ $c }};">
                            <span class="jw-icon"><i class="bi {{ $ic }}"></i></span>
                            <div class="jw-txt"><span class="jw-sm-value" id="{{ $id }}">{{ $zero }}</span><span class="jw-title" style="flex:none;">{{ $label }}</span></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Eight widgets, one medium width each --}}
            <div class="row g-2 mb-2">
                @foreach([
                    ['chartCashFlow', '#0b6aa0', 'bi-graph-up', __('Cash Flow Trend'), $range === 'this_year' ? __('Monthly') : __('Weekly')],
                    ['chartCollectionsByAccount', '#16a34a', 'bi-pie-chart', __('Collections by Account'), __('Top 6')],
                    ['chartPaymentsByAccount', '#dc2626', 'bi-bank', __('Payments by Account'), null],
                ] as [$id, $c, $ic, $label, $tag])
                    <div class="col-xl-3 col-md-6">
                        <div class="jw white" style="--kc:{{ $c }};">
                            <div class="jw-head"><span class="jw-icon"><i class="bi {{ $ic }}"></i></span><span class="jw-title">{{ $label }}</span>@if($tag)<span class="jw-tag">{{ $tag }}</span>@endif</div>
                            <div class="jw-body jw-chart"><canvas id="{{ $id }}"></canvas></div>
                        </div>
                    </div>
                @endforeach
                <div class="col-xl-3 col-md-6">
                    <div class="jw white" style="--kc:#0b6aa0;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-link-45deg"></i></span><span class="jw-title">{{ __('Invoice Settlement') }}</span><span class="jw-tag">{{ __('All-time') }}</span></div>
                        <div class="jw-body" id="listSettlement" style="min-height:150px;"></div>
                    </div>
                </div>
                @foreach([
                    ['chartPaymentStatus', '#f59e0b', 'bi-cash-coin', __('Payment Status')],
                    ['chartCollectionStatus', '#8b5cf6', 'bi-wallet2', __('Collection Status')],
                ] as [$id, $c, $ic, $label])
                    <div class="col-xl-3 col-md-6">
                        <div class="jw white" style="--kc:{{ $c }};">
                            <div class="jw-head"><span class="jw-icon"><i class="bi {{ $ic }}"></i></span><span class="jw-title">{{ $label }}</span></div>
                            <div class="jw-body jw-chart"><canvas id="{{ $id }}"></canvas></div>
                        </div>
                    </div>
                @endforeach
                <div class="col-xl-3 col-md-6">
                    <div class="jw white" style="--kc:#f59e0b;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-truck"></i></span><span class="jw-title">{{ __('Top Suppliers by Payment') }}</span></div>
                        <div class="jw-body" id="listSuppliers" style="max-height:210px;overflow:auto;"></div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="jw white" style="--kc:#0b6aa0;">
                        <div class="jw-head"><span class="jw-icon"><i class="bi bi-people"></i></span><span class="jw-title">{{ __('Top Customers by Collection') }}</span></div>
                        <div class="jw-body" id="listCustomers" style="max-height:210px;overflow:auto;"></div>
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
    // compact money for the small widgets: 1.2M, 340K
    function fmtShort(n) {
        const a = Math.abs(n), s = n < 0 ? '-' : '';
        if (a >= 1e6) return s + (a / 1e6).toFixed(a >= 1e7 ? 0 : 1) + 'M';
        if (a >= 1e4) return s + (a / 1e3).toFixed(0) + 'K';
        return s + fmt(a);
    }

    function render() {
        const d = DATA;
        const setText = (id, v) => { const el = document.getElementById(id); if (el) el.innerText = v; };
        const empty = '<div class="text-center text-muted py-4 small">{{ __('No data') }}</div>';

        // Main widgets
        setText('kpiCollected', fmtShort(d.totalCollected));
        setText('kpiPaid', fmtShort(d.totalPaid));
        const netEl = document.getElementById('kpiNetCashFlow');
        netEl.innerText = fmtShort(d.netCashFlow);
        netEl.style.color = d.netCashFlow >= 0 ? '#16a34a' : '#dc2626';
        setText('kpiAvgTransaction', fmtShort(d.avgTransactionValue));
        setText('colCount', fmt(d.collectionsCount)); setText('colPending', fmt(d.pendingCollections));
        setText('payCount', fmt(d.paymentsCount)); setText('payPending', fmt(d.pendingPayments));
        setText('netIn', fmtShort(d.totalCollected)); setText('netOut', fmtShort(d.totalPaid));
        setText('mixIn', fmt(d.collectionsCount)); setText('mixOut', fmt(d.paymentsCount));

        // Change versus last month
        const mc = d.monthlyComparison || {};
        const prevNet = mc.previous?.net || 0;
        const netChange = prevNet !== 0 ? ((d.netCashFlow - prevNet) / Math.abs(prevNet)) * 100 : 0;
        const chgEl = document.getElementById('kpiNetChange');
        chgEl.innerText = (netChange >= 0 ? '+' : '') + netChange.toFixed(1) + '%';
        chgEl.className = 'jw-sm-value ' + (netChange >= 0 ? 'trend-up' : 'trend-down');
        const pill = document.getElementById('netDelta');
        if ('{{ $range }}' === 'this_month' && prevNet !== 0) {
            pill.className = 'jw-pill ' + (netChange >= 0 ? 'up' : 'down');
            pill.innerHTML = (netChange >= 0 ? '&#9650; ' : '&#9660; ') + Math.abs(netChange).toFixed(1) + '%';
        } else { pill.style.display = 'none'; }

        // Small widgets
        setText('kpiPaymentsCount', fmt(d.paymentsCount));
        setText('kpiCollectionsCount', fmt(d.collectionsCount));
        setText('kpiPendingPayments', fmt(d.pendingPayments));
        setText('kpiPendingCollections', fmt(d.pendingCollections));
        const settle = d.settlementSummary || [];
        setText('kpiOutstanding', fmtShort(settle.length ? settle[0].outstanding : 0));

        // Ranked lists with a share bar behind each row
        const rankRows = (rows, nameFn, subFn, valFn, color) => {
            const max = Math.max(1, ...rows.map(valFn));
            return rows.map((r, i) => `<div class="jw-row">
                <span class="jw-rank">${i + 1}</span>
                <div class="jw-row-main">
                    <div class="jw-row-top"><span class="jw-row-name">${nameFn(r)}</span><span class="jw-row-val">${fmtShort(valFn(r))}</span></div>
                    ${subFn ? `<div class="jw-row-sub">${subFn(r)}</div>` : ''}
                    <div class="jw-track"><span style="width:${Math.max(4, valFn(r) / max * 100)}%;background:${color};"></span></div>
                </div></div>`).join('');
        };
        document.getElementById('listSuppliers').innerHTML = rankRows(d.topSuppliers || [], s => s.name, s => fmt(s.payments) + ' {{ __('Payments') }}', s => s.total, '#f59e0b') || empty;
        document.getElementById('listCustomers').innerHTML = rankRows(d.topCustomers || [], c => c.name, c => fmt(c.collections) + ' {{ __('Collections') }}', c => c.total, '#0b6aa0') || empty;

        // Settlement: one row per invoice type with its settlement rate
        document.getElementById('listSettlement').innerHTML = settle.map(s => {
            const ratePct = (s.rate * 100).toFixed(1);
            const color = s.rate >= 0.8 ? '#16a34a' : s.rate >= 0.4 ? '#f59e0b' : '#dc2626';
            return `<div class="jw-row" style="display:block;">
                <div class="jw-row-top"><span class="jw-row-name">${s.type}</span><span class="jw-row-val" style="color:${color};">${ratePct}%</span></div>
                <div class="jw-track"><span style="width:${Math.max(2, ratePct)}%;background:${color};"></span></div>
                <div class="jw-row-sub">{{ __('Settled') }} ${fmtShort(s.settled)} / ${fmtShort(s.approved)} &middot; <span class="${s.outstanding > 0 ? 'text-danger' : ''}">{{ __('Outstanding') }} ${fmtShort(s.outstanding)}</span></div>
            </div>`;
        }).join('') || empty;

        // Mini charts inside the main widgets
        const flow = d.cashFlowTrend || { collected: [], paid: [] };
        const mini = (id, cfg) => { const el = document.getElementById(id); if (el) new Chart(el.getContext('2d'), cfg); };
        const spark = (data, color, fill) => ({ type: 'line', data: { labels: data.map((_, i) => i + 1), datasets: [{ data, borderColor: color, backgroundColor: fill, fill: true, tension: .4, pointRadius: 0, borderWidth: 2 }] },
            options: { plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false, beginAtZero: true } }, maintainAspectRatio: false } });
        const dn = (vals, colors) => ({ type: 'doughnut', data: { datasets: [{ data: vals, backgroundColor: colors, borderWidth: 0 }] },
            options: { cutout: '70%', plugins: { legend: { display: false }, tooltip: { enabled: false } }, maintainAspectRatio: false } });
        mini('miniCollected', spark(flow.collected, '#16a34a', 'rgba(22,163,74,.15)'));
        mini('miniPaid', spark(flow.paid, '#dc2626', 'rgba(220,38,38,.15)'));
        const inV = Math.max(0, d.totalCollected), outV = Math.max(0, d.totalPaid);
        mini('miniNet', dn(inV + outV ? [inV, outV] : [1], inV + outV ? ['#16a34a', '#dc2626'] : ['#e5e7eb']));
        const cIn = d.collectionsCount || 0, cOut = d.paymentsCount || 0;
        mini('miniMix', dn(cIn + cOut ? [cIn, cOut] : [1], cIn + cOut ? ['#16a34a', '#dc2626'] : ['#e5e7eb']));

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
                        label: '{{ __('Collected') }}',
                        data: trend.collected,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        borderWidth: 2.5
                    },
                    {
                        label: '{{ __('Paid') }}',
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

        // Collections by account — vertical bar
        new Chart(document.getElementById('chartCollectionsByAccount').getContext('2d'), {
            type: 'bar',
            data: {
                labels: (d.collectionsByAccount || []).map(c => c.label),
                datasets: [{
                    data: (d.collectionsByAccount || []).map(c => c.value),
                    backgroundColor: colorPalette.slice(0, Math.max((d.collectionsByAccount || []).length, 1)),
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

        // Payments by account — horizontal bar
        new Chart(document.getElementById('chartPaymentsByAccount').getContext('2d'), {
            type: 'bar',
            data: {
                labels: (d.paymentsByAccount || []).map(c => c.label),
                datasets: [{
                    data: (d.paymentsByAccount || []).map(c => c.value),
                    backgroundColor: colorPalette.slice(0, Math.max((d.paymentsByAccount || []).length, 1)),
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

        // Payment status — doughnut
        const paymentStatuses = d.paymentStatuses || [];
        new Chart(document.getElementById('chartPaymentStatus').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: paymentStatuses.map(s => s.label),
                datasets: [{
                    data: paymentStatuses.map(s => s.count),
                    backgroundColor: paymentStatuses.map(s =>
                        s.status === 'approved' ? '#16a34a' : s.status === 'draft' ? '#94a3b8' : s.status === 'cancelled' ? '#dc2626' : '#f59e0b'
                    ),
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } } },
                maintainAspectRatio: false
            }
        });

        // Collection status — "split" doughnut with gaps between segments
        const collectionStatuses = d.collectionStatuses || [];
        new Chart(document.getElementById('chartCollectionStatus').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: collectionStatuses.map(s => s.label),
                datasets: [{
                    data: collectionStatuses.map(s => s.count),
                    backgroundColor: collectionStatuses.map(s =>
                        s.status === 'approved' ? '#16a34a' : s.status === 'draft' ? '#94a3b8' : s.status === 'cancelled' ? '#dc2626' : '#f59e0b'
                    ),
                    borderWidth: 0,
                    spacing: 6,
                    borderRadius: 6
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } } },
                maintainAspectRatio: false
            }
        });
    }

    document.addEventListener('DOMContentLoaded', render);
    // Period dropdown: shared tom-select styling (the underlying <select> still holds the value).
    document.addEventListener('DOMContentLoaded', function () {
        if (window.initTomSelectForm && window.jQuery) { initTomSelectForm($('#dateRangeWrap')); }
    });

    document.getElementById('btn-apply').addEventListener('click', () => {
        const range = document.getElementById('dateRange').value;
        window.location.href = '/transaction/overview?range=' + range;
    });
    </script>
</x-app-layout>
