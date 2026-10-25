@section('page-title', __('Sales Commission'))
@section('hide-topbar', true)
@section('page-subtitle', __('Incentive on gross profit, paid by when the customer paid'))
@php
    $sum = $report['summary'];
    $one = $report['single'];   // one salesperson chosen: show their rates; all: each person is paid at their own rates
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.') . '%';
    $lateDays = $one['late_days'] ?? null;
    $f = fn ($n) => number_format((float) $n, 2);
    $periods = ['this_quarter' => __('This Quarter'), 'last_quarter' => __('Last Quarter'), 'this_year' => __('This Year'), 'custom' => __('Custom')];
    $query = request()->except(['export']);
@endphp
<x-app-layout>
    <div class="bg-light pb-5">
    <div class="sc-page pt-3">
        <style>
            body { background: #f8fafc; }
            .sc-wrap { max-width: 1320px; margin: 0 auto; padding: 0 1.5rem; }
            .sc-title { display: none; }
            body:not(.has-top-header) .sc-title { display: block; }
            .sc-card { background: #fff; border: 1px solid #e8ecf1; border-radius: 14px; padding: 1.25rem 1.4rem; }
            .sc-h { font-weight: 600; font-size: .95rem; color: #1f2937; margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
            .sc-h .tag { margin-left: auto; font-size: .72rem; color: #6b7280; background: #f3f4f6; border-radius: 999px; padding: .15rem .6rem; font-weight: 500; }
            .sc-seg { display: inline-flex; background: #eef1f5; border-radius: 10px; padding: 3px; }
            .sc-seg a { padding: .35rem .9rem; border-radius: 8px; font-size: .84rem; font-weight: 500; color: #4b5563; text-decoration: none; white-space: nowrap; }
            .sc-seg a.on { background: #fff; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,.08); font-weight: 600; }
            .sc-filters label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; font-weight: 600; margin-bottom: .25rem; }

            .sc-months { display: grid; grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)); border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
            .sc-months > div { padding: .7rem 1rem; border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; margin: 0 -1px -1px 0; }
            .sc-months .l { font-size: .74rem; color: #6b7280; }
            .sc-months .v { font-size: 1.05rem; font-weight: 700; color: #111827; }
            .sc-months .tot { background: #f8fafc; }

            .sc-calc { width: 100%; border-collapse: collapse; }
            .sc-calc td { padding: .6rem .5rem; border-bottom: 1px solid #f1f3f6; vertical-align: middle; }
            .sc-calc td.n { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
            .sc-calc .lbl { color: #374151; }
            .sc-calc .hint { font-size: .76rem; color: #9ca3af; }
            .sc-rate { display: inline-block; min-width: 3.2rem; text-align: center; font-size: .78rem; font-weight: 700; border-radius: 999px; padding: .12rem .5rem; }
            .sc-rate.ok { background: #dcfce7; color: #166534; } .sc-rate.mid { background: #fef3c7; color: #92400e; } .sc-rate.zero { background: #fee2e2; color: #991b1b; }
            .sc-pay td { border-bottom: 0; border-top: 2px solid #111827; font-weight: 800; font-size: 1.05rem; padding-top: .8rem; }
            .sc-pay .amt { background: #ecfdf5; color: #166534; border-radius: 8px; padding: .35rem .8rem; }

            .sc-kpi .l { font-size: .78rem; color: #6b7280; }
            .sc-kpi .v { font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.2; }
            .sc-prog { height: 8px; background: #eef1f5; border-radius: 99px; overflow: hidden; margin-top: .6rem; }
            .sc-prog > span { display: block; height: 100%; border-radius: 99px; }

            .sc-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
            .sc-table th { background: #f8fafc; color: #4b5563; font-size: .7rem; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; padding: .6rem .6rem; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
            .sc-table td { padding: .55rem .6rem; border-bottom: 1px solid #f1f3f6; vertical-align: top; }
            .sc-table td.nw { white-space: nowrap; }
            .sc-table td.n, .sc-table th.n { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
            .sc-table tfoot td { font-weight: 700; border-top: 2px solid #e5e7eb; background: #f8fafc; }
            .sc-st { font-size: .74rem; border-radius: 999px; padding: .1rem .55rem; white-space: nowrap; display: inline-block; }
            .sc-st.paid { background: #dcfce7; color: #166534; } .sc-st.pend { background: #fee2e2; color: #991b1b; }
            .neg { color: #b91c1c; }

            @media print {
                .no-print, .sc-filters, .sc-seg { display: none !important; }
                body, .bg-light { background: #fff !important; }
                .sc-wrap { max-width: none; padding: 0; }
                .sc-card { border: 1px solid #ddd; break-inside: avoid; }
                .sc-print-title { display: block !important; }
            }
            .sc-print-title { display: none; }
        </style>

        <div class="sc-wrap">
            <div class="sc-print-title mb-3">
                <h4 class="fw-bold mb-0">{{ __('Sales Commission') }}</h4>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($from)->format('d-m-Y') }} &ndash; {{ \Carbon\Carbon::parse($to)->format('d-m-Y') }}@if($salespersonId) &middot; {{ $salespeople[$salespersonId] ?? '' }}@endif</div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 no-print">
                <div class="sc-title">
                    <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                    <div class="text-muted small mt-1">@yield('page-subtitle')</div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div class="sc-seg">
                        @foreach(array_slice($periods, 0, 3, true) as $key => $label)
                            <a href="{{ url('/reports/sales-commission?' . http_build_query(array_merge($query, ['period' => $key, 'from' => null, 'to' => null]))) }}" class="{{ $period === $key ? 'on' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                    <a class="btn btn-outline-primary btn-sm" href="{{ url('/reports/customer-wise-sales?' . http_build_query($query)) }}"><i class="bi bi-list-ul me-1"></i>{{ __('Customer wise sales') }}</a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>{{ __('Print') }}</button>
                    <a class="btn btn-outline-success btn-sm" href="{{ url('/reports/sales-commission?' . http_build_query(array_merge($query, ['export' => 'xlsx']))) }}"><i class="bi bi-file-earmark-excel me-1"></i>{{ __('Excel') }}</a>
                </div>
            </div>

            {{-- filters + the scheme's numbers --}}
            <form method="get" action="{{ url('/reports/sales-commission') }}" class="sc-card sc-filters mb-4">
                <input type="hidden" name="period" value="custom">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-2"><label>{{ __('From') }}</label><input type="text" name="from" class="form-control form-control-sm datepicker" value="{{ \Carbon\Carbon::parse($from)->format('d-m-Y') }}"></div>
                    <div class="col-6 col-md-2"><label>{{ __('To') }}</label><input type="text" name="to" class="form-control form-control-sm datepicker" value="{{ \Carbon\Carbon::parse($to)->format('d-m-Y') }}"></div>
                    <div class="col-12 col-md-4"><label>{{ __('Salesperson') }}</label>
                        <select name="salesperson" class="tom-select form-select-sm">
                            <option value="">{{ __('All salespeople') }}</option>
                            @foreach($salespeople as $id => $name)<option value="{{ $id }}" @selected($salespersonId == $id)>{{ $name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-arrow-repeat me-1"></i>{{ __('Apply') }}</button></div>
                </div>
            </form>

            <div class="row g-4 mb-4">
                {{-- the incentive calculation, laid out like the sheet --}}
                <div class="col-lg-7">
                    <div class="sc-card h-100">
                        <div class="sc-h"><i class="bi bi-calculator text-primary"></i>{{ __('Incentive calculation') }}<span class="tag">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span></div>
                        <div class="sc-months mb-3">
                            @foreach($sum['months'] as $m)
                                <div><div class="l">{{ __($m['label']) }}</div><div class="v {{ $m['income'] < 0 ? 'neg' : '' }}">{{ $f($m['income']) }}</div></div>
                            @endforeach
                            <div class="tot"><div class="l">{{ __('Gross profit') }}</div><div class="v">{{ $f($sum['income']) }}</div></div>
                        </div>
                        <table class="sc-calc">
                            @if($sum['target'] > 0)
                                <tr><td class="lbl">{{ __('Target to be achieved') }}</td><td></td><td class="n">{{ $f($sum['target']) }}</td><td></td></tr>
                            @endif
                            <tr><td class="lbl">{{ __('Actual income earned') }}</td><td></td><td class="n fw-semibold">{{ $f($sum['income']) }}</td><td></td></tr>
                            <tr><td class="lbl">{{ __('Eligible to pay') }}<div class="hint">{{ __('Collected on or before the due date') }}</div></td><td><span class="sc-rate ok">{{ $one ? $pct($one['rate_on_time']) : __('Own rate') }}</span></td><td class="n">{{ $f($sum['on_time']) }}</td><td class="n">{{ $f($sum['pay_on_time']) }}</td></tr>
                            <tr><td class="lbl">{{ __('Eligible to pay') }}<div class="hint">{{ $lateDays ? __('Collected late, within :n days of the invoice', ['n' => $lateDays]) : __('Collected late, within the salesperson\'s late limit') }}</div></td><td><span class="sc-rate mid">{{ $one ? $pct($one['rate_late']) : __('Own rate') }}</span></td><td class="n">{{ $f($sum['late']) }}</td><td class="n">{{ $f($sum['pay_late']) }}</td></tr>
                            <tr><td class="lbl">{{ __('Reversal of G/P value') }}<div class="hint">{{ $lateDays ? __('Collected after :n days', ['n' => $lateDays]) : __('Collected after the late limit') }}</div></td><td><span class="sc-rate zero">0%</span></td><td class="n">{{ $f($sum['over']) }}</td><td class="n text-muted">0.00</td></tr>
                            <tr><td class="lbl">{{ __('Reversal of G/P value') }}<div class="hint">{{ __('Not collected yet') }}</div></td><td><span class="sc-rate zero">0%</span></td><td class="n">{{ $f($sum['pending']) }}</td><td class="n text-muted">0.00</td></tr>
                            <tr class="sc-pay"><td colspan="3">{{ __('Incentive payable') }}</td><td class="n"><span class="amt">{{ $f($sum['incentive']) }}</span></td></tr>
                        </table>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="sc-card mb-4">
                        <div class="row g-3 sc-kpi">
                            <div class="col-6"><div class="l">{{ __('Invoiced (ex VAT)') }}</div><div class="v">{{ $f($sum['invoiced']) }}</div></div>
                            <div class="col-6"><div class="l">{{ __('Job cost') }}</div><div class="v">{{ $f($sum['cost']) }}</div></div>
                            <div class="col-6"><div class="l">{{ __('Gross profit') }}</div><div class="v">{{ $f($sum['income']) }}</div></div>
                            <div class="col-6"><div class="l">{{ __('Invoices') }}</div><div class="v">{{ $sum['count'] }}</div></div>
                        </div>
                        @if($sum['target'] > 0)
                            @php($ach = min(100, max(0, $sum['achievement'])))
                            <div class="d-flex justify-content-between small mt-3"><span class="text-muted">{{ __('Target achievement') }}</span><b>{{ $sum['achievement'] }}%</b></div>
                            <div class="sc-prog"><span style="width: {{ $ach }}%; background: {{ $sum['achievement'] >= 100 ? '#16a34a' : '#f59e0b' }};"></span></div>
                        @endif
                    </div>
                    <div class="sc-card">
                        <div class="sc-h"><i class="bi bi-people text-primary"></i>{{ __('By salesperson') }}</div>
                        @forelse($report['people'] as $person)
                            <div class="d-flex justify-content-between align-items-baseline py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                                <div><div class="fw-semibold">{{ $person['name'] }}</div><div class="small text-muted">{{ $person['scheme'] }}</div><div class="small text-muted">{{ __('Profit') }} {{ $f($person['income']) }} &middot; {{ $person['count'] }} {{ __('invoices') }}@if($person['target'] > 0) &middot; {{ __('Target') }} {{ $person['achievement'] }}%@endif</div></div>
                                <div class="fw-bold">{{ $f($person['incentive']) }}</div>
                            </div>
                        @empty
                            <div class="text-muted small text-center py-3">{{ __('No invoices in this period') }}</div>
                        @endforelse
                    </div>
                    @if($one && $one['slabs'])
                        <div class="sc-card mt-4">
                            <div class="sc-h"><i class="bi bi-bar-chart-steps text-primary"></i>{{ __('Commission slabs') }}<span class="tag">{{ $one['name'] }}</span></div>
                            <table class="sc-table">
                                <thead><tr><th>{{ __('Gross profit') }}</th><th class="n">{{ __('On time') }}</th><th class="n">{{ __('Late') }}</th></tr></thead>
                                <tbody>
                                @foreach($one['slabs'] as $i => $slab)
                                    <tr @if($one['slab_index'] === $i) style="background:#ecfdf5;font-weight:600;" @endif>
                                        <td>{{ $f($slab['from']) }} &ndash; {{ ($slab['to'] ?? null) !== null && $slab['to'] !== '' ? $f($slab['to']) : __('and above') }}@if($one['slab_index'] === $i) <span class="sc-st paid ms-1">{{ __('reached') }}</span>@endif</td>
                                        <td class="n">{{ $pct($slab['rate']) }}</td><td class="n">{{ $pct($slab['late_rate']) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.initTomSelectForm && window.jQuery) initTomSelectForm($('.sc-filters'));
            if (window.datepicker) datepicker();
        });
    </script>
</x-app-layout>
