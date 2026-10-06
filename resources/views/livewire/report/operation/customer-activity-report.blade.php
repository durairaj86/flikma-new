@section('js', 'customer_activity_report')
@section('page-title', __('Customer Activity Report'))
@section('page-subtitle', __('Job activity, revenue, and profitability grouped by customer'))
@section('hide-topbar', true)

<div class="provisional-wrapper min-vh-100 bg-light pb-4">
    <div class="container-fluid px-3">
        <style>
            .rpt-title { display: none; }
            body:not(.has-top-header) .rpt-title { display: block; }
        </style>
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-2 d-print-none">
            <div class="rpt-title">
                <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
                @hasSection('page-subtitle')<div class="text-muted small mt-1">@yield('page-subtitle')</div>@endif
            </div>
            <div class="btn-group shadow-sm ms-auto position-relative">
                                <button class="btn btn-white border border-end-0" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                                </button>
                                <div class="btn-group position-relative" x-data="{ open: false, pos: '', toggle() { this.open = !this.open; if (this.open) { const r = this.$refs.btn.getBoundingClientRect(); this.pos = 'position:fixed;left:auto;bottom:auto;right:' + (window.innerWidth - r.right) + 'px;top:' + (r.bottom + 4) + 'px;'; } } }" @click.outside="open = false" @keydown.escape.window="open = false" @scroll.window="open = false" @resize.window="open = false">
                                <button type="button" class="btn btn-white border dropdown-toggle" x-ref="btn" @click="toggle()" :aria-expanded="open">
                                <i class="bi bi-download me-2"></i>{{ __('Export') }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow" :class="{ show: open }" x-cloak :style="pos" @click="open = false">
                                <li><a class="dropdown-item py-2" href="#" onclick="reportExportPdf(event, 'car-print', {orientation: 'portrait'})"><i class="bi bi-file-pdf text-danger me-2"></i>{{ __('PDF Document') }}</a></li>
                                <li><a class="dropdown-item py-2" href="#" wire:click.prevent="exportExcel"><i class="bi bi-file-excel text-success me-2"></i>{{ __('Excel Sheet') }}</a></li>
                                </ul>
                                </div>
                                </div>
        </div>

        {{-- Page Header --}}
                {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 d-print-none">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-4 col-xl-3" id="car-customer-wrap" wire:ignore>
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('Customer') }}</label>
                        <x-common.customers wire:model="customerId" id="car-customer" name="car-customer"
                                            :value="$customerId ? [(int) $customerId] : null" :new="false"
                                            all-label="{{ __('All Customers') }}"
                                            placeholder="{{ __('All Customers') }}"></x-common.customers>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('From Date') }}</label>
                        <input type="hidden" id="car-start-date-hidden" wire:model="startDate" value="{{ $startDate }}" />
                        <input type="text" id="car-start-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $startDate }}" />
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small fw-bold text-uppercase text-muted ls-1">{{ __('To Date') }}</label>
                        <input type="hidden" id="car-end-date-hidden" wire:model="endDate" value="{{ $endDate }}" />
                        <input type="text" id="car-end-date"
                               class="form-control bg-light border-0 py-2"
                               placeholder="dd-mm-yyyy"
                               value="{{ $endDate }}" />
                    </div>
                    <div class="col-lg-12 col-xl-6 col-xxl-5">
                        <div class="d-flex flex-wrap gap-2 justify-content-end align-items-center">
                            <button type="button" class="btn btn-pr fw-bold py-2 shadow-sm"
                                    wire:click="applyFilter" wire:loading.attr="disabled">
                                <i class="bi bi-filter-left me-2"></i>
                                <span wire:loading.remove>{{ __('Generate') }}</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Loading...') }}</span>
                            </button>
