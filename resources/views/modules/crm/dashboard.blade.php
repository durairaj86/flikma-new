@section('page-title', __('CRM Dashboard'))
@section('hide-topbar', true)
@php
    use App\Models\Crm\CrmAction;
    $staleDays = \App\Services\Crm\CrmSettings::get('stale_days');
    $num = fn($v) => $v ? amountFormat($v) : '—';
    $pct = fn($v) => $v === null ? '—' : $v . '%';
@endphp
<x-app-layout>
    <style>
        .cd-page { padding: 1rem 1rem 3rem; }
        .cd-tile { border: 1px solid #e8ecf1; border-radius: 12px; padding: .8rem 1rem; background: #fff; height: 100%; color: inherit; text-decoration: none; display: block; }
        a.cd-tile:hover, a.cd-tile:focus { border-color: #93c5fd; outline: none; color: inherit; }
        .cd-tile .v { font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.2; }
        .cd-tile .l { font-size: .78rem; color: #6b7280; }
        .cd-tile.warn .v { color: #b91c1c; }
        .cd-card { border: 1px solid #e8ecf1; border-radius: 12px; background: #fff; margin-bottom: 1rem; }
        .cd-card h6 { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin: 0; padding: .8rem 1rem; border-bottom: 1px solid #eef0f3; }
        .cd-card table { margin: 0; font-size: .82rem; }
        .cd-card td, .cd-card th { padding: .45rem .7rem; white-space: nowrap; }
        .cd-card th { color: #6b7280; font-weight: 600; background: #f9fafb; }
        .cd-cat { display: inline-block; border-radius: 999px; padding: 1px 9px; font-weight: 600; font-size: .75rem; }
        .cd-zero { color: #c4c9d2; }
        .cd-action td:first-child { padding-inline-start: 2rem; color: #4b5563; }
        .cd-toggle { border: 0; background: none; padding: 0; color: inherit; font: inherit; }
        .cd-toggle i { transition: transform .15s; display: inline-block; }
        .cd-toggle[aria-expanded="true"] i { transform: rotate(90deg); }
        [dir="rtl"] .cd-toggle[aria-expanded="false"] i { transform: scaleX(-1); }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .cd-page { padding: 0 !important; background: #fff !important; }
            .cd-card, .cd-tile { break-inside: avoid; }
            .cd-toggle i { display: none; }
            .table-responsive { overflow: visible !important; }
        }
    </style>
    <main class="cd-page bg-light">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                <div class="small text-muted">{{ __('Where do I stand?') }} · {{ $asOf->translatedFormat('l, d M Y') }}
                    @unless($isToday)<span class="badge bg-warning text-dark ms-1">{{ __('Viewing another date') }}</span>@endunless
                    {{-- on paper the filters are hidden: say whose numbers these are --}}
                    <span class="d-none d-print-inline"> · {{ $picked ? ($salespersons->firstWhere('id', $picked)->name ?? '') : ($own ? __('All mine') : __('All Salesmen')) }}</span>
                </div>
            </div>
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap d-print-none" id="cdFilters">
                <div style="width:150px"><input type="text" name="date" class="form-control datepicker" value="{{ $asOf->format('d-m-Y') }}"
                                                aria-label="{{ __('Viewing date') }}" data-min-date="01-01-2020" data-max-date="31-12-2099"></div>
                <div style="width:190px"><select name="salesperson_id" class="tom-select" data-live-search="true" aria-label="{{ __('Salesperson') }}">
                    @if(!$own || count($salespersons) > 1)
                        <option value="">{{ $own ? __('All mine') : __('All Salesmen') }}</option>
                    @endif
                    @foreach($salespersons as $sp)
                        <option value="{{ $sp->id }}" @selected($picked === $sp->id)>{{ $sp->name }}</option>
                    @endforeach
                </select></div>
                @unless($isToday)<a href="{{ url('sales/crm-dashboard') . ($picked ? '?salesperson_id=' . $picked : '') }}" class="btn btn-outline-secondary rounded-pill px-3">{{ __('Today') }}</a>@endunless
                @php($exportQuery = http_build_query(array_filter(['date' => $isToday ? null : $asOf->format('d-m-Y'), 'salesperson_id' => $picked])))
                <a href="{{ url('sales/crm-dashboard/export') . ($exportQuery ? '?' . $exportQuery : '') }}"
                   class="btn btn-outline-success rounded-pill px-3"><i class="bi bi-file-excel me-1"></i>{{ __('Excel') }}</a>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="window.print()"><i class="bi bi-printer me-1"></i>{{ __('Print') }}</button>
            </form>
        </div>

        {{-- AT A GLANCE --}}
        <div class="row g-2 mb-3">
            @foreach([
                ['lines', __('Customer lines'), '/sales/tracker', false],
                ['jobs_running', __('Jobs running'), '/operation/jobs', false],
                ['rfqs', __('RFQs received (this month)'), '/sales/enquiries', false],
                ['quotes', __('Quotes sent (this month)'), '/sales/quotations', false],
                ['confirmed', __('Confirmed (this month)'), '/sales/quotations', false],
                ['overdue', __('Overdue: planned, not updated'), '/sales/follow-ups', true],
                ['next7', __('Planned next 7 days'), '/sales/follow-ups', false],
                ['stale', __('No contact :n+ days', ['n' => $staleDays]), '/sales/tracker', true],
            ] as [$k, $label, $href, $warn])
                <div class="col-6 col-md-3 col-xl">
                    <a href="{{ url($href) }}" class="cd-tile {{ $warn && $glance[$k] ? 'warn' : '' }}">
                        <div class="v">{{ $glance[$k] }}</div>
                        <div class="l">{{ $label }}</div>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- ACTIVITY SNAPSHOT + ACTION DETAIL --}}
        <div class="cd-card">
            <h6>{{ __('Activity snapshot — how many of each action') }}</h6>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr>
                        <th>{{ __('Category') }}</th>
                        @foreach($periods as [$label, $from, $to])
                            <th class="text-end" title="{{ $from->format('d-m-Y') }} – {{ $to->format('d-m-Y') }}">{{ $label }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                    @foreach(CrmAction::CATEGORIES as $cat => $c)
                        <tr>
                            <td>
                                <button type="button" class="cd-toggle" aria-expanded="false" data-bs-toggle="collapse" data-bs-target=".cd-a-{{ $cat }}">
                                    <i class="bi bi-chevron-right small me-1"></i><span class="cd-cat" style="{{ CrmAction::badgeStyle($cat) }}">{{ __($c['label']) }}</span>
                                </button>
                            </td>
                            @foreach($periods as $key => $p)
                                @php($n = $snapshot['categories'][$cat][$key] ?? 0)
                                <td class="text-end fw-semibold {{ $n ? '' : 'cd-zero' }}">{{ $n }}</td>
                            @endforeach
                        </tr>
                        @foreach($snapshot['actions'][$cat] ?? [] as $name => $counts)
                            <tr class="collapse cd-action cd-a-{{ $cat }}">
                                <td>{{ $name }}</td>
                                @foreach($periods as $key => $p)
                                    <td class="text-end {{ ($counts[$key] ?? 0) ? '' : 'cd-zero' }}">{{ $counts[$key] ?? 0 }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                    <tr class="fw-bold border-top">
                        <td>{{ __('All entries') }}</td>
                        @foreach($periods as $key => $p)
                            <td class="text-end">{{ $snapshot['total'][$key] }}</td>
                        @endforeach
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="small text-muted px-3 pb-2">{{ __('Click a category to see each action. Planned actions count on their due date.') }}</div>
        </div>

        <div class="row g-3">
            {{-- PIPELINE BY STATUS / RANK / SERVICE --}}
            @foreach([[__('Pipeline by status'), $byStatus, __('Status')], [__('By rank'), $byRank, __('Rank')], [__('By pricing executive (handled by)'), $byHandler, __('Handled By')], [__('By service type'), $byService, __('Service Type')]] as [$title, $rows, $first])
                <div class="col-xl-6">
                    <div class="cd-card">
                        <h6>{{ $title }}</h6>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr>
                                    <th>{{ $first }}</th><th class="text-end">{{ __('Lines') }}</th><th class="text-end">{{ __('RFQs') }}</th><th class="text-end">{{ __('Jobs') }}</th>
                                    <th class="text-end">{{ __('Approx Cost') }}</th><th class="text-end">{{ __('Approx Selling') }}</th><th class="text-end">{{ __('Margin') }}</th><th class="text-end">{{ __('Margin %') }}</th>
                                </tr></thead>
                                <tbody>
                                @forelse($rows as $r)
                                    <tr>
                                        <td>{{ $r['label'] }}</td><td class="text-end">{{ $r['lines'] }}</td><td class="text-end">{{ $r['rfqs'] }}</td><td class="text-end">{{ $r['jobs'] }}</td>
                                        <td class="text-end">{{ $num($r['cost']) }}</td><td class="text-end">{{ $num($r['selling']) }}</td>
                                        <td class="text-end {{ ($r['margin'] ?? 0) < 0 ? 'text-danger' : '' }}">{{ $num($r['margin']) }}</td><td class="text-end">{{ $pct($r['margin_pct']) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted py-3">{{ __('No customer lines yet.') }}</td></tr>
                                @endforelse
                                </tbody>
                                @if(count($rows))
                                    <tfoot><tr class="fw-bold">
                                        <td>{{ __('Total') }}</td><td class="text-end">{{ collect($rows)->sum('lines') }}</td><td class="text-end">{{ collect($rows)->sum('rfqs') }}</td><td class="text-end">{{ collect($rows)->sum('jobs') }}</td>
                                        <td class="text-end">{{ $num(collect($rows)->sum('cost')) }}</td><td class="text-end">{{ $num(collect($rows)->sum('selling')) }}</td>
                                        <td class="text-end">{{ $num(collect($rows)->sum('margin')) }}</td>
                                        <td class="text-end">{{ $pct(\App\Services\Crm\CrmDashboard::marginPct($rows)) }}</td>
                                    </tr></tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- CONVERSION --}}
            <div class="col-xl-6">
                <div class="cd-card">
                    <h6>{{ __('Conversion — RFQ to Quote to Confirmed') }}</h6>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr>
                                <th>{{ __('Period') }}</th><th class="text-end">{{ __('RFQs received') }}</th><th class="text-end">{{ __('Quotes sent') }}</th>
                                <th class="text-end">{{ __('Confirmed') }}</th><th class="text-end">{{ __('RFQ > Confirmed') }}</th><th class="text-end">{{ __('Quote > Confirmed') }}</th>
                            </tr></thead>
                            <tbody>
                            @foreach($conversion as $c)
                                <tr>
                                    <td>{{ $c['label'] }}</td><td class="text-end">{{ $c['rfqs'] }}</td><td class="text-end">{{ $c['quotes'] }}</td><td class="text-end">{{ $c['confirmed'] }}</td>
                                    <td class="text-end">{{ $pct($c['rfq_rate']) }}</td><td class="text-end">{{ $pct($c['quote_rate']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="small text-muted px-3 pb-2">{{ __('From the enquiries and quotations themselves; confirmed counts on the day the quotation was accepted.') }}</div>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof datepicker === 'function') datepicker();
            if (typeof initTomSelectForm === 'function') initTomSelectForm($('#cdFilters'));
            // a new date or salesperson reloads the page
            $('#cdFilters').on('change', 'select, input[name=date]', function () { this.form.submit(); });
            // the arrow turns when a category opens
            document.querySelectorAll('.cd-toggle').forEach(function (b) {
                const rows = document.querySelectorAll(b.dataset.bsTarget);
                rows.forEach(r => {
                    r.addEventListener('shown.bs.collapse', () => b.setAttribute('aria-expanded', 'true'));
                    r.addEventListener('hidden.bs.collapse', () => b.setAttribute('aria-expanded', 'false'));
                });
            });
        });
    </script>
</x-app-layout>