<button type="button" class="btn btn-outline-secondary border-0 bg-light py-2 px-3"
                                    wire:click="resetFilter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        @if($totals['total_customers'] > 0)
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Active Customers') }}</div>
                        <div class="h5 fw-bold text-secondary mb-0 tabular-nums">{{ $totals['total_customers'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Total Jobs') }}</div>
                        <div class="h5 fw-bold text-primary mb-0 tabular-nums">{{ $totals['total_jobs'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Total Revenue') }}</div>
                        <div class="h5 fw-bold text-success mb-0 tabular-nums">{{ number_format($totals['total_revenue'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Total Cost') }}</div>
                        <div class="h5 fw-bold text-danger mb-0 tabular-nums">{{ number_format($totals['total_cost'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 text-center">
                        <div class="small text-muted fw-bold text-uppercase mb-1 ls-1">{{ __('Net Profit') }}</div>
                        <div class="h5 fw-bold mb-0 tabular-nums {{ $totals['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($totals['total_profit'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="card border-0 shadow-sm overflow-hidden d-print-none">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold flex-grow-1">
                    <i class="bi bi-people me-2 text-pr"></i>
                    {{ __('Customer Activity Breakdown') }}
                </h6>
                <span class="badge bg-pr-subtle text-pr border border-pr-subtle px-3 py-2 ms-auto flex-shrink-0">
                    {{ $totals['total_customers'] }} {{ __(Str::plural('Customer', $totals['total_customers'])) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                        <th class="ps-4 border-0">{{ __('Customer') }}</th>
                        <th class="text-center border-0">{{ __('Jobs') }}</th>
                        <th class="text-center border-0">{{ __('Active') }}</th>
                        <th class="text-center border-0">{{ __('Completed') }}</th>
                        <th class="text-end border-0">{{ __('Revenue') }}</th>
                        <th class="text-end border-0">{{ __('Cost') }}</th>
                        <th class="text-end border-0">{{ __('Profit / Loss') }}</th>
                        <th class="text-end pe-4 border-0">{{ __('Margin') }}</th>
                    </tr>
                    </thead>
                    <tbody class="border-top-0">
                    @forelse($rows as $row)
                        <tr wire:key="car-{{ $row['customer']->id }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $row['customer']->name }}</span>
                                <div class="text-muted" style="font-size:0.7rem;">{{ $row['customer']->name_ar }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $row['job_count'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $row['active'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $row['completed'] }}</span>
                            </td>
                            <td class="text-end tabular-nums text-success fw-medium">
                                {{ $row['revenue'] > 0 ? number_format($row['revenue'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums text-danger">
                                {{ $row['cost'] > 0 ? number_format($row['cost'], 2) : '—' }}
                            </td>
                            <td class="text-end tabular-nums fw-bold {{ $row['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($row['profit'], 2) }}
                            </td>
                            <td class="text-end pe-4">
                                <span class="badge rounded-pill px-2 py-1 {{ $row['margin'] >= 20 ? 'bg-success-subtle text-success' : ($row['margin'] >= 10 ? 'bg-warning-subtle text-warning' : ($row['margin'] > 0 ? 'bg-secondary-subtle text-secondary' : 'bg-danger-subtle text-danger')) }}">
                                    {{ number_format($row['margin'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                    <i class="bi bi-people h2 text-muted"></i>
                                </div>
                                <div class="small">{{ __('No customer activity found for the selected period.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if(count($rows) > 0)
                    <tfoot class="bg-light border-top-2">
                    <tr class="fw-bold">
                        <td class="ps-4 py-3">{{ $totals['total_customers'] }} {{ __('Customers') }}</td>
                        <td class="text-center text-muted">{{ $totals['total_jobs'] }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-end tabular-nums text-success">{{ number_format($totals['total_revenue'], 2) }}</td>
                        <td class="text-end tabular-nums text-danger">{{ number_format($totals['total_cost'], 2) }}</td>
                        <td class="text-end tabular-nums {{ $totals['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totals['total_profit'], 2) }}</td>
                        <td class="text-end pe-4"></td>
                    </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Bank-statement style layout: used for Print and PDF export only --}}
        <div id="car-print" class="stmt-print d-none d-print-block"
             data-pdf-filename="CustomerActivityReport-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

            <table class="stmt-meta">
                <tr>
                    <td>
                        <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                    </td>
                    <td class="text-end">
                        <div class="stmt-title">{{ __('CUSTOMER ACTIVITY REPORT') }}</div>
                        <div class="stmt-sub">{{ __('Period:') }} {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                        <div class="stmt-sub">{{ __('Generated:') }} {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; {{ __('Currency:') }} SAR</div>
                    </td>
                </tr>
            </table>

            <table class="stmt-table">
                <thead>
                <tr>
                    <th>{{ __('Customer') }}</th>
                    <th class="text-end">{{ __('Jobs') }}</th>
                    <th class="text-end">{{ __('Active') }}</th>
                    <th class="text-end">{{ __('Completed') }}</th>
                    <th class="text-end">{{ __('Draft') }}</th>
                    <th class="text-end">{{ __('Revenue') }}</th>
                    <th class="text-end">{{ __('Cost') }}</th>
                    <th class="text-end">{{ __('Profit / Loss') }}</th>
                    <th class="text-end">{{ __('Margin') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['customer']->name_en }} ({{ $row['customer']->row_no }})</td>
                        <td class="text-end">{{ $row['job_count'] }}</td>
                        <td class="text-end">{{ $row['active'] }}</td>
                        <td class="text-end">{{ $row['completed'] }}</td>
                        <td class="text-end">{{ $row['draft'] }}</td>
                        <td class="text-end">{{ number_format($row['revenue'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['cost'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['profit'], 2) }}</td>
                        <td class="text-end">{{ number_format($row['margin'], 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">{{ __('No customer activity found for the selected period.') }}</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="stmt-strong">
                    <td>{{ $totals['total_customers'] }} {{ __('Customers') }}</td>
                    <td class="text-end">{{ $totals['total_jobs'] }}</td>
                    <td></td><td></td><td></td>
                    <td class="text-end">{{ number_format($totals['total_revenue'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['total_cost'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['total_profit'], 2) }}</td>
                    <td class="text-end"></td>
                </tr>
                </tfoot>
            </table>

            <div class="stmt-signatures">
                <table class="stmt-meta">
                    <tr>
                        <td>{{ __('Prepared By:') }} _________________</td>
                        <td>{{ __('Verified By:') }} _________________</td>
                        <td>{{ __('Approved By:') }} _________________</td>
                    </tr>
                </table>
            </div>
        </div>

        @include('includes.report-print-css', ['orientation' => 'portrait'])

    </div>

    @script
    <script>
        (function () {
            function syncHidden(hiddenId, dateStr) {
                var hidden = document.getElementById(hiddenId);
                if (hidden) {
                    hidden.value = dateStr;
                    hidden.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            function initFlatpickr() {
                var startEl = document.getElementById('car-start-date');
                var endEl   = document.getElementById('car-end-date');

                if (startEl && startEl._flatpickr) { startEl._flatpickr.destroy(); }
                if (endEl   && endEl._flatpickr)   { endEl._flatpickr.destroy(); }

                if (startEl) {
                    flatpickr(startEl, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   startEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('car-start-date-hidden', dateStr);
                        },
                    });
                }

                if (endEl) {
                    flatpickr(endEl, {
                        dateFormat:    'Y-m-d',
                        altInput:      true,
                        altFormat:     'd-m-Y',
                        allowInput:    true,
                        disableMobile: true,
                        defaultDate:   endEl.value || null,
                        onChange: function (selectedDates, dateStr) {
                            syncHidden('car-end-date-hidden', dateStr);
                        },
                    });
                }
            }

            initFlatpickr();

            // Customer picker: shared tom-select component, wire:ignore'd so Livewire commits can't wipe it.
            var carCust = document.getElementById('car-customer');
            if (carCust && !carCust.tomselect) { initTomSelectForm($('#car-customer-wrap')); }
            $wire.on('car-filter-reset', function () {
                if (carCust && carCust.tomselect) { carCust.tomselect.clear(true); }
                var s = document.getElementById('car-start-date'), e = document.getElementById('car-end-date');
                if (s && s._flatpickr) s._flatpickr.setDate($wire.get('startDate'), false);
                if (e && e._flatpickr) e._flatpickr.setDate($wire.get('endDate'), false);
            });

            // ref.succeed()'s callback fires before the DOM morph for this
            // commit is actually applied — re-running initFlatpickr() there
            // re-initializes the still-old (correctly formatted) input,
            // which the morph then immediately overwrites back to the
            // server-rendered raw value, visibly flipping the date field's
            // format after every Generate click. requestAnimationFrame
            // defers until the next frame, after that synchronous morph
            // pass has actually finished (see general-ledger.blade.php /
            // customer-statement.blade.php for the same root cause).
            Livewire.hook('commit', function (ref) {
                ref.succeed(function () {
                    requestAnimationFrame(initFlatpickr);
                });
            });
        })();
    </script>
    @endscript

    <style>
        :root {
            --car-primary: #0ea5e9;
            --car-dark:    #0369a1;
            --car-light:   #f0f9ff;
        }

        .btn-pr { background-color: var(--car-primary); border-color: var(--car-primary); color: #fff; }
        .btn-pr:hover { background-color: var(--car-dark); border-color: var(--car-dark); color: #fff; }
        .text-pr { color: var(--car-primary) !important; }
        .bg-pr-subtle { background-color: #e0f2fe !important; }
        .border-pr-subtle { border-color: #bae6fd !important; }

        .btn-outline-pr { color: var(--car-primary); border-color: var(--car-primary); }
        .btn-outline-pr:hover, .btn-check:checked + .btn-outline-pr {
            background-color: var(--car-primary); border-color: var(--car-primary); color: #fff;
        }

        .bg-primary-subtle { background-color: #e0f2fe !important; }
        .bg-success-subtle { background-color: #dcfce7 !important; }
        .bg-warning-subtle { background-color: #fef3c7 !important; }
        .bg-secondary-subtle { background-color: #f1f5f9 !important; }
        .bg-danger-subtle { background-color: #fee2e2 !important; }

        .ls-1 { letter-spacing: 0.05em; }
        .x-small { font-size: 0.7rem; }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        .card { border-radius: 1rem; }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.1);
            border-color: var(--car-primary);
        }

        thead th { vertical-align: bottom; }

        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</div>
